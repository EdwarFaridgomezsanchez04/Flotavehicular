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

// Fetch nombre_completo and foto_perfil if not in session
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: 'roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Fetch tipos de mantenimiento
$tipos_mantenimiento_query = $con->prepare("SELECT id_tipo_mantenimiento, descripcion FROM tipo_mantenimiento");
$tipos_mantenimiento_query->execute();
$tipos_mantenimiento = $tipos_mantenimiento_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch clasificaciones de trabajo
$trabajos_query = $con->prepare("SELECT id, Trabajo, Precio FROM clasificacion_trabajo");
$trabajos_query->execute();
$trabajos = $trabajos_query->fetchAll(PDO::FETCH_ASSOC);

// Fetch mantenimientos
$mantenimientos_query = $con->prepare("
    SELECT m.*, v.placa, tm.descripcion AS tipo_mantenimiento,
           GROUP_CONCAT(c.Trabajo, ': $', d.subtotal) AS detalles_trabajos
    FROM mantenimiento m
    JOIN vehiculos v ON m.placa = v.placa
    JOIN tipo_mantenimiento tm ON m.id_tipo_mantenimiento = tm.id_tipo_mantenimiento
    LEFT JOIN detalles_mantenimiento_clasificacion d ON m.id_mantenimiento = d.id_mantenimiento
    LEFT JOIN clasificacion_trabajo c ON d.id_trabajo = c.id
    WHERE v.Documento = :documento
    GROUP BY m.id_mantenimiento
");
$mantenimientos_query->bindParam(':documento', $documento, PDO::PARAM_STR);
$mantenimientos_query->execute();
$mantenimientos = $mantenimientos_query->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = $_POST['placa'] ?? '';
    $id_tipo_mantenimiento = $_POST['id_tipo_mantenimiento'] ?? '';
    $fecha_programada = $_POST['fecha_programada'] ?? '';
    $fecha_realizada = $_POST['fecha_realizada'] ?? '';
    $kilometraje_actual = $_POST['kilometraje_actual'] ?? '';
    $proximo_cambio_km = $_POST['proximo_cambio_km'] ?? '';
    $proximo_cambio_fecha = $_POST['proximo_cambio_fecha'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $trabajos_seleccionados = $_POST['trabajos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];

    // Insertar mantenimiento
    $insert_mantenimiento = $con->prepare("
        INSERT INTO mantenimiento (placa, id_tipo_mantenimiento, fecha_programada, fecha_realizada, kilometraje_actual, proximo_cambio_km, proximo_cambio_fecha, observaciones, documento_usuario)
        VALUES (:placa, :id_tipo_mantenimiento, :fecha_programada, :fecha_realizada, :kilometraje_actual, :proximo_cambio_km, :proximo_cambio_fecha, :observaciones, :documento)
    ");
    $insert_mantenimiento->execute([
        ':placa' => $placa,
        ':id_tipo_mantenimiento' => $id_tipo_mantenimiento,
        ':fecha_programada' => $fecha_programada,
        ':fecha_realizada' => $fecha_realizada ?: null,
        ':kilometraje_actual' => $kilometraje_actual ?: null,
        ':proximo_cambio_km' => $proximo_cambio_km ?: null,
        ':proximo_cambio_fecha' => $proximo_cambio_fecha ?: null,
        ':observaciones' => $observaciones,
        ':documento' => $documento
    ]);

    $id_mantenimiento = $con->lastInsertId();

    // Insertar detalles de trabajos
    foreach ($trabajos_seleccionados as $index => $id_trabajo) {
        $cantidad = $cantidades[$index] ?? 1;
        $trabajo_query = $con->prepare("SELECT Precio FROM clasificacion_trabajo WHERE id = :id_trabajo");
        $trabajo_query->execute([':id_trabajo' => $id_trabajo]);
        $trabajo = $trabajo_query->fetch(PDO::FETCH_ASSOC);
        $subtotal = $trabajo['Precio'] * $cantidad;

        $insert_detalle = $con->prepare("
            INSERT INTO detalles_mantenimiento_clasificacion (id_mantenimiento, id_trabajo, cantidad, subtotal)
            VALUES (:id_mantenimiento, :id_trabajo, :cantidad, :subtotal)
        ");
        $insert_detalle->execute([
            ':id_mantenimiento' => $id_mantenimiento,
            ':id_trabajo' => $id_trabajo,
            ':cantidad' => $cantidad,
            ':subtotal' => $subtotal
        ]);
    }

    $_SESSION['success'] = 'Mantenimiento registrado exitosamente.';
    header('Location: gestionar_mantenimiento.php');
    exit;

    
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flotax AGC - Mantenimiento General</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        function agregarTrabajo() {
            const container = document.getElementById('trabajos-container');
            const div = document.createElement('div');
            div.className = 'trabajo-item';
            div.innerHTML = `
                <select name="trabajos[]" required>
                    <option value="">Seleccionar Trabajo</option>
                    <?php foreach ($trabajos as $trabajo): ?>
                        <option value="<?php echo htmlspecialchars($trabajo['id']); ?>">
                            <?php echo htmlspecialchars($trabajo['Trabajo'] . ' ($' . $trabajo['Precio'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="cantidades[]" placeholder="Cantidad" min="1" value="1" required>
                <button type="button" onclick="this.parentElement.remove()">Eliminar</button>
            `;
            container.appendChild(div);
        }
    </script>
</head>
<body>
    <?php include('../header.php'); ?>

    <div class="container">
        

        <form method="POST" action="">
            <div class="form-group">
     <h2>Historial de Mantenimientos</h2>
    <table>
        <thead>
            <tr>
                <th>Placa</th>
                <th>Tipo de Mantenimiento</th>
                <th>Fecha Programada</th>
                <th>Fecha Realizada</th>
                <th>Kilometraje Actual</th>
                <th>Próximo Mantenimiento (km)</th>
                <th>Próximo Mantenimiento (Fecha)</th>
                <th>Detalles de Trabajos</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($mantenimientos as $mantenimiento): ?>
                <tr <?php
                    $hoy = new DateTime();
                    $proximo = new DateTime($mantenimiento['proximo_cambio_fecha']);
                    $diferencia_dias = $hoy->diff($proximo)->days;
                    if ($proximo >= $hoy && $diferencia_dias <= 30) {
                        echo 'class="alerta"';
                    }
                ?>>
                    <td><?php echo htmlspecialchars($mantenimiento['placa']); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['tipo_mantenimiento']); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['fecha_programada']); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['fecha_realizada'] ?: 'No realizada'); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['kilometraje_actual'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['proximo_cambio_km'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['proximo_cambio_fecha'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['detalles_trabajos'] ?: 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($mantenimiento['observaciones'] ?: 'N/A'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</body>