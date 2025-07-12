<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

// Incluir librerías para exportación
require_once('../../vendor/autoload.php'); // Composer autoload para DomPDF y PhpSpreadsheet

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$db = new Database();
$con = $db->conectar();

// Validación de sesión
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Obtener datos del usuario
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;

if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: 'roles/user/css/img/perfil.jpg';
    
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
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
                   tv.vehiculo as tipo_vehiculo
            FROM vehiculos v 
            LEFT JOIN usuarios u ON v.Documento = u.documento 
            LEFT JOIN marca m ON v.id_marca = m.id_marca
            LEFT JOIN estado_vehiculo e ON v.id_estado = e.id_estado
            LEFT JOIN tipo_vehiculo tv ON v.tipo_vehiculo = tv.id_tipo_vehiculo
            WHERE 1=1";
    $params = [];
    if (!empty($filtros['estado'])) {
        $sql .= " AND v.id_estado = :estado";
        $params[':estado'] = $filtros['estado'];
    }
    if (!empty($filtros['placa'])) {
        $sql .= " AND v.placa LIKE :placa";
        $params[':placa'] = '%' . $filtros['placa'] . '%';
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
                $sql = "SELECT m.id_mantenimiento, m.placa, m.fecha_programada, m.fecha_realizada,
                               tm.descripcion as tipo_mantenimiento, m.kilometraje_actual,
                               m.observaciones, m.proximo_cambio_km, m.proximo_cambio_fecha,
                               u.nombre_completo as usuario_responsable,
                               CASE WHEN m.fecha_realizada IS NOT NULL THEN 'Realizado' ELSE 'Pendiente' END as estado
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
                if (!empty($filtros['tipo'])) {
                    $sql .= " AND m.id_tipo_mantenimiento = :tipo";
                    $params[':tipo'] = $filtros['tipo'];
                }
                if (!empty($filtros['estado'])) {
                    if ($filtros['estado'] == 'realizado') {
                        $sql .= " AND m.fecha_realizada IS NOT NULL";
                    } else {
                        $sql .= " AND m.fecha_realizada IS NULL";
                    }
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
                               a.nombre as aseguradora, s.id_estado,
                               CASE 
                                   WHEN s.fecha_vencimiento > CURDATE() THEN 'Vigente' 
                                   ELSE 'Vencido' 
                               END as estado,
                               DATEDIFF(s.fecha_vencimiento, CURDATE()) as dias_restantes,
                               u.nombre_completo as usuario_responsable
                        FROM soat s
                        LEFT JOIN aseguradoras_soat a ON s.id_aseguradora = a.id_asegura
                        LEFT JOIN vehiculos v ON s.id_placa = v.placa
                        LEFT JOIN usuarios u ON v.Documento = u.documento
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
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND s.fecha_expedicion >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND s.fecha_expedicion <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY s.fecha_vencimiento ASC LIMIT 1000";
                break;

            case 'tecnomecanica':
                $sql = "SELECT t.id_rtm, t.id_placa, t.fecha_expedicion, t.fecha_vencimiento,
                               c.centro_revision,
                               CASE 
                                   WHEN t.fecha_vencimiento > CURDATE() THEN 'Vigente' 
                                   ELSE 'Vencido' 
                               END as estado,
                               DATEDIFF(t.fecha_vencimiento, CURDATE()) as dias_restantes,
                               u.nombre_completo as usuario_responsable
                        FROM tecnomecanica t
                        LEFT JOIN centro_rtm c ON t.id_centro_revision = c.id_centro
                        LEFT JOIN vehiculos v ON t.id_placa = v.placa
                        LEFT JOIN usuarios u ON v.Documento = u.documento
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
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND t.fecha_expedicion >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND t.fecha_expedicion <= :fecha_hasta";
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
                if (!empty($filtros['fecha_desde'])) {
                    $sql .= " AND l.fecha_expedicion >= :fecha_desde";
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $sql .= " AND l.fecha_expedicion <= :fecha_hasta";
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
                }
                $sql .= " ORDER BY l.fecha_vencimiento ASC LIMIT 1000";
                break;
case 'alertas':
    $sql = "SELECT 'SOAT' as tipo_alerta,
                   v.placa,
                   s.fecha_vencimiento,
                   DATEDIFF(s.fecha_vencimiento, CURDATE()) as dias_restantes,
                   'SOAT próximo a vencer' as descripcion
            FROM soat s
            JOIN vehiculos v ON s.id_placa = v.placa
            WHERE s.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            SELECT 'Tecnomecánica' as tipo_alerta,
                   v.placa,
                   t.fecha_vencimiento,
                   DATEDIFF(t.fecha_vencimiento, CURDATE()) as dias_restantes,
                   'Tecnomecánica próxima a vencer' as descripcion
            FROM tecnomecanica t
            JOIN vehiculos v ON t.id_placa = v.placa
            WHERE t.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            SELECT 'Licencia' as tipo_alerta,
                   'N/A' as placa,
                   l.fecha_vencimiento,
                   DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes,
                   CONCAT('Licencia de ', u.nombre_completo, ' próxima a vencer') as descripcion
            FROM licencias l
            JOIN usuarios u ON l.id_documento = u.documento
            WHERE l.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    $params = [];
    if (!empty($filtros['tipo'])) {
        $sql = "SELECT * FROM (" . $sql . ") as alertas WHERE tipo_alerta LIKE :tipo";
        $params[':tipo'] = '%' . $filtros['tipo'] . '%';
    }
    if (!empty($filtros['fecha_desde'])) {
        $whereClause = empty($filtros['tipo']) ? " WHERE " : " AND ";
        $sql .= $whereClause . "fecha_vencimiento >= :fecha_desde";
        $params[':fecha_desde'] = $filtros['fecha_desde'];
    }
    if (!empty($filtros['fecha_hasta'])) {
        $whereClause = (empty($filtros['tipo']) && empty($filtros['fecha_desde'])) ? " WHERE " : " AND ";
        $sql .= $whereClause . "fecha_vencimiento <= :fecha_hasta";
        $params[':fecha_hasta'] = $filtros['fecha_hasta'];
    }
    $sql .= " ORDER BY dias_restantes ASC LIMIT 1000";
    break;
            // --- Eliminado el case 'multas' y toda su lógica ---
// ... existing code ...
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
                        LEFT JOIN usuarios resp_s ON v_s.Documento = resp_s.documento";
                $params = [];
                if (!empty($filtros['tipo'])) {
                    $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE tipo_actividad LIKE :tipo";
                    $params[':tipo'] = '%' . $filtros['tipo'] . '%';
                }
                $whereClause = !empty($filtros['tipo']) ? " AND " : " WHERE ";
                if (!empty($filtros['fecha_desde'])) {
                    if (empty($filtros['tipo'])) {
                        $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE fecha >= :fecha_desde";
                    } else {
                        $sql .= " AND fecha >= :fecha_desde";
                    }
                    $params[':fecha_desde'] = $filtros['fecha_desde'];
                }
                if (!empty($filtros['fecha_hasta'])) {
                    $connector = (!empty($filtros['tipo']) || !empty($filtros['fecha_desde'])) ? " AND " : " WHERE ";
                    if (empty($filtros['tipo']) && empty($filtros['fecha_desde'])) {
                        $sql = "SELECT * FROM (" . $sql . ") as actividades WHERE fecha <= :fecha_hasta";
                    } else {
                        $sql .= " AND fecha <= :fecha_hasta";
                    }
                    $params[':fecha_hasta'] = $filtros['fecha_hasta'];
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
        throw new Exception("Error en la consulta de base de datos");
    }
    return $datos;
}



// Función para exportar a PDF
function exportarPDF($datos, $tipo, $titulo) {
    $options = new Options();
    $options->set('defaultFont', 'Arial');
    $options->set('isRemoteEnabled', true);
    
    $dompdf = new Dompdf($options);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            .header { text-align: center; margin-bottom: 20px; }
            .title { font-size: 18px; font-weight: bold; color: #333; }
            .subtitle { font-size: 14px; color: #666; margin-top: 5px; }
            .date { font-size: 10px; color: #999; margin-top: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .status-vigente { color: #27ae60; font-weight: bold; }
            .status-vencido { color: #e74c3c; font-weight: bold; }
            .status-proximo { color: #f39c12; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">FLOTAX AGC - ' . strtoupper($titulo) . '</div>
            <div class="subtitle">Reporte Generado</div>
            <div class="date">Fecha: ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <table>';
    
    if (!empty($datos)) {
        // Generar encabezados
        $html .= '<thead><tr>';
        foreach (array_keys($datos[0]) as $columna) {
            $html .= '<th>' . ucfirst(str_replace('_', ' ', $columna)) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        
        // Generar filas
        foreach ($datos as $fila) {
            $html .= '<tr>';
            foreach ($fila as $key => $valor) {
                $clase = '';
                if ($key == 'estado') {
                    if (strtolower($valor) == 'vigente') $clase = 'status-vigente';
                    elseif (strtolower($valor) == 'vencido') $clase = 'status-vencido';
                    elseif (strpos(strtolower($valor), 'próximo') !== false) $clase = 'status-proximo';
                }
                $html .= '<td class="' . $clase . '">' . htmlspecialchars($valor ?? '') . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
    } else {
        $html .= '<tr><td colspan="100%" class="text-center">No hay datos para mostrar</td></tr>';
    }
    
    $html .= '</table>
        <div style="margin-top: 30px; font-size: 10px; color: #666;">
            <p>Total de registros: ' . count($datos) . '</p>
            <p>Generado por: Flotax AGC - Sistema de Gestión de Flota</p>
        </div>
    </body>
    </html>';
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    $filename = 'reporte_' . $tipo . '_' . date('Y-m-d_H-i-s') . '.pdf';
    $dompdf->stream($filename, array('Attachment' => true));
}

// Función para exportar a Excel
function exportarExcel($datos, $tipo, $titulo) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configurar título
    $sheet->setCellValue('A1', 'FLOTAX AGC - ' . strtoupper($titulo));

    $colCount = !empty($datos) && !empty($datos[0]) ? count($datos[0]) : 1;
    $lastCol = chr(65 + $colCount - 1);

    if ($colCount > 1) {
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue("A2", 'Fecha: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle("A1")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A2")->getAlignment()->setHorizontal('center');
    }

    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

    if (!empty($datos)) {
        // Encabezados
        $col = 'A';
        $row = 4;
        foreach (array_keys($datos[0]) as $columna) {
            $sheet->setCellValue($col . $row, ucfirst(str_replace('_', ' ', $columna)));
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()->setFillType('solid')->getStartColor()->setRGB('E8E8E8');
            $col++;
        }

        // Datos
        $row = 5;
        foreach ($datos as $fila) {
            $col = 'A';
            foreach ($fila as $valor) {
                $sheet->setCellValue($col . $row, $valor ?? '');
                $col++;
            }
            $row++;
        }

        // Autoajustar columnas
        foreach (range('A', chr(65 + $colCount - 1)) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bordes
        $lastRow = 4 + count($datos);
        $sheet->getStyle("A4:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
    } else {
        $sheet->setCellValue('A4', 'No hay datos para mostrar');
    }

    $filename = 'reporte_' . $tipo . '_' . date('Y-m-d_H-i-s') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Manejar exportaciones
if (isset($_GET['exportar']) && isset($_GET['tipo']) && isset($_GET['formato'])) {
    $tipo = $_GET['tipo'];
    $formato = $_GET['formato'];
    $filtros = $_GET['filtros'] ?? [];
    
    // Asegurarse de que los filtros sean un array
    if (!is_array($filtros)) {
        $filtros = [];
    }
    
    $datos = generarReporte($con, $tipo, $filtros);
    $titulo = ucfirst(str_replace('_', ' ', $tipo));
    
    if ($formato == 'pdf') {
        exportarPDF($datos, $tipo, $titulo);
    } elseif ($formato == 'excel') {
        exportarExcel($datos, $tipo, $titulo);
    }
    exit;
}

// Obtener estadísticas generales
try {
    // Estadísticas de vehículos
    $stmt = $con->query("SELECT COUNT(*) as total FROM vehiculos");
    $total_vehiculos = $stmt->fetch()['total'];
    
    $stmt = $con->query("SELECT COUNT(*) as total FROM vehiculos WHERE id_estado = '1'");
    $vehiculos_activos = $stmt->fetch()['total'];
    
    // Estadísticas de documentación próxima a vencer
    $stmt = $con->query("SELECT COUNT(*) as total FROM soat WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $soat_proximo_vencer = $stmt->fetch()['total'];
    
    $stmt = $con->query("SELECT COUNT(*) as total FROM tecnomecanica WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $tecno_proximo_vencer = $stmt->fetch()['total'];
    
    $stmt = $con->query("SELECT COUNT(*) as total FROM licencias WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $licencias_proximo_vencer = $stmt->fetch()['total'];
    
    // Estadísticas de mantenimientos
    $stmt = $con->query("SELECT COUNT(*) as total FROM mantenimiento WHERE fecha_realizada IS NULL");
    $mantenimientos_pendientes = $stmt->fetch()['total'];
    
    $stmt = $con->query("SELECT COUNT(*) as total FROM mantenimiento WHERE fecha_programada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND fecha_realizada IS NULL");
$prontos_mantenimientos = $stmt->fetch()['total'];
    
} catch (PDOException $e) {
    $total_vehiculos = 0;
    $vehiculos_activos = 0;
    $soat_proximo_vencer = 0;
    $tecno_proximo_vencer = 0;
    $licencias_proximo_vencer = 0;
    $mantenimientos_pendientes = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Módulo de Reportes - Flotax AGC</title>
    <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="css/reportes.css" />
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'menu.php'; ?>

    <div class="content">
        <!-- Header de la página -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-graph-up"></i>
                    Módulo de Reportes
                </h1>
                <p class="page-subtitle">Sistema integral de reportes y análisis de datos</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-outline-primary" onclick="actualizarEstadisticas()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Actualizar
                </button>
            </div>
        </div>

        <!-- Estadísticas generales -->
        <div class="stats-overview">
            <div class="stat-card vehiculos">
                <div class="stat-icon">
                    <i class="bi bi-car-front"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $total_vehiculos ?></div>
                    <div class="stat-label">Total Vehículos</div>
                    <div class="stat-sublabel"><?= $vehiculos_activos ?> activos</div>
                </div>
            </div>
            
            <div class="stat-card documentos">
                <div class="stat-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $soat_proximo_vencer + $tecno_proximo_vencer + $licencias_proximo_vencer ?></div>
                    <div class="stat-label">Docs. por Vencer</div>
                    <div class="stat-sublabel">Próximos 30 días</div>
                </div>
            </div>
            
            <div class="stat-card mantenimientos">
                <div class="stat-icon">
                    <i class="bi bi-tools"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?= $mantenimientos_pendientes ?></div>
                    <div class="stat-label">Mantenimientos</div>
                    <div class="stat-sublabel">Pendientes</div>
                </div>
            </div>
        

        <!-- Secciones de reportes -->
        <div class="reports-grid">
            <!-- Reporte de Vehículos -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon vehiculos">
                        <i class="bi bi-car-front"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reportes de Vehículos</h3>
                        <p>Estado general, uso y documentación asociada</p>
                        <p></p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('vehiculos')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('vehiculos', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('vehiculos', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>

            <!-- Reporte de Mantenimientos -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon mantenimientos">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reportes de Mantenimientos</h3>
                        <p>Control de mantenimientos realizados y próximos</p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('mantenimientos')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('mantenimientos', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('mantenimientos', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>

            <!-- Reporte de Llantas -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon llantas">
                        <i class="bi bi-circle"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reportes de Llantas</h3>
                        <p>Estado, presión, cambios recientes y próximos</p>
                        <p></p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('llantas')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('llantas', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('llantas', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>

            <!-- Reporte de SOAT -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon soat">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reportes de SOAT</h3>
                        <p>Control de vigencias y vencimientos próximos</p>
                        <p></p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('soat')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('soat', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('soat', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>

            <!-- Reporte de Tecnomecánica -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon tecnomecanica">
                        <i class="bi bi-gear"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reportes de Tecnomecánica</h3>
                        <p>Control de revisiones técnico-mecánicas</p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('tecnomecanica')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('tecnomecanica', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('tecnomecanica', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>

            <!-- Reporte de Licencias -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon licencias">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reportes de Licencias</h3>
                        <p>Control de licencias de conducción</p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('licencias')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('licencias', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('licencias', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>

            <!-- Reporte de Alertas -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon alertas">
                        <i class="bi bi-bell"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reportes de Alertas</h3>
                        <p>Auditoría de notificaciones automáticas</p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('alertas')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('alertas', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('alertas', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>

   


            <!-- Reporte de Actividad General -->
            <div class="report-card">
                <div class="report-header">
                    <div class="report-icon actividad">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="report-title">
                        <h3>Reporte de Actividad General</h3>
                        <p>Timeline de actividades del sistema</p>
                    </div>
                </div>
                <div class="report-actions">
                    <button class="btn btn-primary btn-sm" onclick="abrirReporte('actividad')">
                        <i class="bi bi-eye"></i> Ver Reporte
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="exportarReporte('actividad', 'excel')">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="exportarReporte('actividad', 'pdf')">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

   <!-- Modal mejorado para mostrar reportes -->
    <div class="modal fade" id="modalReporte" data-bs-show="false" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="modalReporteLabel">
                        <i class="bi bi-graph-up me-2"></i>
                        <span id="tituloReporte">Reporte</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Filtros del reporte mejorados -->
                    <div class="filters-container">
                        <div class="row g-3" id="filtrosReporte">
                            <!-- Filtros dinámicos se cargan aquí -->
                        </div>
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button class="btn btn-outline-secondary btn-sm me-2" onclick="limpiarFiltrosReporte()">
                                    <i class="bi bi-arrow-clockwise"></i> Limpiar Filtros
                                </button>
                                <button class="btn btn-primary btn-sm" onclick="aplicarFiltrosReporte()">
                                    <i class="bi bi-search"></i> Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contenido del reporte -->
                    <div class="report-content" id="contenidoReporte">
                        <div class="loading-container">
                       
                            <div class="loading-text">Preparando reporte...</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cerrar
                    </button>
                    <button type="button" class="btn btn-success" onclick="exportarReporteActual('excel')">
                        <i class="bi bi-file-earmark-excel me-1"></i>
                        Exportar Excel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="exportarReporteActual('pdf')">
                        <i class="bi bi-file-earmark-pdf me-1"></i>
                        Exportar PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/reportes.js"></script>
</body>
</html>
