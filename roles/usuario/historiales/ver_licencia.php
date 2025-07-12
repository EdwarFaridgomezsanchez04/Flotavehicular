<?php
    session_start();
    require_once('../../../conecct/conex.php');
    $db = new Database();
    $con = $db->conectar();
    include '../../../includes/validarsession.php';

    $documento = $_SESSION['documento'] ?? null;

    // Filtros
    $filtro_categoria = $_GET['categoria'] ?? '';
    $filtro_estado = $_GET['estado'] ?? '';

    // Consulta para mostrar solo las licencias del usuario logueado y filtrar por categoría y estado
    $where = "licencias.id_documento = :documento";
    $params = ['documento' => $documento];

    if (!empty($filtro_categoria)) {
        $where .= " AND categoria_licencia.nombre_categoria LIKE :categoria";
        $params['categoria'] = "%$filtro_categoria%";
    }

    if (!empty($filtro_estado)) {
        // El estado se calcula en PHP, así que filtramos después de la consulta
        $filtrar_estado = strtolower($filtro_estado);
    } else {
        $filtrar_estado = '';
    }

    $sql = $con->prepare("
        SELECT licencias.*, categoria_licencia.nombre_categoria, servicios_licencias.nombre_servicios
        FROM licencias
        INNER JOIN categoria_licencia ON licencias.id_categoria = categoria_licencia.id_categoria
        INNER JOIN servicios_licencias ON licencias.id_servicio = servicios_licencias.id_servicio
        WHERE $where
    ");
    $sql->execute($params);
    $licencias = $sql->fetchAll(PDO::FETCH_ASSOC);

    // Datos de perfil
    $nombre_completo = $_SESSION['nombre_completo'] ?? null;
    $foto_perfil = $_SESSION['foto_perfil'] ?? null;
    if (!$nombre_completo || !$foto_perfil) {
        $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
        $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
        $foto_perfil = $user['foto_perfil'] ?? '/Flotavehicular/roles/usuario/css/img/perfil.jpg';
        $_SESSION['nombre_completo'] = $nombre_completo;
        $_SESSION['foto_perfil'] = $foto_perfil;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Licencia</title>
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .badge {
            font-size: 0.9rem;
            padding: 6px 10px;
            border-radius: 12px;
        }

        .estado-vigente { background-color: rgb(100, 253, 184); color: #0f5132; }
        .estado-vencido { background-color: rgb(248, 102, 114); color: rgb(123, 0, 0); }
        .estado-pendiente { background-color: rgb(255, 204, 0); color: rgb(102, 60, 0); }

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
    <h2><i class="bi bi-person-vcard me-2"></i>Listado de Licencias Registradas</h2>

    <!-- Filtros por categoría y estado -->
    <form method="get" class="row mb-4 justify-content-center g-2">
        <div class="col-md-4">
            <input type="text" name="categoria" id="filtroCategoria" class="form-control" placeholder="Buscar por categoría (ej: B1, C1, etc)" value="<?= htmlspecialchars($filtro_categoria) ?>">
        </div>
        <div class="col-md-3">
            <select name="estado" id="filtroEstado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="vigente" <?= $filtro_estado == 'vigente' ? 'selected' : '' ?>>Vigente</option>
                <option value="vencido" <?= $filtro_estado == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                <option value="pendiente" <?= $filtro_estado == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100"><i class="fa fa-search"></i> Buscar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead>
                <tr>
                    <th>Fecha de Expedición</th>
                    <th>Fecha de Vencimiento</th>
                    <th>Categoría</th>
                    <th>Tipo de Servicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $fechaActual = date("Y-m-d");
                $hayRegistros = false;
                foreach ($licencias as $fila):
                    // Determinar estado
                    if (empty($fila['fecha_expedicion'])) {
                        $estado = "pendiente";
                        $clase = "estado-pendiente";
                    } elseif ($fila['fecha_vencimiento'] < $fechaActual) {
                        $estado = "vencido";
                        $clase = "estado-vencido";
                    } else {
                        $estado = "vigente";
                        $clase = "estado-vigente";
                    }
                    // Filtrar por estado si corresponde
                    if ($filtrar_estado && $estado !== $filtrar_estado) continue;
                    $hayRegistros = true;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['fecha_expedicion']) ?></td>
                        <td><?= htmlspecialchars($fila['fecha_vencimiento']) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_categoria']) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_servicios']) ?></td>
                        <td><span class="badge <?= $clase ?>"><?= ucfirst($estado) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$hayRegistros): ?>
                    <tr><td colspan="5" class="text-center">No hay registros de Licencias.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include('../../../includes/auto_logout_modal.php'); ?>
</div>
</body>
</html>