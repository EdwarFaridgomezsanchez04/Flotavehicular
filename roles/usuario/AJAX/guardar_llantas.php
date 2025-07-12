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
    $estado = trim($_POST['estado'] ?? '');
    $ultimo_cambio = trim($_POST['ultimo_cambio'] ?? '');
    $presion_llantas = trim($_POST['presion_llantas'] ?? '');
    $kilometraje_actual = trim($_POST['kilometraje_actual'] ?? '');
    $proximo_cambio_km = trim($_POST['proximo_cambio_km'] ?? '');
    $proximo_cambio_fecha = trim($_POST['proximo_cambio_fecha'] ?? '');
    $notas = trim($_POST['notas'] ?? '');

    if (empty($placa) || empty($estado) || empty($ultimo_cambio) || empty($presion_llantas) || empty($kilometraje_actual) || empty($proximo_cambio_km) || empty($proximo_cambio_fecha) || empty($notas)) {
        echo json_encode(['status' => 'error', 'message' => ' Faltan campos obligatorios.']);
        exit;
    }

    if ($kilometraje_actual < $proximo_cambio_km) {
        echo json_encode(['status' => 'error', 'message' => 'Proximo cambio debe ser mayor al kilometraje actual.']);
        exit;
    }
    
    if (!empty($ultimo_cambio)) {
        $date = new DateTime($ultimo_cambio);
        if ($date > new DateTime()){
            echo json_encode(['status' => 'error', 'message' => 'La fecha de último cambio no puede ser futura.']);
            exit;
        }   
    }

    if (!empty($presion_llantas) && (!is_numeric($presion_llantas) || $presion_llantas < 0.1 || $presion_llantas > 100.0)) {
        echo json_encode(['status' => 'error', 'message' => 'La presión debe estar entre 0.1 y 100.0 PSI.']);
        exit;
    }

    if (!empty($kilometraje_actual) && (!is_numeric($kilometraje_actual) || $kilometraje_actual < 0)) {
        echo json_encode(['status' => 'error', 'message' => 'El kilometraje actual debe ser un número positivo.']);
        exit;
    }

    if (!empty($proximo_cambio_km) && (!is_numeric($proximo_cambio_km) || $proximo_cambio_km < 0)) {
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

    if (!empty($notas) && (strlen($notas) > 500 || !preg_match('/^[a-zA-Z0-9\s.,!?\'-]+$/', $notas))) {
        echo json_encode(['status' => 'error', 'message' => 'Las notas deben tener máximo 500 caracteres y solo letras, números y puntuación básica.']);
        exit;
    }

    try {
        $sql = $con->prepare("INSERT INTO llantas (placa, estado, ultimo_cambio, presion_llantas, kilometraje_actual, proximo_cambio_km, proximo_cambio_fecha, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $resultado = $sql->execute([$placa, $estado, $ultimo_cambio, $presion_llantas, $kilometraje_actual, $proximo_cambio_km, $proximo_cambio_fecha, $notas]);

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' =>  'Revision de llantas guardado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la Revision de llantas.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }

}
