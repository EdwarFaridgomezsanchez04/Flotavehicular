<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

$db = new Database();
$con = $db->conectar();

// if ($_SESSION['tipo'] !== 1) {
//   header('Location:' . BASE_URL . '/includes/exit.php?motivo=acceso-denegado');
//   exit;
// }

// Consulta para contar vehículos por id_estado específico
$sql = $con->prepare("SELECT id_estado, COUNT(*) as cantidad 
    FROM vehiculos 
    WHERE id_estado IN (1,2,3)
    GROUP BY id_estado
");
$sql->execute();
$resultados = $sql->fetchAll(PDO::FETCH_ASSOC);

// Inicializa las cantidades en 0 por defecto
$cantidades = [1 => 0, 2 => 0, 3 => 0];

// Llena las cantidades según los resultados
foreach ($resultados as $row) {
    $cantidades[$row['id_estado']] = $row['cantidad'];
}

$cantidad_id1 = $cantidades[1];
$cantidad_id2 = $cantidades[2];
$cantidad_id3 = $cantidades[3];

// Definir los estados y sus etiquetas
$estados_labels = ['Activo', 'Mantenimiento', 'Inactivo'];
$estados_data = [$cantidad_id1, $cantidad_id2, $cantidad_id3];

// Resto de consultas...
$stmt = $con->prepare("SELECT COUNT(*) AS total FROM vehiculos");
$stmt->execute();
$total_vehiculos = $stmt->fetchColumn();

$stmt1 = $con->prepare("SELECT COUNT(*) AS total FROM usuarios ");
$stmt1->execute();
$total_usuarios = $stmt1->fetchColumn();

$stmt2 = $con->prepare("SELECT COUNT(*) AS total FROM vehiculos WHERE id_estado = 10 ");
$stmt2->execute();
$veh_dia = $stmt2->fetchColumn();

// Nueva consulta para vehículos que NO están al día
$stmt_no_dia = $con->prepare("SELECT COUNT(*) AS total FROM vehiculos WHERE id_estado != 10");
$stmt_no_dia->execute();
$veh_no_dia = $stmt_no_dia->fetchColumn();

$sql = "SELECT * FROM soat 
        WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$stmt3 = $con->prepare($sql);
$stmt3->execute();
$datos = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$sql = $con->prepare("SELECT COUNT(*) AS total
    FROM soat
    WHERE fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
");
$sql->execute();
$row = $sql->fetch(PDO::FETCH_ASSOC);
$soat_vencidos_o_por_vencer = $row['total'];

// RTM próximo a vencer
$sql_rtm = "SELECT * FROM tecnomecanica 
            WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$stmt_rtm = $con->prepare($sql_rtm);
$stmt_rtm->execute();
$datos_rtm = $stmt_rtm->fetchAll(PDO::FETCH_ASSOC);

$sql_rtm = $con->prepare("SELECT COUNT(*) AS total
    FROM tecnomecanica
    WHERE fecha_vencimiento >= CURDATE()
");
$sql_rtm->execute();
$row_rtm = $sql_rtm->fetch(PDO::FETCH_ASSOC);
$tecnomecanica_activa = $row_rtm['total'];

// Agregar esta consulta para contar próximos mantenimientos
$sql_prox_mant = $con->prepare("SELECT COUNT(*) AS total
    FROM mantenimiento
    WHERE proximo_cambio_fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
");
$sql_prox_mant->execute();
$row_prox_mant = $sql_prox_mant->fetch(PDO::FETCH_ASSOC);
$proximos_mantenimientos = $row_prox_mant['total'];

$sql_mantenimiento = "SELECT * FROM mantenimiento 
                      WHERE proximo_cambio_fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
$stmt_mant = $con->prepare($sql_mantenimiento);
$stmt_mant->execute();
$datos_mant = $stmt_mant->fetchAll(PDO::FETCH_ASSOC);

$sql_logs = "
    SELECT 'Vehículo' AS tipo, documento_usuario COLLATE utf8mb4_unicode_ci AS documento_usuario, placa_vehiculo COLLATE utf8mb4_unicode_ci AS dato, fecha_registro
    FROM registro_vehiculos_log

    UNION ALL

    SELECT 'Tecnomecánica' AS tipo, documento_usuario COLLATE utf8mb4_unicode_ci AS documento_usuario, placa_vehiculo COLLATE utf8mb4_unicode_ci AS dato, fecha_registro
    FROM tecnomecanica_log

    UNION ALL

    SELECT 'SOAT' AS tipo, documento_usuario COLLATE utf8mb4_unicode_ci AS documento_usuario, placa_vehiculo COLLATE utf8mb4_unicode_ci AS dato, fecha_registro
    FROM soat_log

    UNION ALL

    SELECT 'Mantenimiento' AS tipo, documento_usuario COLLATE utf8mb4_unicode_ci AS documento_usuario, placa_vehiculo COLLATE utf8mb4_unicode_ci AS dato, fecha_registro
    FROM mantenimiento_log

    UNION ALL

    SELECT 'Llantas' AS tipo, documento_usuario COLLATE utf8mb4_unicode_ci AS documento_usuario, placa_vehiculo COLLATE utf8mb4_unicode_ci AS dato, fecha_registro
    FROM llantas_log

    UNION ALL

    SELECT 'Licencia' AS tipo, documento_usuario COLLATE utf8mb4_unicode_ci AS documento_usuario, NULL AS dato, fecha_registro
    FROM licencia_log

    ORDER BY fecha_registro DESC
    LIMIT 5
";

$stmt_logs = $con->prepare($sql_logs);
$stmt_logs->execute();
$actividades = $stmt_logs->fetchAll(PDO::FETCH_ASSOC);


// Fecha actual para mostrar en el dashboard
$fecha_actual = date("d M Y");
$dia_semana = date("l");

$dias_es = [
  'Monday' => 'Lunes',
  'Tuesday' => 'Martes',
  'Wednesday' => 'Miércoles',
  'Thursday' => 'Jueves',
  'Friday' => 'Viernes',
  'Saturday' => 'Sábado',
  'Sunday' => 'Domingo'
];

$meses_es = [
  'Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr',
  'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago',
  'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'
];

$dia_semana_es = $dias_es[$dia_semana];
$fecha_es = date("d") . " " . $meses_es[date("M")] . " " . date("Y");

// Validación de sesión
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
  header('Location: ../../login.php');
  exit;
}

$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;

if (!$nombre_completo || !$foto_perfil) {
  $user_query = $con->prepare("SELECT * FROM usuarios WHERE documento = :documento");
  $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
  $user_query->execute();
  $user = $user_query->fetch(PDO::FETCH_ASSOC);
  
  $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
  $foto_perfil = $user['foto_perfil'] ?: 'css/img/perfil.jpg';
  
  $_SESSION['nombre_completo'] = $nombre_completo;
  $_SESSION['foto_perfil'] = $foto_perfil;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Panel de Administrador - Flotax AGC</title>
    <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>

    <?php include 'menu.php'; ?>

    <div class="content">
        <!-- Header del Dashboard -->
        <div class="dashboard-header">
            <div>
                <h1 class="dashboard-title">Panel de Control</h1>
                <p class="dashboard-subtitle"><?php echo $dia_semana_es . ', ' . $fecha_es; ?></p>
            </div>
            <div class="dashboard-actions">
                <button class="dashboard-btn" type="button" onclick="window.open('generar_reporte.php?formato=pdf', '_blank')">
                    <i class="bi bi-file-earmark-pdf"></i>
                    PDF
                </button>
                <button class="dashboard-btn" type="button" onclick="window.open('generar_reporte.php?formato=excel', '_blank')">
                    <i class="bi bi-file-earmark-excel"></i>
                    Excel
                </button>
                <button class="dashboard-btn" type="button" onclick="window.open('generar_reporte.php?formato=csv', '_blank')">
                    <i class="bi bi-file-text"></i>
                    CSV
                </button>
            
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="cards">
            <div class="card">
                <i class="bi bi-truck card-icon"></i>
                <h3>Vehículos Registrados</h3>
                <p><?php echo $total_vehiculos; ?></p>
                <div class="trend up">
                </div>
            </div>
            <div class="card">
                <i class="bi bi-people card-icon"></i>
                <h3>Usuarios</h3>
                <p><?php echo $total_usuarios; ?></p>
                <div class="trend up">
                </div>
            </div>
            <div class="card">
                <i class="bi bi-check-circle card-icon"></i>
                <h3>Vehículos al Día</h3>
                <p><?php echo $veh_dia; ?></p>
                <div class="trend up">
                </div>
            </div>
            <div class="card">
                <i class="bi bi-x-circle card-icon"></i>
                <h3>Vehículos que no están al Día</h3>
                <p><?php echo $veh_no_dia; ?></p>
                <div class="trend down">
                </div>
            </div>
            <div class="card">
                <i class="bi bi-exclamation-triangle card-icon"></i>
                <h3>SOAT Vencido o por Vencer</h3>
                <p><?php echo $soat_vencidos_o_por_vencer; ?></p>
                <div class="trend down">
                </div>
            </div>
            <div class="card">
                <i class="bi bi-clipboard-check card-icon"></i>
                <h3>Tecnomecánica Activa</h3>
                <p><?php echo $tecnomecanica_activa; ?></p>
                <div class="trend up">
                </div>
            </div>
            <div class="card">
                <i class="bi bi-tools card-icon"></i>
                <h3>Próximos Mantenimientos</h3>
                <p><?php echo $proximos_mantenimientos; ?></p>
                <div class="trend up">
                </div>
            </div>
        </div>

   
            <!-- Mostrar tabla de vencimientos SOAT -->
            <div class="calendar">
                <h3><i class="bi bi-calendar-event"></i> SOAT Próximos a Vencer</h3>
                <div class="calendar-events">
                    <?php if (!empty($datos)): ?>
                    <?php foreach ($datos as $row): ?>
                    <?php
                    $dias_restantes = (strtotime($row['fecha_vencimiento']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                    $mes = date('M', strtotime($row['fecha_vencimiento']));
                    $dia = date('d', strtotime($row['fecha_vencimiento']));
                    ?>
                    <div class="calendar-event">
                        <div class="event-date">
                            <span class="event-day"><?= $dia ?></span>
                            <span class="event-month"><?= $meses_es[$mes] ?></span>
                        </div>
                        <div class="event-content">
                            <div class="event-title">Vencimiento SOAT (<?= $dias_restantes ?> días)</div>
                            <div class="event-vehicle"><i class="bi bi-car-front"></i> Placa: <?= $row['id_placa'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="calendar-event">
                        <div class="event-date">
                            <span class="event-day"><i class="bi bi-info-circle"></i></span>
                            <span class="event-month"></span>
                        </div>
                        <div class="event-content">
                            <div class="event-title" style="color:#2563eb;">Ningún vehículo próximo a vencer</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Mostrar tabla de vencimientos RTM -->
            <div class="calendar">
                <h3><i class="bi bi-calendar-event"></i> Revisión Técnico-Mecánica Próxima a Vencer</h3>
                <div class="calendar-events">
                    <?php if (!empty($datos_rtm)): ?>
                    <?php foreach ($datos_rtm as $row): ?>
                    <?php
                    $dias_restantes = (strtotime($row['fecha_vencimiento']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                    $mes = date('M', strtotime($row['fecha_vencimiento']));
                    $dia = date('d', strtotime($row['fecha_vencimiento']));
                    ?>
                    <div class="calendar-event">
                        <div class="event-date">
                            <span class="event-day"><?= $dia ?></span>
                            <span class="event-month"><?= $meses_es[$mes] ?></span>
                        </div>
                        <div class="event-content">
                            <div class="event-title">Vencimiento RTM (<?= $dias_restantes ?> días)</div>
                            <div class="event-vehicle"><i class="bi bi-car-front"></i> Placa: <?= $row['id_placa'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="calendar-event">
                        <div class="event-content">
                            <div class="event-title text-danger">Ningún vehículo próximo a vencer</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mostrar tabla de mantenimientos programados -->
            <div class="calendar">
                <h3><i class="bi bi-calendar-event"></i> Próximos Mantenimientos</h3>
                <div class="calendar-events">
                    <?php if (!empty($datos_mant)):?>
                    <?php foreach ($datos_mant as $row): ?>
                    <?php
                    $dias_restantes = (strtotime($row['proximo_cambio_fecha']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
                    $mes = date('M', strtotime($row['proximo_cambio_fecha']));
                    $dia = date('d', strtotime($row['proximo_cambio_fecha']));
                    ?>
                    <div class="calendar-event">
                        <div class="event-date">
                            <span class="event-day"><?= $dia ?></span>
                            <span class="event-month"><?= $meses_es[$mes] ?></span>
                        </div>
                        <div class="event-content">
                            <div class="event-title">Mantenimiento Programado (<?= $dias_restantes ?> días)</div>
                            <div class="event-vehicle"><i class="bi bi-car-front"></i> Placa: <?= $row['placa'] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else:?>
                    <div class="calendar-event">
                        <div class="event-content">
                            <div class="event-title text-danger">No hay Proximos Mantenimientos</div>
                        </div>
                    <?php endif;?>
                </div>
            </div>

            <!-- Mostrar actividad reciente -->
            <div class="recent-activity">
                <h3><i class="bi bi-activity"></i> Actividad Reciente</h3>
                <div class="activity-list">
                 <?php foreach ($actividades as $actividad): ?>
    <?php
    $fecha_actividad = new DateTime($actividad['fecha_registro']);
    $hace_tiempo = $fecha_actividad->diff(new DateTime());

    if ($hace_tiempo->d > 0) {
        $tiempo = 'Hace ' . $hace_tiempo->d . ' día(s)';
    } elseif ($hace_tiempo->h > 0) {
        $tiempo = 'Hace ' . $hace_tiempo->h . ' hora(s)';
    } elseif ($hace_tiempo->i > 0) {
        $tiempo = 'Hace ' . $hace_tiempo->i . ' min';
    } else {
        $tiempo = 'Reciente';
    }

    $info = $actividad['tipo'] . ($actividad['dato'] ? " - Placa: {$actividad['dato']}" : '');
    ?>
    <div class="activity-item">
        <div class="activity-icon">
            <i class="bi bi-clock-history"></i>
        </div>
        <div class="activity-content">
            <div class="activity-title"><?= $info ?></div>
            <div class="activity-subtitle">Documento: <?= $actividad['documento_usuario'] ?></div>
        </div>
        <div class="activity-time"><?= $tiempo ?></div>
    </div>
<?php endforeach; ?>

                </div>
            </div>
        </div>
    </div>

    
</body>
</html>
