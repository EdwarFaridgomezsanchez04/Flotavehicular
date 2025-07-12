<?php
require_once('../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();

// Obtener marcas únicas
$marcas_query = $con->prepare("SELECT id_marca, nombre_marca FROM marca ORDER BY nombre_marca");
$marcas_query->execute();
$marcas_raw = $marcas_query->fetchAll(PDO::FETCH_ASSOC);

// Eliminar duplicados por nombre
$marcas = [];
$nombres_unicos = [];
foreach ($marcas_raw as $marca) {
  $nombre = strtolower(trim($marca['nombre_marca']));
  if (!in_array($nombre, $nombres_unicos)) {
    $marcas[] = $marca;
    $nombres_unicos[] = $nombre;
  }
}

// Obtener colores 
$color_query = $con->prepare("SELECT id_color, color FROM colores ORDER BY color");
$color_query->execute();
$color_raw = $color_query->fetchAll(PDO::FETCH_ASSOC);

// Eliminar duplicados por nombre
$colores = [];
$nombres_unicos_color = [];
foreach ($color_raw as $color_item) {
  $nombre_color = strtolower(trim($color_item['color']));
  if (!in_array($nombre_color, $nombres_unicos_color)) {
    $colores[] = $color_item;
    $nombres_unicos_color[] = $nombre_color;
  }
}

$estados = $con->query("SELECT id_estado, estado FROM estado_vehiculo ORDER BY estado")->fetchAll(PDO::FETCH_ASSOC);

$usuarios = $con->query("
  SELECT u.documento, u.nombre_completo, u.id_rol 
  FROM usuarios u
  WHERE u.id_rol = 2
  ORDER BY u.nombre_completo
")->fetchAll(PDO::FETCH_ASSOC);

$tipos_vehiculo = $con->query("SELECT id_tipo_vehiculo, vehiculo FROM tipo_vehiculo ORDER BY vehiculo")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- MODAL AGREGAR VEHÍCULO -->
<div class="modal fade" id="modalAgregarVehiculo" tabindex="-1" aria-labelledby="modalAgregarVehiculoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Agregar Nuevo Vehículo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formAgregarVehiculo" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="tipoVehiculoAgregar" class="form-label">Tipo de Vehículo</label>
              <select class="form-select" id="tipoVehiculoAgregar" name="tipo_vehiculo" required>
                <option value="">Seleccionar tipo...</option>
                <?php foreach ($tipos_vehiculo as $tipo): ?>
                  <option value="<?= $tipo['id_tipo_vehiculo'] ?>"><?= htmlspecialchars($tipo['vehiculo']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="marcaAgregar" class="form-label">Marca</label>
              <select class="form-select" id="marcaAgregar" name="id_marca" required>
                <option value="">Seleccionar marca...</option>
                <?php foreach ($marcas as $marca): ?>
                  <option value="<?= $marca['id_marca'] ?>"><?= htmlspecialchars($marca['nombre_marca']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Segunda fila: Placa y Año -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div id="grupo_placa">
                <label for="placaAgregar" class="form-label">Placa</label>
                <input type="text" class="form-control" id="placaAgregar" name="placa" required style="text-transform: uppercase;" placeholder="ABC123">
                <div id="validacion_placa" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
                  Formato: 3 letras + 3 números (ej: ABC123)
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div id="grupo_anio">
                <label for="anioAgregar" class="form-label">Año</label>
                <input type="number" class="form-control" id="anioAgregar" name="anio" min="1900" max="2099" required>
                <div id="validacion_anio" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
                  El año debe estar entre 1900 y el año actual + 1
                </div>
              </div>
            </div>
          </div>

          <!-- Tercera fila: Modelo y Color -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div id="grupo_modelo">
                <label for="modeloAgregar" class="form-label">Modelo</label>
                <input type="text" class="form-control" id="modeloAgregar" name="modelo" required placeholder="Ej: Corolla, Civic">
                <div id="validacion_modelo" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
                  Solo letras, números, espacios y guiones (2-50 caracteres)
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="colorAgregar" class="form-label">Color</label>
              <select class="form-select" id="colorAgregar" name="id_color" required>
                <option value="">Seleccionar color...</option>
                <?php foreach ($colores as $color_item): ?>
                  <option value="<?= $color_item['id_color'] ?>"><?= htmlspecialchars($color_item['color']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Cuarta fila: Kilometraje y Estado -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div id="grupo_kilometraje">
                <label for="kilometrajeAgregar" class="form-label">Kilometraje Actual</label>
                <input type="number" class="form-control" id="kilometrajeAgregar" name="kilometraje_actual" min="0" required>
                <div id="validacion_kilometraje" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
                  El kilometraje debe estar entre 0 y 999,999
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label for="estadoAgregar" class="form-label">Estado</label>
              <select class="form-select" id="estadoAgregar" name="id_estado" required>
                <option value="">Seleccionar estado...</option>
                <?php foreach ($estados as $estado): ?>
                  <option value="<?= $estado['id_estado'] ?>"><?= htmlspecialchars($estado['estado']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Quinta fila: Propietario y Foto -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="documentoAgregar" class="form-label">Propietario</label>
              <select class="form-select" id="documentoAgregar" name="documento" required>
                <option value="">Seleccionar propietario...</option>
                <?php foreach ($usuarios as $usuario): ?>
                  <option value="<?= $usuario['documento'] ?>" data-rol="<?= $usuario['id_rol'] ?>">
                    <?= htmlspecialchars($usuario['nombre_completo']) ?> (<?= $usuario['documento'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="fotoVehiculoAgregar" class="form-label">Foto del Vehículo</label>
              <input type="file" class="form-control" id="fotoVehiculoAgregar" name="foto_vehiculo" accept="image/*" onchange="previewImage(this, 'fotoPreviewAgregar')">
            </div>
          </div>

          <div class="mb-3">
            <img id="fotoPreviewAgregar" src="" alt="Vista previa" class="img-fluid" style="display:none; max-height:200px;">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i> Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Guardar Vehículo</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR VEHÍCULO -->
<div class="modal fade" id="editarVehiculoModal" tabindex="-1" aria-labelledby="editarVehiculoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarVehiculoModalLabel">
          <i class="bi bi-pencil-square"></i> Editar Vehículo
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form id="editarVehiculoForm" enctype="multipart/form-data">
        <div class="modal-body">
          <input type="hidden" id="editPlaca" name="placa">
          <div id="grupo_placa_edit" style="display: none;">
            <div id="validacion_placa_edit" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
              Formato: 3 letras + 3 números (ej: ABC123)
            </div>
          </div>

          <!-- Primera fila: Tipo de Vehículo y Marca -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editTipoVehiculo" class="form-label">Tipo de Vehículo</label>
              <select class="form-select" id="editTipoVehiculo" name="tipo_vehiculo" required>
                <option value="">Seleccione un tipo...</option>
                <?php foreach ($tipos_vehiculo as $tipo): ?>
                  <option value="<?= htmlspecialchars($tipo['id_tipo_vehiculo']) ?>">
                    <?= htmlspecialchars($tipo['vehiculo']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="editMarca" class="form-label">Marca</label>
              <select class="form-select" id="editMarca" name="id_marca" required>
                <option value="">Seleccione una marca</option>
                <?php foreach ($marcas as $marca): ?>
                  <option value="<?= htmlspecialchars($marca['id_marca']) ?>">
                    <?= htmlspecialchars($marca['nombre_marca']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Segunda fila: Año y Modelo -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <div id="grupo_anio_edit">
                <label for="editAnio" class="form-label">Año</label>
                <input type="number" class="form-control" id="editAnio" name="anio" min="1900" max="2099" required>
                <div id="validacion_anio_edit" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
                  El año debe estar entre 1900 y el año actual + 1
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div id="grupo_modelo_edit">
                <label for="editModelo" class="form-label">Modelo</label>
                <input type="text" class="form-control" id="editModelo" name="modelo" required placeholder="Ej: Corolla, Civic">
                <div id="validacion_modelo_edit" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
                  Solo letras, números, espacios y guiones (2-50 caracteres)
                </div>
              </div>
            </div>
          </div>

          <!-- Tercera fila: Color y Kilometraje -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editColor" class="form-label">Color</label>
              <select class="form-select" id="editColor" name="id_color" required>
                <option value="">Seleccione un color</option>
                <?php foreach ($colores as $color_item): ?>
                  <option value="<?= htmlspecialchars($color_item['id_color']) ?>">
                    <?= htmlspecialchars($color_item['color']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <div id="grupo_kilometraje_edit">
                <label for="editKilometraje" class="form-label">Kilometraje Actual</label>
                <input type="number" class="form-control" id="editKilometraje" name="kilometraje_actual" min="0" required>
                <div id="validacion_kilometraje_edit" class="text-danger" style="opacity: 0; font-size: 0.8em; margin-top: 5px;">
                  El kilometraje debe estar entre 0 y 999,999
                </div>
              </div>
            </div>
          </div>

          <!-- Cuarta fila: Estado y Propietario -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="editEstado" class="form-label">Estado</label>
              <select class="form-select" id="editEstado" name="id_estado" required>
                <option value="">Seleccione un estado</option>
                <?php foreach ($estados as $estado): ?>
                  <option value="<?= htmlspecialchars($estado['id_estado']) ?>">
                    <?= htmlspecialchars($estado['estado']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="editDocumento" class="form-label">Propietario</label>
              <select class="form-select" id="editDocumento" name="documento" required>
                <option value="">Seleccionar propietario...</option>
                <?php foreach ($usuarios as $usuario): ?>
                  <option value="<?= $usuario['documento'] ?>" data-rol="<?= $usuario['id_rol'] ?>">
                    <?= htmlspecialchars($usuario['nombre_completo']) ?> (<?= $usuario['documento'] ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <!-- Quinta fila: Foto del Vehículo -->
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="editFoto" class="form-label">Foto del Vehículo</label>
              <input type="file" class="form-control" id="editFoto" name="foto_vehiculo" accept="image/*" onchange="previewImage(this, 'editFotoPreview')">
              <small class="form-text text-muted">Deje en blanco para mantener la imagen actual.</small>
            </div>
          </div>

          <!-- Vista previa de la imagen -->
          <div class="mb-3">
            <img id="editFotoPreview" src="" alt="Vista previa" class="img-fluid" style="display: none; max-height: 200px;">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL VER DETALLES DEL VEHÍCULO -->
<div class="modal fade" id="verDetallesVehiculoModal" tabindex="-1" aria-labelledby="verDetallesVehiculoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="verDetallesVehiculoModalLabel">
          <i class="bi bi-info-circle-fill"></i> Detalles Completos del Vehículo
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row">
          <!-- Información Principal del Vehículo -->
          <div class="col-md-8">
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-car-front"></i> Información del Vehículo</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Placa:</label>
                      <p class="mb-0" id="detallePlaca">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Tipo de Vehículo:</label>
                      <p class="mb-0" id="detalleTipoVehiculo">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Marca:</label>
                      <p class="mb-0" id="detalleMarca">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Modelo:</label>
                      <p class="mb-0" id="detalleModelo">-</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Año:</label>
                      <p class="mb-0" id="detalleAnio">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Color:</label>
                      <p class="mb-0" id="detalleColor">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Estado:</label>
                      <p class="mb-0" id="detalleEstado">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Kilometraje Actual:</label>
                      <p class="mb-0" id="detalleKilometraje">-</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Información del Propietario -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-person"></i> Información del Propietario</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Documento:</label>
                      <p class="mb-0" id="detalleDocumento">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Nombre Completo:</label>
                      <p class="mb-0" id="detalleNombrePropietario">-</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Email:</label>
                      <p class="mb-0" id="detalleEmailPropietario">-</p>
                    </div>
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Teléfono:</label>
                      <p class="mb-0" id="detalleTelefonoPropietario">-</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Información de Registro -->
            <div class="card mb-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Información de Registro</h6>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Fecha de Registro:</label>
                      <p class="mb-0" id="detalleFechaRegistro">-</p>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="fw-bold text-muted">Registrado por:</label>
                      <p class="mb-0" id="detalleRegistradoPor">-</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Imagen del Vehículo -->
          <div class="col-md-4">
            <div class="card">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-image"></i> Foto del Vehículo</h6>
              </div>
              <div class="card-body text-center">
                <img id="detalleFotoVehiculo" src="" alt="Foto del vehículo"
                  class="img-fluid rounded" style="max-height: 300px; object-fit: cover;">
                <div class="mt-2">
                  <small class="text-muted" id="detalleInfoFoto">Sin foto disponible</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Cerrar
        </button>
        <button type="button" class="btn btn-primary" id="btnEditarDesdeDetalles">
          <i class="bi bi-pencil-square"></i> Editar Vehículo
        </button>
      </div>
    </div>
  </div>
</div>

<script>
window.addEventListener("DOMContentLoaded", () => {
  // Referencias a campos
  const form = document.getElementById("formAgregarVehiculo");
  const tipoVehiculo = document.getElementById("tipoVehiculoAgregar");
  const marca = document.getElementById("marcaAgregar");
  const placa = document.getElementById("placaAgregar");
  const anio = document.getElementById("anioAgregar");
  const modelo = document.getElementById("modeloAgregar");
  const color = document.getElementById("colorAgregar");
  const kilometraje = document.getElementById("kilometrajeAgregar");
  const estado = document.getElementById("estadoAgregar");
  const propietario = document.getElementById("documentoAgregar");

  form.addEventListener("submit", function(e) {
    // Validar tipo de vehículo
    if (tipoVehiculo.value === "") {
      e.preventDefault();
      alert("Por favor selecciona un tipo de vehículo.");
      tipoVehiculo.focus();
      return;
    }

    // Validar marca
    if (marca.value === "") {
      e.preventDefault();
      alert("Por favor selecciona una marca.");
      marca.focus();
      return;
    }

    // Validar placa
    const placaValor = placa.value.trim().toUpperCase();
    const placaRegex = /^[A-Z]{3}[0-9]{3}$/;
    if (!placaRegex.test(placaValor)) {
      e.preventDefault();
      alert("La placa debe tener el formato correcto: 3 letras seguidas de 3 números (ej: ABC123).");
      placa.focus();
      return;
    }

    // Validar año
    const anioValor = parseInt(anio.value, 10);
    const anioActual = new Date().getFullYear();
    if (isNaN(anioValor) || anioValor < 1900 || anioValor > anioActual + 1) {
      e.preventDefault();
      alert("El año debe estar entre 1900 y " + (anioActual + 1));
      anio.focus();
      return;
    }

    // Validar modelo
    const modeloValor = modelo.value.trim();
    const modeloRegex = /^[A-Za-z0-9\s\-]{2,50}$/;
    if (!modeloRegex.test(modeloValor)) {
      e.preventDefault();
      alert("El modelo debe tener entre 2 y 50 caracteres, solo letras, números, espacios o guiones.");
      modelo.focus();
      return;
    }

    // Validar color
    if (color.value === "") {
      e.preventDefault();
      alert("Por favor selecciona un color.");
      color.focus();
      return;
    }

    // Validar kilometraje
    const kmValor = parseInt(kilometraje.value, 10);
    if (isNaN(kmValor) || kmValor < 0 || kmValor > 999999) {
      e.preventDefault();
      alert("El kilometraje debe ser un número entre 0 y 999999.");
      kilometraje.focus();
      return;
    }

    // Validar estado
    if (estado.value === "") {
      e.preventDefault();
      alert("Por favor selecciona un estado del vehículo.");
      estado.focus();
      return;
    }

    // Validar propietario
    if (propietario.value === "") {
      e.preventDefault();
      alert("Por favor selecciona un propietario.");
      propietario.focus();
      return;
    }

    // Validación completa, permite enviar
  });
});
</script>