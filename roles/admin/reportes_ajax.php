<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

header('Content-Type: application/json');

$db = new Database();
$con = $db->conectar();

// Validación de sesión
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida']);
    exit;
}

// Manejar solicitudes POST para acciones específicas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener_estados_vehiculo':
            $sql = "SELECT DISTINCT id_estado, estado FROM estado_vehiculo ORDER BY estado ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($estados);
            exit;
            
        case 'obtener_marcas':
            $sql = "SELECT DISTINCT id_marca, nombre_marca FROM marca ORDER BY nombre_marca ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($marcas);
            exit;
            
        case 'obtener_tipos_vehiculo':
            $sql = "SELECT DISTINCT id_tipo_vehiculo, vehiculo FROM tipo_vehiculo ORDER BY vehiculo ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tipos);
            exit;
            
        case 'obtener_usuarios':
            $sql = "SELECT DISTINCT documento, nombre_completo FROM usuarios WHERE id_estado_usuario NOT IN (1, 3) ORDER BY nombre_completo ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($usuarios);
            exit;
            
        case 'obtener_anios':
            $sql = "SELECT DISTINCT año FROM vehiculos ORDER BY año DESC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $anios = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode($anios);
            exit;
            
        case 'obtener_tipos_mantenimiento':
            $sql = "SELECT DISTINCT id_tipo_mantenimiento, descripcion FROM tipo_mantenimiento ORDER BY descripcion ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $tipos_mant = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tipos_mant);
            exit;
            
        case 'obtener_categorias_licencia':
            $sql = "SELECT DISTINCT id_categoria, nombre_categoria FROM categoria_licencia ORDER BY nombre_categoria ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($categorias);
            exit;
            
        case 'obtener_servicios_licencia':
            $sql = "SELECT DISTINCT id_servicio, nombre_servicios FROM servicios_licencias ORDER BY nombre_servicios ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($servicios);
            exit;
            
        case 'obtener_companias_soat':
            $sql = "SELECT DISTINCT id_asegura, nombre FROM aseguradoras_soat ORDER BY nombre ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $companias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($companias);
            exit;
            
        case 'obtener_centros_revision':
            $sql = "SELECT DISTINCT id_centro, centro_revision FROM centro_rtm ORDER BY centro_revision ASC";
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $centros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($centros);
            exit;
    }
}

// Obtener parámetros para reportes GET
$tipo = $_GET['tipo'] ?? '';
$filtros = json_decode($_GET['filtros'] ?? '{}', true);

if (empty($tipo)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de reporte no especificado']);
    exit;
}

