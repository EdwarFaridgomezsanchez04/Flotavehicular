<?php
session_start();
require_once('../../../conecct/conex.php');
include '../../../includes/validarsession.php';
$db = new Database();
$con = $db->conectar();
$data = json_decode(file_get_contents("respuesta.json"), true);
    $multas = $data['data']['multas'] ?? [];
    $por_pagina = 5;
    $pagina_actual = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
    $inicio = ($pagina_actual - 1) * $por_pagina;
    $multas_paginadas = array_slice($multas, $inicio, $por_pagina);
    $total_paginas = ceil(count($multas) / $por_pagina);
    
// Fetch nombre_completo and foto_perfil if not in session
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

// Función para determinar la clase del estado
function getEstadoClass($estado) {
    $estado = strtolower($estado);
    if (strpos($estado, 'pagado') !== false) return 'pagado';
    if (strpos($estado, 'vencido') !== false) return 'vencido';
    return 'pendiente';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multas y Comparendos</title>
    <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="tabla-multas.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  
</head>
<body>

<?php include('../header.php'); ?>

<div class="contenedor">
    <h1><i class="bi bi-file-earmark-text"></i> Resultado de la Consulta de Multas</h1>

    <?php if (count($multas_paginadas) > 0): ?>
        <div class="tabla-container">
            <table class="tabla-multas">
                <thead>
                    <tr>
                        <th><i class="bi bi-hash"></i> Tipo</th>
                        <th><i class="bi bi-car-front"></i> Placa</th>
                        <th><i class="bi bi-file-earmark-text"></i> N° Resolución</th>
                        <th><i class="bi bi-calendar-date"></i> Fecha</th>
                        <th><i class="bi bi-building"></i> Secretaría</th>
                        <th><i class="bi bi-exclamation-triangle"></i> Infracción</th>
                        <th><i class="bi bi-info-circle"></i> Estado</th>
                        <th><i class="bi bi-currency-dollar"></i> Valor</th>
                        <th><i class="bi bi-eye"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($multas_paginadas as $index => $multa): ?>
                        <tr>
                            <td><?= htmlspecialchars($multa['numeroComparendo'] ?? 'N/A') ?></td>
                            <td><span class="placa"><?= htmlspecialchars($multa['placa']) ?></span></td>                   
                            <td><?= htmlspecialchars($multa['numeroResolucion']) ?></td>
                            <td><?= htmlspecialchars($multa['fechaResolucion']) ?></td>
                            <td><?= htmlspecialchars($multa['organismoTransito']) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($multa['infracciones'][0]['codigoInfraccion']) ?></strong><br>
                                <small><?= htmlspecialchars($multa['infracciones'][0]['descripcionInfraccion'] ?? 'Sin descripción') ?></small>
                            </td>
                            <td>
                                <span class="estado-badge <?= getEstadoClass($multa['estadoCartera']) ?>">
                                    <?= htmlspecialchars($multa['estadoCartera']) ?>
                                </span>
                            </td>
                            <td class="valor">$<?= number_format($multa['valor'], 0, ',', '.') ?></td>
                            <td>
                                <a href="ver_detalle.php?id=<?= urlencode($multa['numeroComparendo'] ?? $multa['numeroResolucion']) ?>" 
                                   class="btn-detalle">
                                    <i class="bi bi-eye"></i>
                                    Ver detalles
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN MEJORADA -->
        <div class="paginacion">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?= $pagina_actual - 1 ?>">
                    <i class="bi bi-chevron-left"></i> Anterior
                </a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <a href="?pagina=<?= $i ?>" class="<?= $i == $pagina_actual ? 'activo' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?= $pagina_actual + 1 ?>">
                    Siguiente <i class="bi bi-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="sin-multas">
            <i class="bi bi-info-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
            <strong>No hay multas asociadas</strong><br>
            <small>No se encontraron multas o comparendos para mostrar.</small>
        </div>
    <?php endif; ?>
</div>

    <?php
      include('../../../includes/auto_logout_modal.php');
    ?>

</body>
</html>
