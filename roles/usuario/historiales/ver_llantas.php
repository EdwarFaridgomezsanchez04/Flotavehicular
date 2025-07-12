<?php
session_start();
require_once('../../../conecct/conex.php');
require_once('../../../includes/validarsession.php');
$db = new Database();
$con = $db->conectar();

$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../../login/login.php');
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
    $foto_perfil = $user['foto_perfil'] ?: '/Flotavehicular/roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

$filtro_placa = $_GET['placa'] ?? '';

// Consulta de llantas filtrada SOLO para los vehículos del usuario logueado
if (!empty($filtro_placa)) {
    $llantas_query = $con->prepare("
        SELECT l.*, v.placa 
        FROM llantas l 
        JOIN vehiculos v ON l.placa = v.placa 
        WHERE v.placa LIKE :placa AND v.Documento = :documento
    ");
    $llantas_query->execute([
        'placa' => "%$filtro_placa%",
        'documento' => $documento
    ]);
} else {
    $llantas_query = $con->prepare("
        SELECT l.*, v.placa 
        FROM llantas l 
        JOIN vehiculos v ON l.placa = v.placa 
        WHERE v.Documento = :documento
    ");
    $llantas_query->execute(['documento' => $documento]);
}
$llantas = $llantas_query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Llantas</title>
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * { font-family: 'Poppins', sans-serif; }

        body {
            background: #f0f2f5;
            padding-bottom: 60px;
        }

        .container {
            margin-top: 60px;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            color: #333;
        }

        .table thead {
            background-color: #0d6efd;
            color: white;
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }

        .estado-vigente {
            background-color:rgb(100, 253, 184);
            color: #0f5132;
        }

        .estado-vencido {
            background-color:rgb(248, 102, 114);
            color:rgb(123, 0, 0);
        }

        .estado-pendiente {
            background-color:rgb(255, 219, 100);
            color: #664d03;
        }

        @media screen and (max-width: 768px) {
            .container { padding: 15px; }
            table { font-size: 0.9rem; }
            h2 { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<?php include('../header.php'); ?>

<div class="container">
    <h2><i class="fas fa-car me-2"></i>Historial de Revisiones de Llantas</h2>

    <!-- Búsqueda por placa -->
    <div class="mb-4 d-flex justify-content-center">
        <input type="text" id="filtroPlaca" class="form-control w-50 text-uppercase" placeholder="Buscar por placa" value="<?= htmlspecialchars($filtro_placa) ?>" style="text-transform: uppercase;">
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Estado</th>
                    <th>Último Cambio</th>
                    <th>Presión (PSI)</th>
                    <th>Kilometraje Actual</th>
                    <th>Próximo Cambio (km)</th>
                    <th>Próximo Cambio (Fecha)</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($llantas) > 0): ?>
                    <?php foreach ($llantas as $llanta): ?>
                        <tr <?php
                            $hoy = new DateTime();
                            $proximo = new DateTime($llanta['proximo_cambio_fecha']);
                            $diferencia_dias = $hoy->diff($proximo)->days;
                            if ($proximo >= $hoy && $diferencia_dias <= 30) {
                                echo 'class="estado-pendiente"';
                            }
                        ?>>
                            <td><?= htmlspecialchars($llanta['placa']) ?></td>
                            <td>
                                <?php
                                    $estado = strtolower($llanta['estado']);
                                    $clase = match ($estado) {
                                        'bueno' => 'estado-vigente',
                                        'malo' => 'estado-vencido',
                                        'regular' => 'estado-pendiente',
                                        default => 'bg-secondary text-white'
                                    };
                                ?>
                                <span class="badge <?= $clase ?>"><?= ucfirst($estado) ?></span>
                            </td>
                            <td><?= htmlspecialchars($llanta['ultimo_cambio']) ?></td>
                            <td><?= htmlspecialchars($llanta['presion_llantas']) ?></td>
                            <td><?= htmlspecialchars($llanta['kilometraje_actual']) ?></td>
                            <td><?= htmlspecialchars($llanta['proximo_cambio_km']) ?></td>
                            <td><?= htmlspecialchars($llanta['proximo_cambio_fecha']) ?></td>
                            <td><?= htmlspecialchars($llanta['notas']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No hay registros de llantas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Script para búsqueda automática -->
<script>
    const input = document.getElementById('filtroPlaca');
    let timeout = null;

    input.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            const placa = input.value.trim().toUpperCase();
            const params = new URLSearchParams(window.location.search);
            if (placa) {
                params.set('placa', placa);
            } else {
                params.delete('placa');
            }
            window.location.href = window.location.pathname + '?' + params.toString();
        }, 500);
    });
</script>

    <?php
      include('../../../includes/auto_logout_modal.php');
    ?>

</body>
</html>
