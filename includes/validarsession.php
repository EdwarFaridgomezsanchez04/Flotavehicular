<?php
// Verifica si la variable de sesión 'documento' no está establecida (usuario no autenticado)
if (!isset($_SESSION['documento'])) {

    // Elimina la variable de sesión 'documento' por seguridad (aunque ya no existe)
    unset($_SESSION['documento']);
    // Elimina la variable de sesión 'tipo' que almacena el rol del usuario
    unset($_SESSION['tipo']);
    // Elimina la variable de sesión 'estado' que almacena el estado del usuario
    unset($_SESSION['estado']);
    // Limpia completamente el array de sesión, eliminando todas las variables
    $_SESSION = array();
    // Destruye la sesión actual del servidor
    session_destroy();
    // Fuerza la escritura y cierre de la sesión
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

    // Verificar si es una petición AJAX/JSON
    $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    $isJsonRequest = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    $expectsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    
    // Si es una petición que espera JSON, devolver respuesta JSON
    if ($isAjaxRequest || $isJsonRequest || $expectsJson || (isset($_POST['accion']) && !empty($_POST['accion']))) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Sesión expirada. Por favor, inicie sesión nuevamente.', 'redirect' => BASE_URL . '/login/login']);
        exit();
    }
    
    // Para peticiones normales, mostrar alerta y redirigir
    echo "<script>alert('INGRESE CREDENCIALES DE LOGIN');</script>";
    echo "<script>window.location = '" . BASE_URL . "/login/login';</script>";
    exit();
}
?>