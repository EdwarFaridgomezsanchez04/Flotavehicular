<?php
session_start();
require_once('../../../conecct/conex.php');
include '../../../includes/validarsession.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['documento'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$db = new Database();
$con = $db->conectar();
$documento = $_SESSION['documento'];

// Clase para manejar alertas
class AlertasAPI {
    private $con;
    private $documento;

    public function __construct($conexion, $documento_usuario) {
        $this->con = $conexion;
        $this->documento = $documento_usuario;
    }

    // Obtener todas las alertas del usuario
    public function obtenerAlertas($filtros = []) {
        try {
            $sql = "
                SELECT n.id, n.mensaje, n.fecha, n.leido, u.nombre_completo, u.documento as documento_usuario
                FROM notificaciones n
                LEFT JOIN usuarios u ON n.documento_usuario = u.documento
                WHERE n.documento_usuario = :documento
            ";
            
            $params = [':documento' => $this->documento];
            
            // Aplicar filtros
            if (!empty($filtros['tipo'])) {
                $sql .= " AND LOWER(n.mensaje) LIKE :tipo";
                $params[':tipo'] = '%' . strtolower($filtros['tipo']) . '%';
            }
            
            if (!empty($filtros['estado'])) {
                if ($filtros['estado'] === 'leidas') {
                    $sql .= " AND n.leido = 1";
                } elseif ($filtros['estado'] === 'no_leidas') {
                    $sql .= " AND n.leido = 0";
                }
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND DATE(n.fecha) >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND DATE(n.fecha) <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            $sql .= " ORDER BY n.fecha DESC";
            
            if (!empty($filtros['limite'])) {
                $sql .= " LIMIT :limite";
                $params[':limite'] = (int)$filtros['limite'];
            } else {
                $sql .= " LIMIT 50";
            }
            
            $stmt = $this->con->prepare($sql);
            foreach ($params as $key => $value) {
                if ($key === ':limite') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            
            $alertas = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $alertas[] = $this->procesarAlerta($row);
            }
            
            return [
                'success' => true,
                'data' => $alertas,
                'total' => count($alertas)
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener alertas: ' . $e->getMessage()
            ];
        }
    }

    // Obtener una alerta específica
    public function obtenerAlerta($id) {
        try {
            $stmt = $this->con->prepare("
                SELECT n.id, n.mensaje, n.fecha, n.leido, u.nombre_completo, u.documento as documento_usuario
                FROM notificaciones n
                LEFT JOIN usuarios u ON n.documento_usuario = u.documento
                WHERE n.id = :id AND n.documento_usuario = :documento
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            
            $alerta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($alerta) {
                return [
                    'success' => true,
                    'data' => $this->procesarAlerta($alerta)
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Alerta no encontrada'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener alerta: ' . $e->getMessage()
            ];
        }
    }

    // Obtener detalles completos de una notificación
    public function obtenerDetalleNotificacion($id) {
        try {
            $stmt = $this->con->prepare("
                SELECT 
                    n.id, 
                    n.mensaje, 
                    n.fecha, 
                    n.leido, 
                    u.nombre_completo,
                    u.documento as documento_usuario
                FROM notificaciones n
                LEFT JOIN usuarios u ON n.documento_usuario = u.documento
                WHERE n.id = :id AND n.documento_usuario = :documento
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            
            $notificacion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($notificacion) {
                return [
                    'success' => true,
                    'data' => $notificacion
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Notificación no encontrada'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener detalles de notificación: ' . $e->getMessage()
            ];
        }
    }

    // Marcar alerta como leída
    public function marcarComoLeida($id) {
        try {
            $stmt = $this->con->prepare("
                UPDATE notificaciones 
                SET leido = 1 
                WHERE id = :id AND documento_usuario = :documento
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Alerta marcada como leída'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Alerta no encontrada o ya leída'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al marcar alerta: ' . $e->getMessage()
            ];
        }
    }

    // Marcar todas las alertas como leídas
    public function marcarTodasLeidas() {
        try {
            $stmt = $this->con->prepare("
                UPDATE notificaciones 
                SET leido = 1 
                WHERE documento_usuario = :documento AND leido = 0
            ");
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Todas las alertas han sido marcadas como leídas',
                'actualizadas' => $stmt->rowCount()
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al marcar alertas: ' . $e->getMessage()
            ];
        }
    }

    // Eliminar alerta
    public function eliminarAlerta($id) {
        try {
            $stmt = $this->con->prepare("
                DELETE FROM notificaciones 
                WHERE id = :id AND documento_usuario = :documento
            ");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Alerta eliminada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Alerta no encontrada'
                ];
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al eliminar alerta: ' . $e->getMessage()
            ];
        }
    }

    // Obtener estadísticas de alertas
    public function obtenerEstadisticas() {
        try {
            // Total de alertas
            $stmt = $this->con->prepare("
                SELECT COUNT(*) as total 
                FROM notificaciones 
                WHERE documento_usuario = :documento
            ");
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Alertas leídas
            $stmt = $this->con->prepare("
                SELECT COUNT(*) as leidas 
                FROM notificaciones 
                WHERE documento_usuario = :documento AND leido = 1
            ");
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            $leidas = $stmt->fetch(PDO::FETCH_ASSOC)['leidas'];

            // Alertas no leídas
            $stmt = $this->con->prepare("
                SELECT COUNT(*) as no_leidas 
                FROM notificaciones 
                WHERE documento_usuario = :documento AND leido = 0
            ");
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            $no_leidas = $stmt->fetch(PDO::FETCH_ASSOC)['no_leidas'];

            // Alertas de este mes
            $stmt = $this->con->prepare("
                SELECT COUNT(*) as este_mes 
                FROM notificaciones 
                WHERE documento_usuario = :documento 
                AND MONTH(fecha) = MONTH(CURRENT_DATE()) 
                AND YEAR(fecha) = YEAR(CURRENT_DATE())
            ");
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            $este_mes = $stmt->fetch(PDO::FETCH_ASSOC)['este_mes'];

            // Alertas críticas (que contengan palabras clave)
            $stmt = $this->con->prepare("
                SELECT COUNT(*) as criticas 
                FROM notificaciones 
                WHERE documento_usuario = :documento 
                AND (LOWER(mensaje) LIKE '%vencido%' 
                     OR LOWER(mensaje) LIKE '%urgente%' 
                     OR LOWER(mensaje) LIKE '%crítico%')
            ");
            $stmt->bindParam(':documento', $this->documento);
            $stmt->execute();
            $criticas = $stmt->fetch(PDO::FETCH_ASSOC)['criticas'];

            return [
                'success' => true,
                'data' => [
                    'total' => (int)$total,
                    'leidas' => (int)$leidas,
                    'no_leidas' => (int)$no_leidas,
                    'este_mes' => (int)$este_mes,
                    'criticas' => (int)$criticas,
                    'porcentaje_leidas' => $total > 0 ? round(($leidas / $total) * 100, 2) : 0
                ]
            ];
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ];
        }
    }

    // Procesar alerta para el frontend
    private function procesarAlerta($row) {
        $tipo = $this->categorizarNotificacion($row['mensaje']);
        $placa = $this->extraerPlaca($row['mensaje']);
        $prioridad = $this->determinarPrioridad($row['mensaje'], $tipo);
        $estado = $this->determinarEstado($row['mensaje'], $row['leido']);

        return [
            'id' => $row['id'],
            'tipo' => ucfirst($tipo),
            'vehiculo' => $placa,
            'descripcion' => $row['mensaje'],
            'fecha_alerta' => $row['fecha'],
            'fecha_vencimiento' => null,
            'prioridad' => $prioridad,
            'estado' => $estado,
            'leido' => (bool)$row['leido'],
            'detalles' => $row['mensaje'],
            'usuario' => $row['nombre_completo'] ?? 'Sistema',
            'documento_usuario' => $row['documento_usuario']
        ];
    }

    // Función para categorizar notificaciones
    private function categorizarNotificacion($mensaje) {
        $mensaje_lower = strtolower($mensaje);

        if (strpos($mensaje_lower, 'soat') !== false) {
            return 'soat';
        } elseif (strpos($mensaje_lower, 'técnico-mecánica') !== false || strpos($mensaje_lower, 'tecnomecanica') !== false) {
            return 'tecnomecanica';
        } elseif (strpos($mensaje_lower, 'mantenimiento') !== false) {
            return 'mantenimiento';
        } elseif (strpos($mensaje_lower, 'licencia') !== false) {
            return 'licencia';
        } elseif (strpos($mensaje_lower, 'llantas') !== false) {
            return 'llantas';
        } elseif (strpos($mensaje_lower, 'pico y placa') !== false) {
            return 'pico_placa';
        } elseif (strpos($mensaje_lower, 'multa') !== false) {
            return 'multa';
        } elseif (strpos($mensaje_lower, 'registrado') !== false) {
            return 'registro';
        } else {
            return 'general';
        }
    }

    // Función para extraer placa del mensaje
    private function extraerPlaca($mensaje) {
        if (preg_match('/\b[A-Z]{3}[0-9]{3}\b|\b[A-Z]{3}[0-9]{2}[A-Z]\b|\b[A-Z]{2}[0-9]{4}\b/i', $mensaje, $matches)) {
            return strtoupper($matches[0]);
        }
        return 'N/A';
    }

    // Función para determinar prioridad
    private function determinarPrioridad($mensaje, $tipo) {
        $mensaje_lower = strtolower($mensaje);

        if (strpos($mensaje_lower, 'vence') !== false || strpos($mensaje_lower, 'vencido') !== false) {
            return 'alta';
        } elseif (strpos($mensaje_lower, 'próximo') !== false || strpos($mensaje_lower, 'programado') !== false) {
            return 'media';
        } elseif ($tipo === 'registro' || $tipo === 'general') {
            return 'baja';
        } else {
            return 'media';
        }
    }

    // Función para determinar estado
    private function determinarEstado($mensaje, $leido) {
        if ($leido) {
            return 'informativa';
        }

        $mensaje_lower = strtolower($mensaje);

        if (strpos($mensaje_lower, 'vencido') !== false || strpos($mensaje_lower, 'urgente') !== false) {
            return 'critica';
        } elseif (strpos($mensaje_lower, 'vence') !== false || strpos($mensaje_lower, 'próximo') !== false) {
            return 'pendiente';
        } else {
            return 'informativa';
        }
    }
}

// Instanciar la API
$api = new AlertasAPI($con, $documento);

// Manejar las peticiones
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'listar':
                    $filtros = [
                        'tipo' => $_GET['tipo'] ?? '',
                        'estado' => $_GET['estado'] ?? '',
                        'fecha_desde' => $_GET['fecha_desde'] ?? '',
                        'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                        'limite' => $_GET['limite'] ?? 50
                    ];
                    echo json_encode($api->obtenerAlertas($filtros));
                    break;
                    
                case 'obtener':
                    $id = $_GET['id'] ?? null;
                    if ($id) {
                        echo json_encode($api->obtenerAlerta($id));
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID requerido']);
                    }
                    break;
                    
                case 'detalle':
                    $id = $_GET['id'] ?? null;
                    if ($id) {
                        echo json_encode($api->obtenerDetalleNotificacion($id));
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID requerido']);
                    }
                    break;
                    
                // Obtener detalles de una alerta por ID (compatibilidad)
                case 'detalle_alertas':
                    $id = intval($_GET['id']);
                    $query = "SELECT * FROM notificaciones WHERE id = $id AND documento_usuario = '$documento'";
                    $result = $con->query($query);

                    if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        echo json_encode([
                            'success' => true,
                            'data' => $row
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'error' => 'No se encontró la alerta.'
                        ]);
                    }
                    exit;
                    
                case 'estadisticas':
                    echo json_encode($api->obtenerEstadisticas());
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            }
            break;
            
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            
            switch ($action) {
                case 'marcar_leida':
                    $id = $input['id'] ?? null;
                    if ($id) {
                        echo json_encode($api->marcarComoLeida($id));
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID requerido']);
                    }
                    break;
                    
                case 'marcar_todas_leidas':
                    echo json_encode($api->marcarTodasLeidas());
                    break;
                    
                case 'eliminar':
                    $id = $input['id'] ?? null;
                    if ($id) {
                        echo json_encode($api->eliminarAlerta($id));
                    } else {
                        echo json_encode(['success' => false, 'error' => 'ID requerido']);
                    }
                    break;
                    
                default:
                    echo json_encode(['success' => false, 'error' => 'Acción no válida']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?> 