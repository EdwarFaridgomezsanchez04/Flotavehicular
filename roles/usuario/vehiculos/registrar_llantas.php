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
    $foto_perfil = $user['foto_perfil'] ?: '/Flotavehicular/roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Fetch revisiones de llantas
$llantas_query = $con->prepare("
    SELECT l.*, v.placa 
    FROM llantas l 
    JOIN vehiculos v ON l.placa = v.placa
");
$llantas_query->execute();
$llantas = $llantas_query->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flotax AGC - Llantas</title>
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/styles_llantas.css">
</head>
<body onload="formulario.placa.focus()">
    <?php include('../header.php'); ?>

    <div class="container">
        <h1 class="titulo">Gestión de Llantas</h1>
        <form action="" method="post" class="form-llantas" id="formulario">
            <p class="instructions">Selecciona el vehículo y completa los detalles de la revisión de llantas, como su estado, presión y kilometraje. Luego, presiona "Registrar Revisión" para guardar la información.</p>

            <div class="input-gruop">
                <!-- Primer grupo de 3 campos -->
                <div class="input-subgroup">
                    <div>
                        <label for="placa">Vehículo:</label>
                        <div class="input_field_placa" id="grupo_placa">
                            <select name="placa" id="placa" required>
                                <option value="">Seleccionar Vehículo</option>
                                <?php
                                $vehiculos_query = $con->prepare("SELECT placa FROM vehiculos WHERE Documento = :documento");
                                $vehiculos_query->bindParam(':documento', $documento, PDO::PARAM_STR);
                                $vehiculos_query->execute();
                                foreach ($vehiculos_query->fetchAll(PDO::FETCH_ASSOC) as $vehiculo) {
                                    $selected = (isset($_POST['placa']) && $_POST['placa'] === $vehiculo['placa']) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($vehiculo['placa']) . '" ' . $selected . '>' . htmlspecialchars($vehiculo['placa']) . '</option>';
                                }
                                ?>
                            </select>
                            <i class="bi bi-car-front"></i>
                        </div>
                        <div class="formulario_error_placa" id="formulario_correcto_placa">
                            <p class="validacion_placa" id="validacion_placa">Seleccione un vehículo.</p>
                        </div>
                    </div>

                    <div>
                        <label for="estado">Estado de Llantas:</label>
                        <div class="input_field_estado" id="grupo_estado">
                            <select name="estado" id="estado" required>
                                <option value="">Seleccionar Estado</option>
                                <option value="Bueno" <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'Bueno') ? 'selected' : ''; ?>>Bueno</option>
                                <option value="Regular" <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'Regular') ? 'selected' : ''; ?>>Regular</option>
                                <option value="Malo" <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'Malo') ? 'selected' : ''; ?>>Malo</option>
                            </select>
                            <i class="bi bi-gear"></i>
                        </div>
                        <div class="formulario_error_estado" id="formulario_correcto_estado">
                            <p class="validacion_estado" id="validacion_estado">Seleccione un estado.</p>
                        </div>
                    </div>

                    <div>
                        <label for="ultimo_cambio">Último Cambio:</label>
                        <div class="input_field_ultimo_cambio" id="grupo_ultimo_cambio">
                            <input type="date" name="ultimo_cambio" id="ultimo_cambio" value="<?php echo htmlspecialchars($_POST['ultimo_cambio'] ?? ''); ?>">
                            <i class="bi bi-calendar"></i>
                        </div>
                        <div class="formulario_error_ultimo_cambio" id="formulario_correcto_ultimo_cambio">
                            <p class="validacion_ultimo_cambio" id="validacion_ultimo_cambio">Ingrese una fecha antigua.</p>
                        </div>
                    </div>
                </div>

                <!-- Segundo grupo de 3 campos -->
                <div class="input-subgroup">
                    <div>
                        <label for="presion_llantas">Presión de Llantas (PSI):</label>
                        <div class="input_field_presion_llantas" id="grupo_presion_llantas">
                            <input type="number" step="0.1" name="presion_llantas" id="presion_llantas" value="<?php echo htmlspecialchars($_POST['presion_llantas'] ?? ''); ?>">
                            <i class="bi bi-gauge"></i>
                        </div>
                        <div class="formulario_error_presion_llantas" id="formulario_correcto_presion_llantas">
                            <p class="validacion_presion_llantas" id="validacion_presion_llantas">Ingrese un valor entre 0.1 y 100.0 PSI.</p>
                        </div>
                    </div>

                    <div>
                        <label for="kilometraje_actual">Kilometraje Actual:</label>
                        <div class="input_field_kilometraje_actual" id="grupo_kilometraje_actual">
                            <input type="number" name="kilometraje_actual" id="kilometraje_actual" value="<?php echo htmlspecialchars($_POST['kilometraje_actual'] ?? ''); ?>">
                            <i class="bi bi-speedometer"></i>
                        </div>
                        <div class="formulario_error_kilometraje_actual" id="formulario_correcto_kilometraje_actual">
                            <p class="validacion_kilometraje_actual" id="validacion_kilometraje_actual">Ingrese un número positivo.</p>
                        </div>
                    </div>

                    <div>
                        <label for="proximo_cambio_km">Próximo Cambio (km):</label>
                        <div class="input_field_proximo_cambio_km" id="grupo_proximo_cambio_km">
                            <input type="number" name="proximo_cambio_km" id="proximo_cambio_km" value="<?php echo htmlspecialchars($_POST['proximo_cambio_km'] ?? ''); ?>">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div class="formulario_error_proximo_cambio_km" id="formulario_correcto_proximo_cambio_km">
                            <p class="validacion_proximo_cambio_km" id="validacion_proximo_cambio_km">Ingrese un número positivo.</p>
                        </div>
                    </div>
                </div>

                <!-- Campos restantes -->
                <div>
                    <label for="proximo_cambio_fecha">Próximo Cambio (Fecha):</label>
                    <div class="input_field_proximo_cambio_fecha" id="grupo_proximo_cambio_fecha">
                        <input type="date" name="proximo_cambio_fecha" id="proximo_cambio_fecha" value="<?php echo htmlspecialchars($_POST['proximo_cambio_fecha'] ?? ''); ?>">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="formulario_error_proximo_cambio_fecha" id="formulario_correcto_proximo_cambio_fecha">
                        <p class="validacion_proximo_cambio_fecha" id="validacion_proximo_cambio_fecha">Ingrese una fecha futura.</p>
                    </div>
                </div>

                <div>
                    <label for="notas">Notas:</label>
                    <div class="input_field_notas" id="grupo_notas">
                        <textarea name="notas" id="notas" rows="4"><?php echo htmlspecialchars($_POST['notas'] ?? ''); ?></textarea>
                        <i class="bi bi-pencil"></i>
                    </div>
                    <div class="formulario_error_notas" id="formulario_correcto_notas">
                        <p class="validacion_notas" id="validacion_notas">Máximo 500 caracteres, solo letras, números y puntuación básica.</p>
                    </div>
                </div>
            </div>

            <!-- Error general -->
            <div>
                <p class="formulario_error" id="formulario_error"><b>Error:</b> Por favor complete todos los campos correctamente.</p>
            </div>

            <div class="btn-field">
                <button type="submit" class="btn btn-primary">Registrar Revisión</button>
            </div>

            <!-- Mensaje de éxito -->
            <p class="formulario_exito" id="formulario_exito">Revision de llantas registrado correctamente.</p>
        </form>
    </div>
    <script src="../js/scriptllantas.js"></script>
     <?php
      include('../../../includes/auto_logout_modal.php');
    ?>
</body>
</html>