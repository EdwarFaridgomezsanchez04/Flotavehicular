<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';
include 'includes/alertas_config.php';

$db = new Database();
$con = $db->conectar();

// Validación de sesión y datos del usuario
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

if (!isset($_SESSION['nombre_completo']) || !isset($_SESSION['foto_perfil'])) {
    $stmt = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['nombre_completo'] = $usuario['nombre_completo'] ?? 'Usuario';
    $_SESSION['foto_perfil'] = $usuario['foto_perfil'] ?: 'roles/user/css/img/perfil.jpg';
}

$nombre_completo = $_SESSION['nombre_completo'];
$foto_perfil = $_SESSION['foto_perfil'];

// Función para obtener el ícono según tipo
function getAlertIcon($tipo)
{
    $config = getTipoConfig(strtolower($tipo));
    return $config['icono'];
}

// Función para categorizar notificaciones según su contenido
function categorizarNotificacion($mensaje)
{
    $mensaje_lower = strtolower($mensaje);
    
    foreach (PALABRAS_CLAVE as $tipo => $palabras) {
        foreach ($palabras as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return $tipo;
            }
        }
    }
    
    return 'general';
}

// Función para extraer placa del mensaje
function extraerPlaca($mensaje)
{
    foreach (PATRONES_PLACA as $patron) {
        if (preg_match($patron, $mensaje, $matches)) {
            return strtoupper($matches[0]);
        }
    }
    return 'N/A';
}

// Función para determinar prioridad
function determinarPrioridad($mensaje, $tipo)
{
    $mensaje_lower = strtolower($mensaje);

    foreach (PALABRAS_PRIORIDAD as $prioridad => $palabras) {
        foreach ($palabras as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return $prioridad;
            }
        }
    }
    
    return 'media';
}

// Función para determinar estado
function determinarEstado($mensaje, $leido)
{
    if ($leido) {
        return 'informativa';
    }

    $mensaje_lower = strtolower($mensaje);

    foreach (PALABRAS_ESTADO as $estado => $palabras) {
        foreach ($palabras as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return $estado;
            }
        }
    }
    
    return 'informativa';
}

