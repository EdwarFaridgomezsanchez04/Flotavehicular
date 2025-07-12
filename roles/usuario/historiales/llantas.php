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
};

// Fetch revisiones de llantas
$llantas_query = $con->prepare("
    SELECT l.*, v.placa 
    FROM llantas l 
    JOIN vehiculos v ON l.placa = v.placa 
    WHERE l.documento_usuario = :documento
");
$llantas_query->bindParam(':documento', $documento, PDO::PARAM_STR);
$llantas_query->execute();
$llantas = $llantas_query->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = $_POST['placa'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $ultimo_cambio = $_POST['ultimo_cambio'] ?? '';
    $presion_llantas = $_POST['presion_llantas'] ?? '';
    $kilometraje_actual = $_POST['kilometraje_actual'] ?? '';
    $proximo_cambio_km = $_POST['proximo_cambio_km'] ?? '';
    $proximo_cambio_fecha = $_POST['proximo_cambio_fecha'] ?? '';
    $notas = $_POST['notas'] ?? '';

    $insert_query = $con->prepare("
        INSERT INTO llantas (placa, estado, ultimo_cambio, presion_llantas, kilometraje_actual, proximo_cambio_km, proximo_cambio_fecha, notas, documento_usuario)
        VALUES (:placa, :estado, :ultimo_cambio, :presion_llantas, :kilometraje_actual, :proximo_cambio_km, :proximo_cambio_fecha, :notas, :documento)
    ");
    $insert_query->execute([
        ':placa' => $placa,
        ':estado' => $estado,
        ':ultimo_cambio' => $ultimo_cambio ?: null,
        ':presion_llantas' => $presion_llantas ?: null,
        ':kilometraje_actual' => $kilometraje_actual ?: null,
        ':proximo_cambio_km' => $proximo_cambio_km ?: null,
        ':proximo_cambio_fecha' => $proximo_cambio_fecha ?: null,
        ':notas' => $notas,
        ':documento' => $documento
    ]);
    $_SESSION['success'] = 'Revisión de llantas registrada exitosamente.';
    header('Location: gestionar_llantas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flotax AGC - Llantas</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include('../header.php'); ?>


<div class="container">
    
    <h2>Historial de Revisiones de Llantas</h2>
        <table>
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
                <?php foreach ($llantas as $llanta): ?>
                    <tr <?php
                        $hoy = new DateTime();
                        $proximo = new DateTime($llanta['proximo_cambio_fecha']);
                        $diferencia_dias = $hoy->diff($proximo)->days;
                        if ($proximo >= $hoy && $diferencia_dias <= 30) {
                            echo 'class="alerta"';
                        }
                    ?>>
                        <td><?php echo htmlspecialchars($llanta['placa']); ?></td>
                        <td><?php echo htmlspecialchars($llanta['estado']); ?></td>
                        <td><?php echo htmlspecialchars($llanta['ultimo_cambio'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($llanta['presion_llantas'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($llanta['kilometraje_actual'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($llanta['proximo_cambio_km'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($llanta['proximo_cambio_fecha'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($llanta['notas'] ?: 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>