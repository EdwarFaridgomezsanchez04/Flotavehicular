<?php
date_default_timezone_set('America/Bogota');
// filepath: c:\xampp\htdocs\Flotavehicular\roles\usuario\notificaciones_correo\recordatorio_tecno.php
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

echo "<h2>Resultado del Envío de Recordatorios Tecnomecánica</h2><hr>";
registrarLog("== INICIO de ejecución del cron para TECNO ==");

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

    $query = "SELECT t.*, v.placa, u.email, u.documento, u.nombre_completo, c.centro_revision as nombre_aseguradora 
              FROM tecnomecanica t 
              INNER JOIN vehiculos v ON t.id_placa = v.placa 
              INNER JOIN usuarios u ON v.Documento = u.documento 
              INNER JOIN centro_rtm c ON t.id_centro_revision = c.id_centro
              WHERE t.id_estado = 1";

    $stmt = $con->prepare($query);
    $stmt->execute();
    $tecnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tecnos as $tecno) {
        try {
            $fechaVencimiento = new DateTime($tecno['fecha_vencimiento']);
            $diasRestantes = (int)$hoy->diff($fechaVencimiento)->format('%r%a');

            echo "<strong>Placa:</strong> {$tecno['placa']}<br>";
            echo "<strong>Usuario:</strong> {$tecno['nombre_completo']} ({$tecno['email']})<br>";
            echo "<strong>Fecha de vencimiento:</strong> {$tecno['fecha_vencimiento']}<br>";
            echo $tecno['documento'] ? "<strong>Documento:</strong> {$tecno['documento']}<br>" : '';
            echo "<strong>Días restantes:</strong> $diasRestantes<br>";

            $mail->clearAddresses();
            $mail->addAddress($tecno['email']);

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
            $verifica = $con->prepare("SELECT COUNT(*) FROM correos_enviados_tecno WHERE id_rtm = :id_rtm AND email = :email AND tipo_recordatorio = :tipo");
            $verifica->execute([
                'id_rtm' => $tecno['id_rtm'],
                'email' => $tecno['email'],
                'tipo' => $tipo_recordatorio
            ]);
            if ($verifica->fetchColumn() > 0) {
                registrarLog("Ya se envió el recordatorio de tipo $tipo_recordatorio a {$tecno['email']}");
                echo "<span style='color: orange;'>Ya se envió este tipo de recordatorio antes</span><hr>";
                continue;
            }

            // Preparar correo
            if ($tipo_recordatorio == '3_dia') {
                $mail->Subject = '¡URGENTE! Tu Tecnomecánica vence en 3 dias';
                $mensaje = generarMensaje($tecno, '1 día');
            } else if ($tipo_recordatorio == 'vencido') {
                $mail->Subject = '¡ATENCIÓN! Tu Tecnomecánica ha vencido hoy';
                $mensaje = generarMensaje($tecno, 'hoy');
                // Cambiar estado en base de datos
                $updateQuery = "UPDATE tecnomecanica SET id_estado = 2 WHERE id_rtm = :id_rtm";
                $updateStmt = $con->prepare($updateQuery);
                $updateStmt->execute(['id_rtm' => $tecno['id_rtm']]);
            }

            enviarNotificacion($mail, $mensaje);

            $registra = $con->prepare("INSERT INTO correos_enviados_tecno (id_rtm, email, tipo_recordatorio) VALUES (:id_rtm, :email, :tipo)");
            $registra->execute([
                'id_rtm' => $tecno['id_rtm'],
                'email' => $tecno['email'],
                'tipo' => $tipo_recordatorio
            ]);

            registrarLog("Correo enviado a {$tecno['email']} ($tipo_recordatorio)");
            echo "<span style='color: green;'>Correo enviado ($tipo_recordatorio)</span><hr>";

            $mensaje_notif = '';
            if ($tipo_recordatorio == '3_dia') {
                $mensaje_notif = "La tecnomecánica de tu vehículo con placa {$tecno['placa']} vence en 3 días ({$tecno['fecha_vencimiento']}).";
            } else if ($tipo_recordatorio == 'vencido') {
                $mensaje_notif = "¡La tecnomecánica de tu vehículo con placa {$tecno['placa']} ha vencido hoy ({$tecno['fecha_vencimiento']})!";
            }

            if ($mensaje_notif) {
                $insertNotif = $con->prepare("INSERT INTO notificaciones (documento_usuario, mensaje, tipo, fecha, leido) VALUES (?, ?, ?, NOW(), 0)");
                $insertNotif->execute([
                    $tecno['documento'],
                    $mensaje_notif,
                    'tecnomecanica'
                ]);
            }

        } catch (Exception $e) {
            registrarLog("ERROR al enviar a {$tecno['email']}: " . $mail->ErrorInfo);
            echo "<span style='color: red;'>Error al enviar: {$mail->ErrorInfo}</span><hr>";
            continue;
        }
    }

} catch (Exception $e) {
    registrarLog("ERROR GLOBAL: " . $e->getMessage());
    echo "<span style='color: red;'>ERROR GLOBAL: {$e->getMessage()}</span><br>";
}

registrarLog("== FIN de ejecución del cron para TECNO ==");

// === FUNCIONES ===

function generarMensaje($tecno, $tiempo) {
    return "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Recordatorio de Vencimiento de Tecnomecánica</h2>
        <p>Estimado/a {$tecno['nombre_completo']},</p>
        <p>Le informamos que la revisión tecnomecánica de su vehículo con placa <strong>{$tecno['placa']}</strong> 
        realizada en {$tecno['centro_revision']} vence en {$tiempo}.</p>
        <p>Detalles de la Tecnomecánica:</p>
        <ul>
            <li>Fecha de vencimiento: {$tecno['fecha_vencimiento']}</li>
            <li>Placa del vehículo: {$tecno['placa']}</li>
            <li>Centro de revisión: {$tecno['centro_revision']}</li>
        </ul>
        <p>Por favor, programe su revisión tecnomecánica a tiempo para evitar inconvenientes.</p>
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