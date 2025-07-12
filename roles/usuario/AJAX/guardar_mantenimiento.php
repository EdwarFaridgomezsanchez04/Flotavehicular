<?php
session_start();
require_once('../../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();
include '../../../includes/validarsession.php';

header('Content-Type: application/json');

$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró la sesión del usuario.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = trim($_POST['placa'] ?? '');
    $id_tipo_mantenimiento = trim($_POST['id_tipo_mantenimiento'] ?? '');
    $fecha_programada = trim($_POST['fecha_programada'] ?? '');
    $fecha_realizada = trim($_POST['fecha_realizada'] ?? '');
    $kilometraje_actual = trim($_POST['kilometraje_actual'] ?? '');
    $proximo_cambio_km = trim($_POST['proximo_cambio_km'] ?? '');
    $proximo_cambio_fecha = trim($_POST['proximo_cambio_fecha'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (empty($placa) || empty($id_tipo_mantenimiento) || empty($fecha_programada) || empty($kilometraje_actual) || empty($proximo_cambio_km) || empty($proximo_cambio_fecha) || empty($observaciones)) {
        echo json_encode(['status' => 'error', 'message' => ' Faltan algunos campos obligatorios.']);
        exit;
    }

    if (!empty($fecha_realizada)) {
        $date = new DateTime($fecha_realizada);
        if ($date > new DateTime()) {
            echo json_encode(['status' => 'error', 'message' => 'La fecha realizada no puede ser futura.']);
            exit;
        }
    }

    if (!is_numeric($kilometraje_actual) || $kilometraje_actual < 0) {
        echo json_encode(['status' => 'error', 'message' => 'El kilometraje actual debe ser un número positivo.']);
        exit;
    }

    if (!is_numeric($proximo_cambio_km) || $proximo_cambio_km < 0) {
        echo json_encode(['status' => 'error', 'message' => 'El próximo cambio (km) debe ser un número positivo.']);
        exit;
    }

    if (!empty($proximo_cambio_fecha)) {
        $date = new DateTime($proximo_cambio_fecha);
        if ($date < new DateTime()) {
            echo json_encode(['status' => 'error', 'message' => 'La fecha de próximo cambio no puede ser pasada.']);
            exit;
        }
    }

    if (strlen($observaciones) > 500 || !preg_match('/^[a-zA-Z0-9\s.,!?\'-]+$/', $observaciones)) {
        echo json_encode(['status' => 'error', 'message' => 'Las observaciones deben tener máximo 500 caracteres y solo letras, números y puntuación básica.']);
        exit;
    }

    try {
        $sql = $con->prepare("INSERT INTO mantenimiento (placa, id_tipo_mantenimiento, fecha_programada, fecha_realizada, observaciones, kilometraje_actual, proximo_cambio_km, proximo_cambio_fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $resultado = $sql->execute([$placa, $id_tipo_mantenimiento, $fecha_programada, $fecha_realizada, $observaciones, $kilometraje_actual, $proximo_cambio_km, $proximo_cambio_fecha]);

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Mantenimiento guardado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el mantenimiento.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
