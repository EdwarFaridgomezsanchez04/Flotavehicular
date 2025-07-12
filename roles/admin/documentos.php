<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

$db = new Database();
$con = $db->conectar();
$code = $_SESSION['documento'];

// Consulta mejorada para obtener documentos


$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

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

// Función para determinar el estado de un documento
function getDocumentStatus($fecha_vencimiento) {
    if (!$fecha_vencimiento) return 'no-disponible';
    
    $fecha_actual = new DateTime();
    $fecha_venc = new DateTime($fecha_vencimiento);
    $diferencia = $fecha_actual->diff($fecha_venc);
    
    if ($fecha_venc < $fecha_actual) {
        return 'vencido';
    } elseif ($diferencia->days <= 30) {
        return 'proximo';
    } else {
        return 'vigente';
    }
}

// Datos de documentos desde la base de datos
$documentos = [];

$query = $con->prepare("
    SELECT 
        v.placa,
        s.fecha_vencimiento AS soat_vence,
        t.fecha_vencimiento AS tecnomecanica_vence,
        l.fecha_vencimiento AS licencia_vence,
        u.nombre_completo AS propietario
    FROM vehiculos v
    LEFT JOIN soat s ON v.placa = s.id_placa
    LEFT JOIN tecnomecanica t ON v.placa = t.id_placa
    LEFT JOIN licencias l ON v.documento = l.id_documento
    LEFT JOIN usuarios u ON v.documento = u.documento
");

$query->execute();
$resultados = $query->fetchAll(PDO::FETCH_ASSOC);

foreach ($resultados as $row) {
    $estado_licencia = getDocumentStatus($row['licencia_vence']);

    $documentos[] = [
        'placa' => $row['placa'],
        'soat_vence' => $row['soat_vence'],
        'tecnomecanica_vence' => $row['tecnomecanica_vence'],
        'licencia_estado' => $estado_licencia,
        'propietario' => $row['propietario'] ?? 'Desconocido'
    ];
}
?>
 
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Visualizacion de Documentos - Flotax AGC</title>
  <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/documentos.css">

</head>
<body>

  <?php include 'menu.php'; ?>

  <div class="content">
    <div class="container-fluid">
      <!-- Header de la página -->
      <div class="page-header">
        <div>
          <h1 class="page-title">
            <i class="bi bi-folder-check"></i>
            Control de Documentos
          </h1>
          <p class="page-subtitle">Gestión y seguimiento de documentos vehiculares</p>
        </div>
      </div>

      <!-- Estadísticas rápidas -->
      <div class="stats-cards">
        <div class="stat-card success">
          <i class="bi bi-check-circle stat-icon"></i>
          <div class="stat-number">15</div>
          <div class="stat-label">Documentos al día</div>
        </div>
        <div class="stat-card warning">
          <i class="bi bi-exclamation-triangle stat-icon"></i>
          <div class="stat-number">3</div>
          <div class="stat-label">Por vencer (30 días)</div>
        </div>
        <div class="stat-card danger">
          <i class="bi bi-x-circle stat-icon"></i>
          <div class="stat-number">2</div>
          <div class="stat-label">Vencidos</div>
        </div>
        <div class="stat-card">
          <i class="bi bi-file-earmark stat-icon"></i>
          <div class="stat-number">20</div>
          <div class="stat-label">Total documentos</div>
        </div>
      </div>

      <!-- Controles superiores -->
      <div class="controls-section">
        <div class="buscador">
          <input type="text" id="buscar" placeholder="Buscar por placa, documento o estado..." onkeyup="filtrarTabla()">
        </div>
      </div>

      <!-- Tabla de documentos -->
      <div class="table-container">
        <div class="table-responsive">
          <table class="table" id="tablaDocumentos">
            <thead>
              <tr>
                <th><i class="bi bi-car-front"></i> Placa</th>
                <th><i class="bi bi-shield-check"></i> SOAT</th>
                <th><i class="bi bi-gear"></i> TecnoMecánica</th>
                <th><i class="bi bi-person-badge"></i> Licencia</th>
                                <th><i class="bi bi-file-earmark-text"></i>Propietario</th>
                <th><i class="bi bi-gear-fill"></i> Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documentos as $doc): ?>
                <tr>
                  <td>
                    <span class="placa-badge"><?= htmlspecialchars($doc['placa']) ?></span>
                  </td>
                  <td>
                    <?php 
                    $soat_status = getDocumentStatus($doc['soat_vence']);
                    $soat_fecha = new DateTime($doc['soat_vence']);
                    ?>
                   <center> <span class="status-<?= $soat_status ?> fecha-tooltip" 
                          data-tooltip="Vence: <?= $soat_fecha->format('d/m/Y') ?>">
                      <?php if ($soat_status === 'vigente'): ?>
                        Vigente
                      <?php elseif ($soat_status === 'proximo'): ?>
                        Por vencer
                      <?php else: ?>
                        Vencido
                      <?php endif; ?>
                    </span></center>
                  </td>
                  <td>
                    <?php 
                    $tecno_status = getDocumentStatus($doc['tecnomecanica_vence']);
                    $tecno_fecha = new DateTime($doc['tecnomecanica_vence']);
                    ?>
                    <span class="status-<?= $tecno_status ?> fecha-tooltip" 
                          data-tooltip="Vence: <?= $tecno_fecha->format('d/m/Y') ?>">
                      <?php if ($tecno_status === 'vigente'): ?>
                        Vigente
                      <?php elseif ($tecno_status === 'proximo'): ?>
                        Por vencer
                      <?php else: ?>
                        Vencido
                      <?php endif; ?>
                    </span>
                  </td>
                  <td>
                    <span class="status-<?= $doc['licencia_estado'] ?>">
                      <?= ucfirst($doc['licencia_estado']) ?>
                    </span>
                  </td>
                                       <td>
                    <?= htmlspecialchars($doc['propietario']) ?>
                  </td>
                  <td>
                    <button class="btn btn-info btn-sm" onclick="toggleDetalles('detalles_<?= $doc['placa'] ?>')">
                      <i class="bi bi-eye"></i> Ver
                    </button>
                  </td>
                </tr>
                <!-- Fila expandible para detalles -->
                <tr id="detalles_<?= $doc['placa'] ?>" class="detalles-row" style="display: none;">
                  <td colspan="6">
                    <div class="detalles-container">
                      <div class="row">
                        <div class="col-md-6">
                          <h6 class="text-muted mb-3">Información del Vehículo</h6>
                          <div class="mb-3">
                            <strong>Placa:</strong> <span class="badge bg-dark fs-6"><?= htmlspecialchars($doc['placa']) ?></span>
                          </div>
                          <div class="mb-3">
                            <strong>Propietario:</strong> <span><?= htmlspecialchars($doc['propietario']) ?></span>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <h6 class="text-muted mb-3">Resumen de Estados</h6>
                          <div class="resumen-estados">
                            <?php 
                            $soat_status = getDocumentStatus($doc['soat_vence']);
                            $tecno_status = getDocumentStatus($doc['tecnomecanica_vence']);
                            $licencia_status = $doc['licencia_estado'];
                            
                            $estados = [$soat_status, $tecno_status, $licencia_status];
                            $vencidos = count(array_filter($estados, function($e) { return $e === 'vencido'; }));
                            $proximos = count(array_filter($estados, function($e) { return $e === 'proximo'; }));
                            $vigentes = count(array_filter($estados, function($e) { return $e === 'vigente'; }));
                            ?>
                            <?php if ($vencidos > 0): ?>
                              <span class="badge bg-danger me-2"><?= $vencidos ?> Vencido<?= $vencidos > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                            <?php if ($proximos > 0): ?>
                              <span class="badge bg-warning text-dark me-2"><?= $proximos ?> Por vencer</span>
                            <?php endif; ?>
                            <?php if ($vigentes > 0): ?>
                              <span class="badge bg-success me-2"><?= $vigentes ?> Vigente<?= $vigentes > 1 ? 's' : '' ?></span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                      
                      <hr>
                      
                      <div class="row">
                        <div class="col-md-4">
                          <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                              <i class="bi bi-shield-check text-primary fs-1 mb-2"></i>
                              <h6 class="card-title">SOAT</h6>
                              <p class="mb-1"><strong>Vencimiento:</strong></p>
                              <p class="text-muted">
                                <?php 
                                if ($doc['soat_vence'] && $doc['soat_vence'] !== 'null') {
                                  $soat_fecha = new DateTime($doc['soat_vence']);
                                  echo $soat_fecha->format('d/m/Y');
                                } else {
                                  echo 'No disponible';
                                }
                                ?>
                              </p>
                              <span class="badge <?= $soat_status === 'vencido' ? 'bg-danger' : ($soat_status === 'proximo' ? 'bg-warning text-dark' : 'bg-success') ?>">
                                <?= ucfirst($soat_status) ?>
                              </span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                              <i class="bi bi-gear text-warning fs-1 mb-2"></i>
                              <h6 class="card-title">TecnoMecánica</h6>
                              <p class="mb-1"><strong>Vencimiento:</strong></p>
                              <p class="text-muted">
                                <?php 
                                if ($doc['tecnomecanica_vence'] && $doc['tecnomecanica_vence'] !== 'null') {
                                  $tecno_fecha = new DateTime($doc['tecnomecanica_vence']);
                                  echo $tecno_fecha->format('d/m/Y');
                                } else {
                                  echo 'No disponible';
                                }
                                ?>
                              </p>
                              <span class="badge <?= $tecno_status === 'vencido' ? 'bg-danger' : ($tecno_status === 'proximo' ? 'bg-warning text-dark' : 'bg-success') ?>">
                                <?= ucfirst($tecno_status) ?>
                              </span>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="card border-0 shadow-sm">
                            <div class="card-body text-center">
                              <i class="bi bi-person-badge text-success fs-1 mb-2"></i>
                              <h6 class="card-title">Licencia</h6>
                              <p class="mb-1"><strong>Estado:</strong></p>
                              <span class="badge <?= $licencia_status === 'vencido' ? 'bg-danger' : ($licencia_status === 'proximo' ? 'bg-warning text-dark' : 'bg-success') ?>">
                                <?= ucfirst($licencia_status) ?>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
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
        <ul class="pagination" id="paginacion"></ul>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Función de filtrado mejorada
    function filtrarTabla() {
      const input = document.getElementById('buscar').value.toLowerCase();
      const rows = document.querySelectorAll("#tablaDocumentos tbody tr");
      let visibleRows = 0;
      
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const isVisible = text.includes(input);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleRows++;
      });
      
      // Reconfigurar paginación después del filtrado
      configurarPaginacion();
    }

    // Paginación mejorada
    const filasPorPagina = 5;
    function configurarPaginacion() {
      const filas = Array.from(document.querySelectorAll('#tablaDocumentos tbody tr'))
                         .filter(row => row.style.display !== 'none');
      const totalPaginas = Math.ceil(filas.length / filasPorPagina);
      const paginacion = document.getElementById('paginacion');

      function mostrarPagina(pagina) {
        // Ocultar todas las filas
        document.querySelectorAll('#tablaDocumentos tbody tr').forEach(row => {
          row.style.display = 'none';
        });
        
        // Mostrar solo las filas de la página actual
        const inicio = (pagina - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;
        filas.slice(inicio, fin).forEach(row => {
          row.style.display = '';
        });
        
        // Actualizar botones de paginación
        document.querySelectorAll('#paginacion .page-item').forEach(btn => {
          btn.classList.remove('active');
        });
        document.querySelector(`#paginacion .page-item:nth-child(${pagina})`)?.classList.add('active');
      }

      // Crear botones de paginación
      paginacion.innerHTML = '';
      for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = 'page-item' + (i === 1 ? ' active' : '');
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.querySelector('a').addEventListener('click', e => {
          e.preventDefault();
          mostrarPagina(i);
        });
        paginacion.appendChild(li);
      }

      if (totalPaginas > 0) {
        mostrarPagina(1);
      }
    }

    // Función para mostrar/ocultar detalles
    function toggleDetalles(elementId) {
        const detallesRow = document.getElementById(elementId);
        if (detallesRow.style.display === 'none') {
            // Ocultar todos los detalles primero
            document.querySelectorAll('.detalles-row').forEach(row => {
                row.style.display = 'none';
            });
            // Mostrar los detalles seleccionados
            detallesRow.style.display = 'table-row';
        } else {
            // Ocultar los detalles
            detallesRow.style.display = 'none';
        }
    }

    // Inicializar cuando el DOM esté listo
    window.addEventListener('DOMContentLoaded', () => {
      configurarPaginacion();
      
      // Agregar animación a las filas
      const rows = document.querySelectorAll('#tablaDocumentos tbody tr');
      rows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
      });
    });
  </script>
</body>
</html>
