<?php
session_start();

// Verificar autenticación de superadmin
if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../conecct/conex.php';

$database = new Database();
$conexion = $database->conectar();

header('Content-Type: application/json');

try {
    // Verificar licencias vencidas
    $stmt = $conexion->prepare("
        SELECT id, nombre_empresa, fecha_vencimiento, estado 
        FROM sistema_licencias 
        WHERE fecha_vencimiento < CURDATE() 
        AND estado = 'activa'
    ");
    $stmt->execute();
    $licencias_vencidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $usuarios_desactivados = 0;
    
    foreach ($licencias_vencidas as $licencia) {
        // Cambiar estado de licencia a vencida
        $stmt_update = $conexion->prepare("
            UPDATE sistema_licencias 
            SET estado = 'vencida' 
            WHERE id = ?
        ");
        $stmt_update->execute([$licencia['id']]);
        
        // Desactivar usuarios (excepto superadmin)
        $stmt_usuarios = $conexion->prepare("
            UPDATE usuarios 
            SET id_estado_usuario = 2 
            WHERE id_rol != 3
        ");
        $stmt_usuarios->execute();
        
        $usuarios_desactivados += $stmt_usuarios->rowCount();
        
        // Registrar log
        $stmt_log = $conexion->prepare("
            INSERT INTO logs_sistema (usuario, accion, descripcion, fecha, ip_address) 
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt_log->execute([
            $_SESSION['documento'],
            'licencia_vencida',
            "Licencia ID {$licencia['id']} vencida automáticamente - Usuarios desactivados",
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
    
    // Verificar licencias próximas a vencer (30 días)
    $stmt_proximas = $conexion->prepare("
        SELECT id, nombre_empresa, fecha_vencimiento, estado 
        FROM sistema_licencias 
        WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND estado = 'activa'
    ");
    $stmt_proximas->execute();
    $licencias_proximas = $stmt_proximas->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Verificación completada',
        'licencias_vencidas' => count($licencias_vencidas),
        'usuarios_desactivados' => $usuarios_desactivados,
        'licencias_proximas' => count($licencias_proximas),
        'detalles_vencidas' => $licencias_vencidas,
        'detalles_proximas' => $licencias_proximas
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la verificación: ' . $e->getMessage()
    ]);
}
?> 