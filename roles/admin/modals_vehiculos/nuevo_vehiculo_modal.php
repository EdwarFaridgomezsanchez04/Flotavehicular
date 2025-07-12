<?php
// Archivo de modal mejorado para gestión de vehículos
require_once('../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();

// Obtener datos para los dropdowns
$marcas_query = $con->prepare("SELECT DISTINCT id_marca, nombre_marca FROM marca ORDER BY nombre_marca");
$marcas_query->execute();
$marcas = $marcas_query->fetchAll(PDO::FETCH_ASSOC);

$estados_query = $con->prepare("SELECT id_estado, estado FROM estado_vehiculo ORDER BY estado");
$estados_query->execute();
$estados = $estados_query->fetchAll(PDO::FETCH_ASSOC);

$usuarios_query = $con->prepare("SELECT documento, nombre_completo FROM usuarios WHERE id_estado_usuario NOT IN (1, 3) ORDER BY nombre_completo");
$usuarios_query->execute();
$usuarios = $usuarios_query->fetchAll(PDO::FETCH_ASSOC);

$tipos_query = $con->prepare("SELECT id_tipo_vehiculo, vehiculo FROM tipo_vehiculo ORDER BY vehiculo");
$tipos_query->execute();
$tipos_vehiculo = $tipos_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Modal Principal para Gestión de Vehículos -->
<div class="modal fade" id="vehiculoModal" tabindex="-1" aria-labelledby="vehiculoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modern-modal">
            <!-- Header del Modal -->
            <div class="modal-header gradient-header">
                <h5 class="modal-title" id="vehiculoModalLabel">
                    <i class="bi bi-car-front-fill me-2"></i>
                    <span id="modalTitleText">Gestión de Vehículos</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Navegación por Pestañas -->
            <div class="modal-nav">
                <ul class="nav nav-pills nav-justified" id="vehiculoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="agregar-tab" data-bs-toggle="pill" data-bs-target="#agregar-panel" type="button" role="tab">
                            <i class="bi bi-plus-circle me-2"></i>Agregar Vehículo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="editar-tab" data-bs-toggle="pill" data-bs-target="#editar-panel" type="button" role="tab">
                            <i class="bi bi-pencil-square me-2"></i>Editar Vehículo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="detalles-tab" data-bs-toggle="pill" data-bs-target="#detalles-panel" type="button" role="tab">
                            <i class="bi bi-info-circle me-2"></i>Ver Detalles
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Contenido de las Pestañas -->
            <div class="tab-content" id="vehiculoTabContent">
                
                <!-- Panel Agregar Vehículo -->
                <div class="tab-pane fade show active" id="agregar-panel" role="tabpanel">
                    <form id="formAgregarVehiculo" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row g-4">
                                <!-- Información Básica -->
                                <div class="col-12">
                                    <div class="section-header">
                                        <h6 class="section-title">
                                            <i class="bi bi-info-circle me-2"></i>Información Básica
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="placaAgregar" name="placa" placeholder="Placa" required>
                                        <label for="placaAgregar">Placa del Vehículo *</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="tipoVehiculoAgregar" name="tipo_vehiculo" required>
                                            <option value="">Seleccionar...</option>
                                            <?php foreach($tipos_vehiculo as $tipo): ?>
                                                <option value="<?= htmlspecialchars($tipo['id_tipo_vehiculo']) ?>">
                                                    <?= htmlspecialchars($tipo['vehiculo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="tipoVehiculoAgregar">Tipo de Vehículo *</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="marcaAgregar" name="id_marca" required>
                                            <option value="">Seleccionar...</option>
                                            <?php foreach($marcas as $marca): ?>
                                                <option value="<?= $marca['id_marca'] ?>">
                                                    <?= htmlspecialchars($marca['nombre_marca']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="marcaAgregar">Marca *</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="añoAgregar" name="modelo" min="1900" max="2027" placeholder="Modelo" required>
                                        <label for="modeloAgregar">Modelo (Año) *</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="modeloAgregar" name="modelo"  placeholder="Modelo" required>
                                        <label for="modeloAgregar">Modelo</label>
                                    </div>
                                </div>
                                
                                

                                <div class="cold-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="colorAgregar" name="color" placeholder="Color del Vehículo" required>
                                        <label for="colorAgregar">Color del Vehículo *</label>
                                </div>
                                
                                <div class="cold-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="vinAgregar" name="vin" placeholder="Número de Identificación del Vehículo (VIN)" required>
                                        <label for="vinAgregar">Número de Identificación del Vehículo (VIN) *</label>
                                    </div>
                                </div>
                                
                                <!-- Información del Propietario -->
                                <div class="col-12">
                                    <div class="section-header">
                                        <h6 class="section-title">
                                            <i class="bi bi-person me-2"></i>Información del Propietario
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="documentoAgregar" name="documento" required>
                                            <option value="">Seleccionar...</option>
                                            <?php foreach($usuarios as $usuario): ?>
                                                <option value="<?= $usuario['documento'] ?>">
                                                    <?= htmlspecialchars($usuario['nombre_completo']) . ' (' . $usuario['documento'] . ')' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="documentoAgregar">Propietario *</label>
                                    </div>
                                </div>
                                
                                <!-- Estado y Kilometraje -->
                                <div class="col-12">
                                    <div class="section-header">
                                        <h6 class="section-title">
                                            <i class="bi bi-gear me-2"></i>Estado y Mantenimiento
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="estadoAgregar" name="id_estado" required>
                                            <option value="">Seleccionar...</option>
                                            <?php foreach($estados as $estado): ?>
                                                <option value="<?= $estado['id_estado'] ?>">
                                                    <?= htmlspecialchars($estado['estado']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="estadoAgregar">Estado del Vehículo *</label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="kilometrajeAgregar" name="kilometraje_actual" min="0" placeholder="Kilometraje" required>
                                        <label for="kilometrajeAgregar">Kilometraje Actual *</label>
                                    </div>
                                </div>
                                
                                <!-- Foto del Vehículo -->
                                <div class="col-12">
                                    <div class="section-header">
                                        <h6 class="section-title">
                                            <i class="bi bi-camera me-2"></i>Fotografía del Vehículo
                                        </h6>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="upload-area">
                                        <input type="file" class="form-control" id="fotoVehiculoAgregar" name="foto_vehiculo" accept="image/*">
                                        <label for="fotoVehiculoAgregar" class="upload-label">
                                            <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                                            <span class="upload-text">Seleccionar imagen</span>
                                            <small class="text-muted">JPG, PNG, GIF (máx. 5MB)</small>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="preview-container">
                                        <img id="fotoPreviewAgregar" src="" alt="Vista previa" class="img-preview">
                                        <div class="preview-placeholder">
                                            <i class="bi bi-image fs-1 text-muted"></i>
                                            <p class="text-muted">Vista previa de la imagen</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Guardar Vehículo
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Panel Editar Vehículo -->
                <div class="tab-pane fade" id="editar-panel" role="tabpanel">
                    <form id="formEditarVehiculo" enctype="multipart/form-data">
                        <input type="hidden" id="editPlaca" name="placa">
                        <div class="modal-body">
                            <!-- Contenido similar al de agregar pero con campos prellenados -->
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Editando vehículo: <strong id="editPlacaDisplay"></strong>
                            </div>
                            <!-- Aquí van los mismos campos que en agregar pero con IDs diferentes -->
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-pencil-square me-2"></i>Actualizar Vehículo
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Panel Ver Detalles -->
                <div class="tab-pane fade" id="detalles-panel" role="tabpanel">
                    <div class="modal-body">
                        <div class="vehicle-details">
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <div class="details-card">
                                        <h6 class="card-title">Información del Vehículo</h6>
                                        <div class="detail-item">
                                            <span class="detail-label">Placa:</span>
                                            <span class="detail-value" id="detallePlaca">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Tipo:</span>
                                            <span class="detail-value" id="detalleTipo">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Marca:</span>
                                            <span class="detail-value" id="detalleMarca">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Modelo:</span>
                                            <span class="detail-value" id="detalleModelo">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Propietario:</span>
                                            <span class="detail-value" id="detallePropietario">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Estado:</span>
                                            <span class="detail-value" id="detalleEstado">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Kilometraje:</span>
                                            <span class="detail-value" id="detalleKilometraje">-</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="vehicle-photo">
                                        <img id="detalleFoto" src="" alt="Foto del vehículo" class="img-fluid rounded">
                                        <div class="photo-placeholder">
                                            <i class="bi bi-camera fs-1 text-muted"></i>
                                            <p class="text-muted">Sin fotografía</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-2"></i>Cerrar
                        </button>
                        <button type="button" class="btn btn-primary" onclick="editarVehiculoDesdeDetalles()">
                            <i class="bi bi-pencil-square me-2"></i>Editar Vehículo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación para Eliminar -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <i class="bi bi-trash3 text-danger" style="font-size: 3rem;"></i>
                </div>
                <h6>¿Está seguro de eliminar este vehículo?</h6>
                <p class="text-muted">Placa: <strong id="eliminarPlacaTexto"></strong></p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Esta acción no se puede deshacer
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmarEliminarBtn">
                    <i class="bi bi-trash3 me-2"></i>Eliminar Vehículo
                </button>
            </div>
        </div>
    </div>
</div>