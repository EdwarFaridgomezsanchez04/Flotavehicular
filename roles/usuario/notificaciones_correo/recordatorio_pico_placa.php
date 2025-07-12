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

echo "<h2>Resultado del Envío de Recordatorios Pico y Placa</h2><hr>";
registrarLog("== INICIO de ejecución del cron para PICO Y PLACA ==");

try {
    $database = new Database();
    $con = $database->conectar();

    date_default_timezone_set('America/Bogota');
    $tomorrow = date('l', strtotime('+1 day'));
    $dia_semana = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes'
    ];

    if (isset($dia_semana[$tomorrow])) {
        $dia_esp = $dia_semana[$tomorrow];

        // Consultar los dígitos restringidos para el día
        $stmt = $con->prepare("SELECT digitos_restringidos FROM pico_placa WHERE dia = ?");
        $stmt->execute([$dia_esp]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            $digitos = explode(',', $resultado['digitos_restringidos']);

            if (count($digitos) === 0) {
                echo "No hay dígitos restringidos para mañana.<br>";
                registrarLog("No hay dígitos restringidos para $dia_esp");
                exit;
            }

            $placeholders = rtrim(str_repeat('?,', count($digitos)), ',');
            $sql = "
                SELECT v.placa, u.email, u.nombre_completo, u.documento
                FROM vehiculos v
                INNER JOIN usuarios u ON v.Documento = u.documento
                WHERE RIGHT(v.placa, 1) IN ($placeholders)
            ";

            $stmtVehiculos = $con->prepare($sql);
            $stmtVehiculos->execute($digitos);
            $vehiculos = $stmtVehiculos->fetchAll(PDO::FETCH_ASSOC);

            if (empty($vehiculos)) {
                echo "No hay vehículos para enviar recordatorio mañana.<br>";
                registrarLog("No hay vehículos para enviar recordatorio mañana ($dia_esp)");
                exit;
            }

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

            foreach ($vehiculos as $vehiculo) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($vehiculo['email']);

                    $fecha_envio = date('Y-m-d', strtotime('+1 day'));
                    $verifica = $con->prepare("SELECT COUNT(*) FROM correos_enviados_pico_placa WHERE placa = :placa AND email = :email AND fecha_envio = :fecha");
                    $verifica->execute([
                        'placa' => $vehiculo['placa'],
                        'email' => $vehiculo['email'],
                        'fecha' => $fecha_envio
                    ]);
                    if ($verifica->fetchColumn() > 0) {
                        registrarLog("Ya se envió recordatorio a {$vehiculo['email']} para la placa {$vehiculo['placa']} el $fecha_envio");
                        echo "<span style='color: orange;'>Ya se envió recordatorio a {$vehiculo['email']} para la placa {$vehiculo['placa']} el $fecha_envio</span><hr>";
                        continue;
                    }

                    $mail->Subject = "Recordatorio: Pico y Placa mañana para su vehículo";
                    $mensaje = generarMensaje($vehiculo, $dia_esp);
                    if (enviarNotificacion($mail, $mensaje)) {
                        $registra = $con->prepare("INSERT INTO correos_enviados_pico_placa (placa, email, fecha_envio) VALUES (:placa, :email, :fecha)");
                        $registra->execute([
                            'placa' => $vehiculo['placa'],
                            'email' => $vehiculo['email'],
                            'fecha' => $fecha_envio
                        ]);
                        registrarLog("Correo enviado a: {$vehiculo['email']} (Pico y Placa $fecha_envio)");
                        echo "<span style='color: green;'>Correo enviado a: {$vehiculo['email']} (Pico y Placa $fecha_envio)</span><hr>";
                    } else {
                        registrarLog("Error al enviar correo a {$vehiculo['email']}: {$mail->ErrorInfo}");
                        echo "<span style='color: red;'>Error al enviar correo a {$vehiculo['email']}: {$mail->ErrorInfo}</span><hr>";
                    }

                    // Guardar notificación en la tabla
                    $mensaje_notif = "Mañana ({$dia_esp}) tu vehículo con placa {$vehiculo['placa']} tiene restricción de Pico y Placa en Ibague.";
                    $insertNotif = $con->prepare("INSERT INTO notificaciones (documento_usuario, mensaje, tipo, fecha, leido) VALUES (?, ?, ?, NOW(), 0)");
                    $insertNotif->execute([
                        $vehiculo['documento'] ?? null,
                        $mensaje_notif,
                        'pico_placa'
                    ]);

                } catch (Exception $e) {
                    registrarLog("Excepción al enviar a {$vehiculo['email']}: {$mail->ErrorInfo}");
                    echo "<span style='color: red;'>Excepción al enviar a {$vehiculo['email']}: {$mail->ErrorInfo}</span><hr>";
                    continue;
                }
            }
        } else {
            echo "No hay configuración de pico y placa para el día $dia_esp.<br>";
            registrarLog("No hay configuración de pico y placa para el día $dia_esp");
        }
    } else {
        echo "Mañana no hay pico y placa.<br>";
        registrarLog("Mañana no hay pico y placa.");
    }
} catch (Exception $e) {
    registrarLog("ERROR GLOBAL: " . $e->getMessage());
    echo "<span style='color: red;'>ERROR GLOBAL: {$e->getMessage()}</span><br>";
}

registrarLog("== FIN de ejecución del cron para PICO Y PLACA ==");

// === FUNCIONES ===

function generarMensaje($vehiculo, $dia) {
    return "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Recordatorio de Pico y Placa</h2>
        <p>Estimado/a {$vehiculo['nombre_completo']},</p>
        <p>Le recordamos que mañana en la ciudad de Ibague<strong>{$dia}</strong> su vehículo con placa 
           <strong>{$vehiculo['placa']}</strong> tiene restricción de Pico y Placa.</p>
        <p>Horarios de restricción:</p>
        <ul>
            <li>Dia: <strong>6:00 AM - 9:00 PM</strong></li>
        </ul>
        <p><strong>Importante:</strong>Los conductores que tengan matriculado los vehiculos en la ciudad de Ibague pueden disfrutar del beneficio de la hora valle y transitar el dia de su pico y placa en los siguientes horarios:</p>
        <ul>
            <li>Desde: <strong>9:00 AM</strong> Hasta: <strong> 11:00 AM </strong></li>
            <li>Desde: <strong>3:00 PM</strong> Hasta: <strong> 5:00 PM </strong></li>
        </ul>
        <p>Planifique sus desplazamientos teniendo en cuenta esta restricción.</p>
        <p>Atentamente,<br>Sistema de Recordatorios</p>
    </body>
    </html>";
}

function enviarNotificacion($mail, $mensaje) {
    $mail->isHTML(true);
    $mail->Body = $mensaje;
    return $mail->send();
}

?>