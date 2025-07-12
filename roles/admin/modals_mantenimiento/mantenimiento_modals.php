<?php
// Incluir archivo de conexión a la base de datos
require_once('../../conecct/conex.php');

// Crear instancia de conexión a la base de datos para cargar datos dinámicos
$db = new Database();
$con = $db->conectar();

// Consulta para obtener vehículos
$vehiculos_query = $con->prepare("SELECT placa FROM vehiculos ORDER BY placa");
$vehiculos_query->execute();
$vehiculos = $vehiculos_query->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener tipos de mantenimiento
$tipos_query = $con->prepare("SELECT id_tipo_mantenimiento, descripcion FROM tipo_mantenimiento ORDER BY descripcion");
$tipos_query->execute();
$tipos = $tipos_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Agregar Mantenimiento -->
<div class="modal fade" id="agregarMantenimientoModal" tabindex="-1" aria-labelledby="agregarMantenimientoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="agregarMantenimientoModalLabel">Agregar Nuevo Mantenimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <form id="agregarMantenimientoForm">
          <div class="mb-3">
            <label for="placaAgregar" class="form-label">Vehículo</label>
            <select class="form-select" id="placaAgregar" name="placa" required>
              <option value="">Seleccione un vehículo</option>
              <?php foreach($vehiculos as $vehiculo): ?>
                <option value="<?= htmlspecialchars($vehiculo['placa']) ?>"><?= htmlspecialchars($vehiculo['placa']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="tipoMantenimientoAgregar" class="form-label">Tipo de Mantenimiento</label>
            <select class="form-select" id="tipoMantenimientoAgregar" name="id_tipo_mantenimiento" required>
              <option value="">Seleccione el tipo</option>
              <?php foreach($tipos as $tipo): ?>
                <option value="<?= $tipo['id_tipo_mantenimiento'] ?>"><?= htmlspecialchars($tipo['descripcion']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="fechaProgramadaAgregar" class="form-label">Fecha Programada</label>
            <input type="date" class="form-control" id="fechaProgramadaAgregar" name="fecha_programada" required>
          </div>
          
          <div class="mb-3">
            <label for="fechaRealizadaAgregar" class="form-label">Fecha Realizada (Opcional)</label>
            <input type="date" class="form-control" id="fechaRealizadaAgregar" name="fecha_realizada">
          </div>
          
          <div class="mb-3">
            <label for="kilometrajeAgregar" class="form-label">Kilometraje Actual</label>
            <input type="number" class="form-control" id="kilometrajeAgregar" name="kilometraje_actual" min="0" step="1">
          </div>
          
          <div class="mb-3">
            <label for="observacionesAgregar" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observacionesAgregar" name="observaciones" rows="3" maxlength="500"></textarea>
            <div class="form-text">Máximo 500 caracteres</div>
          </div>
        </form>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardarMantenimiento">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Mantenimiento -->
<div class="modal fade" id="editarMantenimientoModal" tabindex="-1" aria-labelledby="editarMantenimientoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarMantenimientoModalLabel">Editar Mantenimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <form id="editarMantenimientoForm">
          <input type="hidden" id="idMantenimientoEditar" name="id_mantenimiento">
          
          <div class="mb-3">
            <label for="placaEditar" class="form-label">Vehículo</label>
            <select class="form-select" id="placaEditar" name="placa" required>
              <option value="">Seleccione un vehículo</option>
              <?php foreach($vehiculos as $vehiculo): ?>
                <option value="<?= htmlspecialchars($vehiculo['placa']) ?>"><?= htmlspecialchars($vehiculo['placa']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="tipoMantenimientoEditar" class="form-label">Tipo de Mantenimiento</label>
            <select class="form-select" id="tipoMantenimientoEditar" name="id_tipo_mantenimiento" required>
              <option value="">Seleccione el tipo</option>
              <?php foreach($tipos as $tipo): ?>
                <option value="<?= $tipo['id_tipo_mantenimiento'] ?>"><?= htmlspecialchars($tipo['descripcion']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="fechaProgramadaEditar" class="form-label">Fecha Programada</label>
            <input type="date" class="form-control" id="fechaProgramadaEditar" name="fecha_programada" required>
          </div>
          
          <div class="mb-3">
            <label for="fechaRealizadaEditar" class="form-label">Fecha Realizada (Opcional)</label>
            <input type="date" class="form-control" id="fechaRealizadaEditar" name="fecha_realizada">
          </div>
          
          <div class="mb-3">
            <label for="kilometrajeEditar" class="form-label">Kilometraje Actual</label>
            <input type="number" class="form-control" id="kilometrajeEditar" name="kilometraje_actual" min="0" step="1">
          </div>
          
          <div class="mb-3">
            <label for="observacionesEditar" class="form-label">Observaciones</label>
            <textarea class="form-control" id="observacionesEditar" name="observaciones" rows="3" maxlength="500"></textarea>
            <div class="form-text">Máximo 500 caracteres</div>
          </div>
        </form>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="actualizarMantenimiento">Actualizar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Mantenimiento -->
<div class="modal fade" id="verMantenimientoModal" tabindex="-1" aria-labelledby="verMantenimientoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verMantenimientoModalLabel">Detalles del Mantenimiento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <p><strong>ID:</strong> <span id="verIdMantenimiento"></span></p>
            <p><strong>Vehículo:</strong> <span id="verPlacaMantenimiento"></span></p>
            <p><strong>Tipo:</strong> <span id="verTipoMantenimiento"></span></p>
            <p><strong>Estado:</strong> <span id="verEstadoMantenimiento"></span></p>
          </div>
          <div class="col-md-6">
            <p><strong>Fecha Programada:</strong> <span id="verFechaProgramada"></span></p>
            <p><strong>Fecha Realizada:</strong> <span id="verFechaRealizada"></span></p>
            <p><strong>Kilometraje:</strong> <span id="verKilometraje"></span></p>
          </div>
        </div>
        <div class="mt-3">
          <p><strong>Observaciones:</strong></p>
          <p id="verObservaciones"></p>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Eliminar Mantenimiento -->
<div class="modal fade" id="eliminarMantenimientoModal" tabindex="-1" aria-labelledby="eliminarMantenimientoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="eliminarMantenimientoModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <p>¿Estás seguro de que quieres eliminar este mantenimiento?</p>
        <p><strong>Vehículo:</strong> <span id="elimPlacaMantenimiento"></span></p>
        <p><strong>Tipo:</strong> <span id="elimTipoMantenimiento"></span></p>
        <input type="hidden" id="elimIdMantenimiento">
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmarEliminarMantenimiento">Eliminar</button>
      </div>
    </div>
  </div>
</div>