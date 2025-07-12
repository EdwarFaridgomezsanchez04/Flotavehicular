<?php
// Iniciar sesión para validar autenticación del usuario
session_start();

// Incluir archivo de conexión a la base de datos
require_once('../../../conecct/conex.php');

// Validación de sesión personalizada para respuestas JSON
// Verificar si existe una sesión activa del usuario
if (!isset($_SESSION['documento'])) {
    // Establecer tipo de contenido JSON para la respuesta
    header('Content-Type: application/json');
    
    // Retornar respuesta JSON con información de sesión inválida y redirección
    echo json_encode([
        'success' => false, 
        'message' => 'Sesión no válida',
        'redirect' => true,
        'redirect_url' => '/Flotavehicular/login/login.php'
    ]);
    exit();
}

// Crear instancia de conexión a la base de datos
$db = new Database();
$con = $db->conectar();

// Establecer el tipo de contenido de respuesta como JSON
header('Content-Type: application/json');

// Verificar que se haya proporcionado el parámetro 'documento' por GET
if (isset($_GET['documento'])) {
    // Obtener el documento del usuario del parámetro GET
    $documento = $_GET['documento'];
    
    try {
        // Preparar consulta para obtener todos los datos del usuario por documento
        $query = $con->prepare("SELECT * FROM usuarios WHERE documento = :documento");
        $query->bindParam(':documento', $documento, PDO::PARAM_STR);
        $query->execute();
        
        // Obtener el resultado como array asociativo
        $usuario = $query->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontró el usuario
        if ($usuario) {
            // Retornar respuesta exitosa con los datos del usuario
            echo json_encode(['success' => true, 'data' => $usuario]);
        } else {
            // Usuario no encontrado en la base de datos
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
    }
} else {
    // Error si no se proporciona el parámetro documento
    echo json_encode(['success' => false, 'message' => 'Documento no proporcionado']);
}
?>