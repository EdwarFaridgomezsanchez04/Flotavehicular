<?php
// filepath: c:\xampp\htdocs\Flotavehicular\roles\usuario\notificaciones_correo\recordatorio_mantenimiento_aceite.php
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

echo "<h2>Resultado del Envío de Recordatorios de Mantenimiento de Aceite</h2><hr>";
registrarLog("== INICIO de ejecución del cron para MANTENIMIENTO ACEITE ==");

try {
    $database = new Database();
    $con = $database->conectar();

    $hoy = new DateTime();

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

    // Consulta: mantenimientos de aceite próximos a cambio
    $query = "SELECT m.*, v.placa, u.email, u.documento, u.nombre_completo, tm.descripcion AS tipo_mantenimiento

              FROM mantenimiento m
              JOIN vehiculos v ON m.placa = v.placa
              JOIN usuarios u ON v.Documento = u.documento
              JOIN tipo_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
              WHERE m.proximo_cambio_fecha IS NOT NULL";

    $stmt = $con->prepare($query);
    $stmt->execute();
    $mantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mantenimientos as $mantenimiento) {
        try {
            // Fechas exactas
            $fechaHoy = new DateTime(date('Y-m-d'));
            $fechaCambio = new DateTime(date('Y-m-d', strtotime($mantenimiento['proximo_cambio_fecha'])));
            $interval = $fechaHoy->diff($fechaCambio);
            $diasRestantes = (int)$interval->format('%r%a');

            echo "<strong>Placa:</strong> {$mantenimiento['placa']}<br>";
            echo "<strong>Usuario:</strong> {$mantenimiento['nombre_completo']} ({$mantenimiento['email']})<br>";
            echo "<strong>Fecha próximo cambio:</strong> {$mantenimiento['proximo_cambio_fecha']}<br>";
            echo $mantenimiento['documento'] ? "<strong>documento:</strong> {$mantenimiento['documento']}<br>" : '';

            echo "<strong>Días exactos restantes:</strong> $diasRestantes<br>";

            $mail->clearAddresses();
            $mail->addAddress($mantenimiento['email']);

            // ¿Debe enviarse el recordatorio?
            if ($diasRestantes === 3) {
                $tipo_recordatorio = '3_dias';
            } else if ($diasRestantes === 1) {
                $tipo_recordatorio = '1_dia';
            } else if ($diasRestantes === 0) {
                $tipo_recordatorio = 'hoy';
            } else {
                echo "<span style='color: gray;'>No se cumple condición de envío</span><hr>";
                continue;
            }

            // ¿Ya se envió antes?
            $verifica = $con->prepare("SELECT COUNT(*) FROM correos_enviados_mantenimiento WHERE id_mantenimiento = :id_mantenimiento AND email = :email AND tipo_recordatorio = :tipo_recordatorio");
            $verifica->execute([
                'id_mantenimiento' => $mantenimiento['id_mantenimiento'],
                'email' => $mantenimiento['email'],
                'tipo_recordatorio' => $tipo_recordatorio
            ]);
            if ($verifica->fetchColumn() > 0) {
                registrarLog("Ya se envió el recordatorio de tipo $tipo_recordatorio a {$mantenimiento['email']} para mantenimiento {$mantenimiento['id_mantenimiento']}");
                echo "<span style='color: orange;'>Ya se envió este tipo de recordatorio antes</span><hr>";
                continue;
            }

            // Preparar correo
            if ($tipo_recordatorio == '3_dias') {
                $mail->Subject = 'Recordatorio: Cambio de aceite en 3 días';
                $mensaje = generarMensajeMantenimientoAceite($mantenimiento, '3 días');
            } else if ($tipo_recordatorio == '1_dia') {
                $mail->Subject = '¡Atención! Cambio de aceite mañana';
                $mensaje = generarMensajeMantenimientoAceite($mantenimiento, '1 día');
            } else if ($tipo_recordatorio == 'hoy') {
                $mail->Subject = '¡URGENTE! Hoy es el día de cambio de aceite';
                $mensaje = generarMensajeMantenimientoAceite($mantenimiento, 'hoy');
            }

            enviarNotificacion($mail, $mensaje);

            $registra = $con->prepare("INSERT INTO correos_enviados_mantenimiento (id_mantenimiento, email, tipo_recordatorio) VALUES (:id_mantenimiento, :email, :tipo_recordatorio)");
            $registra->execute([
                'id_mantenimiento' => $mantenimiento['id_mantenimiento'],
                'email' => $mantenimiento['email'],
                'tipo_recordatorio' => $tipo_recordatorio
            ]);

            registrarLog("Correo enviado a {$mantenimiento['email']} ($tipo_recordatorio)");
            echo "<span style='color: green;'>Correo enviado ($tipo_recordatorio)</span><hr>";

            // Registro en tabla de notificaciones
            $mensaje_notif = '';
            if ($tipo_recordatorio == '3_dias') {
                $mensaje_notif = "El mantenimiento {$mantenimiento['tipo_mantenimiento']} para tu vehículo con placa {$mantenimiento['placa']} es en 3 días ({$mantenimiento['proximo_cambio_fecha']}).";
            } else if ($tipo_recordatorio == '1_dia') {
                $mensaje_notif = "El mantenimiento de {$mantenimiento['tipo_mantenimiento']} para tu vehículo con placa {$mantenimiento['placa']} es mañana ({$mantenimiento['proximo_cambio_fecha']}).";
            } else if ($tipo_recordatorio == 'hoy') {
                $mensaje_notif = "¡Hoy debes realizar el mantenimiento {$mantenimiento['tipo_mantenimiento']} para tu vehículo con placa {$mantenimiento['placa']}!";
            }

            if ($mensaje_notif) {
                $insertNotif = $con->prepare("INSERT INTO notificaciones (documento_usuario, mensaje, tipo, fecha, leido) VALUES (?, ?, ?, NOW(), 0)");
                $insertNotif->execute([
                    $mantenimiento['documento'], // o el campo correcto
                    $mensaje_notif,
                    'mantenimiento'
                ]);
            }


        } catch (Exception $e) {
            registrarLog("ERROR al enviar a {$mantenimiento['email']}: " . $mail->ErrorInfo);
            echo "<span style='color: red;'>Error al enviar: {$mail->ErrorInfo}</span><hr>";
            continue;
        }
    }

} catch (Exception $e) {
    registrarLog("ERROR GLOBAL: " . $e->getMessage());
    echo "<span style='color: red;'>ERROR GLOBAL: {$e->getMessage()}</span><br>";
}

