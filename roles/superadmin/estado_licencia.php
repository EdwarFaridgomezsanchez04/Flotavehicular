<?php
session_start();
require_once '../../conecct/conex.php';

if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    http_response_code(403);
    echo "Acceso no autorizado";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_licencia = $_POST['id_licencia'] ?? null;
    $estado_actual = $_POST['estado_actual'] ?? null;

    if (!$id_licencia || !$estado_actual) {
        http_response_code(400);
        echo "Parámetros inválidos";
        exit;
    }

    try {
        $db = new Database();
        $conexion = $db->conectar();

        // Validar estado actual y calcular el nuevo estado
        $nuevo_estado = match ($estado_actual) {
            'activa'     => 'suspendida',
            'suspendida', 'vencida' => 'activa',
            default      => 'activa'
        };

        $stmt = $conexion->prepare("UPDATE sistema_licencias SET estado = :nuevo_estado, fecha_actualizacion = NOW() WHERE id = :id");
        $stmt->execute([
            ':nuevo_estado' => $nuevo_estado,
            ':id' => $id_licencia
        ]);

        header("Location: licenciamiento.php");
        exit;
    } catch (PDOException $e) {
        error_log("Error al cambiar estado de licencia: " . $e->getMessage());
        echo "Error en el servidor";
        exit;
    }
}
?>
