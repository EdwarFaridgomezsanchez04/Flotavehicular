<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

header('Content-Type: application/json');

$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['documento'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$accion = $_POST['accion'] ?? '';

try {
    switch ($accion) {
        case 'agregar':
            $campos_requeridos = ['tipo_vehiculo', 'id_marca', 'placa', 'modelo', 'anio', 'id_color', 'kilometraje_actual', 'id_estado', 'documento'];
            foreach ($campos_requeridos as $campo) {
                if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
                    echo json_encode(['success' => false, 'message' => "El campo '$campo' es requerido."]);
                    exit;
                }
            }

            // Sanitizar y asignar
            $placa = strtoupper(trim($_POST['placa']));
            $modelo = trim($_POST['modelo']);
            $anio = (int)$_POST['anio'];
            $kilometraje = (int)$_POST['kilometraje_actual'];

            // Validar placa: 3 letras y 2 o 3 números
            if (!preg_match('/^[A-Z]{3}[0-9]{2,3}$/', $placa)) {
                echo json_encode(['success' => false, 'message' => 'Placa inválida. Debe tener 3 letras y 2 o 3 números (ej: ABC12 o ABC123).']);
                exit;
            }

            // Validar duplicado
            $check_placa = $con->prepare("SELECT 1 FROM vehiculos WHERE placa = :placa");
            $check_placa->bindParam(':placa', $placa);
            $check_placa->execute();
            if ($check_placa->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'La placa ya está registrada.']);
                exit;
            }

            // Validar modelo
            if (!preg_match('/^[A-Za-z0-9\s\-]{2,50}$/', $modelo)) {
                echo json_encode(['success' => false, 'message' => 'Modelo inválido. Debe tener entre 2 y 50 caracteres (letras, números, espacios, guiones).']);
                exit;
            }

            // Validar año
            $anio_actual = (int)date('Y') + 1;
            if ($anio < 1900 || $anio > $anio_actual) {
                echo json_encode(['success' => false, 'message' => "Año inválido. Debe estar entre 1900 y $anio_actual."]);
                exit;
            }

            // Validar kilometraje
            if ($kilometraje < 0 || $kilometraje > 999999) {
                echo json_encode(['success' => false, 'message' => 'Kilometraje inválido. Debe estar entre 0 y 999999.']);
                exit;
            }

            // Procesar imagen si se subió
            $foto_vehiculo = null;
            if (isset($_FILES['foto_vehiculo']) && $_FILES['foto_vehiculo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/vehiculos/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $file_extension = pathinfo($_FILES['foto_vehiculo']['name'], PATHINFO_EXTENSION);
                $foto_vehiculo = $placa . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $foto_vehiculo;
                $foto_vehiculo = 'uploads/vehiculos/' . $foto_vehiculo;

                if (!move_uploaded_file($_FILES['foto_vehiculo']['tmp_name'], $upload_path)) {
                    echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
                    exit;
                }
            }

            // Insertar
            $stmt = $con->prepare("INSERT INTO vehiculos (
                tipo_vehiculo, id_marca, placa, modelo, `año`, id_color, kilometraje_actual, id_estado, Documento, foto_vehiculo, fecha_registro, registrado_por
            ) VALUES (
                :tipo_vehiculo, :id_marca, :placa, :modelo, :anio, :id_color, :kilometraje, :estado, :documento, :foto_vehiculo, NOW(), :registrado_por
            )");

            $stmt->bindParam(':tipo_vehiculo', $_POST['tipo_vehiculo']);
            $stmt->bindParam(':id_marca', $_POST['id_marca']);
            $stmt->bindParam(':placa', $placa);
            $stmt->bindParam(':modelo', $modelo);
            $stmt->bindParam(':anio', $anio);
            $stmt->bindParam(':id_color', $_POST['id_color']);
            $stmt->bindParam(':kilometraje', $kilometraje);
            $stmt->bindParam(':estado', $_POST['id_estado']);
            $stmt->bindParam(':documento', $_POST['documento']);
            $stmt->bindParam(':foto_vehiculo', $foto_vehiculo);
            $stmt->bindParam(':registrado_por', $_SESSION['documento']);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Vehículo agregado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar el vehículo']);
            }
            break;

        case 'editar':
            // En desarrollo
            break;

        case 'eliminar':
            // En desarrollo
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
