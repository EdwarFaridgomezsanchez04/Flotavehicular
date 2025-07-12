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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $placa = $_POST['placa'] ?? '';
    $centro = $_POST['centro'] ?? '';
    $fecha_exp = $_POST['fechaExpedicion'] ?? '';
    $fecha_ven = $_POST['fechaVencimiento'] ?? '';
    $estado = $_POST['estado'] ?? '';

    if (empty($placa) || empty($centro) || empty($fecha_exp) || empty($fecha_ven) || empty($estado)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    // Verificar si ya existe un registro exacto
    $verificar = $con->prepare("SELECT COUNT(*) FROM tecnomecanica WHERE id_placa = ? AND fecha_expedicion = ? AND fecha_vencimiento = ?");
    $verificar->execute([$placa, $fecha_exp, $fecha_ven]);
    if ($verificar->fetchColumn() > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Ya ha registrado un Tecnomecanica con estas fechas, no se puede duplicar.']);
        exit;
    }

    $nuevaFechaExp = new DateTime($fecha_exp);

    if ($estado == 2) {
        // Buscar el Tecnomecanica vigente actual (estado diferente de 2)
        $consultaVigente = $con->prepare("SELECT fecha_expedicion FROM tecnomecanica WHERE id_placa = ? AND id_estado != 2 ORDER BY fecha_expedicion DESC LIMIT 1");
        $consultaVigente->execute([$placa]);
        $soatVigente = $consultaVigente->fetch(PDO::FETCH_ASSOC);

        if ($soatVigente) {
            $fechaExpVigente = new DateTime($soatVigente['fecha_expedicion']);
            if ($nuevaFechaExp >= $fechaExpVigente) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No puede registrar una tecnomecanica vencido con fecha igual o posterior al Tecnomecanica vigente (' . $fechaExpVigente->format('Y-m-d') . ').'
                ]);
                exit;
            }
        }
    } else {
        // Si no es estado 2, la nueva fecha debe ser posterior al vencimiento del último SOAT
        $consultaUltimo = $con->prepare("SELECT fecha_vencimiento FROM tecnomecanica WHERE id_placa = ? ORDER BY fecha_expedicion DESC LIMIT 1");
        $consultaUltimo->execute([$placa]);
        $ultimo = $consultaUltimo->fetch(PDO::FETCH_ASSOC);

        if ($ultimo) {
            $ultimaFechaVen = new DateTime($ultimo['fecha_vencimiento']);
            if ($nuevaFechaExp <= $ultimaFechaVen) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'La nueva fecha de expedición debe ser posterior a la fecha de vencimiento del último Tecnomecanica registrado para esta placa (' . $ultimaFechaVen->format('Y-m-d') . ').'
                ]);
                exit;
            }
        }
    }

    try {
        $sql = $con->prepare("INSERT INTO tecnomecanica (id_placa, id_centro_revision, fecha_expedicion, fecha_vencimiento, id_estado) VALUES (?, ?, ?, ?, ?)");
        $resultado = $sql->execute([$placa, $centro, $fecha_exp, $fecha_ven, $estado]);

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Tecnomecanica guardado correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el SOAT.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>
