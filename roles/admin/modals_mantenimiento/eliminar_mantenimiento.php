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

// Verificar que la petición sea POST y que se haya enviado el ID del mantenimiento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Obtener el ID del mantenimiento a eliminar
    $id = $_POST['id'];

    try {
        // Verificar que el mantenimiento existe en la base de datos antes de eliminarlo
        $checkQuery = $con->prepare("SELECT COUNT(*) FROM mantenimiento WHERE id_mantenimiento = :id");
        $checkQuery->bindParam(':id', $id, PDO::PARAM_INT);
        $checkQuery->execute();
        
        // Si el mantenimiento no existe, retornar error
        if ($checkQuery->fetchColumn() == 0) {
            echo json_encode(['success' => false, 'error' => 'Mantenimiento no encontrado']);
            exit;
        }

        // Preparar consulta para eliminar el mantenimiento de la tabla mantenimiento
        $query = $con->prepare("DELETE FROM mantenimiento WHERE id_mantenimiento = :id");
        $query->bindParam(':id', $id, PDO::PARAM_INT);

        // Ejecutar la eliminación y verificar si fue exitosa
        if ($query->execute()) {
            // Respuesta exitosa en formato JSON
            echo json_encode(['success' => true, 'message' => 'Mantenimiento eliminado exitosamente']);
        } else {
            // Error en la ejecución de la consulta
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el mantenimiento']);
        }
    } catch (PDOException $e) {
        // Manejo de errores de base de datos - registrar en log y retornar error genérico
        error_log("Database error in eliminar_mantenimiento.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos']);
    }
} else {
    // Error si no se proporciona el ID o no es una petición POST
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
}
?>