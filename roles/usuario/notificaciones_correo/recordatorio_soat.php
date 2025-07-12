<?php
date_default_timezone_set('America/Bogota');
require_once '../../../conecct/conex.php';
require '../../../src/Exception.php';
require '../../../src/PHPMailer.php';
require '../../../src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==== CONFIGURAR LOGS ==== //
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/log_general.txt';

function registrarLog($mensaje) {
    global $logFile;
    $fecha = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$fecha] $mensaje\n", FILE_APPEND);
}

echo "<h2>Resultado del Envío de Recordatorios SOAT</h2><hr>";
registrarLog("== INICIO de ejecución del cron para SOAT ==");

try {
    $database = new Database();
    $con = $database->conectar();

    $hoy = new DateTime(date('Y-m-d'));

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'flotavehicularagc@gmail.com';
    $mail->Password = 'brgl znfz eqfk mcct';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->setFrom('flotavehicularagc@gmail.com', 'Sistema de Recordatorios');

    $query = "SELECT s.*, v.placa, u.email, u.documento, u.nombre_completo, a.nombre as nombre_aseguradora 
              FROM soat s 
              INNER JOIN vehiculos v ON s.id_placa = v.placa 
              INNER JOIN usuarios u ON v.Documento = u.documento 
              INNER JOIN aseguradoras_soat a ON s.id_aseguradora = a.id_asegura
              WHERE s.id_estado = 1";

    $stmt = $con->prepare($query);
    $stmt->execute();
    $soats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($soats as $soat) {
        try {
            $fechaVencimiento = new DateTime($soat['fecha_vencimiento']);
            $diasRestantes = (int)$hoy->diff($fechaVencimiento)->format('%r%a');

            echo "<strong>Placa:</strong> {$soat['placa']}<br>";
            echo "<strong>Usuario:</strong> {$soat['nombre_completo']} ({$soat['email']})<br>";
            echo "<strong>Fecha de vencimiento:</strong> {$soat['fecha_vencimiento']}<br>";
            echo $soat['documento'] ? "<strong>Documento:</strong> {$soat['documento']}<br>" : '';
            echo "<strong>Días restantes:</strong> $diasRestantes<br>";

            $mail->clearAddresses();
            $mail->addAddress($soat['email']);

            // ¿Debe enviarse el recordatorio?
            
            if ($diasRestantes == 3) {
                $tipo_recordatorio = '3_dia';
            } else if ($diasRestantes == 0) {
                $tipo_recordatorio = 'vencido';
            } else {
                echo "<span style='color: gray;'>No se cumple condición de envío</span><hr>";
                continue;
            }

            // ¿Ya se envió antes?
            $verifica = $con->prepare("SELECT COUNT(*) FROM correos_enviados_soat WHERE id_soat = :id_soat AND email = :email AND tipo_recordatorio = :tipo");
            $verifica->execute([
                'id_soat' => $soat['id_soat'],
                'email' => $soat['email'],
                'tipo' => $tipo_recordatorio
            ]);
            if ($verifica->fetchColumn() > 0) {
                registrarLog("Ya se envió el recordatorio de tipo $tipo_recordatorio a {$soat['email']}");
                echo "<span style='color: orange;'>Ya se envió este tipo de recordatorio antes</span><hr>";
                continue;
            }

            // Preparar correo
            if ($tipo_recordatorio == '3_dia') {
                $mail->Subject = '¡URGENTE! Tu SOAT vence en 3 dias';
                $mensaje = generarMensaje($soat, '3 día');
            } else if ($tipo_recordatorio == 'vencido') {
                $mail->Subject = '¡ATENCIÓN! Tu SOAT ha vencido hoy';
                $mensaje = generarMensaje($soat, 'hoy');
                // Cambiar estado en base de datos
                $updateQuery = "UPDATE soat SET id_estado = 2 WHERE id_soat = :id_soat";
                $updateStmt = $con->prepare($updateQuery);
                $updateStmt->execute(['id_soat' => $soat['id_soat']]);
            }

            enviarNotificacion($mail, $mensaje);

            $registra = $con->prepare("INSERT INTO correos_enviados_soat (id_soat, email, tipo_recordatorio) VALUES (:id_soat, :email, :tipo)");
            $registra->execute([
                'id_soat' => $soat['id_soat'],
                'email' => $soat['email'],
                'tipo' => $tipo_recordatorio
            ]);

            registrarLog("Correo enviado a {$soat['email']} ($tipo_recordatorio)");
            echo "<span style='color: green;'>Correo enviado ($tipo_recordatorio)</span><hr>";

            // Mensaje para la interfaz
            $mensaje_notif = '';
            if ($tipo_recordatorio == '3_dia') {
                $mensaje_notif = "Tu SOAT para el vehículo con placa {$soat['placa']} vence en 3 días ({$soat['fecha_vencimiento']}).";
            } else if ($tipo_recordatorio == 'vencido') {
                $mensaje_notif = "¡Tu SOAT para el vehículo con placa {$soat['placa']} ha vencido hoy ({$soat['fecha_vencimiento']})!";
            }

            // Guardar notificación en la tabla
            if ($mensaje_notif) {
                $insertNotif = $con->prepare("INSERT INTO notificaciones (documento_usuario, mensaje, tipo, fecha, leido) VALUES (?, ?, ?, NOW(), 0)");
                $insertNotif->execute([
                    $soat['documento'],
                    $mensaje_notif,
                    'soat'
                ]);
            }

        } catch (Exception $e) {
            registrarLog("ERROR al enviar a {$soat['email']}: " . $mail->ErrorInfo);
            echo "<span style='color: red;'>Error al enviar: {$mail->ErrorInfo}</span><hr>";
            continue;
        }
    }

} catch (Exception $e) {
    registrarLog("ERROR GLOBAL: " . $e->getMessage());
    echo "<span style='color: red;'>ERROR GLOBAL: {$e->getMessage()}</span><br>";
}

registrarLog("== FIN de ejecución del cron para SOAT ==");

// === FUNCIONES ===

function generarMensaje($soat, $tiempo) {
    return "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Recordatorio de Vencimiento de SOAT</h2>
        <p>Estimado/a {$soat['nombre_completo']},</p>
        <p>Le informamos que el SOAT de su vehículo con placa <strong>{$soat['placa']}</strong> 
        de la aseguradora {$soat['nombre_aseguradora']} vence en {$tiempo}.</p>
        <p>Detalles del SOAT:</p>
        <ul>
            <li>Fecha de vencimiento: {$soat['fecha_vencimiento']}</li>
            <li>Placa del vehículo: {$soat['placa']}</li>
            <li>Aseguradora: {$soat['nombre_aseguradora']}</li>
        </ul>
        <p>Por favor, renueve su SOAT a tiempo para evitar inconvenientes.</p>
        <p>Atentamente,<br>Sistema de Recordatorios</p>
    </body>
    </html>";
}

function enviarNotificacion($mail, $mensaje) {
    $mail->isHTML(true);
    $mail->Body = $mensaje;
    $mail->send();
}
?>
