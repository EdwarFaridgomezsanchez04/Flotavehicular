<?php
// Inicia la sesión para poder acceder a las variables de sesión existentes
session_start();

// Elimina todas las variables de sesión de superadmin
unset($_SESSION['superadmin_documento']);
unset($_SESSION['superadmin_nombre']);
unset($_SESSION['superadmin_email']);
unset($_SESSION['superadmin_rol']);
unset($_SESSION['superadmin_logged']);

// También elimina las variables de sesión normales por si acaso
unset($_SESSION['documento']);
unset($_SESSION['tipo']);
unset($_SESSION['estado']);

// Destruye completamente la sesión actual, eliminando todos los datos
session_destroy();

// Fuerza la escritura de los datos de sesión y cierra la sesión
session_write_close();

// Verifica si la constante BASE_URL ya está definida para evitar redefinición
if (!defined('BASE_URL')) {
    // Detecta si el servidor es localhost (entorno de desarrollo)
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        // Define BASE_URL para entorno local (XAMPP)
        define('BASE_URL', '/flotavehicular'); // Asegúrate de que esta ruta sea correcta según tu configuración local
    } else {
        // Define BASE_URL para entorno de producción (hosting)
        define('BASE_URL', ''); // O '/subcarpeta' si tu proyecto está en una subcarpeta en el hosting
    }
}

// Redirige al usuario a la página de login después del logout
header("Location: " . BASE_URL . "/login/login.php");

// Termina la ejecución del script para asegurar que la redirección se ejecute
exit;
?>
