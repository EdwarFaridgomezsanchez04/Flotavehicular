<?php
// Inicia la sesión y verifica el acceso del usuario
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';
$db = new Database();
$con = $db->conectar();

// Verificación de inicio de sesión
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Consulta todos los vehículos del usuario logueado
$vehiculos_query = $con->prepare("SELECT placa FROM vehiculos WHERE Documento = :documento");
$vehiculos_query->bindParam(':documento', $documento, PDO::PARAM_STR);
$vehiculos_query->execute();
$vehiculos = $vehiculos_query->fetchAll(PDO::FETCH_ASSOC);

// Obtiene nombre completo y foto de perfil si no están en sesión
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: '/roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Función declarada una sola vez
function getDocumentStatus($fecha) {
    if (!$fecha || $fecha === 'No registrado') {
        return ['status' => 'no-disponible', 'text' => 'No registrado', 'icon' => 'bi-question-circle'];
    }
    
    $fecha_actual = new DateTime();
    $fecha_venc = new DateTime($fecha);
    $diferencia = $fecha_actual->diff($fecha_venc);
    
    if ($fecha_venc < $fecha_actual) {
        return ['status' => 'vencido', 'text' => 'Vencido', 'icon' => 'bi-x-circle'];
    } elseif ($diferencia->days <= 30) {
        return ['status' => 'proximo', 'text' => 'Próximo', 'icon' => 'bi-exclamation-triangle'];
    } else {
        return ['status' => 'vigente', 'text' => 'Vigente', 'icon' => 'bi-check-circle'];
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flotax AGC - Inicio</title>
    <!-- Favicon y estilos principales -->
    <link rel="shortcut icon" href="css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php
    // Incluye el header del usuario
    include('header.php');
?>

<!-- Notificaciones de éxito o error -->
<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
<div class="notification">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <div class="alert error"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Sección de accesos rápidos a alertas de documentos y mantenimientos -->
<div class="alertas">
    <h1>Mis Alertas</h1>
    <div class="alertas-grid">
        <a href="vehiculos/registrar_soat" class="boton">
            <i class="bi bi-shield-check"></i> SOAT
        </a>
        <a href="vehiculos/registrar_tecnomecanica" class="boton">
            <i class="bi bi-tools"></i> Tecnomecánica
        </a>
        <a href="vehiculos/registrar_licencia" class="boton">
            <i class="bi bi-card-heading"></i> Licencia de Conducción
        </a>
        <a href="vehiculos/pico_placa" class="boton">
            <i class="bi bi-sign-stop"></i> Pico y Placa
        </a>
        <a href="vehiculos/registrar_llantas" class="boton">
            <i class="bi bi-circle"></i> Llantas
        </a>
        <a href="vehiculos/registrar_mantenimiento" class="boton">
            <i class="bi bi-gear"></i> Mantenimiento y Aceite
        </a>
        <a href="vehiculos/multas" class="boton">
            <i class="bi bi-receipt-cutoff"></i> Multas
        </a>
    </div>
</div>

<!-- Tabla de documentos y vencimientos de los vehículos del usuario -->
<div class="garage-table">
    <h3>Documentos y Vencimientos</h3>
    <table>
        <thead>
            <tr>
                <th>Placa</th>
                <th>SOAT</th>
                <th>Tecnomecánica</th>
                <th>Licencia</th>
                <th>Llantas</th>
                <th>Mantenimiento</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Bucle que usa la función
            foreach ($vehiculos as $vehiculo) {
                $placa = $vehiculo['placa'];

                // Consulta fechas de vencimiento para cada documento
                // SOAT
                $soat = $con->prepare("SELECT fecha_vencimiento FROM soat WHERE id_placa = :placa ORDER BY fecha_vencimiento DESC LIMIT 1");
                $soat->bindParam(':placa', $placa, PDO::PARAM_STR);
                $soat->execute();
                $soat_fecha = $soat->fetchColumn();

                // Tecnomecánica
                $tecno = $con->prepare("SELECT fecha_vencimiento FROM tecnomecanica WHERE id_placa = :placa ORDER BY fecha_vencimiento DESC LIMIT 1");
                $tecno->bindParam(':placa', $placa, PDO::PARAM_STR);
                $tecno->execute();
                $tecno_fecha = $tecno->fetchColumn();

                // Licencia
                $lic = $con->prepare("SELECT fecha_vencimiento FROM licencias WHERE id_documento = :documento ORDER BY fecha_vencimiento DESC LIMIT 1");
                $lic->bindParam(':documento', $documento, PDO::PARAM_STR);
                $lic->execute();
                $lic_fecha = $lic->fetchColumn();

                // Llantas
                $llantas = $con->prepare("SELECT proximo_cambio_fecha FROM llantas WHERE placa = :placa ORDER BY proximo_cambio_fecha DESC LIMIT 1");
                $llantas->bindParam(':placa', $placa, PDO::PARAM_STR);
                $llantas->execute();
                $llantas_fecha = $llantas->fetchColumn();

                // Mantenimiento
                $mantenimiento = $con->prepare("SELECT proximo_cambio_fecha FROM mantenimiento WHERE placa = :placa ORDER BY proximo_cambio_fecha DESC LIMIT 1");
                $mantenimiento->bindParam(':placa', $placa, PDO::PARAM_STR);
                $mantenimiento->execute();
                $mantenimiento_fecha = $mantenimiento->fetchColumn();

                // Obtener estados
                $soat_status = getDocumentStatus($soat_fecha);
                $tecno_status = getDocumentStatus($tecno_fecha);
                $lic_status = getDocumentStatus($lic_fecha);
                $llantas_status = getDocumentStatus($llantas_fecha);
                $mantenimiento_status = getDocumentStatus($mantenimiento_fecha);

                echo "<tr>
                        <td><strong>" . htmlspecialchars($placa) . "</strong></td>
                        <td>
                            <span class='document-status " . $soat_status['status'] . "'>
                                <i class='bi " . $soat_status['icon'] . " status-icon'></i>
                                " . $soat_status['text'] . "
                            </span>
                        </td>
                        <td>
                            <span class='document-status " . $tecno_status['status'] . "'>
                                <i class='bi " . $tecno_status['icon'] . " status-icon'></i>
                                " . $tecno_status['text'] . "
                            </span>
                        </td>
                        <td>
                            <span class='document-status " . $lic_status['status'] . "'>
                                <i class='bi " . $lic_status['icon'] . " status-icon'></i>
                                " . $lic_status['text'] . "
                            </span>
                        </td>
                        <td>
                            <span class='document-status " . $llantas_status['status'] . "'>
                                <i class='bi " . $llantas_status['icon'] . " status-icon'></i>
                                " . $llantas_status['text'] . "
                            </span>
                        </td>
                        <td>
                            <span class='document-status " . $mantenimiento_status['status'] . "'>
                                <i class='bi " . $mantenimiento_status['icon'] . " status-icon'></i>
                                " . $mantenimiento_status['text'] . "
                            </span>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
</div>
<!-- Fin de la tabla -->

<!-- Sección de selección y visualización de vehículos del usuario -->
<div class="garage">
    <div class="garage-content">
        <h2>Mis Vehículos</h2>
        <form action="vehiculos/listar/listar" method="get">
            <div class="form-group">
                <select name="vehiculo">
                    <option value="">Seleccionar Vehículo</option>
                    <?php
                    foreach ($vehiculos as $vehiculo) {
                        echo '<option value="' . htmlspecialchars($vehiculo['placa']) . '">' . htmlspecialchars($vehiculo['placa']) . '</option>';
                    }
                    ?>
                </select>
                <button type="submit">Mostrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Botón de cierre de sesión -->
<div class="sidebar">
    <a href="../../includes/salir.php" class="logout" title="Cerrar Sesión">
        <i class="bi bi-box-arrow-right"></i>
    </a>
</div>

<?php
    // Incluye el modal de cierre de sesión automático
    include('../../includes/auto_logout_modal.php');
?>


</body>
</html>