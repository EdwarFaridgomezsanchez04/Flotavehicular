<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';

$db = new Database();
$con = $db->conectar();

// Validar sesión
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Obtener datos de usuario si no están en sesión
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $query->bindParam(':documento', $documento);
    $query->execute();
    $usuario = $query->fetch(PDO::FETCH_ASSOC);

    $nombre_completo = $usuario['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $usuario['foto_perfil'] ?: 'roles/user/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Consulta de estadísticas
$stats_query = $con->prepare("
    SELECT 
        COUNT(*) AS total_mantenimientos,
        SUM(CASE WHEN fecha_realizada IS NOT NULL THEN 1 ELSE 0 END) AS mantenimientos_completados,
        SUM(CASE WHEN fecha_realizada IS NULL THEN 1 ELSE 0 END) AS mantenimientos_pendientes
    FROM mantenimiento m
");
$stats_query->execute();
$stats = $stats_query->fetch(PDO::FETCH_ASSOC);

// Consulta de mantenimientos
$mantenimientos_query = $con->prepare("
    SELECT 
        m.id_mantenimiento AS id,
        m.placa,
        tm.descripcion AS tipo,
        m.kilometraje_actual AS kilometraje,
        m.observaciones AS descripcion,
        m.fecha_programada AS fecha,
        m.fecha_realizada AS fecha_completado,
        m.proximo_cambio_fecha,
        CASE 
            WHEN m.fecha_realizada IS NULL THEN 'Pendiente'
            ELSE 'Completado'
        END AS estado,
        m.observaciones AS detalles
    FROM mantenimiento m
    LEFT JOIN tipo_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
    ORDER BY m.fecha_programada DESC
");
$mantenimientos_query->execute();
$mantenimientos = $mantenimientos_query->fetchAll(PDO::FETCH_ASSOC);

// Consulta de vehículos para el filtro
$vehiculos_query = $con->prepare("
    SELECT DISTINCT placa 
    FROM vehiculos 
    ORDER BY placa
");
$vehiculos_query->execute();
$vehiculos = $vehiculos_query->fetchAll(PDO::FETCH_ASSOC);

// Funciones para estilos CSS
function getEstadoClass($estado) {
    switch (strtolower($estado)) {
        case 'completado': return 'estado-completado';
        case 'pendiente': return 'estado-pendiente';
        default: return '';
    }
}
function getTipoClass($tipo) {
    switch (strtolower($tipo)) {
        case 'preventivo': return 'tipo-preventivo';
        case 'correctivo': return 'tipo-correctivo';
        case 'emergencia': return 'tipo-emergencia';
        default: return '';
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Mantenimientos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/mantenimiento.css">
    <link rel="stylesheet" href="css/mantenimiento-modals.css">
</head>
<body>
  <?php include 'menu.php'; ?>

  <div class="content">
    <!-- Header de la página -->
    <div class="page-header">
      <div>
        <h1 class="page-title">
          <i class="bi bi-tools"></i>
          Historial de Mantenimientos
        </h1>
        <p class="page-subtitle">Registro y seguimiento de mantenimientos vehiculares</p>
      </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="stats-overview">
      <div class="stat-card total">
        <i class="bi bi-clipboard-check stat-icon"></i>
        <div class="stat-number"><?= $stats['total_mantenimientos'] ?? 0 ?></div>
        <div class="stat-label">Total Mantenimientos</div>
      </div>
      <div class="stat-card completados">
        <i class="bi bi-check-circle stat-icon"></i>
        <div class="stat-number"><?= $stats['mantenimientos_completados'] ?? 0 ?></div>
        <div class="stat-label">Completados</div>
      </div>
      <div class="stat-card pendientes">
        <i class="bi bi-hourglass-split stat-icon"></i>
        <div class="stat-number"><?= $stats['mantenimientos_pendientes'] ?? 0 ?></div>
        <div class="stat-label">Pendientes</div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
      <div class="filter-group">
        <label class="filter-label">Estado</label>
        <select class="filter-select" id="filtroEstado" onchange="aplicarFiltros()">
          <option value="">Todos los estados</option>
          <option value="completado">Completado</option>
          <option value="pendiente">Pendiente</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Tipo</label>
        <select class="filter-select" id="filtroTipo" onchange="aplicarFiltros()">
          <option value="">Todos los tipos</option>
          <option value="preventivo">Preventivo</option>
          <option value="correctivo">Correctivo</option>
        </select>
      </div>
      <div class="filter-group">
        <label class="filter-label">Desde</label>
        <input type="date" class="filter-date" id="filtroDesde" onchange="aplicarFiltros()">
      </div>
      <div class="filter-group">
        <label class="filter-label">Hasta</label>
        <input type="date" class="filter-date" id="filtroHasta" onchange="aplicarFiltros()">
      </div>
      <div class="filter-group">
        <label class="filter-label">Vehículo</label>
        <select class="filter-select" id="filtroVehiculo" onchange="aplicarFiltros()">
          <option value="">Todos los vehículos</option>
          <?php foreach($vehiculos as $vehiculo): ?>
            <option value="<?= htmlspecialchars($vehiculo['placa']) ?>"><?= htmlspecialchars($vehiculo['placa']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Controles superiores -->
    <div class="controls-section">
      <div class="buscador">
        <input type="text" id="buscar" class="form-control" placeholder="Buscar por placa, tipo, descripción..." onkeyup="filtrarTabla()">
      </div>
    </div>

    <!-- Tabla de mantenimientos -->
    <div class="table-container">
      <div class="table-responsive">
        <table class="table" id="tablaUsuarios">
          <thead>
            <tr>
              <th><i class="bi bi-calendar"></i> Fecha</th>
              <th><i class="bi bi-car-front"></i> Placa</th>
              <th><i class="bi bi-tag"></i> Tipo</th>
              <th><i class="bi bi-speedometer2"></i> Kilometraje</th>
              <th><i class="bi bi-card-text"></i> Descripción</th>
              <th><i class="bi bi-info-circle"></i> Estado</th>
              <th><i class="bi bi-tools"></i> Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($mantenimientos as $mant): ?>
            <tr>
              <td><span class="fecha-cell"><?= date('d/m/Y', strtotime($mant['fecha'])) ?></span></td>
              <td><span class="placa-cell"><?= htmlspecialchars($mant['placa']) ?></span></td>
              <td><span class="tipo-cell <?= getTipoClass($mant['tipo']) ?>"><?= htmlspecialchars($mant['tipo']) ?></span></td>
              <td><span class="kilometraje-cell"><?= $mant['kilometraje'] ? number_format($mant['kilometraje'], 0, ',', '.') . ' km' : 'N/A' ?></span></td>
              <td><span class="descripcion-cell"><?= htmlspecialchars($mant['descripcion'] ?? 'Sin descripción') ?></span></td>
              <td>
                <span class="estado-cell <?= getEstadoClass($mant['estado']) ?>">
                  <?php if ($mant['estado'] == 'Completado'): ?>
                    <i class="bi bi-check-circle-fill"></i>
                  <?php elseif ($mant['estado'] == 'Pendiente'): ?>
                    <i class="bi bi-clock"></i>
                  <?php else: ?>
                    <i class="bi bi-x-circle"></i>
                  <?php endif; ?>
                  <?= htmlspecialchars($mant['estado']) ?>
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button class="action-icon view view-mantenimiento" data-id="<?= $mant['id'] ?>" title="Ver detalles">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="action-icon edit edit-mantenimiento" data-id="<?= $mant['id'] ?>" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="action-icon delete delete-mantenimiento" data-id="<?= $mant['id'] ?>" data-placa="<?= htmlspecialchars($mant['placa']) ?>" data-tipo="<?= htmlspecialchars($mant['tipo']) ?>" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </button>
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

    <!-- Botón agregar -->
    <div class="boton-agregar">
      <button id="btnAgregarMantenimiento" class="boton">
        <i class="bi bi-plus-circle"></i>
        <i class="bi bi-tools"></i>
        Registrar Mantenimiento
      </button>
    </div>
  </div>

  <!-- Incluir modales -->
  <?php include 'modals_mantenimiento/mantenimiento_modals.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="modals_mantenimiento/mantenimiento-scripts.js"></script>
  <script>
    // Función de filtrado mejorada
    function filtrarTabla() {
      const input = document.getElementById('buscar').value.toLowerCase();
      const rows = document.querySelectorAll("#tablaUsuarios tbody tr");
      let visibleRows = 0;
      
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const isVisible = text.includes(input);
        row.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleRows++;
      });
      
      configurarPaginacion();
    }

    // Aplicar filtros combinados
    function aplicarFiltros() {
      const filtroEstado = document.getElementById('filtroEstado').value.toLowerCase();
      const filtroTipo = document.getElementById('filtroTipo').value.toLowerCase();
      const filtroVehiculo = document.getElementById('filtroVehiculo').value.toLowerCase();
      const filtroDesde = document.getElementById('filtroDesde').value;
      const filtroHasta = document.getElementById('filtroHasta').value;
      
      const rows = document.querySelectorAll("#tablaUsuarios tbody tr");
      
      rows.forEach(row => {
        const estado = row.querySelector('.estado-cell')?.textContent.toLowerCase() || '';
        const tipo = row.querySelector('.tipo-cell')?.textContent.toLowerCase() || '';
        const placa = row.querySelector('.placa-cell')?.textContent.toLowerCase() || '';
        const fechaText = row.querySelector('.fecha-cell')?.textContent || '';
        
        // Convertir fecha de dd/mm/yyyy a yyyy-mm-dd para comparación
        const fechaParts = fechaText.split('/');
        const fecha = fechaParts.length === 3 ? 
          `${fechaParts[2]}-${fechaParts[1]}-${fechaParts[0]}` : '';
        
        let mostrar = true;
        
        if (filtroEstado && !estado.includes(filtroEstado)) mostrar = false;
        if (filtroTipo && !tipo.includes(filtroTipo)) mostrar = false;
        if (filtroVehiculo && !placa.includes(filtroVehiculo)) mostrar = false;
        if (filtroDesde && fecha < filtroDesde) mostrar = false;
        if (filtroHasta && fecha > filtroHasta) mostrar = false;
        
        row.style.display = mostrar ? '' : 'none';
      });
      
      configurarPaginacion();
    }

    // Paginación mejorada
    const filasPorPagina = 10;
    function configurarPaginacion() {
      const filas = Array.from(document.querySelectorAll('#tablaUsuarios tbody tr'))
                         .filter(row => row.style.display !== 'none');
      const totalPaginas = Math.ceil(filas.length / filasPorPagina);
      const paginacion = document.getElementById('paginacion');

      function mostrarPagina(pagina) {
        document.querySelectorAll('#tablaUsuarios tbody tr').forEach(row => {
          row.style.display = 'none';
        });
        
        const inicio = (pagina - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;
        filas.slice(inicio, fin).forEach(row => {
          row.style.display = '';
        });
        
        document.querySelectorAll('#paginacion .page-item').forEach(btn => {
          btn.classList.remove('active');
        });
        document.querySelector(`#paginacion .page-item:nth-child(${pagina})`)?.classList.add('active');
      }

      paginacion.innerHTML = '';
      for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = 'page-item' + (i === 1 ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', function (e) {
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

    // Inicializar cuando el DOM esté listo
    window.addEventListener('DOMContentLoaded', () => {
      configurarPaginacion();
      
      // Agregar animación a las filas
      const rows = document.querySelectorAll('#tablaUsuarios tbody tr');
      rows.forEach((row, index) => {
        row.style.animationDelay = `${index * 0.1}s`;
      });
    });
  </script>
</body>
</html>
