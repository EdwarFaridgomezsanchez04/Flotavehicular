<?php
header('Content-Type: application/json; charset=utf-8');
require_once('../../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();

if (isset($_POST['placa'])) {
    $placa = $_POST['placa'];
    $ultimo_digito = substr($placa, -1);

    try {
        $stmt = $con->prepare("SELECT DISTINCT dia, digitos_restringidos FROM pico_placa WHERE digitos_restringidos LIKE ?");
        $stmt->execute(["%$ultimo_digito%"]);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No se recibió ninguna placa']);
}
