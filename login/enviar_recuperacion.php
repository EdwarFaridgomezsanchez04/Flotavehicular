<?php
/**
 * ENVÍO DE RECUPERACIÓN DE CONTRASEÑA
 * 
 * Este archivo maneja el proceso de envío de correos electrónicos para la recuperación
 * de contraseñas. Genera tokens únicos con expiración y envía enlaces de recuperación
 * mediante PHPMailer con plantillas HTML profesionales.
 */

// ===== CONFIGURACIÓN INICIAL =====
// Establece la zona horaria para Colombia (importante para tokens con expiración)
date_default_timezone_set('America/Bogota');

// ===== INCLUSIÓN DE DEPENDENCIAS =====
// Incluye las clases de PHPMailer para el envío de correos electrónicos
require '../src/PHPMailer.php';
require '../src/SMTP.php';
require '../src/Exception.php';
// Incluye la clase de conexión a la base de datos
require '../conecct/conex.php';

// ===== IMPORTACIÓN DE NAMESPACES =====
// Importa las clases de PHPMailer para uso directo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===== CONFIGURACIÓN DINÁMICA DE BASE_URL =====
/**
 * Define la URL base del sistema de forma dinámica
 * Detecta automáticamente si está en desarrollo local o producción
 */
if (!defined('BASE_URL')) {
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        // Configuración para desarrollo local
        define('BASE_URL', '/Flotavehicular');
    } else {
        // Configuración para servidor de producción
        define('BASE_URL', 'https://flotaxagc.com');
    }
}

