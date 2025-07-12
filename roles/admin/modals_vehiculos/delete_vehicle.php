<?php
session_start();
require_once('../../../conecct/conex.php');

header('Content-Type: application/json');

$db = new Database();
$con = $db->conectar();

if (!$con) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

try {
    $placa = $_POST['placa'] ?? '';
    
    if (empty($placa)) {
        echo json_encode(['success' => false, 'message' => 'Placa no proporcionada']);
        exit;
    }

    // Eliminar registros relacionados primero
    $tablas = [
        'correos_enviados_pico_placa' => 'placa',
        'mantenimiento' => 'placa', 
        'llantas' => 'placa',
        'soat' => 'id_placa',
        'tecnomecanica' => 'id_placa'
    ];

    foreach ($tablas as $tabla => $campo) {
        $sql = "DELETE FROM `$tabla` WHERE `$campo` = ?";
        $stmt = $con->prepare($sql);
        $stmt->execute([$placa]);
    }

    // Obtener ruta de la imagen
    $stmt = $con->prepare("SELECT `foto_vehiculo` FROM `vehiculos` WHERE `placa` = ?");
    $stmt->execute([$placa]);
    $foto = $stmt->fetchColumn();

    // Eliminar imagen si existe
    if ($foto && $foto !== 'sin_foto_carro.png') {
        $ruta = '../../../' . $foto;
        if (file_exists($ruta)) {
            unlink($ruta);
        }
    }

    // Eliminar vehículo
    $stmt = $con->prepare("DELETE FROM `vehiculos` WHERE `placa` = ?");
    $resultado = $stmt->execute([$placa]);

    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Vehículo eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el vehículo']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>