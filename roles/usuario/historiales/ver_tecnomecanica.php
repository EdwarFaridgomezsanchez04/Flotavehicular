<?php
session_start();
require_once('../../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();
include '../../../includes/validarsession.php';


$documento = $_SESSION['documento'] ?? null;
$filtro_placa = $_GET['placa'] ?? '';

// Consulta dinámica con filtro por placa
if (!empty($filtro_placa)) {
    $sql = $con->prepare("
        SELECT t.id_rtm, v.placa, t.fecha_expedicion, t.fecha_vencimiento,
               c.centro_revision, e.soat_est
        FROM tecnomecanica t
        INNER JOIN vehiculos v ON t.id_placa = v.placa
        INNER JOIN centro_rtm c ON t.id_centro_revision = c.id_centro
        INNER JOIN estado_soat e ON t.id_estado = e.id_stado
        WHERE v.placa LIKE :placa AND v.Documento = :documento
        ORDER BY t.fecha_expedicion DESC
    ");
    $sql->execute([
        'placa' => "%$filtro_placa%",
        'documento' => $documento
    ]);
} else {
    $sql = $con->prepare("
        SELECT t.id_rtm, v.placa, t.fecha_expedicion, t.fecha_vencimiento,
               c.centro_revision, e.soat_est
        FROM tecnomecanica t
        INNER JOIN vehiculos v ON t.id_placa = v.placa
        INNER JOIN centro_rtm c ON t.id_centro_revision = c.id_centro
        INNER JOIN estado_soat e ON t.id_estado = e.id_stado
        WHERE v.Documento = :documento
        ORDER BY t.fecha_expedicion DESC
    ");
    $sql->execute(['documento' => $documento]);
}

$tecnomecanicas = $sql->fetchAll(PDO::FETCH_ASSOC);

// Perfil
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?? 'roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Tecnomecánica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f2f5; padding-bottom: 60px; }
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
        .badge {
            font-size: 0.9rem;
            padding: 6px 10px;
            border-radius: 12px;
        }
        .estado-vigente { background-color:rgb(100, 253, 184); color: #0f5132; }
        .estado-vencido { background-color:rgb(248, 102, 114); color:rgb(123, 0, 0); }
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
    <h2><i class="fas fa-file-shield me-2"></i>Listado de Tecnomecánicas Registradas</h2>

    <!-- Campo de búsqueda -->
    <div class="mb-4 d-flex justify-content-center">
        <input type="text" id="filtroPlaca" class="form-control w-50 text-uppercase" placeholder="Buscar por placa" value="<?= htmlspecialchars($filtro_placa) ?>" style="text-transform: uppercase;">
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Centro Revisión</th>
                    <th>Fecha Expedición</th>
                    <th>Fecha Vencimiento</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($tecnomecanicas) > 0): ?>
                    <?php foreach ($tecnomecanicas as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['placa']) ?></td>
                            <td><?= htmlspecialchars($row['centro_revision']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_expedicion']) ?></td>
                            <td><?= htmlspecialchars($row['fecha_vencimiento']) ?></td>
                            <td>
                                <?php
                                    $estado = strtolower($row['soat_est']);
                                    $clase = match ($estado) {
                                        'vigente' => 'estado-vigente',
                                        'vencido' => 'estado-vencido',
                                        default => 'bg-secondary text-white'
                                    };
                                ?>
                                <span class="badge <?= $clase ?>"><?= ucfirst($estado) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No hay registros de tecnomecánica.</td></tr>
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
