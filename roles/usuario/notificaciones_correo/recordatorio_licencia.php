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

echo "<h2>Resultado del Envío de Recordatorios Licencia</h2><hr>";
registrarLog("== INICIO de ejecución del cron para LICENCIA ==");

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

    $query = "SELECT l.*, u.email, u.nombre_completo, c.nombre_categoria, s.nombre_servicios
              FROM licencias l 
              INNER JOIN usuarios u ON l.id_documento = u.documento 
              INNER JOIN categoria_licencia c ON l.id_categoria = c.id_categoria
              INNER JOIN servicios_licencias s ON l.id_servicio = s.id_servicio
              WHERE l.fecha_vencimiento >= CURDATE()";  // Solo licencias vigentes

    $stmt = $con->prepare($query);
    $stmt->execute();
    $licencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($licencias as $licencia) {
        try {
            $fechaVencimiento = new DateTime($licencia['fecha_vencimiento']);
            $diasRestantes = (int)$hoy->diff($fechaVencimiento)->format('%r%a');

            echo "<strong>Usuario:</strong> {$licencia['nombre_completo']} ({$licencia['email']})<br>";
            echo "<strong>Fecha de vencimiento:</strong> {$licencia['fecha_vencimiento']}<br>";
            echo $licencia['id_documento'] ? "<strong>Documento:</strong> {$licencia['id_documento']}<br>" : '';
            echo "<strong>Días restantes:</strong> $diasRestantes<br>";
            

            $mail->clearAddresses();
            $mail->addAddress($licencia['email']);

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
            $verifica = $con->prepare("SELECT COUNT(*) FROM correos_enviados_licencia WHERE id_licencia = :id_licencia AND email = :email AND tipo_recordatorio = :tipo");
            $verifica->execute([
                'id_licencia' => $licencia['id_licencia'],
                'email' => $licencia['email'],
                'tipo' => $tipo_recordatorio
            ]);
            if ($verifica->fetchColumn() > 0) {
                registrarLog("Ya se envió el recordatorio de tipo $tipo_recordatorio a {$licencia['email']}");
                echo "<span style='color: orange;'>Ya se envió este tipo de recordatorio antes</span><hr>";
                continue;
            }

            // Preparar correo
            if ($tipo_recordatorio == '3_dia') {
                $mail->Subject = '¡URGENTE! Tu Licencia vence en 3 días';
                $mensaje = generarMensaje($licencia, '3 días');
            } else if ($tipo_recordatorio == 'vencido') {
                $mail->Subject = '¡ATENCIÓN! Tu Licencia ha vencido hoy';
                $mensaje = generarMensaje($licencia, 'hoy');
            }

            enviarNotificacion($mail, $mensaje);

            $registra = $con->prepare("INSERT INTO correos_enviados_licencia (id_licencia, email, tipo_recordatorio) VALUES (:id_licencia, :email, :tipo)");
            $registra->execute([
                'id_licencia' => $licencia['id_licencia'],
                'email' => $licencia['email'],
                'tipo' => $tipo_recordatorio
            ]);

            registrarLog("Correo enviado a {$licencia['email']} ($tipo_recordatorio)");
            echo "<span style='color: green;'>Correo enviado ($tipo_recordatorio)</span><hr>";

            $mensaje_notif = '';
            if ($tipo_recordatorio == '3_dia') {
                $mensaje_notif = "Tu licencia ({$licencia['nombre_categoria']}) vence en 3 días ({$licencia['fecha_vencimiento']}).";
            } else if ($tipo_recordatorio == 'vencido') {
                $mensaje_notif = "¡Tu licencia ({$licencia['nombre_categoria']}) ha vencido hoy ({$licencia['fecha_vencimiento']})!";
            }

            if ($mensaje_notif) {
                $insertNotif = $con->prepare("INSERT INTO notificaciones (documento_usuario, mensaje, tipo, fecha, leido) VALUES (?, ?, ?, NOW(), 0)");
                $insertNotif->execute([
                    $licencia['id_documento'],
                    $mensaje_notif,
                    'licencia'
                ]);
            }

        } catch (Exception $e) {
            registrarLog("ERROR al enviar a {$licencia['email']}: " . $mail->ErrorInfo);
            echo "<span style='color: red;'>Error al enviar: {$mail->ErrorInfo}</span><hr>";
            continue;
        }
    }

} catch (Exception $e) {
    registrarLog("ERROR GLOBAL: " . $e->getMessage());
    echo "<span style='color: red;'>ERROR GLOBAL: {$e->getMessage()}</span><br>";
}

registrarLog("== FIN de ejecución del cron para LICENCIA ==");

// === FUNCIONES ===

function generarMensaje($licencia, $tiempo) {
    return "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Recordatorio de Vencimiento de Licencia</h2>
        <p>Estimado/a {$licencia['nombre_completo']},</p>
        <p>Le informamos que su licencia de conducción categoría <strong>{$licencia['nombre_categoria']}</strong> 
        para servicio {$licencia['nombre_servicios']} vence en {$tiempo}.</p>
        <p>Detalles de la Licencia:</p>
        <ul>
            <li>Fecha de vencimiento: {$licencia['fecha_vencimiento']}</li>
            <li>Categoría: {$licencia['nombre_categoria']}</li>
            <li>Tipo de servicio: {$licencia['nombre_servicios']}</li>
            <li>Restricciones: {$licencia['restricciones']}</li>
        </ul>
        <p>Por favor, renueve su licencia a tiempo para evitar inconvenientes.</p>
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