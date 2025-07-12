<?php
session_start();
require_once('../../../conecct/conex.php');
include '../../../includes/validarsession.php';

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

$db = new Database();
$con = $db->conectar();

// Validar que existe una sesión activa
if (!isset($_SESSION['documento'])) {
    echo json_encode(['success' => false, 'redirect' => '../../login.php']);
    exit;
}

if (!$con) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

$placa = $_GET['placa'] ?? '';
if (!$placa) {
    echo json_encode(['success' => false, 'message' => 'Placa no proporcionada']);
    exit;
}

try {
            // Consulta completa con todas las relaciones
        $query = $con->prepare("
            SELECT 
                v.*,
                m.nombre_marca,
                e.estado AS estado_vehiculo,
                c.color AS color_nombre,
                tv.vehiculo AS tipo_vehiculo_nombre,
                u.nombre_completo AS nombre_propietario,
                u.email AS email_propietario,
                u.telefono AS telefono_propietario,
                registrador.nombre_completo AS nombre_registrador,
                registrador.documento AS documento_registrador
            FROM vehiculos v 
            LEFT JOIN marca m ON v.id_marca = m.id_marca 
            LEFT JOIN estado_vehiculo e ON v.id_estado = e.id_estado 
            LEFT JOIN colores c ON v.id_color = c.id_color
            LEFT JOIN tipo_vehiculo tv ON v.tipo_vehiculo = tv.id_tipo_vehiculo
            LEFT JOIN usuarios u ON v.Documento = u.documento
            LEFT JOIN usuarios registrador ON v.registrado_por = registrador.documento
            WHERE v.placa = :placa
        ");
    
    $query->bindParam(':placa', $placa, PDO::PARAM_STR);
    $query->execute();
    $vehicle = $query->fetch(PDO::FETCH_ASSOC);

    if ($vehicle) {
        // Formatear la fecha de registro
        if ($vehicle['fecha_registro']) {
            $vehicle['fecha_registro_formateada'] = date('d/m/Y', strtotime($vehicle['fecha_registro']));
        } else {
            $vehicle['fecha_registro_formateada'] = 'No disponible';
        }

        // Formatear el kilometraje
        $vehicle['kilometraje_formateado'] = number_format($vehicle['kilometraje_actual'], 0, ',', '.') . ' km';

        // Manejar la imagen
        if ($vehicle['foto_vehiculo'] && $vehicle['foto_vehiculo'] !== 'sin_foto_carro.png') {
            $vehicle['foto_url'] = '../../' . $vehicle['foto_vehiculo'];
            $vehicle['tiene_foto'] = true;
        } else {
            $vehicle['foto_url'] = '../../uploads/vehiculos/sin_foto_carro.png';
            $vehicle['tiene_foto'] = false;
        }

        echo json_encode(['success' => true, 'vehicle' => $vehicle]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $e->getMessage()]);
}
?> 