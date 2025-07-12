<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../conecct/conex.php';

$database = new Database();
$conexion = $database->conectar();

header('Content-Type: application/json');

try {
    // Verificar si existe la tabla sistema_licencias
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'sistema_licencias'");
    $stmt->execute();
    $tabla_existe = $stmt->rowCount() > 0;
    
    if (!$tabla_existe) {
        echo json_encode(['success' => false, 'message' => 'Tabla sistema_licencias no existe']);
        exit;
    }
    
    // Verificar si hay licencias
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM sistema_licencias");
    $stmt->execute();
    $total_licencias = $stmt->fetchColumn();
    
    // Obtener la primera licencia
    $stmt = $conexion->prepare("SELECT id, nombre_empresa, estado FROM sistema_licencias LIMIT 1");
    $stmt->execute();
    $licencia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar usuarios
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios WHERE id_rol != 3");
    $stmt->execute();
    $total_usuarios = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'message' => 'Prueba de conexión exitosa',
        'tabla_existe' => $tabla_existe,
        'total_licencias' => $total_licencias,
        'licencia_ejemplo' => $licencia,
        'total_usuarios' => $total_usuarios,
        'session_data' => [
            'documento' => $_SESSION['documento'],
            'tipo' => $_SESSION['tipo']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 