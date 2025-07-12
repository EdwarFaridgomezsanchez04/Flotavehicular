<?php
// Incluir archivo de conexión a la base de datos
require_once('../../../conecct/conex.php');

// Crear instancia de la base de datos y obtener la conexión
$db = new Database();
$con = $db->conectar();

// Verificar que la petición sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $placa = $_POST['placa'];                                    // Placa del vehículo
    $id_tipo_mantenimiento = $_POST['id_tipo_mantenimiento'];    // ID del tipo de mantenimiento
    $fecha_programada = $_POST['fecha_programada'];              // Fecha programada del mantenimiento
    $fecha_realizada = $_POST['fecha_realizada'] ?? null;        // Fecha realizada (opcional)
    $kilometraje_actual = $_POST['kilometraje_actual'] ?? null;  // Kilometraje actual (opcional)
    $observaciones = $_POST['observaciones'] ?? null;            // Observaciones (opcional)

    // Validar que los campos requeridos no estén vacíos
    if (empty($placa) || empty($id_tipo_mantenimiento) || empty($fecha_programada)) {
        echo "Todos los campos obligatorios deben estar completos";
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

    // Preparar consulta SQL para insertar el nuevo mantenimiento
    $query = $con->prepare("INSERT INTO mantenimiento (placa, id_tipo_mantenimiento, fecha_programada, fecha_realizada, kilometraje_actual, observaciones) VALUES (:placa, :id_tipo_mantenimiento, :fecha_programada, :fecha_realizada, :kilometraje_actual, :observaciones)");
    
    // Vincular parámetros para prevenir inyección SQL
    $query->bindParam(':placa', $placa, PDO::PARAM_STR);                           // Placa como string
    $query->bindParam(':id_tipo_mantenimiento', $id_tipo_mantenimiento, PDO::PARAM_INT); // ID tipo como entero
    $query->bindParam(':fecha_programada', $fecha_programada, PDO::PARAM_STR);     // Fecha programada como string
    $query->bindParam(':fecha_realizada', $fecha_realizada, PDO::PARAM_STR);       // Fecha realizada como string (puede ser null)
    $query->bindParam(':kilometraje_actual', $kilometraje_actual, PDO::PARAM_INT); // Kilometraje como entero (puede ser null)
    $query->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);           // Observaciones como string (puede ser null)

    // Ejecutar la consulta y mostrar resultado
    if ($query->execute()) {
        echo "Mantenimiento agregado exitosamente";
    } else {
        echo "Error al agregar el mantenimiento";
    }
}
?>