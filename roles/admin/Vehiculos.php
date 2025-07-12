<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';
$db = new Database();
$con = $db->conectar();

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

$marcas_stmt = $con->prepare("SELECT DISTINCT nombre_marca FROM marca ORDER BY nombre_marca ASC");
$marcas_stmt->execute();
$marcas_unicas = $marcas_stmt->fetchAll(PDO::FETCH_COLUMN);



$estado = $con->prepare("SELECT DISTINCT estado FROM estado_vehiculo ORDER BY estado DESC");
$estado->execute();
$estado = $estado->fetchAll(PDO::FETCH_COLUMN);

$stats_query = $con->prepare("SELECT COUNT(*) AS total_vehiculos,
        SUM(CASE WHEN vehiculos.id_estado = '10' THEN 1 ELSE 0 END) AS vehiculos_activos,
        SUM(CASE WHEN vehiculos.id_estado = '2' THEN 1 ELSE 0 END) AS vehiculos_inactivos,
        SUM(CASE WHEN vehiculos.id_estado = '3' THEN 1 ELSE 0 END) AS vehiculos_mantenimiento
    FROM vehiculos 
    INNER JOIN estado_vehiculo ON vehiculos.id_estado = estado_vehiculo.id_estado");
$stats_query->execute();
$stats = $stats_query->fetch(PDO::FETCH_ASSOC);

function getEstadoClass($estado)
{
    switch (strtolower($estado)) {
        case 'activo':
            return 'estado-activo';
        case 'en mantenimiento':
            return 'estado-mantenimiento';
        case 'inactivo':
            return 'estado-inactivo';
        default:
            return 'estado-activo';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Gestión de Vehículos - Flotax AGC</title>
    <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/vehiculos.css">
    <link rel="stylesheet" href="css/vehiculos-modal.css">
    <link rel="stylesheet" href="css/vehiculo-validaciones.css">

</head>

<body>
    <?php include 'menu.php'; ?>
    <div class="content">
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-truck"></i>
                    Gestión de Vehículos
                </h1>
                <p class="page-subtitle">Administración y control de la flota vehicular</p>
            </div>
        </div>

        <div class="stats-overview">
            <div class="stat-card vehicles">
                <i class="bi bi-truck stat-icon"></i>
                <div class="stat-number"><?= $stats['total_vehiculos'] ?></div>
                <div class="stat-label">Total Vehículos</div>
            </div>
            <div class="stat-card active">
                <i class="bi bi-check-circle stat-icon"></i>
                <div class="stat-number"><?= $stats['vehiculos_activos'] ?></div>
                <div class="stat-label">Vehículos en Uso</div>
            </div>
            <div class="stat-card maintenance">
                <i class="bi bi-tools stat-icon"></i>
                <div class="stat-number"><?= $stats['vehiculos_mantenimiento'] ?></div>
                <div class="stat-label">En Mantenimiento</div>
            </div>
            <div class="stat-card inactive">
                <i class="bi bi-x-circle stat-icon"></i>
                <div class="stat-number"><?= $stats['vehiculos_inactivos'] ?></div>
                <div class="stat-label">Inactivos</div>
            </div>
        </div>

        <div class="filters-section">
            <div class="filter-group">
                <label class="filter-label">Estado</label>
                <select class="filter-select" id="filtroEstado" onchange="aplicarFiltros()">
                    <option value="">Todos los Estados</option>
                    <?php foreach ($estado as $estadoItem): ?>
                        <option value="<?= htmlspecialchars($estadoItem) ?>">
                            <?= htmlspecialchars($estadoItem) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Marca</label>
                <select class="filter-select" id="filtroMarca" onchange="aplicarFiltros()">
                    <option value="">Todas las marcas</option>
                    <?php foreach ($marcas_unicas as $marca): ?>
                        <option value="<?= strtolower(htmlspecialchars($marca)) ?>">
                            <?= htmlspecialchars($marca) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Año</label>
                <select class="filter-select" id="filtroAno" onchange="aplicarFiltros()">
                    <option value="">Todos los años</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Tipo Vehículo</label>
                <select class="filter-select" id="filtroTipo" onchange="aplicarFiltros()">
                    <option value="">Todos los tipos</option>
                    <?php
                    $tipos_stmt = $con->prepare("SELECT DISTINCT tipo_vehiculo.vehiculo
                             FROM vehiculos
                             INNER JOIN tipo_vehiculo ON vehiculos.tipo_vehiculo = tipo_vehiculo.id_tipo_vehiculo
                             ORDER BY tipo_vehiculo.vehiculo ASC");
                    $tipos_stmt->execute();
                    $tipos = $tipos_stmt->fetchAll(PDO::FETCH_COLUMN);

                    foreach ($tipos as $tipo): ?>
                        <option value="<?= strtolower(htmlspecialchars($tipo)) ?>"><?= htmlspecialchars($tipo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Registrado por</label>
                <select class="filter-select" id="filtroRegistrado" onchange="aplicarFiltros()">
                    <option value="">Todos</option>
                    <?php
                    $registradores = $con->prepare("SELECT DISTINCT u2.nombre_completo
                                         FROM vehiculos
                                         INNER JOIN usuarios u2 ON vehiculos.registrado_por = u2.documento
                                         ORDER BY u2.nombre_completo ASC");
                    $registradores->execute();
                    $nombres_registradores = $registradores->fetchAll(PDO::FETCH_COLUMN);
                    foreach ($nombres_registradores as $registrador):
                    ?>
                        <option value="<?= strtolower(htmlspecialchars($registrador)) ?>"><?= htmlspecialchars($registrador) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="controls-section">
            <div class="buscador">
                <input type="text" id="buscar" class="form-control" placeholder="Buscar por placa, propietario, documento..." onkeyup="filtrarTabla()">
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><i class="bi bi-car-front"></i> Placa</th>
                            <th><i class="bi bi-truck-flatbed"></i> Tipo</th>
                            <th><i class="bi bi-calendar3"></i> Año</th>
                            <th><i class="bi bi-tags"></i> Marca</th>
                            <th><i class="bi bi-calendar"></i> Modelo</th>
                            <th><i class="bi bi-info-circle"></i> Estado</th>
                            <th><i class="bi bi-person-check"></i> Registrado por</th>
                            <th><i class="bi bi-image"></i> Imagen</th>
                            <th><i class="bi bi-tools"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = $con->prepare("SELECT vehiculos.*, 
                            marca.nombre_marca, 
                            vehiculos.documento AS documento_propietario,
                            estado_vehiculo.estado,
                            tipo_vehiculo.vehiculo AS nombre_tipo,
                            registrador.nombre_completo AS nombre_registrador
                        FROM vehiculos
                        INNER JOIN marca ON vehiculos.id_marca = marca.id_marca
                        INNER JOIN estado_vehiculo ON vehiculos.id_estado = estado_vehiculo.id_estado
                        INNER JOIN tipo_vehiculo ON vehiculos.tipo_vehiculo = tipo_vehiculo.id_tipo_vehiculo
                        LEFT JOIN usuarios AS registrador ON vehiculos.registrado_por = registrador.documento
                        ORDER BY vehiculos.fecha_registro DESC");


                        $sql->execute();
                        $vehiculos = $sql->fetchAll(PDO::FETCH_ASSOC);
                        $count = 1;

                        if (count($vehiculos) > 0):
                            foreach ($vehiculos as $resu):
                                $image_path = $resu['foto_vehiculo'] ? '../../' . $resu['foto_vehiculo'] : '../../uploads/vehiculos/sin_foto_carro.png';
                        ?>
                                <tr>
                                    <td><?= $count++ ?></td>
                                    <td><?= htmlspecialchars($resu['placa']) ?></td>
                                    <td class="tipo-cell"><?= htmlspecialchars($resu['nombre_tipo']) ?></td>
                                    <td class="ano-cell"><?= htmlspecialchars($resu['año']) ?></td>
                                    <td class="marca-cell"><?= htmlspecialchars($resu['nombre_marca']) ?></td>
                                    <td><?= htmlspecialchars($resu['modelo']) ?></td>
                                    <td class="<?= getEstadoClass($resu['estado']) ?> estado-cell">
                                        <?= htmlspecialchars($resu['estado']) ?>
                                    </td>
                                    <td class="registrado-cell"><?= htmlspecialchars($resu['nombre_registrador'] ?? '---') ?></td>
                                    <td>
                                        <img src="<?= $image_path ?>" alt="Vehículo <?= htmlspecialchars($resu['placa']) ?>"
                                            class="img-thumbnail"
                                            style="width: 80px; height: auto; max-height: 60px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a class="action-icon view" 
                                               title="Ver detalles"
                                               data-placa="<?= htmlspecialchars($resu['placa']) ?>">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a class="action-icon edit"
                                                title="Editar vehículo"
                                                data-placa="<?= htmlspecialchars($resu['placa']) ?>"
                                                data-documento="<?= htmlspecialchars($resu['documento_propietario']) ?>"
                                                data-tipo="<?= htmlspecialchars($resu['tipo_vehiculo']) ?>"
                                                data-anio="<?= htmlspecialchars($resu['año']) ?>"
                                                data-marca="<?= htmlspecialchars($resu['id_marca']) ?>"
                                                data-modelo="<?= htmlspecialchars($resu['modelo']) ?>"
                                                data-kilometraje="<?= htmlspecialchars($resu['kilometraje_actual']) ?>"
                                                data-estado="<?= htmlspecialchars($resu['id_estado']) ?>"
                                                data-foto="<?= htmlspecialchars($resu['foto_vehiculo']) ?>">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a class="action-icon delete" data-id="<?= htmlspecialchars($resu['placa']) ?>" title="Eliminar vehículo">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <tr>
                                <td colspan="10" class="no-data">
                                    <i class="bi bi-truck"></i>
                                    <h3>No hay vehículos registrados</h3>
                                    <p>Comienza agregando tu primer vehículo a la flota</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-container">
            <nav>
                <ul class="pagination" id="paginacion"></ul>
            </nav>
        </div>
        <div class="boton-agregar">
            <button type="button" class="boton" id="btnAgregarVehiculo">
                <i class="bi bi-plus-circle"></i>
                <i class="bi bi-truck"></i>
                Agregar Vehículo
            </button>
        </div>
    </div>
    <div id="contenedor-alertas" class="position-fixed top-0 end-0 p-3" style="z-index: 2000;"></div>

    <?php include 'modals_vehiculos/vehiculo_modals.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/vehiculo-validaciones.js"></script>
    <script src="modals_vehiculos/vehiculos-scripts.js"></script>
    <script>
        // Función de filtrado mejorada
        function filtrarTabla() {
            const input = document.getElementById('buscar').value.toLowerCase();
            const rows = document.querySelectorAll("#tablaUsuarios tbody tr");
            let visibleRows = 0;

            rows.forEach(row => {
                if (row.querySelector('.no-data')) return;

                const text = row.innerText.toLowerCase();
                const isVisible = text.includes(input);
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleRows++;
            });

            configurarPaginacion();
        }

        function aplicarFiltros() {
            const filtroEstado = document.getElementById('filtroEstado').value.toLowerCase();
            const filtroMarca = document.getElementById('filtroMarca').value.toLowerCase();
            const filtroAno = document.getElementById('filtroAno').value;
            const filtroTipo = document.getElementById('filtroTipo').value.toLowerCase();
            const filtroRegistrado = document.getElementById('filtroRegistrado').value.toLowerCase();

            const rows = document.querySelectorAll("#tablaUsuarios tbody tr");

            rows.forEach(row => {
                if (row.querySelector('.no-data')) return;

                const estado = row.querySelector('.estado-cell')?.textContent.toLowerCase() || '';
                const marca = row.querySelector('.marca-cell')?.textContent.toLowerCase() || '';
                const ano = row.querySelector('.ano-cell')?.textContent.trim() || '';
                const tipo = row.querySelector('.tipo-cell')?.textContent.toLowerCase() || '';
                const registrado = row.querySelector('.registrado-cell')?.textContent.toLowerCase() || '';

                let mostrar = true;

                if (filtroEstado && !estado.includes(filtroEstado)) mostrar = false;
                if (filtroMarca && !marca.includes(filtroMarca)) mostrar = false;
                if (filtroAno && ano !== filtroAno) mostrar = false;
                if (filtroTipo && !tipo.includes(filtroTipo)) mostrar = false;
                if (filtroRegistrado && !registrado.includes(filtroRegistrado)) mostrar = false;

                row.style.display = mostrar ? '' : 'none';
            });

            configurarPaginacion();
        }


        const filasPorPagina = 5;

        function configurarPaginacion() {
            const filas = Array.from(document.querySelectorAll('#tablaUsuarios tbody tr'))
                .filter(row => row.style.display !== 'none' && !row.querySelector('.no-data'));
            const totalPaginas = Math.ceil(filas.length / filasPorPagina);
            const paginacion = document.getElementById('paginacion');

            function mostrarPagina(pagina) {
                document.querySelectorAll('#tablaUsuarios tbody tr').forEach(row => {
                    if (!row.querySelector('.no-data')) {
                        row.style.display = 'none';
                    }
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

        window.addEventListener('DOMContentLoaded', () => {
            configurarPaginacion();
            cargarOpcionesFiltros();

            const rows = document.querySelectorAll('#tablaUsuarios tbody tr');
            rows.forEach((row, index) => {
                if (!row.querySelector('.no-data')) {
                    row.style.animationDelay = `${index * 0.1}s`;
                }
            });

            // Validación de formulario (crear y editar vehículo)
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!form.id.includes('Vehiculo')) return;

                const modelo = form.querySelector('[name="modelo"]');
                const kilometraje = form.querySelector('[name="kilometraje_actual"]');
                const propietario = form.querySelector('[name="documento"]');
                const imagen = form.querySelector('[name="foto_vehiculo"]');

                // Validar modelo
                const modeloVal = parseInt(modelo.value);
                if (modeloVal < 2000 || modeloVal > 2026) {
                    e.preventDefault();
                    alert("El modelo del vehículo debe estar entre el año 2000 y 2026.");
                    modelo.focus();
                    return;
                }

                // Validar kilometraje (solo 6 dígitos numéricos)
                if (kilometraje && !/^\d{1,6}$/.test(kilometraje.value)) {
                    e.preventDefault();
                    alert("El kilometraje debe contener solo números y máximo 6 dígitos.");
                    kilometraje.focus();
                    return;
                }



                // Validar imagen (si existe)
                if (imagen && imagen.value) {
                    const ext = imagen.value.split('.').pop().toLowerCase();
                    if (!['jpg', 'jpeg', 'png'].includes(ext)) {
                        e.preventDefault();
                        alert("La imagen debe ser un archivo JPG, JPEG o PNG.");
                        imagen.focus();
                        return;
                    }
                }
            });
        });

        function cargarOpcionesFiltros() {
            const selectMarca = document.getElementById('filtroMarca');

            // Cargar años únicos
            const anos = [...new Set(
                Array.from(document.querySelectorAll('.ano-cell'))
                .map(el => el.textContent.trim())
            )];


            const selectAno = document.getElementById('filtroAno');
            anos.sort((a, b) => b - a).forEach(ano => {
                const option = document.createElement('option');
                option.value = ano;
                option.textContent = ano;
                selectAno.appendChild(option);
            });
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".action-icon.edit").forEach(btn => {
                btn.addEventListener("click", function() {
                    // Cargar valores al formulario del modal
                    document.getElementById("editPlaca").value = this.dataset.placa;
                    document.getElementById("editDocumento").value = this.dataset.documento;
                    if (document.getElementById("editTipoVehiculo"))
                        document.getElementById("editTipoVehiculo").value = this.dataset.tipo;
                    document.getElementById("editMarca").value = this.dataset.marca;
                    document.getElementById("editModelo").value = this.dataset.anio;
                    document.getElementById("editKilometraje").value = this.dataset.kilometraje;
                    document.getElementById("editEstado").value = this.dataset.estado;

                    // Vista previa de la foto si existe
                    const foto = this.dataset.foto;
                    const img = document.getElementById("editFotoPreview");
                    if (foto && foto.trim() !== "") {
                        img.src = "../../" + foto;
                        img.style.display = "block";
                    } else {
                        img.src = "../../uploads/vehiculos/sin_foto_carro.png";
                        img.style.display = "block";
                    }
                });
            });
        });

        // Funcionalidad para el modal de detalles
        document.addEventListener('DOMContentLoaded', function() {
            // Event listener para el botón de ver detalles
            document.querySelectorAll('.action-icon.view').forEach(btn => {
                btn.addEventListener('click', function() {
                    const placa = this.getAttribute('data-placa');
                    cargarDetallesVehiculo(placa);
                });
            });

            // Event listener para el botón de editar desde detalles
            document.getElementById('btnEditarDesdeDetalles').addEventListener('click', function() {
                const placa = document.getElementById('detallePlaca').textContent;
                if (placa && placa !== '-') {
                    // Cerrar modal de detalles
                    const modalDetalles = bootstrap.Modal.getInstance(document.getElementById('verDetallesVehiculoModal'));
                    modalDetalles.hide();
                    
                    // Abrir modal de editar
                    setTimeout(() => {
                        const editBtn = document.querySelector(`.action-icon.edit[data-placa="${placa}"]`);
                        if (editBtn) {
                            editBtn.click();
                        }
                    }, 300);
                }
            });
        });

        function cargarDetallesVehiculo(placa) {
            fetch(`modals_vehiculos/get_vehicle_details.php?placa=${encodeURIComponent(placa)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const vehicle = data.vehicle;
                        
                        // Llenar información del vehículo
                        document.getElementById('detallePlaca').textContent = vehicle.placa || '-';
                        document.getElementById('detalleTipoVehiculo').textContent = vehicle.tipo_vehiculo_nombre || '-';
                        document.getElementById('detalleMarca').textContent = vehicle.nombre_marca || '-';
                        document.getElementById('detalleModelo').textContent = vehicle.modelo || '-';
                        document.getElementById('detalleAnio').textContent = vehicle.año || '-';
                        document.getElementById('detalleColor').textContent = vehicle.color_nombre || '-';
                        document.getElementById('detalleEstado').textContent = vehicle.estado_vehiculo || '-';
                        document.getElementById('detalleKilometraje').textContent = vehicle.kilometraje_formateado || '-';
                        
                        // Llenar información del propietario
                        document.getElementById('detalleDocumento').textContent = vehicle.Documento || '-';
                        document.getElementById('detalleNombrePropietario').textContent = vehicle.nombre_propietario || '-';
                        document.getElementById('detalleEmailPropietario').textContent = vehicle.email_propietario || '-';
                        document.getElementById('detalleTelefonoPropietario').textContent = vehicle.telefono_propietario || '-';
                        
                        // Llenar información de registro
                        document.getElementById('detalleFechaRegistro').textContent = vehicle.fecha_registro_formateada || '-';
                        document.getElementById('detalleRegistradoPor').textContent = vehicle.nombre_registrador || '-';
                        
                        // Manejar la imagen
                        const imgElement = document.getElementById('detalleFotoVehiculo');
                        const infoFoto = document.getElementById('detalleInfoFoto');
                        
                        if (vehicle.tiene_foto) {
                            imgElement.src = vehicle.foto_url;
                            infoFoto.textContent = 'Foto del vehículo';
                        } else {
                            imgElement.src = vehicle.foto_url;
                            infoFoto.textContent = 'Sin foto disponible';
                        }
                        
                        // Mostrar el modal
                        const modal = new bootstrap.Modal(document.getElementById('verDetallesVehiculoModal'));
                        modal.show();
                    } else {
                        alert('Error al cargar los detalles del vehículo: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles del vehículo');
                });
        }
    </script>

    


</body>

</html>