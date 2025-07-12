<?php
session_start();

// Verificar autenticación de superadmin
if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    header('Location: ../../login/login.php');
    exit;
}

$nombre_superadmin = $_SESSION['documento'] ?? 'Superadmin';
$documento_superadmin = $_SESSION['documento'] ?? '';

// Incluir conexión a la base de datos
require_once '../../conecct/conex.php';

// Crear instancia de la base de datos
$database = new Database();
$conexion = $database->conectar();

try {
    // Total de vehículos
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM vehiculos");
    $stmt->execute();
    $total_vehiculos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total de usuarios
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Usuarios por rol
    $stmt = $conexion->prepare("
        SELECT r.tip_rol, COUNT(*) as cantidad 
        FROM usuarios u 
        LEFT JOIN roles r ON u.id_rol = r.id_rol 
        GROUP BY u.id_rol, r.tip_rol
    ");
    $stmt->execute();
    $usuarios_por_rol = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Vehículos por estado
    $stmt = $conexion->prepare("
        SELECT ev.estado, COUNT(*) as cantidad 
        FROM vehiculos v 
        LEFT JOIN estado_vehiculo ev ON v.id_estado = ev.id_estado 
        GROUP BY v.id_estado, ev.estado
    ");
    $stmt->execute();
    $vehiculos_por_estado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Documentos próximos a vencer (30 días)
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total FROM (
            SELECT fecha_vencimiento FROM soat WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            SELECT fecha_vencimiento FROM tecnomecanica WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            UNION ALL
            SELECT fecha_vencimiento FROM licencias WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ) as documentos
    ");
    $stmt->execute();
    $documentos_proximos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Últimos usuarios registrados (solo si joined_at no es NULL)
    $stmt = $conexion->prepare("
        SELECT u.nombre_completo, u.documento, u.joined_at, r.tip_rol 
        FROM usuarios u 
        LEFT JOIN roles r ON u.id_rol = r.id_rol 
        WHERE u.joined_at IS NOT NULL
        ORDER BY u.joined_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $ultimos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Total licencias del sistema
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM sistema_licencias");
    $stmt->execute();
    $total_licencias = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (Exception $e) {
    $total_vehiculos = 0;
    $total_usuarios = 0;
    $usuarios_por_rol = [];
    $vehiculos_por_estado = [];
    $documentos_proximos = 0;
    $ultimos_usuarios = [];
    $total_licencias = 0;
    error_log("Error en dashboard: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Superadmin - Sistema de Gestión de Flota</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
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

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.primary {
            border-left-color: var(--primary-color);
        }

        .stat-card.success {
            border-left-color: var(--success-color);
        }

        .stat-card.warning {
            border-left-color: var(--warning-color);
        }

        .stat-card.danger {
            border-left-color: var(--danger-color);
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }

        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
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
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users me-2"></i> Gestión de Usuarios
                    </a>
                    <a class="nav-link" href="vehiculos.php">
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
                    <h1 class="h3 mb-0">Panel de Control Superadmin</h1>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Último acceso: <?php echo date('d/m/Y H:i'); ?></span>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-shield me-2"></i><?php echo htmlspecialchars($nombre_superadmin); ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Section -->
                <div id="dashboard-section" class="content-section">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stat-card primary">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-1"><?php echo $total_vehiculos; ?></h3>
                                        <p class="text-muted mb-0">Total Vehículos</p>
                                    </div>
                                    <div class="stat-icon text-primary">
                                        <i class="fas fa-car"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card success">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-1"><?php echo $total_usuarios; ?></h3>
                                        <p class="text-muted mb-0">Total Usuarios</p>
                                    </div>
                                    <div class="stat-icon text-success">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card warning">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-1"><?php echo $documentos_proximos; ?></h3>
                                        <p class="text-muted mb-0">Documentos Próximos a Vencer</p>
                                    </div>
                                    <div class="stat-icon text-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stat-card danger">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-1"><?php echo $total_licencias; ?></h3>
                                        <p class="text-muted mb-0">Licencias del Sistema</p>
                                    </div>
                                    <div class="stat-icon text-danger">
                                        <i class="fas fa-certificate"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Tables Row -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-container">
                                <h5 class="mb-3">Últimos Usuarios Registrados</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Documento</th>
                                                <th>Rol</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($ultimos_usuarios as $usuario): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                                    <td><?php echo htmlspecialchars($usuario['documento']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $usuario['tip_rol'] == 'Administrador' ? 'primary' : ($usuario['tip_rol'] == 'Superadmin' ? 'danger' : 'secondary'); ?>">
                                                            <?php echo htmlspecialchars($usuario['tip_rol'] ?? 'Usuario'); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y', strtotime($usuario['joined_at'])); ?></td>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Eliminar la línea que incluye Chart.js -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
    <script>
        // Función para mostrar secciones
        function showSection(sectionName) {
            // Ocultar todas las secciones
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.style.display = 'none';
            });

            // Mostrar la sección seleccionada
            document.getElementById(sectionName + '-section').style.display = 'block';

            // Actualizar navegación activa
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Auto-refresh cada 5 minutos
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>

</html>