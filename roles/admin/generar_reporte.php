<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

// Incluir librerías para exportación
require_once('../../vendor/autoload.php');

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

// Función para generar reporte del dashboard
function generarReporteDashboard($con) {
    $datos = [];
    try {
        // Obtener estadísticas generales del dashboard
        $consultas = [
            ['label' => 'Total de Vehículos', 'query' => "SELECT COUNT(*) as cantidad FROM vehiculos"],
            ['label' => 'Vehículos al Día', 'query' => "SELECT COUNT(*) as cantidad FROM vehiculos WHERE id_estado = 10"],
            ['label' => 'Total de Usuarios', 'query' => "SELECT COUNT(*) as cantidad FROM usuarios"],
            ['label' => 'SOAT Vencido o por Vencer', 'query' => "SELECT COUNT(*) as cantidad FROM soat WHERE fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)"],
            ['label' => 'Tecnomecánica Activa', 'query' => "SELECT COUNT(*) as cantidad FROM tecnomecanica WHERE fecha_vencimiento > CURDATE()"],
            ['label' => 'Próximos Mantenimientos', 'query' => "SELECT COUNT(*) as cantidad FROM mantenimiento WHERE fecha_programada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND fecha_realizada IS NULL"],
            ['label' => 'Mantenimientos Pendientes', 'query' => "SELECT COUNT(*) as cantidad FROM mantenimiento WHERE fecha_realizada IS NULL"],
            ['label' => 'Licencias por Vencer', 'query' => "SELECT COUNT(*) as cantidad FROM licencias WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)"]
        ];
        
        foreach ($consultas as $consulta) {
            $stmt = $con->prepare($consulta['query']);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $datos[] = [
                'indicador' => $consulta['label'],
                'cantidad' => $resultado['cantidad'] ?? 0,
                'fecha_consulta' => date('Y-m-d H:i:s')
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error en consulta SQL para reporte dashboard: " . $e->getMessage());
        throw new Exception("Error en la consulta de base de datos");
    }
    return $datos;
}

// Función para exportar a PDF
function exportarPDF($datos, $titulo) {
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
            .number { text-align: right; font-weight: bold; color: #2c3e50; }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="title">FLOTAX AGC - ' . strtoupper($titulo) . '</div>
            <div class="subtitle">Reporte General del Dashboard</div>
            <div class="date">Fecha de Generación: ' . date('d/m/Y H:i:s') . '</div>
        </div>
        
        <table>';
    
    if (!empty($datos)) {
        $html .= '<thead><tr>
                    <th>Indicador</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Fecha de Consulta</th>
                  </tr></thead><tbody>';
        
        foreach ($datos as $fila) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($fila['indicador']) . '</td>
                        <td class="number">' . number_format($fila['cantidad']) . '</td>
                        <td class="text-center">' . htmlspecialchars($fila['fecha_consulta']) . '</td>
                      </tr>';
        }
        $html .= '</tbody>';
    } else {
        $html .= '<tr><td colspan="3" class="text-center">No hay datos para mostrar</td></tr>';
    }
    
    $html .= '</table>
        <div style="margin-top: 30px; font-size: 10px; color: #666;">
            <p>Total de indicadores: ' . count($datos) . '</p>
            <p>Generado por: Flotax AGC - Sistema de Gestión de Flota</p>
        </div>
    </body>
    </html>';
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    $filename = 'reporte_dashboard_' . date('Y-m-d_H-i-s') . '.pdf';
    $dompdf->stream($filename, array('Attachment' => true));
}

// Función para exportar a Excel
function exportarExcel($datos, $titulo) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configurar título
    $sheet->setCellValue('A1', 'FLOTAX AGC - ' . strtoupper($titulo));
    $sheet->mergeCells('A1:C1');
    $sheet->setCellValue('A2', 'Reporte General del Dashboard');
    $sheet->mergeCells('A2:C2');
    $sheet->setCellValue('A3', 'Fecha: ' . date('d/m/Y H:i:s'));
    $sheet->mergeCells('A3:C3');
    
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A1:A3')->getAlignment()->setHorizontal('center');

    if (!empty($datos)) {
        // Encabezados
        $sheet->setCellValue('A5', 'Indicador');
        $sheet->setCellValue('B5', 'Cantidad');
        $sheet->setCellValue('C5', 'Fecha de Consulta');
        
        $sheet->getStyle('A5:C5')->getFont()->setBold(true);
        $sheet->getStyle('A5:C5')->getFill()->setFillType('solid')->getStartColor()->setRGB('E8E8E8');

        // Datos
        $row = 6;
        foreach ($datos as $fila) {
            $sheet->setCellValue('A' . $row, $fila['indicador']);
            $sheet->setCellValue('B' . $row, $fila['cantidad']);
            $sheet->setCellValue('C' . $row, $fila['fecha_consulta']);
            $row++;
        }

        // Autoajustar columnas
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Bordes
        $lastRow = 5 + count($datos);
        $sheet->getStyle("A5:C{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
        
        // Formato de números
        $sheet->getStyle("B6:B{$lastRow}")->getAlignment()->setHorizontal('right');
    } else {
        $sheet->setCellValue('A5', 'No hay datos para mostrar');
    }

    $filename = 'reporte_dashboard_' . date('Y-m-d_H-i-s') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Función para exportar a CSV (mantener compatibilidad)
function exportarCSV($datos, $titulo) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_dashboard_' . date('Y-m-d_H-i-s') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Encabezado general
    fputcsv($output, ['==============================================']);
    fputcsv($output, ['FLOTAX AGC - ' . strtoupper($titulo)]);
    fputcsv($output, ['==============================================']);
    fputcsv($output, ['Fecha de Generación:', date('d/m/Y H:i:s')]);
    fputcsv($output, []); // Línea en blanco
    
    // Encabezado de tabla
    fputcsv($output, ['Indicador', 'Cantidad', 'Fecha de Consulta']);
    fputcsv($output, ['--------------------------', '---------', '-------------------']);
    
    // Datos
    foreach ($datos as $fila) {
        fputcsv($output, [$fila['indicador'], $fila['cantidad'], $fila['fecha_consulta']]);
    }
    
    fputcsv($output, []); // Línea final en blanco
    fputcsv($output, ['Total de indicadores: ' . count($datos)]);
    fputcsv($output, ['Generado por: Flotax AGC']);
    fclose($output);
    exit;
}

// Manejar la exportación
$formato = $_GET['formato'] ?? 'pdf'; // Por defecto PDF
$datos = generarReporteDashboard($con);
$titulo = 'Reporte General Dashboard';

switch ($formato) {
    case 'pdf':
        exportarPDF($datos, $titulo);
        break;
    case 'excel':
        exportarExcel($datos, $titulo);
        break;
    case 'csv':
        exportarCSV($datos, $titulo);
        break;
    default:
        exportarPDF($datos, $titulo);
        break;
}
?>