registrarLog("== FIN de ejecución del cron para MANTENIMIENTO ACEITE ==");

// === FUNCIONES ===

function generarMensajeMantenimientoAceite($mantenimiento, $tiempo) {
    return "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Recordatorio de mantenimiento {$mantenimiento['tipo_mantenimiento']}</h2>

        <p>Estimado/a {$mantenimiento['nombre_completo']},</p>
        <p>Le recordamos que el próximo mantenimiento <strong>{$mantenimiento['tipo_mantenimiento']}</strong> para su vehículo con placa <strong>{$mantenimiento['placa']}</strong> está programado para <strong>{$mantenimiento['proximo_cambio_fecha']}</strong> ({$tiempo}).</p>
        <p>Detalles del mantenimiento:</p>
        <ul>
            <li>Tipo de mantenimiento: {$mantenimiento['tipo_mantenimiento']}</li>
            <li>Fecha programada: {$mantenimiento['fecha_programada']}</li>
            <li>Fecha realizada: {$mantenimiento['fecha_realizada']}</li>
            <li>Kilometraje actual: {$mantenimiento['kilometraje_actual']}</li>
            <li>Próximo cambio (km): {$mantenimiento['proximo_cambio_km']}</li>
            <li>Observaciones: {$mantenimiento['observaciones']}</li>
        </ul>
        <p>Por favor, realice el mantenimiento <strong>{$mantenimiento['tipo_mantenimiento']}</strong> a tiempo para el buen funcionamiento de su vehículo.</p>
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