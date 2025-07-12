<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

$db = new Database();
$con = $db->conectar();

// Validación de sesión
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Fetch nombre_completo y foto_perfil si no están en sesión
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

// Función para obtener el icono según el tipo de evento
function getEventIcon($tipo)
{
    switch (strtolower($tipo)) {
        case 'mantenimiento':
            return 'bi-tools';
        case 'licencia':
            return 'bi-card-text';
        case 'llantas':
            return 'bi-circle';
        case 'soat':
            return 'bi-shield-check';
        case 'tecnomecanica':
            return 'bi-gear';
        case 'registro':
            return 'bi-plus-circle';
        default:
            return 'bi-circle';
    }
}

// Función para formatear fechas
function formatearFecha($fecha)
{
    return date('d/m/Y H:i', strtotime($fecha));
}

// Función para obtener el nombre del usuario
function obtenerNombreUsuario($con, $documento)
{
    if (!$documento) return 'Sistema';

    $query = $con->prepare("SELECT nombre_completo FROM usuarios WHERE documento = :documento");
    $query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['nombre_completo'] : 'Usuario Desconocido';
}

// Función para obtener nombre de aseguradora
function obtenerAseguradora($con, $id_aseguradora)
{
    if (!$id_aseguradora) return 'N/A';

    $query = $con->prepare("SELECT nombre FROM aseguradoras_soat WHERE id_asegura = :id");
    $query->bindParam(':id', $id_aseguradora, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['nombre'] : 'N/A';
}

// Función para obtener centro de revisión
function obtenerCentroRevision($con, $id_centro)
{
    if (!$id_centro) return 'N/A';

    $query = $con->prepare("SELECT centro_revision FROM centro_rtm WHERE id_centro = :id");
    $query->bindParam(':id', $id_centro, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    return $result ? $result['centro_revision'] : 'N/A';
}

// Obtener todos los registros de las tablas log
$historial = [];

try {
    // 1. Log de registros de usuarios
    $query_registros = $con->prepare("
        SELECT 
            'registro' as tipo,
            fecha_registro as fecha,
            'N/A' as vehiculo,
            CONCAT('Nuevo usuario registrado: ', email_usuario) as descripcion,
            descripcion as detalles,
            documento_usuario,
            'completado' as estado
        FROM log_registros 
        ORDER BY fecha_registro DESC
    ");
    $query_registros->execute();
    $registros = $query_registros->fetchAll(PDO::FETCH_ASSOC);

    foreach ($registros as $registro) {
        $historial[] = [
            'id' => 'reg_' . $registro['documento_usuario'] . '_' . strtotime($registro['fecha']),
            'tipo' => $registro['tipo'],
            'fecha' => $registro['fecha'],
            'vehiculo' => $registro['vehiculo'],
            'descripcion' => $registro['descripcion'],
            'detalles' => $registro['detalles'],
            'usuario' => obtenerNombreUsuario($con, $registro['documento_usuario']),
            'estado' => $registro['estado']
        ];
    }

    // 2. Log de mantenimientos
    $query_mantenimiento = $con->prepare("
        SELECT 
            'mantenimiento' as tipo,
            fecha_registro as fecha,
            placa_vehiculo as vehiculo,
            CONCAT('Mantenimiento programado para ', fecha_programada) as descripcion,
            CONCAT('Tipo: ', CASE WHEN id_tipo_mantenimiento = 1 THEN 'Preventivo' ELSE 'Correctivo' END, 
                   '. Kilometraje: ', kilometraje_actual, ' km. ', 
                   COALESCE(observaciones, '')) as detalles,
            documento_usuario,
            CASE WHEN fecha_realizada IS NOT NULL THEN 'completado' ELSE 'pendiente' END as estado,
            proximo_cambio_km,
            proximo_cambio_fecha
        FROM mantenimiento_log 
        ORDER BY fecha_registro DESC
    ");
    $query_mantenimiento->execute();
    $mantenimientos = $query_mantenimiento->fetchAll(PDO::FETCH_ASSOC);

    foreach ($mantenimientos as $mant) {
        $historial[] = [
            'id' => 'mant_' . $mant['documento_usuario'] . '_' . strtotime($mant['fecha']),
            'tipo' => $mant['tipo'],
            'fecha' => $mant['fecha'],
            'vehiculo' => $mant['vehiculo'],
            'descripcion' => $mant['descripcion'],
            'detalles' => $mant['detalles'],
            'usuario' => obtenerNombreUsuario($con, $mant['documento_usuario']),
            'estado' => $mant['estado'],
            'proximo_cambio_km' => $mant['proximo_cambio_km'],
            'proximo_cambio_fecha' => $mant['proximo_cambio_fecha']
        ];
    }

    // 3. Log de llantas
    $query_llantas = $con->prepare("
        SELECT 
            'llantas' as tipo,
            fecha_registro as fecha,
            placa_vehiculo as vehiculo,
            CONCAT('Registro de llantas - Estado: ', estado_llantas) as descripcion,
            CONCAT('Último cambio: ', ultimo_cambio, 
                   '. Presión: ', presion_llantas, ' PSI. ',
                   'Kilometraje: ', kilometraje_actual, ' km. ',
                   COALESCE(notas, '')) as detalles,
            documento_usuario,
            CASE WHEN estado_llantas = 'Bueno' THEN 'vigente' 
                 WHEN estado_llantas = 'Regular' THEN 'pendiente' 
                 ELSE 'vencido' END as estado,
            proximo_cambio_fecha
        FROM llantas_log 
        ORDER BY fecha_registro DESC
    ");
    $query_llantas->execute();
    $llantas = $query_llantas->fetchAll(PDO::FETCH_ASSOC);

    foreach ($llantas as $llanta) {
        $historial[] = [
            'id' => 'llanta_' . $llanta['documento_usuario'] . '_' . strtotime($llanta['fecha']),
            'tipo' => $llanta['tipo'],
            'fecha' => $llanta['fecha'],
            'vehiculo' => $llanta['vehiculo'],
            'descripcion' => $llanta['descripcion'],
            'detalles' => $llanta['detalles'],
            'usuario' => obtenerNombreUsuario($con, $llanta['documento_usuario']),
            'estado' => $llanta['estado'],
            'proximo_cambio_fecha' => $llanta['proximo_cambio_fecha']
        ];
    }

    // 4. Log de SOAT
    $query_soat = $con->prepare("
        SELECT 
            'soat' as tipo,
            fecha_registro as fecha,
            placa_vehiculo as vehiculo,
            CONCAT('SOAT registrado - Vigencia hasta ', fecha_vencimiento) as descripcion,
            CONCAT('Expedición: ', fecha_expedicion, 
                   '. Vencimiento: ', fecha_vencimiento, 
                   '. Aseguradora ID: ', id_aseguradora) as detalles,
            documento_usuario,
            CASE WHEN fecha_vencimiento > CURDATE() THEN 'vigente' ELSE 'vencido' END as estado,
            fecha_vencimiento,
            id_aseguradora
        FROM soat_log 
        ORDER BY fecha_registro DESC
    ");
    $query_soat->execute();
    $soats = $query_soat->fetchAll(PDO::FETCH_ASSOC);

    foreach ($soats as $soat) {
        $historial[] = [
            'id' => 'soat_' . $soat['documento_usuario'] . '_' . strtotime($soat['fecha']),
            'tipo' => $soat['tipo'],
            'fecha' => $soat['fecha'],
            'vehiculo' => $soat['vehiculo'],
            'descripcion' => $soat['descripcion'],
            'detalles' => $soat['detalles'],
            'usuario' => obtenerNombreUsuario($con, $soat['documento_usuario']),
            'estado' => $soat['estado'],
            'fecha_vencimiento' => $soat['fecha_vencimiento'],
            'aseguradora' => obtenerAseguradora($con, $soat['id_aseguradora'])
        ];
    }

    // 5. Log de Tecnomecánica
    $query_tecno = $con->prepare("
        SELECT 
            'tecnomecanica' as tipo,
            fecha_registro as fecha,
            placa_vehiculo as vehiculo,
            CONCAT('Tecnomecánica registrada - Vigencia hasta ', fecha_vencimiento) as descripcion,
            CONCAT('Expedición: ', fecha_expedicion, 
                   '. Vencimiento: ', fecha_vencimiento, 
                   '. Centro: ', centro_revision) as detalles,
            documento_usuario,
            CASE WHEN fecha_vencimiento > CURDATE() THEN 'vigente' ELSE 'vencido' END as estado,
            fecha_vencimiento,
            centro_revision
        FROM tecnomecanica_log 
        ORDER BY fecha_registro DESC
    ");
    $query_tecno->execute();
    $tecnos = $query_tecno->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tecnos as $tecno) {
        $historial[] = [
            'id' => 'tecno_' . $tecno['documento_usuario'] . '_' . strtotime($tecno['fecha']),
            'tipo' => $tecno['tipo'],
            'fecha' => $tecno['fecha'],
            'vehiculo' => $tecno['vehiculo'],
            'descripcion' => $tecno['descripcion'],
            'detalles' => $tecno['detalles'],
            'usuario' => obtenerNombreUsuario($con, $tecno['documento_usuario']),
            'estado' => $tecno['estado'],
            'fecha_vencimiento' => $tecno['fecha_vencimiento'],
            'centro' => obtenerCentroRevision($con, $tecno['centro_revision'])
        ];
    }

    // 6. Log de Licencias
    $query_licencias = $con->prepare("
        SELECT 
            'licencia' as tipo,
            fecha_registro as fecha,
            'N/A' as vehiculo,
            CONCAT('Licencia registrada - Categoría ', id_categoria) as descripcion,
            CONCAT('Expedición: ', fecha_expedicion, 
                   '. Vencimiento: ', fecha_vencimiento, 
                   '. Servicio: ', CASE WHEN id_servicio = 1 THEN 'Particular' ELSE 'Público' END,
                   '. ', COALESCE(observaciones, '')) as detalles,
            documento_usuario,
            CASE WHEN fecha_vencimiento > CURDATE() THEN 'vigente' ELSE 'vencido' END as estado,
            fecha_vencimiento,
            id_categoria
        FROM licencia_log 
        ORDER BY fecha_registro DESC
    ");
    $query_licencias->execute();
    $licencias = $query_licencias->fetchAll(PDO::FETCH_ASSOC);

    foreach ($licencias as $licencia) {
        $historial[] = [
            'id' => 'lic_' . $licencia['documento_usuario'] . '_' . strtotime($licencia['fecha']),
            'tipo' => $licencia['tipo'],
            'fecha' => $licencia['fecha'],
            'vehiculo' => $licencia['vehiculo'],
            'descripcion' => $licencia['descripcion'],
            'detalles' => $licencia['detalles'],
            'usuario' => obtenerNombreUsuario($con, $licencia['documento_usuario']),
            'estado' => $licencia['estado'],
            'fecha_vencimiento' => $licencia['fecha_vencimiento'],
            'categoria' => $licencia['id_categoria']
        ];
    }
} catch (PDOException $e) {
    error_log("Error al obtener historial: " . $e->getMessage());
    $historial = [];
}

// Ordenar historial por fecha (más reciente primero)
usort($historial, function ($a, $b) {
    return strtotime($b['fecha']) - strtotime($a['fecha']);
});

// Calcular estadísticas
$total_eventos = count($historial);
$mantenimientos = count(array_filter($historial, fn($h) => $h['tipo'] === 'mantenimiento'));
$licencias = count(array_filter($historial, fn($h) => $h['tipo'] === 'licencia'));
$llantas = count(array_filter($historial, fn($h) => $h['tipo'] === 'llantas'));
$soats = count(array_filter($historial, fn($h) => $h['tipo'] === 'soat'));
$tecnomecanicas = count(array_filter($historial, fn($h) => $h['tipo'] === 'tecnomecanica'));
$registros = count(array_filter($historial, fn($h) => $h['tipo'] === 'registro'));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Historiales del Sistema - Flotax AGC</title>
    <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="css/historial.css" />
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
                    <i class="bi bi-clock-history"></i>
                    Historiales del Sistema
                </h1>
                <p class="page-subtitle">Registro completo de actividades y eventos del sistema</p>
            </div>
        </div>

        <!-- Estadísticas del historial -->
        <div class="history-stats">
            <div class="stat-card mantenimientos" onclick="filtrarPorTipo('mantenimiento')">
                <div class="stat-number mantenimientos">
                    <span><?= $mantenimientos ?></span>
                    <i class="bi bi-tools stat-icon"></i>
                </div>
                <div class="stat-label">Mantenimientos</div>
            </div>
            <div class="stat-card licencias" onclick="filtrarPorTipo('licencia')">
                <div class="stat-number licencias">
                    <span><?= $licencias ?></span>
                    <i class="bi bi-card-text stat-icon"></i>
                </div>
                <div class="stat-label">Licencias</div>
            </div>
            <div class="stat-card llantas" onclick="filtrarPorTipo('llantas')">
                <div class="stat-number llantas">
                    <span><?= $llantas ?></span>
                    <i class="bi bi-circle stat-icon"></i>
                </div>
                <div class="stat-label">Llantas</div>
            </div>
            <div class="stat-card soats" onclick="filtrarPorTipo('soat')">
                <div class="stat-number soats">
                    <span><?= $soats ?></span>
                    <i class="bi bi-shield-check stat-icon"></i>
                </div>
                <div class="stat-label">SOAT</div>
            </div>
            <div class="stat-card tecnomecanicas" onclick="filtrarPorTipo('tecnomecanica')">
                <div class="stat-number tecnomecanicas">
                    <span><?= $tecnomecanicas ?></span>
                    <i class="bi bi-gear stat-icon"></i>
                </div>
                <div class="stat-label">Tecnomecánica</div>
            </div>
            <div class="stat-card registros" onclick="filtrarPorTipo('registro')">
                <div class="stat-number registros">
                    <span><?= $registros ?></span>
                    <i class="bi bi-plus-circle stat-icon"></i>
                </div>
                <div class="stat-label">Registros</div>
            </div>
        </div>

        <!-- Filtros avanzados -->
        <div class="filters-section">
            <div class="filters-header">
                <h3 class="filters-title">
                    <i class="bi bi-funnel"></i>
                    Filtros Avanzados
                </h3>
                <button class="filters-toggle" onclick="toggleFilters()">
                    <i class="bi bi-chevron-down"></i>
                    <span>Mostrar filtros</span>
                </button>
            </div>

            <div class="filters-grid" id="filtersGrid" style="display: none;">
                <div class="filter-group">
                    <label class="filter-label">Tipo de Evento</label>
                    <select class="filter-control" id="filtroTipo" onchange="aplicarFiltros()">
                        <option value="">Todos los tipos</option>
                        <option value="mantenimiento">Mantenimientos</option>
                        <option value="licencia">Licencias</option>
                        <option value="llantas">Llantas</option>
                        <option value="soat">SOAT</option>
                        <option value="tecnomecanica">Tecnomecánica</option>
                        <option value="registro">Registros</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Vehículo</label>
                    <input type="text" class="filter-control" id="filtroVehiculo" placeholder="Placa del vehículo" onkeyup="aplicarFiltros()">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Fecha Desde</label>
                    <input type="date" class="filter-control" id="filtroDesde" onchange="aplicarFiltros()">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Fecha Hasta</label>
                    <input type="date" class="filter-control" id="filtroHasta" onchange="aplicarFiltros()">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Usuario</label>
                    <input type="text" class="filter-control" id="filtroUsuario" placeholder="Nombre del usuario" onkeyup="aplicarFiltros()">
                </div>
                <div class="filter-group">
                    <div class="filter-actions">
                        <button class="filter-btn primary" onclick="aplicarFiltros()">
                            <i class="bi bi-search"></i>
                            Buscar
                        </button>
                        <button class="filter-btn secondary" onclick="limpiarFiltros()">
                            <i class="bi bi-arrow-clockwise"></i>
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista de historial -->
        <div class="history-view">
            <div class="history-header">
                <h3 class="history-title">
                    <i class="bi bi-list-ul"></i>
                    Historial de Eventos
                </h3>
                <div class="view-toggle">
                    <button class="view-btn active" onclick="cambiarVista('timeline')" id="timelineBtn">
                        <i class="bi bi-clock"></i>
                        Timeline
                    </button>
                    <button class="view-btn" onclick="cambiarVista('table')" id="tableBtn">
                        <i class="bi bi-table"></i>
                        Tabla
                    </button>
                </div>
                <span class="history-count" id="historyCount"><?= $total_eventos ?> eventos</span>
            </div>

            <!-- Vista Timeline -->
            <div class="timeline-container" id="timelineView">
                <div class="timeline">
                    <?php if (empty($historial)): ?>
                        <div class="no-history">
                            <i class="bi bi-clock-history"></i>
                            <h3>No hay eventos registrados</h3>
                            <p>Aún no se han registrado eventos en el sistema.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($historial as $evento): ?>
                            <div class="timeline-item <?= $evento['tipo'] ?>"
                                data-tipo="<?= $evento['tipo'] ?>"
                                data-vehiculo="<?= strtolower($evento['vehiculo']) ?>"
                                data-fecha="<?= date('Y-m-d', strtotime($evento['fecha'])) ?>"
                                data-usuario="<?= strtolower($evento['usuario']) ?>">

                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div class="timeline-type <?= $evento['tipo'] ?>">
                                            <i class="<?= getEventIcon($evento['tipo']) ?>"></i>
                                            <?= ucfirst($evento['tipo']) ?>
                                        </div>
                                        <div class="timeline-date">
                                            <i class="bi bi-calendar"></i>
                                            <?= formatearFecha($evento['fecha']) ?>
                                        </div>
                                    </div>

                                    <?php if ($evento['vehiculo'] !== 'N/A'): ?>
                                        <div class="timeline-vehicle"><?= htmlspecialchars($evento['vehiculo']) ?></div>
                                    <?php endif; ?>

                                    <div class="timeline-description">
                                        <?= htmlspecialchars($evento['descripcion']) ?>
                                    </div>

                                    <div class="timeline-details">
                                        <div class="timeline-detail">
                                            <span class="timeline-detail-label">Usuario:</span>
                                            <span class="timeline-detail-value"><?= htmlspecialchars($evento['usuario']) ?></span>
                                        </div>

                                        <?php if (isset($evento['aseguradora'])): ?>
                                            <div class="timeline-detail">
                                                <span class="timeline-detail-label">Aseguradora:</span>
                                                <span class="timeline-detail-value"><?= htmlspecialchars($evento['aseguradora']) ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (isset($evento['centro'])): ?>
                                            <div class="timeline-detail">
                                                <span class="timeline-detail-label">Centro:</span>
                                                <span class="timeline-detail-value"><?= htmlspecialchars($evento['centro']) ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="timeline-detail">
                                            <span class="timeline-detail-label">Estado:</span>
                                            <span class="timeline-detail-value">
                                                <span class="status-indicator <?= $evento['estado'] === 'completado' || $evento['estado'] === 'vigente' ? 'success' : ($evento['estado'] === 'pendiente' ? 'warning' : 'danger') ?>"></span>
                                                <?= ucfirst($evento['estado']) ?>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="timeline-actions">
                                        <a href="#" onclick="verDetalles('<?= $evento['id'] ?>')" class="timeline-action view">
                                            <i class="bi bi-eye"></i>
                                            Ver detalles
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Vista Tabla -->
            <div class="table-container" id="tableView" style="display: none;">
                <div class="table-responsive">
                    <table class="table" id="historyTable">
                        <thead>
                            <tr>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-tag"></i> Tipo</th>
                                <th><i class="bi bi-car-front"></i> Vehículo</th>
                                <th><i class="bi bi-card-text"></i> Descripción</th>
                                <th><i class="bi bi-person"></i> Usuario</th>
                                <th><i class="bi bi-info-circle"></i> Estado</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $evento): ?>
                                <tr data-tipo="<?= $evento['tipo'] ?>"
                                    data-vehiculo="<?= strtolower($evento['vehiculo']) ?>"
                                    data-fecha="<?= date('Y-m-d', strtotime($evento['fecha'])) ?>"
                                    data-usuario="<?= strtolower($evento['usuario']) ?>">
                                    <td><?= formatearFecha($evento['fecha']) ?></td>
                                    <td>
                                        <span class="event-type <?= $evento['tipo'] ?>">
                                            <i class="<?= getEventIcon($evento['tipo']) ?>"></i>
                                            <?= ucfirst($evento['tipo']) ?>
                                        </span>
                                    </td>
                                    <td><?= $evento['vehiculo'] !== 'N/A' ? '<strong>' . htmlspecialchars($evento['vehiculo']) . '</strong>' : 'N/A' ?></td>
                                    <td class="tooltip-trigger" data-tooltip="<?= htmlspecialchars($evento['detalles']) ?>">
                                        <?= htmlspecialchars(substr($evento['descripcion'], 0, 50)) ?>...
                                    </td>
                                    <td><?= htmlspecialchars($evento['usuario']) ?></td>
                                    <td>
                                        <span class="status-indicator <?= $evento['estado'] === 'completado' || $evento['estado'] === 'vigente' ? 'success' : ($evento['estado'] === 'pendiente' ? 'warning' : 'danger') ?>"></span>
                                        <?= ucfirst($evento['estado']) ?>
                                    </td>
                                    <td>
                                        <div class="timeline-actions">
                                            <a href="#" onclick="verDetalles('<?= $evento['id'] ?>')" class="timeline-action view">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Paginación -->
            <div class="pagination-container">
                <ul class="pagination" id="pagination"></ul>
            </div>
        </div>

        <!-- Mensaje cuando no hay eventos -->
        <div class="no-history" id="noHistory" style="display: none;">
            <i class="bi bi-clock-history"></i>
            <h3>No se encontraron eventos</h3>
            <p>No hay eventos que coincidan con los filtros seleccionados.</p>
        </div>

        <!-- Modal para detalles -->
        
    <div class="modal fade modal-overlay" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-custom">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title modal-title-custom" id="modalDetallesLabel">Detalles del Evento</h5>
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal" aria-label="Cerrar">×</button>
                </div>
                <div class="modal-body modal-body-custom" id="detallesContenido">
                    <div class="text-center py-4 loading-content-custom">
                        
                        <div class="spinner-border spinner-border-custom" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('modalDetalles');
            const loadingContent = modal.querySelector('.loading-content');
            const dynamicContent = modal.querySelector('.dynamic-content');

            // Mostrar loading cuando se abre el modal
            modal.addEventListener('show.bs.modal', function() {
                loadingContent.classList.remove('d-none');
                dynamicContent.innerHTML = '';
            });

            // Ocultar loading cuando se cierra el modal
            modal.addEventListener('hidden.bs.modal', function() {
                loadingContent.classList.add('d-none');
                dynamicContent.innerHTML = '';
            });

            // Función para cargar contenido dinámico
            window.cargarContenidoModal = function(contenido) {
                loadingContent.classList.add('d-none');
                dynamicContent.innerHTML = contenido;
                // Animar la entrada del contenido
                dynamicContent.style.opacity = '0';
                setTimeout(() => {
                    dynamicContent.style.transition = 'opacity 0.3s ease';
                    dynamicContent.style.opacity = '1';
                }, 50);
            };
        });
        </script>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentView = 'timeline';
        let filtersVisible = false;

        // Toggle filtros
        function toggleFilters() {
            const filtersGrid = document.getElementById('filtersGrid');
            const toggleBtn = document.querySelector('.filters-toggle');
            const toggleText = toggleBtn.querySelector('span');
            const toggleIcon = toggleBtn.querySelector('i');

            filtersVisible = !filtersVisible;

            if (filtersVisible) {
                filtersGrid.style.display = 'grid';
                toggleText.textContent = 'Ocultar filtros';
                toggleIcon.className = 'bi bi-chevron-up';
            } else {
                filtersGrid.style.display = 'none';
                toggleText.textContent = 'Mostrar filtros';
                toggleIcon.className = 'bi bi-chevron-down';
            }
        }

        // Cambiar vista
        function cambiarVista(vista) {
            const timelineView = document.getElementById('timelineView');
            const tableView = document.getElementById('tableView');
            const timelineBtn = document.getElementById('timelineBtn');
            const tableBtn = document.getElementById('tableBtn');

            currentView = vista;

            if (vista === 'timeline') {
                timelineView.style.display = 'block';
                tableView.style.display = 'none';
                timelineBtn.classList.add('active');
                tableBtn.classList.remove('active');
            } else {
                timelineView.style.display = 'none';
                tableView.style.display = 'block';
                timelineBtn.classList.remove('active');
                tableBtn.classList.add('active');
                configurarPaginacion();
            }
        }

        // Filtrar por tipo desde las tarjetas de estadísticas
        function filtrarPorTipo(tipo) {
            document.getElementById('filtroTipo').value = tipo;
            if (!filtersVisible) {
                toggleFilters();
            }
            aplicarFiltros();
        }

        // Aplicar filtros
        function aplicarFiltros() {
            const filtroTipo = document.getElementById('filtroTipo').value.toLowerCase();
            const filtroVehiculo = document.getElementById('filtroVehiculo').value.toLowerCase();
            const filtroDesde = document.getElementById('filtroDesde').value;
            const filtroHasta = document.getElementById('filtroHasta').value;
            const filtroUsuario = document.getElementById('filtroUsuario').value.toLowerCase();

            const timelineItems = document.querySelectorAll('.timeline-item');
            const tableRows = document.querySelectorAll('#historyTable tbody tr');
            let eventosVisibles = 0;

            // Filtrar timeline
            timelineItems.forEach(item => {
                const tipo = item.dataset.tipo || '';
                const vehiculo = item.dataset.vehiculo || '';
                const fecha = item.dataset.fecha || '';
                const usuario = item.dataset.usuario || '';

                let mostrar = true;

                if (filtroTipo && tipo !== filtroTipo) mostrar = false;
                if (filtroVehiculo && !vehiculo.includes(filtroVehiculo)) mostrar = false;
                if (filtroUsuario && !usuario.includes(filtroUsuario)) mostrar = false;
                if (filtroDesde && fecha < filtroDesde) mostrar = false;
                if (filtroHasta && fecha > filtroHasta) mostrar = false;

                item.style.display = mostrar ? 'block' : 'none';
                if (mostrar) eventosVisibles++;
            });

            // Filtrar tabla
            tableRows.forEach(row => {
                const tipo = row.dataset.tipo || '';
                const vehiculo = row.dataset.vehiculo || '';
                const fecha = row.dataset.fecha || '';
                const usuario = row.dataset.usuario || '';

                let mostrar = true;

                if (filtroTipo && tipo !== filtroTipo) mostrar = false;
                if (filtroVehiculo && !vehiculo.includes(filtroVehiculo)) mostrar = false;
                if (filtroUsuario && !usuario.includes(filtroUsuario)) mostrar = false;
                if (filtroDesde && fecha < filtroDesde) mostrar = false;
                if (filtroHasta && fecha > filtroHasta) mostrar = false;

                row.style.display = mostrar ? '' : 'none';
            });

            // Actualizar contador
            document.getElementById('historyCount').textContent = `${eventosVisibles} eventos`;

            // Mostrar mensaje si no hay eventos
            const noHistory = document.getElementById('noHistory');
            const historyView = document.querySelector('.history-view');

            if (eventosVisibles === 0) {
                noHistory.style.display = 'block';
                historyView.style.display = 'none';
            } else {
                noHistory.style.display = 'none';
                historyView.style.display = 'block';
            }

            if (currentView === 'table') {
                configurarPaginacion();
            }
        }

        // Limpiar filtros
        function limpiarFiltros() {
            document.getElementById('filtroTipo').value = '';
            document.getElementById('filtroVehiculo').value = '';
            document.getElementById('filtroDesde').value = '';
            document.getElementById('filtroHasta').value = '';
            document.getElementById('filtroUsuario').value = '';
            aplicarFiltros();
        }

        // Paginación para vista de tabla
        const filasPorPagina = 10;

        function configurarPaginacion() {
            const filas = Array.from(document.querySelectorAll('#historyTable tbody tr'))
                .filter(row => row.style.display !== 'none');
            const totalPaginas = Math.ceil(filas.length / filasPorPagina);
            const paginacion = document.getElementById('pagination');

            function mostrarPagina(pagina) {
                document.querySelectorAll('#historyTable tbody tr').forEach(row => {
                    row.style.display = 'none';
                });

                const inicio = (pagina - 1) * filasPorPagina;
                const fin = inicio + filasPorPagina;
                filas.slice(inicio, fin).forEach(row => {
                    row.style.display = '';
                });

                document.querySelectorAll('#pagination .page-item').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelector(`#pagination .page-item:nth-child(${pagina})`)?.classList.add('active');
            }

            paginacion.innerHTML = '';
            for (let i = 1; i <= totalPaginas; i++) {
                const li = document.createElement('li');
                li.className = 'page-item' + (i === 1 ? ' active' : '');
                const a = document.createElement('a');
                a.className = 'page-link';
                a.href = '#';
                a.textContent = i;
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    mostrarPagina(i);
                });
                li.appendChild(a);
                paginacion.appendChild(li);
            }
            if (totalPaginas > 0) {
                mostrarPagina(1);
            }
        }

        // Ver detalles de un evento
        function verDetalles(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
            const detallesContenido = document.getElementById('detallesContenido');

            // Buscar el evento en los datos
            const eventos = <?= json_encode($historial) ?>;
            const evento = eventos.find(e => e.id === id);

            if (evento) {
                detallesContenido.innerHTML = `
                    <div class="p-3">
                        <div class="mb-4">
                            <h4 class="text-primary">Evento: ${evento.tipo.charAt(0).toUpperCase() + evento.tipo.slice(1)}</h4>
                            <p class="text-muted">Información detallada del evento</p>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Tipo:</strong> ${evento.tipo.charAt(0).toUpperCase() + evento.tipo.slice(1)}</p>
                                <p><strong>Vehículo:</strong> ${evento.vehiculo}</p>
                                <p><strong>Fecha:</strong> ${new Date(evento.fecha).toLocaleString('es-ES')}</p>
                                <p><strong>Usuario:</strong> ${evento.usuario}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Estado:</strong> <span class="badge ${evento.estado === 'completado' || evento.estado === 'vigente' ? 'bg-success' : (evento.estado === 'pendiente' ? 'bg-warning' : 'bg-danger')}">${evento.estado.charAt(0).toUpperCase() + evento.estado.slice(1)}</span></p>
                                ${evento.aseguradora ? `<p><strong>Aseguradora:</strong> ${evento.aseguradora}</p>` : ''}
                                ${evento.centro ? `<p><strong>Centro:</strong> ${evento.centro}</p>` : ''}
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h5>Descripción</h5>
                            <p>${evento.descripcion}</p>
                        </div>
                        
                        <div class="mb-3">
                            <h5>Detalles completos</h5>
                            <p>${evento.detalles}</p>
                        </div>
                    </div>
                `;
            } else {
                detallesContenido.innerHTML = `
                    <div class="p-3">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            No se pudieron cargar los detalles del evento.
                        </div>
                    </div>
                `;
            }

            modal.show();
        }

        // Exportar historial
        function exportarHistorial() {
            if (confirm('¿Desea exportar el historial completo a Excel?')) {
                // Implementar exportación
                console.log('Exportar historial completo');
                // window.open('exportar_historial.php', '_blank');
            }
        }

        // Inicializar cuando el DOM esté listo
        window.addEventListener('DOMContentLoaded', () => {
            // Configurar fechas por defecto (último mes)
            const hoy = new Date();
            const hace30dias = new Date();
            hace30dias.setDate(hoy.getDate() - 30);

            document.getElementById('filtroDesde').value = hace30dias.toISOString().split('T')[0];
            document.getElementById('filtroHasta').value = hoy.toISOString().split('T')[0];

            // Aplicar filtros iniciales
            aplicarFiltros();

            // Agregar animación a los elementos del timeline
            const timelineItems = document.querySelectorAll('.timeline-item');
            timelineItems.forEach((item, index) => {
                item.style.animationDelay = `${index * 0.1}s`;
            });

            // Agregar animación a las filas de la tabla
            const tableRows = document.querySelectorAll('#historyTable tbody tr');
            tableRows.forEach((row, index) => {
                row.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>

</html>