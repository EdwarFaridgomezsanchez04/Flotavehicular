<?php
session_start();
require_once '../../../conecct/conex.php';
include '../../../includes/validarsession.php';
$database = new Database();
$con = $database->conectar();

// Check if the connection is successful
if (!$con) {
    die("Error: No se pudo conectar a la base de datos. Verifique el archivo conex.php.");
}

// Check for documento in session
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Fetch user's full name for the profile section
$nombre_completo = $_SESSION['nombre_completo'] ?? 'Usuario';


// Fetch vehicles for the user
$query_vehiculos = "SELECT placa FROM vehiculos WHERE Documento = :documento";
$stmt_vehiculos = $con->prepare($query_vehiculos);
$stmt_vehiculos->bindParam(':documento', $documento);
$stmt_vehiculos->execute();
$vehiculos = $stmt_vehiculos->fetchAll(PDO::FETCH_ASSOC);

// Fetch aseguradoras
$query_aseguradora = "SELECT id_asegura, nombre FROM aseguradoras_soat";
$stmt_asegura = $con->prepare($query_aseguradora);
$stmt_asegura->execute();
$aseguradoras = $stmt_asegura->fetchAll(PDO::FETCH_ASSOC);

// fetch estado
$sql_estado = $con->prepare("SELECT id_stado, soat_est FROM estado_soat");
$sql_estado->execute();
$estado = $sql_estado->fetchAll(PDO::FETCH_ASSOC);

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

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro SOAT</title>
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/styles_soatytecno.css">
</head>
<body onload="formsoat.placa.focus()">
  <?php
    include('../header.php')   
  ?>


    <div class="contenedor">
        <form id="formsoat" class="formsoat" method="post" action="">
            <div class="titulo">
                <h1>Registrar Soat</h1>
                <p class="instructions">Completa los detalles del soat(seguro) para recibir alertas de vencimiento.</p>
    
            </div>

            <!-- Placa del Vehículo -->
            <div class="input_field_placa" id="grupo_placa">
                <label for="placa">Placa del Vehículo:*</label>
                <i class="bi bi-car-front"></i>
                <select id="placa" name="placa" required>
                    <option value="">Seleccione una placa</option>
                    <?php foreach ($vehiculos as $row) { ?>
                        <option value="<?php echo htmlspecialchars($row['placa']); ?>">
                            <?php echo htmlspecialchars($row['placa']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="formulario_error_placa" id="formulario_correcto_placa">
                <p class="validacion" id="validacion">Seleccione una placa válida.</p>
            </div>

            <!-- Fecha de Expedición -->
            <div class="input_field_expedicion" id="grupo_expedicion">
                <label for="fechaExpedicion">Fecha de Expedición:*</label>
                <i class="bi bi-calendar-check"></i>
                <input type="date" id="fechaExpedicion" name="fechaExpedicion" required>
            </div>
            <div class="formulario_error_expedicion" id="formulario_correcto_expedicion">
                <p class="validacion" id="validacion1">Seleccione una fecha válida.</p>
            </div>

            <!-- Fecha de Vencimiento -->
            <div class="input_field_vencimiento" id="grupo_vencimiento">
                <label for="fechaVencimiento">Fecha de Vencimiento:*</label>
                <i class="bi bi-calendar-x"></i>
                <input type="date" id="fechaVencimiento" name="fechaVencimiento" required>
            </div>
            <div class="formulario_error_vencimiento" id="formulario_correcto_vencimiento">
                <p class="validacion" id="validacion2">Seleccione una fecha válida.</p>
            </div>

            <!-- Aseguradora -->
            <div class="input_field_aseguradora" id="grupo_aseguradora">
                <label for="aseguradora">Aseguradora:*</label>
                <i class="bi bi-shield-shaded"></i>
                <select id="aseguradora" name="aseguradora" required>
                    <option value="">Seleccione una aseguradora</option>
                    <?php foreach ($aseguradoras as $row) { ?>
                        <option value="<?php echo htmlspecialchars($row['id_asegura']); ?>">
                            <?php echo htmlspecialchars($row['nombre']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="formulario_error_aseguradora" id="formulario_correcto_aseguradora">
                <p class="validacion" id="validacion3">Seleccione una aseguradora válida.</p>
            </div>
            
            <!-- estado soat -->
            <div class="input_field_estado" id="grupo_estado">
                <label for="estado">Estado:*</label>
                <i class="bi bi-check-circle-fill"></i>
                <select id="estado" name="estado" required>
                    <option value="">Seleccione un estado</option>
                    <?php foreach ($estado as $row) { ?>
                        <option value="<?php echo htmlspecialchars($row['id_stado']); ?>">
                            <?php echo htmlspecialchars($row['soat_est']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="formulario_error_estado" id="formulario_correcto_estado">
                <p class="validacion" id="validacion4">Seleccione un estado valido.</p>
            </div>

            <!-- Error general -->
            <div>
                <p class="formulario_error" id="formulario_error"><b>Error:</b> Por favor complete todos los campos correctamente.</p>
            </div>

            <!-- Botón -->
            <div class="btn-field">
                <button type="submit" class="btn btn-success">Registrar SOAT</button>
            </div>

            <!-- Mensaje de éxito -->
            <p class="formulario_exito" id="formulario_exito">SOAT registrado correctamente.</p>

        </form>
        <div class="comprar">
            <p class="info">No tienes soat? Compralo aqui</p>
            <div class="cont_com">
                <a href="https://soatmundial.com.co" class="link" target="_blank">Comprar soat</a>
            </div>
            
        </div>
    </div>

    <script src="../js/registrar_soat.js"></script>
    <?php
      include('../../../includes/auto_logout_modal.php');
    ?>
</body>
</html>