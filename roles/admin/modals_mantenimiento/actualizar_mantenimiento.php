<?php
// Inicializar sesión para validar el usuario autenticado
session_start();

// Incluir archivo de conexión a la base de datos
require_once('../../../conecct/conex.php');

// Validación de sesión activa
if (!isset($_SESSION['documento'])) {
    echo "Sesión no válida. Por favor, inicie sesión nuevamente.";
    exit();
}

// Crear instancia de la base de datos y obtener la conexión
$db = new Database();
$con = $db->conectar();

// Verificar que la petición sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $id_mantenimiento = $_POST['id_mantenimiento'];              // ID del mantenimiento (clave primaria)
    $placa = $_POST['placa'];                                    // Placa del vehículo
    $id_tipo_mantenimiento = $_POST['id_tipo_mantenimiento'];    // ID del tipo de mantenimiento
    $fecha_programada = $_POST['fecha_programada'];              // Fecha programada del mantenimiento
    $fecha_realizada = $_POST['fecha_realizada'] ?? null;        // Fecha realizada (opcional)
    $kilometraje_actual = $_POST['kilometraje_actual'] ?? null;  // Kilometraje actual (opcional)
    $observaciones = $_POST['observaciones'] ?? null;            // Observaciones (opcional)

    // Validar que los campos requeridos no estén vacíos
    if (empty($id_mantenimiento) || empty($placa) || empty($id_tipo_mantenimiento) || empty($fecha_programada)) {
        echo "Todos los campos obligatorios deben estar completos";
        exit;
    }

    // Verificar que el mantenimiento existe
    $checkMantenimiento = $con->prepare("SELECT COUNT(*) FROM mantenimiento WHERE id_mantenimiento = :id");
    $checkMantenimiento->bindParam(':id', $id_mantenimiento, PDO::PARAM_INT);
    $checkMantenimiento->execute();
    
    if ($checkMantenimiento->fetchColumn() == 0) {
        echo "El mantenimiento seleccionado no existe";
        exit;
    }

    // Verificar que el vehículo existe
    $checkVehiculo = $con->prepare("SELECT COUNT(*) FROM vehiculos WHERE placa = :placa");
    $checkVehiculo->bindParam(':placa', $placa, PDO::PARAM_STR);
    $checkVehiculo->execute();
    
    if ($checkVehiculo->fetchColumn() == 0) {
        echo "El vehículo seleccionado no existe";
        exit;
    }

    // Verificar que el tipo de mantenimiento existe
    $checkTipo = $con->prepare("SELECT COUNT(*) FROM tipo_mantenimiento WHERE id_tipo_mantenimiento = :id_tipo");
    $checkTipo->bindParam(':id_tipo', $id_tipo_mantenimiento, PDO::PARAM_INT);
    $checkTipo->execute();
    
    if ($checkTipo->fetchColumn() == 0) {
        echo "El tipo de mantenimiento seleccionado no existe";
        exit;
    }

    // Preparar consulta SQL para actualizar los datos del mantenimiento
    // Se actualiza por id_mantenimiento ya que es la clave primaria
    $query = $con->prepare("UPDATE mantenimiento SET placa = :placa, id_tipo_mantenimiento = :id_tipo_mantenimiento, fecha_programada = :fecha_programada, fecha_realizada = :fecha_realizada, kilometraje_actual = :kilometraje_actual, observaciones = :observaciones WHERE id_mantenimiento = :id_mantenimiento");
    
    // Vincular parámetros para prevenir inyección SQL
    $query->bindParam(':id_mantenimiento', $id_mantenimiento, PDO::PARAM_INT);     // ID como entero
    $query->bindParam(':placa', $placa, PDO::PARAM_STR);                           // Placa como string
    $query->bindParam(':id_tipo_mantenimiento', $id_tipo_mantenimiento, PDO::PARAM_INT); // ID tipo como entero
    $query->bindParam(':fecha_programada', $fecha_programada, PDO::PARAM_STR);     // Fecha programada como string
    $query->bindParam(':fecha_realizada', $fecha_realizada, PDO::PARAM_STR);       // Fecha realizada como string (puede ser null)
    $query->bindParam(':kilometraje_actual', $kilometraje_actual, PDO::PARAM_INT); // Kilometraje como entero (puede ser null)
    $query->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);           // Observaciones como string (puede ser null)

    // Ejecutar la consulta y mostrar resultado
    if ($query->execute()) {
        echo "Mantenimiento actualizado exitosamente";
    } else {
        echo "Error al actualizar el mantenimiento";
    }
}
?>