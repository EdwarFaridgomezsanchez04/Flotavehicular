<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['documento'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

require_once '../conecct/conex.php';

$database = new Database();
$conexion = $database->conectar();

header('Content-Type: application/json');

// Prueba simple de conexión
try {
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM sistema_licencias");
    $stmt->execute();
    $total = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'message' => 'Backend funcionando correctamente',
        'total_licencias' => $total,
        'session_data' => [
            'documento' => $_SESSION['documento'] ?? 'no',
            'tipo' => $_SESSION['tipo'] ?? 'no',
            'superadmin_logged' => $_SESSION['superadmin_logged'] ?? 'no'
        ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 