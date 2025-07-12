<?php
// Bloque PHP para manejo de autenticación de usuarios

// Iniciar sesión PHP para manejo de variables de sesión
session_start();

// Incluir archivo de conexión a la base de datos
require_once('../conecct/conex.php');

// Línea comentada - validación de sesión (actualmente deshabilitada)
// include 'validarsesion.php';

// Crear instancia de la clase Database
$db = new Database();

// Establecer conexión con la base de datos
$con = $db->conectar();

// Configurar el tipo de contenido de respuesta como JSON
header('Content-Type: application/json');

// Verificar que la petición HTTP sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario usando null coalescing operator
    $doc = $_POST['doc'] ?? '';     // Documento de identidad del usuario
    $passw = $_POST['passw'] ?? ''; // Contraseña del usuario

    // Validar que ambos campos estén completos
    if (empty($doc) || empty($passw)) {
        // Retornar error si algún campo está vacío
        echo json_encode(['status' => 'error', 'message' => 'Error :Todos los campos son obligatorios.']);
        exit; // Terminar ejecución del script
    }

    // Preparar consulta SQL para buscar usuario por documento
    $sql = $con->prepare("SELECT * FROM usuarios WHERE documento = ?");
    $sql->execute([$doc]); // Ejecutar consulta con el documento como parámetro
    $fila = $sql->fetch(); // Obtener el registro del usuario

    // Verificar si el usuario existe en la base de datos
    if (!$fila) {
        // Retornar error si el documento no está registrado
        echo json_encode(['status' => 'error', 'message' => 'Error: Documento no encontrado']);
        exit; // Terminar ejecución del script
    }

    // Verificar la contraseña usando password_verify para contraseñas hasheadas
    if (!password_verify($passw, $fila['password'])) {
        // Retornar error si la contraseña no coincide
        echo json_encode(['status' => 'error', 'message' => 'Error: Contraseña incorrecta']);
        exit; // Terminar ejecución del script
    }

    // Verificar que el usuario esté activo (estado = 1)
    if ($fila['id_estado_usuario'] != 1) {
        // Retornar error si el usuario está inactivo
        echo json_encode(['status' => 'error', 'message' => 'Error: Acceso denegado. La licencia del sistema ha sido suspendida. Contacte al administrador.']);
        exit; // Terminar ejecución del script
    }

    // Establecer variables de sesión para el usuario autenticado
    $_SESSION['documento'] = $fila['documento']; // Guardar documento en sesión
    $_SESSION['tipo'] = $fila['id_rol'];         // Guardar rol del usuario en sesión

    // Retornar respuesta exitosa con información del rol
    echo json_encode([
        'status' => 'success',
        'rol' => $fila['id_rol'] == 1 ? 'admin' : ($fila['id_rol'] == 3 ? 'superadmin' : 'usuario') // Determinar tipo de rol
    ]);
} else {
    // Manejar peticiones que no sean POST
    echo json_encode(['status' => 'error', 'message' => 'Petición no válida']);
    exit; // Terminar ejecución del script
}
// Cierre del bloque PHP
?>