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

// Verificar que se haya proporcionado el parámetro 'id' por GET
if (isset($_GET['id'])) {
    // Obtener el ID del mantenimiento del parámetro GET
    $id = $_GET['id'];
    
    try {
        // Preparar consulta para obtener todos los datos del mantenimiento por ID
        // Incluyendo JOIN con tipo_mantenimiento para obtener la descripción
        $query = $con->prepare("
            SELECT m.*, tm.descripcion as descripcion_tipo,
                   CASE 
                       WHEN m.fecha_realizada IS NOT NULL THEN 'Completado'
                       ELSE 'Pendiente'
                   END as estado
            FROM mantenimiento m
            LEFT JOIN tipo_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
            WHERE m.id_mantenimiento = :id
        ");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        
        // Obtener el resultado como array asociativo
        $mantenimiento = $query->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontró el mantenimiento
        if ($mantenimiento) {
            // Retornar respuesta exitosa con los datos del mantenimiento
            echo json_encode(['success' => true, 'data' => $mantenimiento]);
        } else {
            // Mantenimiento no encontrado en la base de datos
            echo json_encode(['success' => false, 'message' => 'Mantenimiento no encontrado']);
        }
    } catch (PDOException $e) {
        // Manejo de errores de base de datos
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos']);
    }
} else {
    // Error si no se proporciona el parámetro id
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
}
?> 