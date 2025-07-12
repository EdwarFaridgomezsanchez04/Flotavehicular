<?php
session_start();
require_once('../../../conecct/conex.php');
require_once('../../../includes/validarsession.php');
$db = new Database();
$con = $db->conectar();

$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../../login/login.php');
    exit;
}

// Fetch nombre_completo and foto_perfil if not in session
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: '/Flotavehicular/roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Filtro por placa
$filtro_placa = $_GET['placa'] ?? '';

// Consulta de mantenimientos solo para los vehículos del usuario logueado y filtro por placa
if (!empty($filtro_placa)) {
    $mantenimientos_query = $con->prepare("
        SELECT m.*, v.placa, tm.descripcion AS tipo_mantenimiento,
               GROUP_CONCAT(CONCAT(c.Trabajo, ': $', d.subtotal) SEPARATOR ', ') AS detalles_trabajos
        FROM mantenimiento m
        JOIN vehiculos v ON m.placa = v.placa
        JOIN tipo_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
        LEFT JOIN detalles_mantenimiento_clasificacion d ON m.id_mantenimiento = d.id_mantenimiento
        LEFT JOIN clasificacion_trabajo c ON d.id_trabajo = c.id
        WHERE v.Documento = :documento
        AND v.placa LIKE :placa
        GROUP BY m.id_mantenimiento
        ORDER BY m.fecha_programada DESC
    ");
    $mantenimientos_query->execute([
        'documento' => $documento,
        'placa' => "%$filtro_placa%"
    ]);
} else {
    $mantenimientos_query = $con->prepare("
        SELECT m.*, v.placa, tm.descripcion AS tipo_mantenimiento,
               GROUP_CONCAT(CONCAT(c.Trabajo, ': $', d.subtotal) SEPARATOR ', ') AS detalles_trabajos
        FROM mantenimiento m
        JOIN vehiculos v ON m.placa = v.placa
        JOIN tipo_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
        LEFT JOIN detalles_mantenimiento_clasificacion d ON m.id_mantenimiento = d.id_mantenimiento
        LEFT JOIN clasificacion_trabajo c ON d.id_trabajo = c.id
        WHERE v.Documento = :documento
        GROUP BY m.id_mantenimiento
        ORDER BY m.fecha_programada DESC
    ");
    $mantenimientos_query->execute(['documento' => $documento]);
}
$mantenimientos = $mantenimientos_query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Flotax AGC - Mantenimiento General</title>
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
        * { font-family: 'Poppins', sans-serif; }

        body {
            background: #f0f2f5;
            padding-bottom: 60px;
        }

        .container {
            margin-top: 60px;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .table thead {
            background-color: #0d6efd;
            color: white;
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }

        .badge {
            font-size: 0.9rem;
            padding: 6px 10px;
            border-radius: 12px;
        }

        .estado-vigente { background-color: rgb(100, 253, 184); color: #0f5132; }
        .estado-vencido { background-color: rgb(248, 102, 114); color: rgb(123, 0, 0); }
        .estado-pendiente { background-color: rgb(255, 204, 0); color: rgb(102, 60, 0); }

        @media screen and (max-width: 768px) {
            .container { padding: 15px; }
            table { font-size: 0.9rem; }
            h2 { font-size: 1.4rem; }
        }
    </style>
  
</head>
<body>

    <?php include('../header.php'); ?>

    <div class="container">
        <h2><i class="fas fa-tools me-3"></i>Historial de Mantenimientos</h2>

        <!-- Campo de búsqueda por placa -->
        <div class="search-container">
            <div class="d-flex justify-content-center">
                <input type="text" id="filtroPlaca" class="form-control w-75 text-uppercase" placeholder="🔍 Buscar por placa del vehículo" value="<?= htmlspecialchars($filtro_placa) ?>" style="text-transform: uppercase;">
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-car me-2"></i>Placa</th>
                            <th><i class="fas fa-cogs me-2"></i>Tipo</th>
                            <th><i class="fas fa-calendar-alt me-2"></i>F. Programada</th>
                            <th><i class="fas fa-calendar-check me-2"></i>F. Realizada</th>
                            <th><i class="fas fa-tachometer-alt me-2"></i>Kilometraje</th>
                            <th><i class="fas fa-eye me-2"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($mantenimientos) > 0): ?>
                            <?php foreach ($mantenimientos as $mantenimiento): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($mantenimiento['placa']); ?></strong></td>
                                    <td>
                                        <?php
                                            $tipo = strtolower($mantenimiento['tipo_mantenimiento']);
                                            $clase = match ($tipo) {
                                                'correctivo' => 'estado-vencido',
                                                'preventivo' => 'estado-pendiente',
                                                default => 'bg-secondary text-white'
                                            };
                                        ?>
                                        <span class="badge <?= $clase ?>"><?= ucfirst($tipo); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($mantenimiento['fecha_programada']); ?></td>
                                    <td>
                                        <?php if($mantenimiento['fecha_realizada']): ?>
                                            <span class="badge estado-vigente"><?php echo htmlspecialchars($mantenimiento['fecha_realizada']); ?></span>
                                        <?php else: ?>
                                            <span class="badge estado-vencido">No realizada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($mantenimiento['kilometraje_actual'] ?: 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-details" onclick="verDetalles(<?php echo $mantenimiento['id_mantenimiento']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">
                                    <i class="fas fa-tools"></i><br>
                                    No hay registros de mantenimiento disponibles
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">
                        <i class="fas fa-info-circle me-2"></i>Detalles del Mantenimiento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalDetallesContent">
                    <!-- Contenido dinámico -->
                </div>
            </div>
        </div>
    </div>

    <?php include('../../../includes/auto_logout_modal.php'); ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Búsqueda automática
        const input = document.getElementById('filtroPlaca');
        let timeout = null;

        input.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const placa = input.value.trim().toUpperCase();
                const params = new URLSearchParams(window.location.search);
                if (placa) {
                    params.set('placa', placa);
                } else {
                    params.delete('placa');
                }
                window.location.href = window.location.pathname + '?' + params.toString();
            }, 500);
        });

        // Función para ver detalles
        function verDetalles(idMantenimiento) {
            // Buscar el mantenimiento en los datos PHP
            const mantenimientos = <?php echo json_encode($mantenimientos); ?>;
            const mantenimiento = mantenimientos.find(m => m.id_mantenimiento == idMantenimiento);
            
            if (mantenimiento) {
                const modalContent = document.getElementById('modalDetallesContent');
                
                modalContent.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-card">
                                <div class="detail-label">Placa del Vehículo</div>
                                <div class="detail-value"><i class="fas fa-car me-2"></i>${mantenimiento.placa}</div>
                                
                                <div class="detail-label">Tipo de Mantenimiento</div>
                                <div class="detail-value"><i class="fas fa-cogs me-2"></i>${mantenimiento.tipo_mantenimiento}</div>
                                
                                <div class="detail-label">Fecha Programada</div>
                                <div class="detail-value"><i class="fas fa-calendar-alt me-2"></i>${mantenimiento.fecha_programada}</div>
                                
                                <div class="detail-label">Fecha Realizada</div>
                                <div class="detail-value"><i class="fas fa-calendar-check me-2"></i>${mantenimiento.fecha_realizada || 'No realizada'}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-card">
                                <div class="detail-label">Kilometraje Actual</div>
                                <div class="detail-value"><i class="fas fa-tachometer-alt me-2"></i>${mantenimiento.kilometraje_actual || 'N/A'} km</div>
                                
                                <div class="detail-label">Próximo Mantenimiento (km)</div>
                                <div class="detail-value"><i class="fas fa-road me-2"></i>${mantenimiento.proximo_cambio_km || 'N/A'} km</div>
                                
                                <div class="detail-label">Próximo Mantenimiento (Fecha)</div>
                                <div class="detail-value"><i class="fas fa-calendar-plus me-2"></i>${mantenimiento.proximo_cambio_fecha || 'N/A'}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <div class="detail-label">Observaciones</div>
                        <div class="detail-value">
                            <i class="fas fa-sticky-note me-2"></i>
                            ${mantenimiento.observaciones || 'Sin observaciones registradas'}
                        </div>
                    </div>
                    
                    ${mantenimiento.detalles_trabajos ? `
                        <div class="detail-card">
                            <div class="detail-label">Trabajos Realizados</div>
                            <div class="trabajos-list">
                                <i class="fas fa-wrench me-2"></i>
                                ${mantenimiento.detalles_trabajos}
                            </div>
                        </div>
                    ` : ''}
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
                modal.show();
            }
        }
    </script>
</body>
</html>