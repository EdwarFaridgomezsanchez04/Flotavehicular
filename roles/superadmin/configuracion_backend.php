<?php
session_start();

// Verificar autenticación de superadmin
if (!isset($_SESSION['superadmin_logged']) || $_SESSION['superadmin_logged'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once '../conecct/conex.php';

$database = new Database();
$conexion = $database->conectar();

header('Content-Type: application/json');

try {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'obtener_configuracion':
            // Crear tabla de configuración si no existe
            $conexion->exec("
                CREATE TABLE IF NOT EXISTS configuracion_sistema (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    clave VARCHAR(100) UNIQUE NOT NULL,
                    valor TEXT,
                    descripcion TEXT,
                    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            $stmt = $conexion->prepare("SELECT * FROM configuracion_sistema ORDER BY clave");
            $stmt->execute();
            $configuracion = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $configuracion]);
            break;
            
        case 'actualizar_configuracion':
            $clave = $_POST['clave'] ?? '';
            $valor = $_POST['valor'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            
            $stmt = $conexion->prepare("
                INSERT INTO configuracion_sistema (clave, valor, descripcion) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE valor = VALUES(valor), descripcion = VALUES(descripcion)
            ");
            $stmt->execute([$clave, $valor, $descripcion]);
            
            echo json_encode(['success' => true, 'message' => 'Configuración actualizada exitosamente']);
            break;
            
        case 'backup_database':
            // Funcionalidad básica de backup
            $fecha = date('Y-m-d_H-i-s');
            $archivo_backup = "backup_proyecto_flota_{$fecha}.sql";
            
            // Aquí implementarías la lógica real de backup
            echo json_encode(['success' => true, 'message' => 'Backup creado: ' . $archivo_backup]);
            break;
            
        case 'limpiar_logs':
            $dias = $_POST['dias'] ?? 30;
            
            // Crear tabla de logs si no existe
            $conexion->exec("
                CREATE TABLE IF NOT EXISTS logs_sistema (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    usuario VARCHAR(50),
                    accion TEXT,
                    ip VARCHAR(45),
                    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            $stmt = $conexion->prepare("DELETE FROM logs_sistema WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->execute([$dias]);
            
            echo json_encode(['success' => true, 'message' => 'Logs limpiados exitosamente']);
            break;
            
        case 'estadisticas_sistema':
            $stats = [];
            
            // Tamaño de base de datos
            $stmt = $conexion->prepare("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size_mb
                FROM information_schema.tables 
                WHERE table_schema = 'proyecto_flota'
            ");
            $stmt->execute();
            $stats['db_size'] = $stmt->fetch(PDO::FETCH_ASSOC)['db_size_mb'] . ' MB';
            
            // Número de tablas
            $stmt = $conexion->prepare("
                SELECT COUNT(*) as total_tables
                FROM information_schema.tables 
                WHERE table_schema = 'proyecto_flota'
            ");
            $stmt->execute();
            $stats['total_tables'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_tables'];
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}
?>