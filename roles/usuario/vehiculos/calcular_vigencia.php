<?php
require_once('../../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();

header('Content-Type: application/json');

if (!isset($_POST['id_categoria'], $_POST['fecha_nacimiento'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id_categoria = intval($_POST['id_categoria']);
$fecha_nacimiento = $_POST['fecha_nacimiento'];

try {
    // Obtener la edad mínima y vigencia desde la tabla vigencia_categoria_servicio
    $query = $con->prepare("SELECT edad_minima, vigencia_años FROM vigencia_categoria_servicio WHERE id_categoria = :id_categoria");
    $query->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount() === 0) {
        echo json_encode(['error' => 'No se encontró información de vigencia para esta categoría']);
        exit;
    }

    $datos = $query->fetch(PDO::FETCH_ASSOC);
    $edad_minima = intval($datos['edad_minima']);
    $vigencia_años = intval($datos['vigencia_años']);

    // Calcular la edad actual de la persona
    $fecha_nacimiento_dt = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $edad_actual = $hoy->diff($fecha_nacimiento_dt)->y;

    if ($edad_actual < $edad_minima) {
        echo json_encode(['error' => "No cumple con la edad mínima requerida de {$edad_minima} años. Actualmente tiene {$edad_actual} años."]);
        exit;
    }

    echo json_encode(['vigencia_años' => $vigencia_años]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}
