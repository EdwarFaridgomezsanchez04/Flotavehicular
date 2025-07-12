<?php
// Iniciar sesión para validar autenticación del usuario
session_start();

// Incluir archivos necesarios para conexión a base de datos y validación de sesión
require_once('../../../conecct/conex.php');
require_once('../../../includes/validarsession.php');

// Crear instancia de conexión a la base de datos
$db = new Database();
$con = $db->conectar();

// Establecer el tipo de contenido de respuesta como JSON
header('Content-Type: application/json');

// Verificar que la petición sea POST y que se haya enviado el documento del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['documento'])) {
    // Obtener el documento del usuario a eliminar
    $documento = $_POST['documento'];

    try {
        // Verificar que el usuario existe en la base de datos antes de eliminarlo
        $checkQuery = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE documento = :documento");
        $checkQuery->bindParam(':documento', $documento, PDO::PARAM_STR);
        $checkQuery->execute();
        
        // Si el usuario no existe, retornar error
        if ($checkQuery->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
            exit;
        }

        // Preparar consulta para eliminar el usuario de la tabla usuarios
        $query = $con->prepare("DELETE FROM usuarios WHERE documento = :documento");
        $query->bindParam(':documento', $documento, PDO::PARAM_STR);

        // Ejecutar la eliminación y verificar si fue exitosa
        if ($query->execute()) {
            // Respuesta exitosa en formato JSON
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } else {
            // Error en la ejecución de la consulta
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el usuario']);
        }
    } catch (PDOException $e) {
        // Manejo de errores de base de datos - registrar en log y retornar error genérico
        error_log("Database error in eliminar_usuario.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
    }
} else {
    // Error si no se proporciona el documento o no es una petición POST
    echo json_encode(['success' => false, 'error' => 'Documento no proporcionado']);
}
?>