// ===== PROCESAMIENTO DEL FORMULARIO =====
/**
 * Verifica que la solicitud sea POST antes de procesar
 * Solo procesa si se envió el formulario de recuperación
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ===== CAPTURA Y VALIDACIÓN DE DATOS =====
    // Obtiene el email del formulario y elimina espacios en blanco
    $email = trim($_POST["email"]);

    // ===== VALIDACIÓN: CAMPO NO VACÍO =====
    /**
     * Verifica que el campo email no esté vacío
     * Redirige a recovery con mensaje de error si está vacío
     */
    if (empty($email)) {
        echo '<script>alert("Ningún dato puede estar vacío");</script>';
        echo '<script>window.location = "recovery";</script>';
        exit;
    }

    // ===== CONEXIÓN A LA BASE DE DATOS =====
    // Establece conexión usando la clase Database
    $db = new Database();
    $con = $db->conectar();

    // ===== VERIFICACIÓN DE USUARIO EXISTENTE =====
    /**
     * Busca en la base de datos si existe un usuario con el email proporcionado
     * Solo obtiene el documento (ID) del usuario para operaciones posteriores
     */
    $stmt = $con->prepare("SELECT documento FROM usuarios WHERE email = ? ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ===== VALIDACIÓN: USUARIO EXISTE =====
    /**
     * Si no se encuentra el usuario, muestra error y redirige
     * Esto previene ataques de enumeración de usuarios
     */
    if (!$user) {
        echo '<script>alert("Email incorrecto");</script>';
        echo '<script>window.location = "recovery";</script>';
        exit;
    }

    // ===== GENERACIÓN DE TOKEN ÚNICO =====
    /**
     * Genera un token criptográficamente seguro de 100 caracteres hexadecimales
     * Utiliza random_bytes() para máxima seguridad
     */
    $token = bin2hex(random_bytes(50));
    
    // ===== CONFIGURACIÓN DE EXPIRACIÓN =====
    // Asegura que la zona horaria esté configurada antes de calcular la expiración
    date_default_timezone_set('America/Bogota');
    // Establece que el token expire en 1 hora desde el momento actual
    $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // ===== ALMACENAMIENTO DEL TOKEN =====
    /**
     * Actualiza la base de datos con el token y su fecha de expiración
     * Asocia el token al documento del usuario para posterior validación
     */
    $stmt = $con->prepare("UPDATE usuarios SET reset_token = ?, reset_expira = ? WHERE documento = ?");
    $stmt->execute([$token, $expira, $user['documento']]);

    // ===== CONFIGURACIÓN DE PHPMAILER =====
    /**
     * Inicializa PHPMailer con configuración para Gmail SMTP
     * Incluye configuración de caracteres UTF-8 y codificación base64
     */
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';        // Soporte para caracteres especiales
    $mail->Encoding = 'base64';      // Codificación para contenido HTML
    
    try {
        // ===== CONFIGURACIÓN SMTP =====
        $mail->isSMTP();                                    // Habilita SMTP
        $mail->Host = 'smtp.gmail.com';                    // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;                            // Habilita autenticación SMTP
        $mail->Username = 'flotavehicularagc@gmail.com';   // Email de la aplicación
        $mail->Password = 'brgl znfz eqfk mcct';           // Contraseña de aplicación de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encriptación TLS
        $mail->Port = 587;                                 // Puerto para TLS

        // ===== CONFIGURACIÓN DEL CORREO =====
        // Establece el remitente con nombre descriptivo
        $mail->setFrom('flotavehicularagc@gmail.com', 'Recuperar Contraseña');
        // Añade el destinatario (usuario que solicita recuperación)
        $mail->addAddress($email);
        // Establece el asunto del correo
        $mail->Subject = 'Recuperación de contraseña - Flota Vehicular';

        // ===== GENERACIÓN DINÁMICA DEL ENLACE DE RECUPERACIÓN =====
        /**
         * Construye el enlace de recuperación de forma dinámica
         * Detecta si está en localhost o producción para generar la URL correcta
         */
        $host = $_SERVER['HTTP_HOST'];
        if (strpos($host, 'localhost') !== false) {
            // Configuración para desarrollo local
            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $reset_link = $protocolo . "://" . $host . BASE_URL . "/login/change.php?token=" . urlencode($token);
        } else {
            // Configuración para producción
            $reset_link = BASE_URL . "/login/change.php?token=" . urlencode($token);
        }

        // ===== CONFIGURACIÓN DE RECURSOS EXTERNOS =====
        // URL del logo alojado externamente para evitar problemas de adjuntos
        $logoUrl = 'https://logosinfondo.netlify.app/logo_sinfondo.png';

        // ===== PLANTILLA HTML DEL CORREO =====
        /**
         * Plantilla HTML profesional con diseño responsivo y tema oscuro
         * Incluye logo, mensaje personalizado, botón de acción y footer
         */
        $mail->isHTML(true); // Habilita contenido HTML
        $mail->Body = "
        <div style='background-color: #1a1a1a; width: 100%; padding: 20px 0; font-family: Arial, sans-serif;'>
            <!-- Contenedor principal con fondo oscuro y sombra -->
            <div style='background-color: #262626; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.5);'>
                <!-- Logo centrado con imagen de fondo -->
                <div style='width: 150px; height: 150px; margin: auto; background-image: url($logoUrl); 
                background-size: contain; background-repeat: no-repeat; background-position: center;'></div>
                
                <!-- Título principal -->
                <h2 style='color: #ffffff; text-align: center; margin-bottom: 20px; font-size: 24px;'>Recuperación de contraseña</h2>
                
                <!-- Contenido principal del mensaje -->
                <div style='color: #e0e0e0; text-align: center; line-height: 1.6;'>
                    <h4>Hola, has solicitado recuperar tu contraseña.</h4>
                    <p>Haz clic en el siguiente botón para restablecerla:</p>
                    
                    <!-- Botón de acción principal -->
                    <div style='margin: 30px 0;'>
                        <a href='$reset_link' style='background-color: #d32f2f; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; transition: background-color 0.3s ease;'>Restablecer contraseña</a>
                    </div>
                    
                    <!-- Información adicional y advertencias -->
                    <p style='color: #888888; font-size: 14px;'>Si no solicitaste este cambio, ignora este mensaje.</p>
                    <p style='color: #888888; font-size: 14px;'>Este enlace expira en 1 hora.</p>
                </div>
                
                <!-- Footer con información de copyright -->
                <div style='margin-top: 30px; border-top: 1px solid #444444; padding-top: 20px; text-align: center;'>
                    <p style='color: #888888; font-size: 12px;'>© 2024 Flota Vehicular. Todos los derechos reservados.</p>
                </div>
            </div>
        </div>";

        // ===== ENVÍO DEL CORREO =====
        /**
         * Intenta enviar el correo electrónico
         * Si es exitoso, muestra mensaje de confirmación y redirige al login
         */
        $mail->send();
        echo '<script>alert("Revisa tu correo para restablecer la contraseña.");</script>';
        echo '<script>window.location = "login";</script>';
        
    } catch (Exception $e) {
        // ===== MANEJO DE ERRORES DE ENVÍO =====
        /**
         * Captura cualquier error durante el envío del correo
         * Muestra el error específico para debugging (en producción debería ser más genérico)
         */
        echo '<script>alert("Error al enviar el correo: ' . $mail->ErrorInfo . '");</script>';
    }
}
?>
