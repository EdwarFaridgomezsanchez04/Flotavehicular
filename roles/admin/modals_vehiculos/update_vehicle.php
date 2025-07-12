<?php
session_start();
require_once('../../../conecct/conex.php');
include '../../../includes/validarsession.php';

$db = new Database();
$con = $db->conectar();

header('Content-Type: application/json');

// Validar que existe una sesión activa
if (!isset($_SESSION['documento'])) {
    echo json_encode(['success' => false, 'redirect' => '../../login.php']);
    exit;
}

if (!$con) {
    echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
    exit;
}

try {
    $placa = $_POST['placa'] ?? '';
    $tipo_vehiculo = $_POST['tipo_vehiculo'] ?? '';
    $año = $_POST['anio'] ?? $_POST['año'] ?? '';
    $id_color = $_POST['id_color'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $id_marca = $_POST['id_marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $kilometraje_actual = $_POST['kilometraje_actual'] ?? '';
    $id_estado = $_POST['id_estado'] ?? '';


    // Debug: verificar qué campos están vacíos
    $campos_vacios = [];
    if (empty($placa)) $campos_vacios[] = 'placa';
    if (empty($tipo_vehiculo)) $campos_vacios[] = 'tipo_vehiculo';
    if (empty($documento)) $campos_vacios[] = 'documento';
    if (empty($id_marca)) $campos_vacios[] = 'id_marca';
    if (empty($modelo)) $campos_vacios[] = 'modelo';
    if (empty($kilometraje_actual)) $campos_vacios[] = 'kilometraje_actual';
    if (empty($año)) $campos_vacios[] = 'año';
    if (empty($id_color)) $campos_vacios[] = 'id_color';
    if (empty($id_estado)) $campos_vacios[] = 'id_estado';
    
    if (!empty($campos_vacios)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Campos obligatorios faltantes: ' . implode(', ', $campos_vacios),
            'debug' => [
                'placa' => $placa,
                'tipo_vehiculo' => $tipo_vehiculo,
                'documento' => $documento,
                'id_marca' => $id_marca,
                'modelo' => $modelo,
                'kilometraje_actual' => $kilometraje_actual,
                'año' => $año,
                'id_color' => $id_color,
                'id_estado' => $id_estado
            ]
        ]);
        exit;
    }



    // Ruta donde se guardan las imágenes
    $upload_dir = '../../../uploads/vehiculos/';
    $foto_vehiculo = null;

    // Si viene una imagen nueva
    if (isset($_FILES['foto_vehiculo']) && $_FILES['foto_vehiculo']['size'] > 0) {
        $extension = strtolower(pathinfo($_FILES['foto_vehiculo']['name'], PATHINFO_EXTENSION));
        $file_name = 'vehiculo_' . uniqid() . '.' . $extension;
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['foto_vehiculo']['tmp_name'], $file_path)) {
            $foto_vehiculo = 'uploads/vehiculos/' . $file_name;

            // Eliminar imagen anterior si existe
            $old_query = $con->prepare("SELECT foto_vehiculo FROM vehiculos WHERE placa = :placa");
            $old_query->bindParam(':placa', $placa, PDO::PARAM_STR);
            $old_query->execute();
            $old_image = $old_query->fetchColumn();

            if ($old_image && $old_image !== 'sin_foto_carro.png') {
                $old_image_path = '../../../' . $old_image;
                if (file_exists($old_image_path)) {
                    unlink($old_image_path);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
            exit;
        }
    }

    // Construir SQL dinámico si hay nueva imagen
    $sql = "UPDATE vehiculos SET tipo_vehiculo = :tipo_vehiculo, `año` = :anio_param, Documento = :documento, id_marca = :id_marca, modelo = :modelo, id_color = :id_color, kilometraje_actual = :kilometraje_actual, id_estado = :id_estado";
    if ($foto_vehiculo) {
        $sql .= ", foto_vehiculo = :foto_vehiculo";
    }
    $sql .= " WHERE placa = :placa";

    $query = $con->prepare($sql);
    $query->bindParam(':placa', $placa, PDO::PARAM_STR);
    $query->bindParam(':tipo_vehiculo', $tipo_vehiculo, PDO::PARAM_INT);
    $query->bindParam(':anio_param', $año, PDO::PARAM_STR);
    $query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $query->bindParam(':id_marca', $id_marca, PDO::PARAM_INT);
    $query->bindParam(':modelo', $modelo, PDO::PARAM_STR);
    $query->bindParam(':id_color', $id_color, PDO::PARAM_INT);
    $query->bindParam(':kilometraje_actual', $kilometraje_actual, PDO::PARAM_INT);
    $query->bindParam(':id_estado', $id_estado, PDO::PARAM_STR);
    if ($foto_vehiculo) {
        $query->bindParam(':foto_vehiculo', $foto_vehiculo, PDO::PARAM_STR);
    }

    if ($query->execute()) {
        echo json_encode(['success' => true, 'message' => 'Vehículo actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el vehículo']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>