try {
    $datos = generarReporte($con, $tipo, $filtros);
    echo json_encode([
        'success' => true,
        'datos' => $datos,
        'total' => count($datos),
        'filtros_aplicados' => $filtros
    ]);
} catch (Exception $e) {
    error_log("Error en reporte AJAX: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}

function generarReporte($con, $tipo, $filtros = []) {
    $datos = [];
    try {
        switch ($tipo) {
           case 'vehiculos':
     $sql = "SELECT v.placa, 
                   m.nombre_marca as marca, 
                   v.modelo, 
                   v.kilometraje_actual, 
                   e.estado as estado, 
                   v.fecha_registro, 
                   u.nombre_completo as usuario_responsable,
                   tv.vehiculo as tipo_vehiculo,
                   v.año
            FROM vehiculos v 
            LEFT JOIN usuarios u ON v.Documento = u.documento 
            LEFT JOIN marca m ON v.id_marca = m.id_marca
            LEFT JOIN estado_vehiculo e ON v.id_estado = e.id_estado
            LEFT JOIN tipo_vehiculo tv ON v.tipo_vehiculo = tv.id_tipo_vehiculo
            WHERE 1=1";
    $params = [];
    if (!empty($filtros['estado'])) {
        $sql .= " AND e.estado = :estado";
        $params[':estado'] = $filtros['estado'];
    }
    if (!empty($filtros['placa'])) {
        $sql .= " AND v.placa LIKE :placa";
        $params[':placa'] = '%' . $filtros['placa'] . '%';
    }
    if (!empty($filtros['marca'])) {
        $sql .= " AND v.id_marca = :marca";
        $params[':marca'] = $filtros['marca'];
    }
    if (!empty($filtros['tipo_vehiculo'])) {
        $sql .= " AND v.tipo_vehiculo = :tipo_vehiculo";
        $params[':tipo_vehiculo'] = $filtros['tipo_vehiculo'];
    }
    if (!empty($filtros['anio'])) {
        $sql .= " AND v.año = :anio";
        $params[':anio'] = $filtros['anio'];
    }
    if (!empty($filtros['usuario'])) {
        $sql .= " AND v.Documento = :usuario";
        $params[':usuario'] = $filtros['usuario'];
    }
    if (!empty($filtros['fecha_desde'])) {
        $sql .= " AND v.fecha_registro >= :fecha_desde";
        $params[':fecha_desde'] = $filtros['fecha_desde'];
    }
    if (!empty($filtros['fecha_hasta'])) {
        $sql .= " AND v.fecha_registro <= :fecha_hasta";
        $params[':fecha_hasta'] = $filtros['fecha_hasta'];
    }
    $sql .= " ORDER BY v.fecha_registro DESC LIMIT 1000";
    break;

            case 'mantenimientos':
                $sql = "SELECT m.id_mantenimiento, m.placa, tm.descripcion as tipo_mantenimiento,
                               m.kilometraje_actual, m.observaciones, m.fecha_programada,
                               m.fecha_realizada, m.proximo_cambio_fecha,
                               CASE 
                                   WHEN m.fecha_realizada IS NULL THEN 'Pendiente'
                                   ELSE 'Realizado'
                               END as estado,
                               u.nombre_completo as responsable
                        FROM mantenimiento m
                        LEFT JOIN tipo_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
                        LEFT JOIN vehiculos v ON m.placa = v.placa
                        LEFT JOIN usuarios u ON v.Documento = u.documento
                        WHERE 1=1";
                $params = [];
                if (!empty($filtros['placa'])) {
                    $sql .= " AND m.placa LIKE :placa";
                    $params[':placa'] = '%' . $filtros['placa'] . '%';
                }
                if (!empty($filtros['estado'])) {
                    if ($filtros['estado'] == 'pendiente') {
                        $sql .= " AND m.fecha_realizada IS NULL";
                    } elseif ($filtros['estado'] == 'realizado') {
                        $sql .= " AND m.fecha_realizada IS NOT NULL";
                    }
                }
                if (!empty($filtros['tipo_mantenimiento'])) {
                    $sql .= " AND m.id_tipo_mantenimiento = :tipo_mantenimiento";
                    $params[':tipo_mantenimiento'] = $filtros['tipo_mantenimiento'];
                }
                if (!empty($filtros['responsable'])) {
                    $sql .= " AND v.Documento = :responsable";
                    $params[':responsable'] = $filtros['responsable'];
                }
                if (!empty($filtros['kilometraje_min'])) {
                    $sql .= " AND m.kilometraje_actual >= :kilometraje_min";
                    $params[':kilometraje_min'] = $filtros['kilometraje_min'];
                }
                if (!empty($filtros['kilometraje_max'])) {
                    $sql .= " AND m.kilometraje_actual <= :kilometraje_max";
                    $params[':kilometraje_max'] = $filtros['kilometraje_max'];
                }
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND m.fecha_programada >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND m.fecha_programada <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY m.fecha_programada DESC LIMIT 1000";
                break;

            case 'llantas':
                $sql = "SELECT l.id_llanta, l.placa, l.ultimo_cambio, l.estado,
                               l.presion_llantas, l.kilometraje_actual, l.proximo_cambio_fecha,
                               l.notas, u.nombre_completo as usuario_responsable
                        FROM llantas l
                        LEFT JOIN vehiculos v ON l.placa = v.placa
                        LEFT JOIN usuarios u ON v.Documento = u.documento
                        WHERE 1=1";
                $params = [];
                if (!empty($filtros['placa'])) {
                    $sql .= " AND l.placa LIKE :placa";
                    $params[':placa'] = '%' . $filtros['placa'] . '%';
                }
                if (!empty($filtros['estado'])) {
                    $sql .= " AND l.estado = :estado";
                    $params[':estado'] = $filtros['estado'];
                }
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND l.ultimo_cambio >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND l.ultimo_cambio <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY l.ultimo_cambio DESC LIMIT 1000";
                break;

            case 'soat':
                $sql = "SELECT s.id_soat, s.id_placa, s.fecha_expedicion, s.fecha_vencimiento,
                               a.nombre as compania_aseguradora, 
                               CASE 
                                   WHEN s.fecha_vencimiento > CURDATE() THEN 'Vigente'
                                   ELSE 'Vencido'
                               END as estado,
                               DATEDIFF(s.fecha_vencimiento, CURDATE()) as dias_restantes,
                               u.nombre_completo as usuario_responsable
                        FROM soat s
                        LEFT JOIN vehiculos v ON s.id_placa = v.placa
                        LEFT JOIN usuarios u ON v.Documento = u.documento
                        LEFT JOIN aseguradoras_soat a ON s.id_aseguradora = a.id_asegura
                        WHERE 1=1";
                $params = [];
                if (!empty($filtros['placa'])) {
                    $sql .= " AND s.id_placa LIKE :placa";
                    $params[':placa'] = '%' . $filtros['placa'] . '%';
                }
                if (!empty($filtros['estado'])) {
                    if ($filtros['estado'] == 'vigente') {
                        $sql .= " AND s.fecha_vencimiento > CURDATE()";
                    } elseif ($filtros['estado'] == 'vencido') {
                        $sql .= " AND s.fecha_vencimiento <= CURDATE()";
                    } elseif ($filtros['estado'] == 'proximo_vencer') {
                        $sql .= " AND s.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                    }
                }
                if (!empty($filtros['compania'])) {
                    $sql .= " AND s.id_aseguradora = :compania";
                    $params[':compania'] = $filtros['compania'];
                }
                if (!empty($filtros['usuario'])) {
                    $sql .= " AND v.Documento = :usuario";
                    $params[':usuario'] = $filtros['usuario'];
                }
                if (!empty($filtros['expedicion_desde'])) {
                    $sql .= " AND s.fecha_expedicion >= :expedicion_desde";
                    $params[':expedicion_desde'] = $filtros['expedicion_desde'];
                }
                if (!empty($filtros['expedicion_hasta'])) {
                    $sql .= " AND s.fecha_expedicion <= :expedicion_hasta";
                    $params[':expedicion_hasta'] = $filtros['expedicion_hasta'];
                }
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND s.fecha_vencimiento >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND s.fecha_vencimiento <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY s.fecha_vencimiento ASC LIMIT 1000";
                break;

            case 'tecnomecanica':
                $sql = "SELECT t.id_rtm, t.id_placa, t.fecha_expedicion, t.fecha_vencimiento,
                               c.centro_revision as centro_diagnostico,
                               CASE 
                                   WHEN t.fecha_vencimiento > CURDATE() THEN 'Vigente'
                                   ELSE 'Vencido'
                               END as estado,
                               DATEDIFF(t.fecha_vencimiento, CURDATE()) as dias_restantes,
                               COALESCE(u.nombre_completo, 'Usuario no encontrado') as usuario_responsable
                        FROM tecnomecanica t
                        LEFT JOIN vehiculos v ON t.id_placa = v.placa
                        LEFT JOIN usuarios u ON v.Documento = u.documento
                        LEFT JOIN centro_rtm c ON t.id_centro_revision = c.id_centro
                        WHERE 1=1";
                $params = [];
                if (!empty($filtros['placa'])) {
                    $sql .= " AND t.id_placa LIKE :placa";
                    $params[':placa'] = '%' . $filtros['placa'] . '%';
                }
                if (!empty($filtros['estado'])) {
                    if ($filtros['estado'] == 'vigente') {
                        $sql .= " AND t.fecha_vencimiento > CURDATE()";
                    } elseif ($filtros['estado'] == 'vencido') {
                        $sql .= " AND t.fecha_vencimiento <= CURDATE()";
                    } elseif ($filtros['estado'] == 'proximo_vencer') {
                        $sql .= " AND t.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                    }
                }
                if (!empty($filtros['centro'])) {
                    $sql .= " AND t.id_centro_revision = :centro";
                    $params[':centro'] = $filtros['centro'];
                }
                if (!empty($filtros['usuario'])) {
                    $sql .= " AND v.Documento = :usuario";
                    $params[':usuario'] = $filtros['usuario'];
                }
                if (!empty($filtros['expedicion_desde'])) {
                    $sql .= " AND t.fecha_expedicion >= :expedicion_desde";
                    $params[':expedicion_desde'] = $filtros['expedicion_desde'];
                }
                if (!empty($filtros['expedicion_hasta'])) {
                    $sql .= " AND t.fecha_expedicion <= :expedicion_hasta";
                    $params[':expedicion_hasta'] = $filtros['expedicion_hasta'];
                }
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND t.fecha_vencimiento >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND t.fecha_vencimiento <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY t.fecha_vencimiento ASC LIMIT 1000";
                break;

            case 'licencias':
                $sql = "SELECT l.id_licencia, l.id_documento, l.fecha_expedicion, l.fecha_vencimiento,
                               c.nombre_categoria as categoria, s.nombre_servicios as servicio,
                               CASE 
                                   WHEN l.fecha_vencimiento > CURDATE() THEN 'Vigente' 
                                   ELSE 'Vencido' 
                               END as estado,
                               DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes,
                               u.nombre_completo as usuario_responsable, l.observaciones
                        FROM licencias l
                        LEFT JOIN categoria_licencia c ON l.id_categoria = c.id_categoria
                        LEFT JOIN servicios_licencias s ON l.id_servicio = s.id_servicio
                        LEFT JOIN usuarios u ON l.id_documento = u.documento
                        WHERE 1=1";
                $params = [];
                if (!empty($filtros['documento'])) {
                    $sql .= " AND l.id_documento LIKE :documento";
                    $params[':documento'] = '%' . $filtros['documento'] . '%';
                }
                if (!empty($filtros['estado'])) {
                    if ($filtros['estado'] == 'vigente') {
                        $sql .= " AND l.fecha_vencimiento > CURDATE()";
                    } elseif ($filtros['estado'] == 'vencido') {
                        $sql .= " AND l.fecha_vencimiento <= CURDATE()";
                    } elseif ($filtros['estado'] == 'proximo_vencer') {
                        $sql .= " AND l.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
                    }
                }
                if (!empty($filtros['categoria'])) {
                    $sql .= " AND l.id_categoria = :categoria";
                    $params[':categoria'] = $filtros['categoria'];
                }
                if (!empty($filtros['servicio'])) {
                    $sql .= " AND l.id_servicio = :servicio";
                    $params[':servicio'] = $filtros['servicio'];
                }
                if (!empty($filtros['usuario'])) {
                    $sql .= " AND l.id_documento = :usuario";
                    $params[':usuario'] = $filtros['usuario'];
                }
                if (!empty($filtros['expedicion_desde'])) {
                    $sql .= " AND l.fecha_expedicion >= :expedicion_desde";
                    $params[':expedicion_desde'] = $filtros['expedicion_desde'];
                }
                if (!empty($filtros['expedicion_hasta'])) {
                    $sql .= " AND l.fecha_expedicion <= :expedicion_hasta";
                    $params[':expedicion_hasta'] = $filtros['expedicion_hasta'];
                }
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND l.fecha_vencimiento >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND l.fecha_vencimiento <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY l.fecha_vencimiento ASC LIMIT 1000";
                break;
                case 'alertas':
    $sql = "SELECT n.id,
                   n.mensaje as descripcion,
                   n.fecha,
                   n.leido,
                   u.nombre_completo as usuario_responsable,
                   CASE 
                       WHEN n.leido = 1 THEN 'Leído'
                       ELSE 'No leído'
                   END as estado
            FROM notificaciones n
            LEFT JOIN usuarios u ON n.documento_usuario = u.documento
            WHERE 1=1";
                $params = [];
                if (!empty($filtros['leido'])) {
                    if ($filtros['leido'] == 'si') {
                        $sql .= " AND n.leido = 1";
                    } elseif ($filtros['leido'] == 'no') {
                        $sql .= " AND n.leido = 0";
                    }
                }
                if (!empty($filtros['usuario'])) {
                    $sql .= " AND n.documento_usuario = :usuario";
                    $params[':usuario'] = $filtros['usuario'];
                }
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND n.fecha >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND n.fecha <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY n.fecha DESC LIMIT 1000";
                break;

               case 'actividad':
                // Simulación de log de actividades (ajustar según tu estructura de BD)
                $sql = "SELECT 'Vehículo' as tipo_actividad, 
                               CONCAT('Registro de vehículo: ', v.placa) as descripcion,
                               v.fecha_registro as fecha,
                               u.nombre_completo as usuario_responsable,
                               v.placa as referencia
                        FROM vehiculos v
                        LEFT JOIN usuarios u ON v.Documento = u.documento
                        UNION ALL
                        SELECT 'Mantenimiento' as tipo_actividad,
                               CONCAT('Mantenimiento programado: ', m.placa) as descripcion,
                               m.fecha_programada as fecha,
                               resp.nombre_completo as usuario_responsable,
                               m.placa as referencia
                        FROM mantenimiento m
                        LEFT JOIN vehiculos v_m ON m.placa = v_m.placa
                        LEFT JOIN usuarios resp ON v_m.Documento = resp.documento
                        UNION ALL
                        SELECT 'SOAT' as tipo_actividad,
                               CONCAT('SOAT registrado: ', s.id_placa) as descripcion,
                               s.fecha_expedicion as fecha,
                               resp_s.nombre_completo as usuario_responsable,
                               s.id_placa as referencia
                        FROM soat s
                        LEFT JOIN vehiculos v_s ON s.id_placa = v_s.placa
                        LEFT JOIN usuarios resp_s ON v_s.Documento = resp_s.documento
                        UNION ALL
                        SELECT 'Tecnomecánica' as tipo_actividad,
                               CONCAT('Tecnomecánica registrada: ', t.id_placa) as descripcion,
                               t.fecha_expedicion as fecha,
                               resp_t.nombre_completo as usuario_responsable,
                               t.id_placa as referencia
                        FROM tecnomecanica t
                        LEFT JOIN vehiculos v_t ON t.id_placa = v_t.placa
                        LEFT JOIN usuarios resp_t ON v_t.Documento = resp_t.documento
                        UNION ALL
                        SELECT 'Licencia' as tipo_actividad,
                               CONCAT('Licencia registrada: ', l.id_documento) as descripcion,
                               l.fecha_expedicion as fecha,
                               resp_l.nombre_completo as usuario_responsable,
                               l.id_documento as referencia
                        FROM licencias l
                        LEFT JOIN usuarios resp_l ON l.id_documento = resp_l.documento";
                $params = [];
                if (!empty($filtros['tipo'])) {
                    $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE tipo_actividad = :tipo";
                    $params[':tipo'] = $filtros['tipo'];
                }
                if (!empty($filtros['usuario'])) {
                    $whereClause = !empty($filtros['tipo']) ? " AND " : " WHERE ";
                    if (empty($filtros['tipo'])) {
                        $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE usuario_responsable LIKE :usuario";
                    } else {
                        $sql .= " AND usuario_responsable LIKE :usuario";
                    }
                    $params[':usuario'] = '%' . $filtros['usuario'] . '%';
                }
                if (!empty($filtros['referencia'])) {
                    $whereClause = (!empty($filtros['tipo']) || !empty($filtros['usuario'])) ? " AND " : " WHERE ";
                    if (empty($filtros['tipo']) && empty($filtros['usuario'])) {
                        $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE referencia LIKE :referencia";
                    } else {
                        $sql .= " AND referencia LIKE :referencia";
                    }
                    $params[':referencia'] = '%' . $filtros['referencia'] . '%';
                }
                if (!empty($filtros['fecha_desde_actividad'])) {
                    $whereClause = (!empty($filtros['tipo']) || !empty($filtros['usuario']) || !empty($filtros['referencia'])) ? " AND " : " WHERE ";
                    if (empty($filtros['tipo']) && empty($filtros['usuario']) && empty($filtros['referencia'])) {
                        $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE fecha >= :fecha_desde_actividad";
                    } else {
                        $sql .= " AND fecha >= :fecha_desde_actividad";
                    }
                    $params[':fecha_desde_actividad'] = $filtros['fecha_desde_actividad'];
                }
                if (!empty($filtros['fecha_hasta_actividad'])) {
                    $whereClause = (!empty($filtros['tipo']) || !empty($filtros['usuario']) || !empty($filtros['referencia']) || !empty($filtros['fecha_desde_actividad'])) ? " AND " : " WHERE ";
                    if (empty($filtros['tipo']) && empty($filtros['usuario']) && empty($filtros['referencia']) && empty($filtros['fecha_desde_actividad'])) {
                        $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE fecha <= :fecha_hasta_actividad";
                    } else {
                        $sql .= " AND fecha <= :fecha_hasta_actividad";
                    }
                    $params[':fecha_hasta_actividad'] = $filtros['fecha_hasta_actividad'];
                }
                $sql .= " ORDER BY fecha DESC LIMIT 1000";
                break;

            default:
                throw new Exception("Tipo de reporte no válido: " . $tipo);
        }

        $stmt = $con->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Error en consulta SQL para reporte $tipo: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
        throw new Exception("Error en la consulta de base de datos: " . $e->getMessage());
    }
    return $datos;
}
?>
