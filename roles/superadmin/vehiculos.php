<?php
session_start();

// Verificar autenticación de superadmin
if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    header('Location: ../../login/login.php');
    exit;
}

$nombre_superadmin = $_SESSION['documento'] ?? 'Superadmin';

require_once '../../conecct/conex.php';
$database = new Database();
$conexion = $database->conectar();

// Obtener vehículos
try {
    $stmt = $conexion->prepare("
    SELECT 
        v.placa,
        v.Documento,
        u.nombre_completo AS propietario,
        v.id_marca,
        m.nombre_marca AS marca_nombre,
        v.modelo,
        v.año,
        v.kilometraje_actual,
        tv.vehiculo,
        v.id_estado,
        ev.estado AS estado_vehiculo,
        v.fecha_registro,
        v.foto_vehiculo,
        c.color AS color_nombre
    FROM vehiculos v
    LEFT JOIN usuarios u ON v.Documento = u.documento
    LEFT JOIN marca m ON v.id_marca = m.id_marca
    LEFT JOIN estado_vehiculo ev ON v.id_estado = ev.id_estado
    LEFT JOIN colores c ON v.id_color = c.id_color
    LEFT JOIN tipo_vehiculo tv ON v.tipo_vehiculo = tv.id_tipo_vehiculo
    ORDER BY v.placa
");


    $stmt->execute();
    $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $vehiculos = [];
}

// Obtener usuarios para el formulario
try {
    $stmt_usuarios = $conexion->prepare("SELECT documento, nombre_completo FROM usuarios WHERE id_estado_usuario = 1 ORDER BY nombre_completo");
    $stmt_usuarios->execute();
    $usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
}

// Obtener estados de vehículo
try {
    $stmt_estados = $conexion->prepare("SELECT * FROM estado_vehiculo ORDER BY estado");
    $stmt_estados->execute();
    $estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $estados = [];
}

// Obtener tipos de vehículo
try {
    $stmt_tipos = $conexion->prepare("SELECT * FROM tipo_vehiculo ORDER BY vehiculo");
    $stmt_tipos->execute();
    $tipos = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tipos = [];
}

// Obtener marcas
try {
    $stmt_marcas = $conexion->prepare("SELECT * FROM marca ORDER BY nombre_marca");
    $stmt_marcas->execute();
    $marcas = $stmt_marcas->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $marcas = [];
}

// Obtener colores
try {
    $stmt_colores = $conexion->prepare("SELECT id_color, color FROM colores ORDER BY color");
    $stmt_colores->execute();
    $colores = $stmt_colores->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $colores = [];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vehículos - Superadmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .superadmin-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .card-header {
            background: linear-gradient(135deg, #d32f2f, #b71c1c);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .btn-gradient {
            background: linear-gradient(135deg, #d32f2f, #b71c1c);
            border: none;
            color: white;
            border-radius: 10px;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, #b71c1c, #8e0000);
            color: white;
        }

        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-activo {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactivo {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-mantenimiento {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-revision {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Estilos del sidebar */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #34495e;
            --light-color: #ecf0f1;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-color), var(--dark-color));
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background-color: var(--secondary-color);
            color: white;
        }

        .main-content {
            padding: 20px;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px;
            margin: 20px 10px;
            text-align: center;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 1.5rem;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h6 class="text-white mb-1"><?php echo htmlspecialchars($nombre_superadmin); ?></h6>
                    <small class="text-white-50">Superadministrador</small>
                </div>

                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users me-2"></i> Gestión de Usuarios
                    </a>
                    <a class="nav-link active" href="vehiculos.php">
                        <i class="fas fa-truck me-2"></i> Gestión de Vehículos
                    </a>
                    <a class="nav-link" href="licenciamiento.php">
                        <i class="fas fa-certificate me-2"></i> Licenciamiento
                    </a>
                    <hr class="text-white-50">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">Gestión de Vehículos</h1>
                    <button class="btn btn-primary" onclick="abrirModalNuevoVehiculo()">
                        <i class="bi bi-plus-circle"></i> Nuevo Vehículo
                    </button>
                </div>

                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary"><?php echo count($vehiculos); ?></h4>
                                <p class="text-muted">Total Vehículos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-success"><?php echo count(array_filter($vehiculos, function ($v) {
                                                                return $v['id_estado'] == '1';
                                                            })); ?></h4>
                                <p class="text-muted">Vehículos Activos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-warning"><?php echo count(array_filter($vehiculos, function ($v) {
                                                                return $v['id_estado'] == '3';
                                                            })); ?></h4>
                                <p class="text-muted">En Mantenimiento</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-info"><?php echo count(array_filter($vehiculos, function ($v) {
                                                            return $v['id_estado'] == '8';
                                                        })); ?></h4>
                                <p class="text-muted">Disponibles</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Vehículos -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Placa</th>
                                                <th>Propietario</th>
                                                <th>Marca</th>
                                                <th>Modelo</th>
                                                <th>Año</th>
                                                <th>Tipo</th>
                                                <th>Kilometraje</th>
                                                <th>Foto</th>
                                                <th>Estado</th>
                                                <th>Color</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($vehiculos as $vehiculo): ?>
                                                <tr>
                                                    <td><strong><?php echo htmlspecialchars($vehiculo['placa']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($vehiculo['propietario'] ?? 'Sin propietario'); ?></td>
                                                    <td><?php echo htmlspecialchars($vehiculo['marca_nombre'] ?? 'N/A'); ?></td>
                                                    <td><?php echo htmlspecialchars($vehiculo['modelo']); ?></td>
                                                    <td><?php echo htmlspecialchars($vehiculo['año']); ?></td>
                                                    <td><?php echo htmlspecialchars($vehiculo['vehiculo']); ?></td>
                                                    <td><?php echo htmlspecialchars($vehiculo['kilometraje_actual']); ?> km</td>
                                                    <td>
                                                        <?php if (!empty($vehiculo['foto_vehiculo'])): ?>
                                                            <img src="<?php echo '../../' . htmlspecialchars($vehiculo['foto_vehiculo']); ?>" class="img-thumbnail" style="height: 60px; object-fit: cover; border-radius: 8px;" alt="Foto vehículo">
                                                        <?php else: ?>
                                                            Sin foto
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $vehiculo['estado_vehiculo'] ?? 'activo')); ?>">
                                                            <?php echo htmlspecialchars($vehiculo['estado_vehiculo'] ?? 'Activo'); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($vehiculo['color_nombre']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-outline-primary" onclick="editarVehiculo('<?php echo $vehiculo['placa']; ?>')">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="eliminarVehiculo('<?php echo $vehiculo['placa']; ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Nuevo/Editar Vehículo -->
        <div class="modal fade" id="modalVehiculo" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle">Nuevo Vehículo</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="formVehiculo" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Placa</label>
                                    <input type="text" class="form-control" name="placa" id="placa" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Propietario</label>
                                    <select class="form-select" name="Documento" id="Documento" required>
                                        <option value="">Seleccionar Propietario</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                            <option value="<?php echo $usuario['documento']; ?>">
                                                <?php echo htmlspecialchars($usuario['nombre_completo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Marca</label>
                                    <select class="form-select" name="id_marca" id="id_marca" required>
                                        <option value="">Seleccionar Marca</option>
                                        <?php foreach ($marcas as $marca): ?>
                                            <option value="<?php echo $marca['id_marca']; ?>">
                                                <?php echo htmlspecialchars($marca['nombre_marca']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Modelo</label>
                                    <input type="text" class="form-control" name="modelo" id="modelo" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Año</label>
                                    <input type="number" class="form-control" name="año" id="año" min="2000" max="2026" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Vehículo</label>
                                    <select class="form-select" name="tipo_vehiculo" id="tipo_vehiculo" required>
                                        <option value="">Seleccionar Tipo</option>
                                        <?php foreach ($tipos as $tipo): ?>
                                            <option value="<?php echo $tipo['id_tipo_vehiculo']; ?>">
                                                <?php echo htmlspecialchars($tipo['vehiculo']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kilometraje Actual</label>
                                    <input type="number" class="form-control" name="kilometraje_actual" id="kilometraje_actual" min="0" max="999999" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Foto del Vehículo</label>
                                    <input type="file" class="form-control" name="foto_vehiculo" id="foto_vehiculo" accept="image/*">
                                    <div id="vistaPreviaFoto" class="mt-2"></div>
                                </div>



                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Color</label>
                                    <select class="form-select" name="id_color" id="id_color" required>
                                        <option value="">Seleccionar Color</option>
                                        <?php foreach ($colores as $color): ?>
                                            <option value="<?php echo $color['id_color']; ?>">
                                                <?php echo htmlspecialchars($color['color']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" name="id_estado" id="id_estado" required>
                                        <option value="">Seleccionar Estado</option>
                                        <?php foreach ($estados as $estado): ?>
                                            <option value="<?php echo $estado['id_estado']; ?>">
                                                <?php echo htmlspecialchars($estado['estado']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <script>
            let modalVehiculo;
            let modoEdicion = false;
            let placaActual = '';


            /* …todo lo anterior… */
            document.getElementById('placa').addEventListener('input', e => {
                e.target.value = e.target.value.toUpperCase();
            });
            // ─── FUNCIÓN PARA VALIDAR PLACA ESPECIAL ───
            function esPlacaValida(placa) {
                placa = placa.toUpperCase().trim(); // normaliza
                if (!/^[A-Z]/.test(placa)) return false; // debe empezar con letra
                if (!/^[A-Z0-9]+$/.test(placa)) return false; // solo letras y números

                const letras = placa.match(/[A-Z]/g) || [];
                const numeros = placa.match(/\d/g) || [];

                return (
                    letras.length >= 2 && letras.length <= 4 &&
                    numeros.length >= 2 && numeros.length <= 3
                );
            }

            function validarFormularioVehiculo() {
                const placa = document.getElementById('placa').value.trim();
                const modelo = document.getElementById('modelo').value.trim();
                const año = parseInt(document.getElementById('año').value, 10);
                const kilometraje = parseInt(document.getElementById('kilometraje_actual').value, 10);
                const foto = document.getElementById('foto_vehiculo').files[0];

                const documento = document.getElementById('Documento').value;
                const marca = document.getElementById('id_marca').value;
                const tipo = document.getElementById('tipo_vehiculo').value;
                const color = document.getElementById('id_color').value;
                const estado = document.getElementById('id_estado').value;


                const modeloRegex = /^[a-zA-Z0-9\s\-]{2,15}$/;


                if (!esPlacaValida(placa)) {
                    alert('La placa debe empezar con letra y contener 2‑4 letras + 2‑3 números (ej: SA2D2, A12B, ABC23).');
                    return false;
                }

                if (!documento) {
                    alert('Debe seleccionar un propietario.');
                    return false;
                }

                if (!marca) {
                    alert('Debe seleccionar una marca.');
                    return false;
                }

                if (!modeloRegex.test(modelo)) {
                    alert('El modelo debe tener entre 2 y 15 caracteres (letras, números, espacios o guiones).');
                    return false;
                }

                const añoActual = new Date().getFullYear();
                if (isNaN(año) || año < 1970 || año > añoActual) {
                    alert(`El año debe estar entre 1970 y ${añoActual}.`);
                    return false;
                }

                if (!tipo) {
                    alert('Debe seleccionar el tipo de vehículo.');
                    return false;
                }

                if (isNaN(kilometraje) || kilometraje < 0 || kilometraje > 999999) {
                    alert('Debe ingresar un número entre 0 y 999999 para el kilometraje.');
                    return false;
                }

                if (!color) {
                    alert('Debe seleccionar el color del vehículo.');
                    return false;
                }

                if (!estado) {
                    alert('Debe seleccionar el estado del vehículo.');
                    return false;
                }

                if (foto && !foto.type.startsWith('image/')) {
                    alert('La foto del vehículo debe ser una imagen.');
                    return false;
                }

                return true;
            }

            function abrirModalNuevoVehiculo() {
                modoEdicion = false;
                placaActual = '';
                document.getElementById('modalTitle').textContent = 'Nuevo Vehículo';
                document.getElementById('formVehiculo').reset();
                document.querySelectorAll('#formVehiculo input, #formVehiculo select').forEach(el => el.disabled = false);
                document.getElementById('vistaPreviaFoto').innerHTML = '';
                modalVehiculo.show();
            }


            function editarVehiculo(placa) {
                modoEdicion = true;
                placaActual = placa;
                document.getElementById('modalTitle').textContent = 'Editar Vehículo';

                // Desactivar todos los campos por defecto
                document.querySelectorAll('#formVehiculo input, #formVehiculo select').forEach(el => el.disabled = true);

                // Habilitar solo los campos que se pueden editar
                document.getElementById('kilometraje_actual').disabled = false;
                document.getElementById('id_color').disabled = false;
                document.getElementById('foto_vehiculo').disabled = false;
                document.getElementById('id_estado').disabled = false;

                // Cargar datos del vehículo
                fetch('vehiculos_backend.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=obtener_vehiculo&placa=' + encodeURIComponent(placa)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const vehiculo = data.vehiculo;

                            document.getElementById('placa').value = vehiculo.placa;
                            document.getElementById('Documento').value = vehiculo.Documento;
                            document.getElementById('id_marca').value = vehiculo.id_marca;
                            document.getElementById('modelo').value = vehiculo.modelo;
                            document.getElementById('año').value = vehiculo.año;
                            document.getElementById('tipo_vehiculo').value = vehiculo.tipo_vehiculo;
                            document.getElementById('kilometraje_actual').value = vehiculo.kilometraje_actual;
                            document.getElementById('id_color').value = vehiculo.id_color;
                            document.getElementById('id_estado').value = vehiculo.id_estado;

                            if (vehiculo.foto_vehiculo) {
                                document.getElementById('vistaPreviaFoto').innerHTML = `
                                <div class="text-center">
                                    <img src="../../${vehiculo.foto_vehiculo}" alt="Foto actual" class="img-thumbnail mb-2" style="width: 150px; height: auto; object-fit: cover; border-radius: 8px;">
                                </div>`;

                            } else {
                                document.getElementById('vistaPreviaFoto').innerHTML = 'Sin foto';
                            }

                            modalVehiculo.show();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error al cargar datos del vehículo', 'error');
                    });
            }


            function guardarVehiculo() {

                if (!validarFormularioVehiculo()) {
                    return; // Si la validación falla, no continúa
                }
                const formData = new FormData(document.getElementById('formVehiculo'));
                formData.append('action', modoEdicion ? 'actualizar_vehiculo' : 'crear_vehiculo');

                if (modoEdicion) {
                    formData.append('placa_original', placaActual);
                }

                const año = parseInt(formData.get('año'), 10);
                const kilometraje = parseInt(formData.get('kilometraje_actual'), 10);

                if (año < 2000) {
                    Swal.fire('Año inválido', 'El año debe ser mayor o igual a 2000.', 'warning');
                    return;
                }

                if (kilometraje > 999999 || kilometraje < 0 || isNaN(kilometraje)) {
                    Swal.fire('Kilometraje inválido', 'El kilometraje debe ser un número positivo de máximo 6 dígitos.', 'warning');
                    return;
                }


                fetch('vehiculos_backend.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: data.message
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Error al procesar la solicitud', 'error');
                    });
            }

            function eliminarVehiculo(placa) {
                Swal.fire({
                    title: '¿Eliminar Vehículo?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'eliminar_vehiculo');
                        formData.append('placa', placa);

                        fetch('vehiculos_backend.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Eliminado', data.message, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                Swal.fire('Error', 'Error al eliminar vehículo', 'error');
                            });
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const modalElement = document.getElementById('modalVehiculo');
                if (modalElement) {
                    modalVehiculo = new bootstrap.Modal(modalElement);
                }

                // Captura el envío del formulario
                document.getElementById('formVehiculo').addEventListener('submit', function(e) {
                    e.preventDefault(); // Evita el envío normal
                    guardarVehiculo(); // Llama a la función JS
                });
            });
        </script>
</body>

</html>