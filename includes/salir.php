<?php
// Inicia la sesión para poder acceder a las variables de sesión existentes
session_start();
// Elimina la variable de sesión 'documento' que almacena la identificación del usuario
unset($_SESSION['documento']);
// Elimina la variable de sesión 'tipo' que almacena el rol del usuario (admin/usuario)
unset($_SESSION['tipo']);
// Elimina la variable de sesión 'estado' que almacena el estado del usuario
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
        define('BASE_URL', '/Flotavehicular');
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
