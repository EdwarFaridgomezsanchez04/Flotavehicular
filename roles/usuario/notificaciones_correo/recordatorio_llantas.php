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

echo "<h2>Resultado del Envío de Recordatorios de Cambio de Llantas</h2><hr>";
registrarLog("== INICIO de ejecución del cron para LLANTAS ==");

try {
    $database = new Database();
    $con = $database->conectar();

    $hoy = new DateTime((date('Y-m-d')));


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

    // Consulta: llantas activas y próximas a cambio
    $query = "SELECT l.*, v.placa, u.email, u.nombre_completo, u.documento

              FROM llantas l
              INNER JOIN vehiculos v ON l.placa = v.placa
              INNER JOIN usuarios u ON v.Documento = u.documento
              WHERE l.proximo_cambio_fecha IS NOT NULL";

    $stmt = $con->prepare($query);
    $stmt->execute();
    $llantas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($llantas as $llanta) {
        try {
            // Asegura que ambas fechas sean solo 'Y-m-d'
            $fechaHoy = new DateTime(date('Y-m-d'));
            $fechaCambio = new DateTime(substr($llanta['proximo_cambio_fecha'], 0, 10)); // solo la fecha
            $diasRestantes = (int)$fechaHoy->diff($fechaCambio)->format('%r%a');


            echo "<strong>Placa:</strong> {$llanta['placa']}<br>";
            echo "<strong>Usuario:</strong> {$llanta['nombre_completo']} ({$llanta['email']})<br>";
            echo "<strong>Fecha próximo cambio:</strong> {$llanta['proximo_cambio_fecha']}<br>";
            echo $llanta['documento'] ? "<strong>Documento:</strong> {$llanta['documento']}<br>" : '';

            echo "<strong>Días exactos restantes:</strong> $diasRestantes<br>";

            $mail->clearAddresses();
            $mail->addAddress($llanta['email']);

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
            $verifica = $con->prepare("SELECT COUNT(*) FROM correos_enviados_llantas WHERE id_llantas = :id_llantas AND email = :email AND tipo_recordatorio = :tipo");
            $verifica->execute([
                'id_llantas' => $llanta['id_llanta'],
                'email' => $llanta['email'],
                'tipo' => $tipo_recordatorio
            ]);
            if ($verifica->fetchColumn() > 0) {
                registrarLog("Ya se envió el recordatorio de tipo $tipo_recordatorio a {$llanta['email']} para llanta {$llanta['id_llanta']}");
                echo "<span style='color: orange;'>Ya se envió este tipo de recordatorio antes</span><hr>";
                continue;
            }

            // Preparar correo
            if ($tipo_recordatorio == '3_dias') {
                $mail->Subject = 'Recordatorio: Cambio de llantas en 3 días';
                $mensaje = generarMensajeLlantas($llanta, '3 días');
            } else if ($tipo_recordatorio == '1_dia') {
                $mail->Subject = '¡Atención! Cambio de llantas mañana';
                $mensaje = generarMensajeLlantas($llanta, '1 día');
            } else if ($tipo_recordatorio == 'hoy') {
                $mail->Subject = '¡URGENTE! Hoy es el día de cambio de llantas';
                $mensaje = generarMensajeLlantas($llanta, 'hoy');
            }

            enviarNotificacion($mail, $mensaje);

            $registra = $con->prepare("INSERT INTO correos_enviados_llantas (id_llantas, email, tipo_recordatorio) VALUES (:id_llantas, :email, :tipo_recordatorio)");
            $registra->execute([
                'id_llantas' => $llanta['id_llanta'],
                'email' => $llanta['email'],
                'tipo_recordatorio' => $tipo_recordatorio
            ]);

            registrarLog("Correo enviado a {$llanta['email']} ($tipo_recordatorio)");
            echo "<span style='color: green;'>Correo enviado ($tipo_recordatorio)</span><hr>";

            // Registro en tabla de notificaciones
            $mensaje_notif = '';
            if ($tipo_recordatorio == '3_dias') {
                $mensaje_notif = "El próximo cambio de llantas para tu vehículo con placa {$llanta['placa']} es en 3 días ({$llanta['proximo_cambio_fecha']}).";
            } else if ($tipo_recordatorio == '1_dia') {
                $mensaje_notif = "El próximo cambio de llantas para tu vehículo con placa {$llanta['placa']} es mañana ({$llanta['proximo_cambio_fecha']}).";
            } else if ($tipo_recordatorio == 'hoy') {
                $mensaje_notif = "¡Hoy debes realizar el cambio de llantas para tu vehículo con placa {$llanta['placa']}!";
            }

            if ($mensaje_notif) {
                $insertNotif = $con->prepare("INSERT INTO notificaciones (documento_usuario, mensaje, tipo, fecha, leido) VALUES (?, ?, ?, NOW(), 0)");
                $insertNotif->execute([
                    $llanta['documento'], // o el campo correcto
                    $mensaje_notif,
                    'llantas'
                ]);
            }

        } catch (Exception $e) {
            registrarLog("ERROR al enviar a {$llanta['email']}: " . $mail->ErrorInfo);
            echo "<span style='color: red;'>Error al enviar: {$mail->ErrorInfo}</span><hr>";
            continue;
        }
    }

} catch (Exception $e) {
    registrarLog("ERROR GLOBAL: " . $e->getMessage());
    echo "<span style='color: red;'>ERROR GLOBAL: {$e->getMessage()}</span><br>";
}

registrarLog("== FIN de ejecución del cron para LLANTAS ==");

// === FUNCIONES ===

function generarMensajeLlantas($llanta, $tiempo) {
    return "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Recordatorio de Cambio de Llantas</h2>
        <p>Estimado/a {$llanta['nombre_completo']},</p>
        <p>Le recordamos que el próximo cambio de llantas para su vehículo con placa <strong>{$llanta['placa']}</strong> está programado para <strong>{$llanta['proximo_cambio_fecha']}</strong> ({$tiempo}).</p>
        <p>Detalles de la llanta:</p>
        <ul>
            <li>Último cambio: {$llanta['ultimo_cambio']}</li>
            <li>Presión recomendada: {$llanta['presion_llantas']}</li>
            <li>Kilometraje actual: {$llanta['kilometraje_actual']}</li>
            <li>Próximo cambio (km): {$llanta['proximo_cambio_km']}</li>
            <li>Notas: {$llanta['notas']}</li>
        </ul>
        <p>Por favor, realice el cambio de llantas a tiempo para su seguridad.</p>
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