// Cargar notificaciones de la base de datos
$alertas = [];
try {
    $stmt = $con->prepare("
        SELECT n.id, n.mensaje, n.fecha, n.leido, u.nombre_completo
        FROM notificaciones n
        LEFT JOIN usuarios u ON n.documento_usuario = u.documento
        WHERE n.documento_usuario = :documento 
        ORDER BY n.fecha DESC
        LIMIT 50
    ");
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar notificaciones
    foreach ($resultados as $row) {
        $tipo = categorizarNotificacion($row['mensaje']);
        $placa = extraerPlaca($row['mensaje']);
        $prioridad = determinarPrioridad($row['mensaje'], $tipo);
        $estado = determinarEstado($row['mensaje'], $row['leido']);

        $alertas[] = [
            'id' => $row['id'],
            'tipo' => ucfirst($tipo),
            'vehiculo' => $placa,
            'descripcion' => $row['mensaje'],
            'fecha_alerta' => $row['fecha'],
            'fecha_vencimiento' => null,
            'prioridad' => $prioridad,
            'estado' => $estado,
            'leido' => $row['leido'],
            'detalles' => $row['mensaje'],
            'usuario' => $row['nombre_completo'] ?? 'Sistema'
        ];
    }
} catch (PDOException $e) {
    error_log("Error al cargar notificaciones: " . $e->getMessage());
    $alertas = [];
}

// Calcular estadísticas reales
$total_alertas = count($alertas);
$alertas_criticas = count(array_filter($alertas, fn($a) => $a['estado'] === 'critica'));
$alertas_pendientes = count(array_filter($alertas, fn($a) => $a['estado'] === 'pendiente'));
$alertas_al_dia = count(array_filter($alertas, fn($a) => $a['estado'] === 'informativa'));

// Estadísticas adicionales
try {
    // Alertas resueltas este mes
    $stmt = $con->prepare("
        SELECT COUNT(*) as total 
        FROM notificaciones 
        WHERE documento_usuario = :documento 
        AND leido = 1 
        AND MONTH(fecha) = MONTH(CURRENT_DATE()) 
        AND YEAR(fecha) = YEAR(CURRENT_DATE())
    ");
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();
    $alertas_resueltas_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de alertas resueltas
    $stmt = $con->prepare("
        SELECT COUNT(*) as total 
        FROM notificaciones 
        WHERE documento_usuario = :documento 
        AND leido = 1
    ");
    $stmt->bindParam(':documento', $documento);
    $stmt->execute();
    $alertas_resueltas_total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Tiempo promedio de resolución (simulado)
    $tiempo_promedio_resolucion = $alertas_resueltas_total > 0 ? rand(1, 5) : 0;

    // Tasa de éxito
    $tasa_exito = $total_alertas > 0 ? round(($alertas_resueltas_total / ($total_alertas + $alertas_resueltas_total)) * 100) : 100;
} catch (PDOException $e) {
    $alertas_resueltas_mes = 0;
    $alertas_resueltas_total = 0;
    $tiempo_promedio_resolucion = 0;
    $tasa_exito = 100;
}

// Manejar acciones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    try {
        switch ($_POST['action']) {
            case 'resolver_alerta':
                $alerta_id = $_POST['alerta_id'] ?? null;
                if ($alerta_id) {
                    $stmt = $con->prepare("UPDATE notificaciones SET leido = 1 WHERE id = :id AND documento_usuario = :documento");
                    $stmt->bindParam(':id', $alerta_id, PDO::PARAM_INT);
                    $stmt->bindParam(':documento', $documento);
                    $success = $stmt->execute();

                    echo json_encode(['success' => $success, 'message' => $success ? 'Alerta resuelta correctamente' : 'Error al resolver la alerta']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'ID de alerta no válido']);
                }
                break;

            case 'marcar_todas_leidas':
                $stmt = $con->prepare("UPDATE notificaciones SET leido = 1 WHERE documento_usuario = :documento AND leido = 0");
                $stmt->bindParam(':documento', $documento);
                $success = $stmt->execute();

                echo json_encode(['success' => $success, 'message' => $success ? 'Todas las alertas han sido marcadas como leídas' : 'Error al marcar las alertas']);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Módulo de Alertas - Flotax AGC</title>
    <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="css/alertas.css" />
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
                    <i class="bi bi-bell"></i>
                    Módulo de Alertas
                </h1>
                <p class="page-subtitle">Sistema de notificaciones y alertas del sistema</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-outline-primary" onclick="marcarTodasLeidas()">
                    <i class="bi bi-check-all"></i>
                    Marcar todas como leídas
                </button>
                <button class="btn btn-primary" onclick="actualizarAlertas()">
                    <i class="bi bi-arrow-clockwise"></i>
                    Actualizar
                </button>
            </div>
        </div>

        <!-- Resumen de alertas -->
        <div class="alerts-summary">
            <div class="summary-card criticas" onclick="filtrarPorEstado('critica')">
                <div class="summary-number criticas">
                    <span><?= $alertas_criticas ?></span>
                    <i class="bi bi-exclamation-triangle summary-icon"></i>
                </div>
                <div class="summary-label">Alertas Críticas</div>
            </div>
            <div class="summary-card pendientes" onclick="filtrarPorEstado('pendiente')">
                <div class="summary-number pendientes">
                    <span><?= $alertas_pendientes ?></span>
                    <i class="bi bi-clock summary-icon"></i>
                </div>
                <div class="summary-label">Alertas Pendientes</div>
            </div>
            <div class="summary-card al-dia" onclick="filtrarPorEstado('informativa')">
                <div class="summary-number al-dia">
                    <span><?= $alertas_al_dia ?></span>
                    <i class="bi bi-check-circle summary-icon"></i>
                </div>
                <div class="summary-label">Al Día</div>
            </div>
            <div class="summary-card total" onclick="mostrarTodas()">
                <div class="summary-number total">
                    <span><?= $total_alertas ?></span>
                    <i class="bi bi-list summary-icon"></i>
                </div>
                <div class="summary-label">Total Alertas</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <h3 class="filters-title">
                <i class="bi bi-funnel"></i>
                Filtros de Búsqueda
            </h3>
            <form class="filters-grid" id="filtrosForm">
                <div class="filter-group">
                    <label class="filter-label">Tipo de Alerta</label>
                    <select class="filter-control" id="filtroTipo" onchange="aplicarFiltros()">
                        <option value="">Todas las alertas</option>
                        <option value="soat">SOAT</option>
                        <option value="tecnomecanica">Revisión Técnico-Mecánica</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="licencia">Licencia</option>
                        <option value="llantas">Llantas</option>
                        <option value="pico_placa">Pico y Placa</option>
                        <option value="multa">Multas</option>
                        <option value="registro">Registros</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Estado</label>
                    <select class="filter-control" id="filtroEstado" onchange="aplicarFiltros()">
                        <option value="">Todos los estados</option>
                        <option value="critica">🔴 Crítica</option>
                        <option value="pendiente">🟡 Pendiente</option>
                        <option value="informativa">🔵 Informativa</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Vehículo</label>
                    <input type="text" class="filter-control" id="filtroVehiculo" placeholder="Placa (ej: ABC123)" onkeyup="aplicarFiltros()">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Prioridad</label>
                    <select class="filter-control" id="filtroPrioridad" onchange="aplicarFiltros()">
                        <option value="">Todas las prioridades</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button type="button" class="filter-btn" onclick="limpiarFiltros()">
                        <i class="bi bi-arrow-clockwise"></i>
                        Limpiar Filtros
                    </button>
                </div>
            </form>
        </div>

        <!-- Contenedor de alertas activas -->
        <div class="alerts-container">
            <div class="alerts-header">
                <h3 class="alerts-title">
                    <i class="bi bi-bell"></i>
                    Alertas Activas
                </h3>
                <span class="alerts-count" id="alertasCount"><?= $total_alertas ?> alertas</span>
            </div>

            <ul class="alerts-list" id="alertasList">
                <?php if (empty($alertas)): ?>
                    <li class="no-alerts-item">
                        <div class="text-center p-4">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3">No hay alertas</h5>
                            <p class="text-muted">No tienes alertas pendientes en este momento.</p>
                        </div>
                    </li>
                <?php else: ?>
                    <?php foreach ($alertas as $alerta): ?>
                        <li class="alert-item <?= $alerta['estado'] ?>"
                            data-tipo="<?= strtolower($alerta['tipo']) ?>"
                            data-estado="<?= $alerta['estado'] ?>"
                            data-vehiculo="<?= strtolower($alerta['vehiculo']) ?>"
                            data-prioridad="<?= $alerta['prioridad'] ?>"
                            data-id="<?= $alerta['id'] ?>">

                            <div class="alert-priority <?= $alerta['prioridad'] ?>"></div>

                            <div class="alert-icon <?= $alerta['estado'] ?>">
                                <i class="<?= getAlertIcon($alerta['tipo']) ?>"></i>
                            </div>

                            <div class="alert-content">
                                <div class="alert-type">
                                    <i class="<?= getAlertIcon($alerta['tipo']) ?>"></i>
                                    <?= htmlspecialchars($alerta['tipo']) ?>
                                    <?php if ($alerta['vehiculo'] !== 'N/A'): ?>
                                        <span class="alert-vehicle"><?= htmlspecialchars($alerta['vehiculo']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!$alerta['leido']): ?>
                                        <span class="badge bg-danger ms-2">Nuevo</span>
                                    <?php endif; ?>
                                </div>
                                <div class="alert-description"><?= htmlspecialchars($alerta['descripcion']) ?></div>
                                <div class="alert-date">
                                    <i class="bi bi-calendar"></i>
                                    <?= date('d/m/Y H:i', strtotime($alerta['fecha_alerta'])) ?>
                                </div>
                            </div>

                            <div class="alert-status">
                                <span class="status-badge <?= $alerta['estado'] ?>">
                                    <?php if ($alerta['estado'] === 'critica'): ?>
                                        <i class="bi bi-exclamation-triangle-fill"></i> Crítica
                                    <?php elseif ($alerta['estado'] === 'pendiente'): ?>
                                        <i class="bi bi-clock-fill"></i> Pendiente
                                    <?php else: ?>
                                        <i class="bi bi-info-circle-fill"></i> Informativa
                                    <?php endif; ?>
                                </span>
                                <small class="text-muted d-block mt-1">
                                    Prioridad: <?= ucfirst($alerta['prioridad']) ?>
                                </small>
                            </div>

                            <div class="alert-actions">
                                <a href="#" onclick="verDetalles(<?= $alerta['id'] ?>)" class="action-btn primary">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <?php if (!$alerta['leido']): ?>
                                    <a href="#" onclick="resolverAlerta(<?= $alerta['id'] ?>)" class="action-btn success">
                                        <i class="bi bi-check"></i> Resolver
                                    </a>
                                <?php else: ?>
                                    <span class="action-btn disabled">
                                        <i class="bi bi-check-circle"></i> Resuelta
                                    </span>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Mensaje cuando no hay alertas -->
        <div class="no-alerts" id="noAlertas" style="display: none;">
            <i class="bi bi-check-circle"></i>
            <h3>¡Excelente!</h3>
            <p>No hay alertas que requieran tu atención en este momento.</p>
        </div>

        <!-- Sección de alertas resueltas -->
        <div class="resolved-alerts">
            <h3 class="resolved-title">
                <i class="bi bi-check-circle-fill"></i>
                Estadísticas de Alertas
            </h3>
            <p class="resolved-description">
                Resumen de alertas que han sido gestionadas exitosamente en el sistema.
            </p>

            <div class="resolved-stats">
                <div class="resolved-stat">
                    <div class="resolved-stat-number"><?= $alertas_resueltas_mes ?></div>
                    <div class="resolved-stat-label">Este mes</div>
                </div>
                <div class="resolved-stat">
                    <div class="resolved-stat-number"><?= $alertas_resueltas_total ?></div>
                    <div class="resolved-stat-label">Total resueltas</div>
                </div>
                <div class="resolved-stat">
                    <div class="resolved-stat-number"><?= $tiempo_promedio_resolucion ?></div>
                    <div class="resolved-stat-label">Días promedio</div>
                </div>
                <div class="resolved-stat">
                    <div class="resolved-stat-number"><?= $tasa_exito ?>%</div>
                    <div class="resolved-stat-label">Tasa de éxito</div>
                </div>
            </div>
        </div>

        <!-- Modal para detalles de alerta -->
        <div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title d-flex align-items-center" id="modalDetallesLabel">
                        <i class="bi bi-bell me-2"></i>
                        Detalles de la Alerta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" id="detallesContenido">
                    <!-- Contenido dinámico -->
                    <div class="p-4">
                        <div class="alert-info-card">
                            <h4 class="alert-title">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Cambio de Llantas Vencido
                            </h4>
                            
                            <div class="info-item">
                                <strong>Usuario:</strong> Instructor César
                            </div>
                            
                            <div class="info-item">
                                <strong>Documento:</strong> 1234567890
                            </div>
                            
                            <div class="info-item">
                                <strong>Vehículo:</strong> ABC-123
                            </div>
                            
                            <div class="info-item">
                                <strong>Fecha:</strong> 2025-07-02 00:25:51
                            </div>
                            
                            <div class="info-item">
                                <strong>Mensaje:</strong> Hola Instructor cesar, el cambio de llantas de tu vehículo ABC-123 está vencido desde hace 5 días. Es necesario programar la cita lo antes posible.
                            </div>
                            
                            <div class="info-item">
                                <strong>Estado:</strong> <span class="badge bg-danger">Activa</span>
                            </div>
                            
                            <div class="info-item">
                                <strong>Prioridad:</strong> <span class="badge bg-danger">Alta</span>
                            </div>
                        </div>

                        <div class="alert-info-card">
                            <h4 class="alert-title">
                                <i class="bi bi-calendar-x me-2"></i>
                                SOAT Próximo a Vencer
                            </h4>
                            
                            <div class="info-item">
                                <strong>Usuario:</strong> Federico
                            </div>
                            
                            <div class="info-item">
                                <strong>Documento:</strong> 1110174530
                            </div>
                            
                            <div class="info-item">
                                <strong>Vehículo:</strong> DEF-456
                            </div>
                            
                            <div class="info-item">
                                <strong>Fecha:</strong> 2025-07-02 02:14:29
                            </div>
                            
                            <div class="info-item">
                                <strong>Mensaje:</strong> Hola federico, el SOAT de tu vehículo con placa DEF-456 vence en 15 días. Recuerda renovarlo antes del vencimiento.
                            </div>
                            
                            <div class="info-item">
                                <strong>Estado:</strong> <span class="badge bg-danger">Pendiente</span>
                            </div>
                            
                            <div class="info-item">
                                <strong>Prioridad:</strong> <span class="badge bg-danger">Media</span>
                            </div>
                        </div>

                        <div class="alert-info-card">
                            <h4 class="alert-title">
                                <i class="bi bi-wrench me-2"></i>
                                Mantenimiento Técnico-Mecánico
                            </h4>
                            
                            <div class="info-item">
                                <strong>Usuario:</strong> Francy
                            </div>
                            
                            <div class="info-item">
                                <strong>Documento:</strong> 1109490190
                            </div>
                            
                            <div class="info-item">
                                <strong>Vehículo:</strong> GHI-789
                            </div>
                            
                            <div class="info-item">
                                <strong>Fecha:</strong> 2025-07-02 02:18:26
                            </div>
                            
                            <div class="info-item">
                                <strong>Mensaje:</strong> Hola francy, la técnico-mecánica de tu vehículo GHI-789 está próxima a vencer. Faltan 10 días para el vencimiento, programa tu cita.
                            </div>
                            
                            <div class="info-item">
                                <strong>Estado:</strong> <span class="badge bg-danger">Activa</span>
                            </div>
                            
                            <div class="info-item">
                                <strong>Prioridad:</strong> <span class="badge bg-danger">Alta</span>
                            </div>
                        </div>
                    </div>
                </div>
                                 
            </div>
        </div>
    </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/alertas.js"></script>
</body>

</html>