<?php
session_start();

// Verificar autenticación de superadmin
if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    header('Location: ../../login/login.php');
    exit;
}

$nombre_superadmin = $_SESSION['documento'] ?? 'Superadmin';
$documento_superadmin = $_SESSION['documento'] ?? '';

require_once '../../includes/validarsession.php';
require_once '../../conecct/conex.php';

try {
    $database = new Database();
    $conexion = $database->conectar();

    // Verificar y crear tabla empresas si no existe
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'empresas'");
    $stmt->execute();
    $tabla_empresas_existe = $stmt->rowCount() > 0;

    if (!$tabla_empresas_existe) {
        $sql_empresas = "
            CREATE TABLE IF NOT EXISTS empresas (
                id_empresa INT AUTO_INCREMENT PRIMARY KEY,
                nombre_empresa VARCHAR(255) NOT NULL,
                nit VARCHAR(20) UNIQUE NOT NULL,
                direccion VARCHAR(255),
                telefono VARCHAR(20),
                email VARCHAR(100),
                fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                estado ENUM('activa', 'inactiva') DEFAULT 'activa'
            )
        ";
        $conexion->exec($sql_empresas);

        // Insertar empresa por defecto
        $stmt = $conexion->prepare("
            INSERT INTO empresas (nombre_empresa, nit, direccion, telefono, email) 
            VALUES ('FlotaX AGC', '900123456-1', 'Calle Principal 123', '3001234567', 'admin@flotaxagc.com')
        ");
        $stmt->execute();
    }

    // Obtener ID de empresa por defecto
    $stmt = $conexion->prepare("SELECT id_empresa FROM empresas WHERE nombre_empresa = 'FlotaX AGC' LIMIT 1");
    $stmt->execute();
    $id_empresa_default = $stmt->fetchColumn();

    // Verificar y crear tabla sistema_licencias si no existe
    $stmt = $conexion->prepare("SHOW TABLES LIKE 'sistema_licencias'");
    $stmt->execute();
    $tabla_licencias_existe = $stmt->rowCount() > 0;

    if (!$tabla_licencias_existe) {
        $sql_licencias = "
            CREATE TABLE IF NOT EXISTS sistema_licencias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_empresa INT NOT NULL,
                tipo_licencia ENUM('basica', 'profesional', 'empresarial') NOT NULL,
                fecha_inicio DATE NOT NULL,
                fecha_vencimiento DATE NOT NULL,
                max_usuarios INT,
                max_vehiculos INT,
                estado ENUM('activa', 'inactiva', 'vencida', 'suspendida') DEFAULT 'activa',
                clave_licencia VARCHAR(50) NOT NULL,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP NULL,
                FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)
            )
        ";
        $conexion->exec($sql_licencias);

        // Insertar licencia por defecto
        $stmt = $conexion->prepare("
            INSERT INTO sistema_licencias (id_empresa, tipo_licencia, fecha_inicio, fecha_vencimiento, max_usuarios, max_vehiculos, clave_licencia) 
            VALUES (?, 'empresarial', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 100, 500, ?)
        ");
        $clave_default = 'FLOTAX-' . strtoupper(bin2hex(random_bytes(8)));
        $stmt->execute([$id_empresa_default, $clave_default]);
    }

    // Obtener licencias con nombre de empresa
    $stmt = $conexion->prepare("
    SELECT 
        e.id_empresa,
        e.nombre_empresa,
        e.nit,
        e.direccion,
        e.telefono,
        e.email,
        sl.id AS id_licencia,
        sl.tipo_licencia,
        sl.fecha_inicio,
        sl.fecha_vencimiento,
        sl.max_usuarios,
        sl.max_vehiculos,
        sl.estado AS estado_licencia,
        sl.clave_licencia,
        sl.fecha_creacion
        FROM empresas e
        LEFT JOIN sistema_licencias sl ON e.id_empresa = sl.id_empresa
        ORDER BY e.nombre_empresa
    ");

    $stmt->execute();
    $licencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener la licencia actual (la más reciente activa)
    $licencia_actual = null;
    foreach ($licencias as $licencia) {
        if ($licencia['estado_licencia'] === 'activa') {
            $licencia_actual = $licencia;
            break;
        }
    }

    $stmt = $conexion->prepare("
    SELECT e.*
    FROM empresas e
    LEFT JOIN sistema_licencias sl ON e.id_empresa = sl.id_empresa
    WHERE sl.id IS NULL AND e.estado = 'activa'
    ORDER BY e.nombre_empresa
    ");
    $stmt->execute();
    $empresas_sin_licencia = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Obtener empresas activas
    $stmt = $conexion->prepare("SELECT * FROM empresas WHERE estado = 'activa' ORDER BY nombre_empresa");
    $stmt->execute();
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Estadísticas
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $usuarios_actuales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM vehiculos");
    $stmt->execute();
    $vehiculos_actuales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    error_log("Error en licenciamiento: " . $e->getMessage());
    $licencias = [];
    $empresas = [];
    $usuarios_actuales = 0;
    $vehiculos_actuales = 0;
    $licencia_actual = null;
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Licenciamiento - Sistema de Gestión de Flota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --dark-color: #34495e;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
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

        .license-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin-bottom: 20px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .status-activa {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }

        .status-vencida {
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white;
        }

        .status-suspendida {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }

        .progress-custom {
            height: 10px;
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.1);
        }

        .progress-bar-custom {
            border-radius: 10px;
            transition: width 0.6s ease;
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

        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
    </style>
</head>

<body>
    <!-- Después de abrir <body> -->
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
                    <a class="nav-link" href="vehiculos.php">
                        <i class="fas fa-truck me-2"></i> Gestión de Vehículos
                    </a>
                    <a class="nav-link active" href="licenciamiento.php">
                        <i class="fas fa-certificate me-2"></i> Licenciamiento
                    </a>
                    <hr class="text-white-50">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                </nav>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-white">Gestión de Licencias y Empresas</h3>
                    <div>
                        <button class="btn btn-gradient me-2" data-bs-toggle="modal" data-bs-target="#modalNuevaEmpresa">
                            <i class="fas fa-building me-1"></i> Nueva Empresa
                        </button>
                        <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#modalAsignarLicencia">
                            <i class="fas fa-certificate me-1"></i> Asignar Licencia
                        </button>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($licencias as $licencia): ?>
                        <div class="col-md-6">
                            <div class="license-card">
                                <h5 class="mb-1"><?= htmlspecialchars($licencia['nombre_empresa']) ?></h5>

                                <?php if ($licencia['id_licencia']): ?>
                                    <p class="mb-2 text-muted">
                                        <?= ucfirst($licencia['tipo_licencia']) ?> | Clave:
                                        <strong><?= htmlspecialchars($licencia['clave_licencia']) ?></strong>
                                    </p>
                                    <p><strong>Inicio:</strong> <?= $licencia['fecha_inicio'] ?> |
                                        <strong>Vence:</strong> <?= $licencia['fecha_vencimiento'] ?>
                                    </p>
                                    <p><strong>Usuarios:</strong> <?= $licencia['max_usuarios'] ?> |
                                        <strong>Vehículos:</strong> <?= $licencia['max_vehiculos'] ?>
                                    </p>

                                    <span class="status-badge
                    <?= $licencia['estado_licencia'] === 'activa' ? 'status-activa' : ($licencia['estado_licencia'] === 'vencida' ? 'status-vencida' : 'status-suspendida') ?>">
                                        <?= strtoupper($licencia['estado_licencia']) ?>
                                    </span>

                                    <form action="estado_licencia.php" method="POST" class="d-inline">
                                        <input type="hidden" name="id_licencia" value="<?= $licencia['id_licencia'] ?>">
                                        <input type="hidden" name="estado_actual" value="<?= $licencia['estado_licencia'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $licencia['estado_licencia'] === 'activa' ? 'btn-danger' : 'btn-success' ?>">
                                            <?= $licencia['estado_licencia'] === 'activa' ? 'Suspender' : 'Activar' ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p class="text-muted"><em>Sin licencia asignada</em></p>
                                    <span class="status-badge status-suspendida">SIN LICENCIA</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div> <!-- CIERRE DE ROW -->
    </div> <!-- CIERRE DE CONTAINER-FLUID -->

    <!-- MODAL: Nueva Empresa -->
    <div class="modal fade" id="modalNuevaEmpresa" tabindex="-1" aria-labelledby="modalNuevaEmpresaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4">
                <form id="formNuevaEmpresa" action="" method="POST" class="needs-validation" novalidate>
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-building me-2"></i>Registrar Nueva Empresa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre de la Empresa</label>
                                <input type="text" name="nombre_empresa" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIT</label>
                                <input type="text" name="nit" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dirección</label>
                                <input type="text" name="direccion" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="submit" class="btn btn-gradient">Guardar Empresa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- MODAL: Asignar Licencia -->
    <div class="modal fade" id="modalAsignarLicencia" tabindex="-1" aria-labelledby="modalAsignarLicenciaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4">
                <form id="formAsignarLicencia" action="" method="POST" class="needs-validation" novalidate>
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-certificate me-2"></i>Asignar Nueva Licencia</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Empresa</label>
                                <select name="id_empresa" class="form-select" required>
                                    <option value="" disabled selected>Seleccione una empresa</option>
                                    <?php foreach ($empresas_sin_licencia as $empresa): ?>
                                        <option value="<?= $empresa['id_empresa'] ?>"><?= htmlspecialchars($empresa['nombre_empresa']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de Licencia</label>
                                <select name="tipo_licencia" class="form-select" required>
                                    <option value="basica">Básica</option>
                                    <option value="profesional">Profesional</option>
                                    <option value="empresarial">Empresarial</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Clave de Licencia</label>
                                <input type="text" name="clave_licencia" class="form-control" readonly placeholder="Se genera automáticamente">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Inicio</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Vencimiento</label>
                                <input type="date" name="fecha_vencimiento" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="submit" class="btn btn-gradient">Asignar Licencia</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const formEmpresa = document.getElementById("formNuevaEmpresa");

            if (formEmpresa) {
                formEmpresa.addEventListener("submit", function(e) {
                    e.preventDefault();

                    const nombre = formEmpresa.nombre_empresa.value.trim();
                    const nit = formEmpresa.nit.value.trim();
                    const direccion = formEmpresa.direccion.value.trim();
                    const telefono = formEmpresa.telefono.value.trim();
                    const email = formEmpresa.email.value.trim();

                    const nombreRegex = /^[a-zA-ZÀ-ÿ0-9 .,'\\-]{3,100}$/;
                    const nitRegex = /^[0-9\-]{5,20}$/;
                    const direccionRegex = /^.{5,150}$/;
                    const telRegex = /^\d{8,15}$/;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (!nombre || !nombreRegex.test(nombre)) {
                        alert("El nombre de la empresa debe tener entre 3 y 100 caracteres.");
                        return;
                    }

                    if (!nit || !nitRegex.test(nit)) {
                        alert("El NIT debe tener entre 5 y 20 caracteres, solo números y guiones.");
                        return;
                    }

                    if (!direccion || !direccionRegex.test(direccion)) {
                        alert("La dirección debe tener entre 5 y 150 caracteres.");
                        return;
                    }

                    if (!telefono || !telRegex.test(telefono)) {
                        alert("El teléfono debe tener entre 8 y 15 dígitos numéricos.");
                        return;
                    }

                    if (!email || !emailRegex.test(email)) {
                        alert("El correo electrónico no tiene un formato válido.");
                        return;
                    }

                    const formData = new FormData(formEmpresa);
                    formData.append("action", "crear_empresa");

                    fetch("licenciamiento_backend.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) location.reload();
                        })
                        .catch(() => alert("Error al registrar la empresa."));
                });

                const nitInput = formEmpresa.querySelector('[name="nit"]');
                const telInput = formEmpresa.querySelector('[name="telefono"]');

                if (nitInput) {
                    nitInput.addEventListener("input", function() {
                        this.value = this.value.replace(/[^0-9\-]/g, '');
                    });
                }

                if (telInput) {
                    telInput.addEventListener("input", function() {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });
                }
            }

            const formLicencia = document.querySelector('#formAsignarLicencia');
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);

            const fechaInicio = document.querySelector('[name="fecha_inicio"]');
            const fechaVenc = document.querySelector('[name="fecha_vencimiento"]');

            if (fechaInicio) fechaInicio.setAttribute("min", hoy.toISOString().split("T")[0]);
            if (fechaVenc) fechaVenc.setAttribute("min", hoy.toISOString().split("T")[0]);

            if (formLicencia) {
                const tipoLicencia = formLicencia.querySelector('[name="tipo_licencia"]');
                const maxUsuarios = document.createElement('input');
                const maxVehiculos = document.createElement('input');
                const claveInput = formLicencia.querySelector('[name="clave_licencia"]');

                tipoLicencia.addEventListener("change", () => {
                    switch (tipoLicencia.value) {
                        case "basica":
                            maxUsuarios.value = 5;
                            maxVehiculos.value = 10;
                            break;
                        case "profesional":
                            maxUsuarios.value = 20;
                            maxVehiculos.value = 50;
                            break;
                        case "empresarial":
                            maxUsuarios.value = 100;
                            maxVehiculos.value = 500;
                            break;
                    }
                });

                if (claveInput) {
                    claveInput.setAttribute("readonly", true);
                    claveInput.value = "Se generará automáticamente";
                }

                formLicencia.addEventListener("submit", function(e) {
                    e.preventDefault();

                    const fInicio = new Date(fechaInicio.value);
                    const fVenc = new Date(fechaVenc.value);

                    fInicio.setHours(0, 0, 0, 0);
                    fVenc.setHours(0, 0, 0, 0);

                    let errores = [];

                    if (!fechaInicio.value || fInicio < hoy) errores.push("La fecha de inicio no puede ser anterior a hoy.");
                    if (!fechaVenc.value || fVenc <= fInicio) errores.push("La fecha de vencimiento debe ser posterior a la fecha de inicio.");

                    if (errores.length > 0) {
                        alert(errores.join("\n"));
                        return;
                    }

                    const formData = new FormData(formLicencia);
                    formData.append("action", "crear_licencia");

                    fetch("licenciamiento_backend.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) location.reload();
                        })
                        .catch(() => alert("Error al asignar licencia."));
                });
            }
        });
    </script>

</body>

</html>