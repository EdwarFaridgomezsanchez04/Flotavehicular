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

    // Fetch tipos de mantenimiento
    $tipos_mantenimiento_query = $con->prepare("SELECT id_tipo_mantenimiento, descripcion FROM tipo_mantenimiento");
    $tipos_mantenimiento_query->execute();
    $tipos_mantenimiento = $tipos_mantenimiento_query->fetchAll(PDO::FETCH_ASSOC);

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
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Flotax AGC - Mantenimiento General</title>
        <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link rel="stylesheet" href="../css/styles_mantenimiento.css">
    </head>
    <body onload="formulario.placa.focus()">
        <?php include('../header.php'); ?>

        <div class="container">
            <form action="gestionar_mantenimiento.php" method="post" class="form-llantas" id="formulario">
                <h1>Gestión de Mantenimientos</h1>
                <p class="instructions">Selecciona el vehículo y completa los detalles del mantenimiento, incluyendo fechas, kilometraje y trabajos realizados. Luego, presiona "Registrar Mantenimiento" para guardar la información.</p>
                

                <div class="input-gruop">
                    <!-- Primer grupo de 3 campos -->
                    <div class="input-subgroup">
                        <div class="input-box">
                            <label for="placa">Vehículo:*</label>
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
                            <p class="validacion_placa" id="validacion_placa">Seleccione un vehículo.</p>
                        </div>

                        <div class="input-box">
                            <label for="id_tipo_mantenimiento">Tipo de Mantenimiento:*</label>
                            <div class="input_field_id_tipo_mantenimiento" id="grupo_id_tipo_mantenimiento">
                                <select name="id_tipo_mantenimiento" id="id_tipo_mantenimiento" required>
                                    <option value="">Seleccionar Tipo</option>
                                    <?php foreach ($tipos_mantenimiento as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo['id_tipo_mantenimiento']); ?>" <?php echo (isset($_POST['id_tipo_mantenimiento']) && $_POST['id_tipo_mantenimiento'] === $tipo['id_tipo_mantenimiento']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['descripcion']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="bi bi-wrench"></i>
                            </div>
                            <p class="validacion_id_tipo_mantenimiento" id="validacion_id_tipo_mantenimiento">Seleccione un tipo de mantenimiento.</p>
                        </div>

                        <div class="input-box">
                            <label for="fecha_programada">Fecha Programada:* </label>
                            <div class="input_field_fecha_programada" id="grupo_fecha_programada">
                                <input type="date" name="fecha_programada" id="fecha_programada" required value="<?php echo htmlspecialchars($_POST['fecha_programada'] ?? ''); ?>">
                                <i class="bi bi-calendar"></i>
                            </div>
                            <p class="validacion_fecha_programada" id="validacion_fecha_programada">Seleccione una fecha válida.</p>
                        </div>
                    </div>

                    <!-- Segundo grupo de 3 campos -->
                    <div class="input-subgroup">
                        <div class="input-box">
                            <label for="fecha_realizada">Fecha Realizada (opcional):</label>
                            <div class="input_field_fecha_realizada" id="grupo_fecha_realizada">
                                <input type="date" name="fecha_realizada" id="fecha_realizada" value="<?php echo htmlspecialchars($_POST['fecha_realizada'] ?? ''); ?>">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <p class="validacion_fecha_realizada" id="validacion_fecha_realizada">Fecha no puede ser futura.</p>
                        </div>

                        <div class="input-box">
                            <label for="kilometraje_actual">Kilometraje Actual:*</label>
                            <div class="input_field_kilometraje_actual" id="grupo_kilometraje_actual">
                                <input type="number" name="kilometraje_actual" id="kilometraje_actual" value="<?php echo htmlspecialchars($_POST['kilometraje_actual'] ?? ''); ?>">
                                <i class="bi bi-speedometer"></i>
                            </div>
                            <p class="validacion_kilometraje_actual" id="validacion_kilometraje_actual">Ingrese un número positivo.</p>
                        </div>

                        <div class="input-box">
                            <label for="proximo_cambio_km">Próximo Mantenimiento (km):*</label>
                            <div class="input_field_proximo_cambio_km" id="grupo_proximo_cambio_km">
                                <input type="number" name="proximo_cambio_km" id="proximo_cambio_km" value="<?php echo htmlspecialchars($_POST['proximo_cambio_km'] ?? ''); ?>">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <p class="validacion_proximo_cambio_km" id="validacion_proximo_cambio_km">Ingrese un número positivo.</p>
                        </div>
                    </div>

                    <!-- Tercer grupo de 2 campos -->
                    <div class="input-subgroup">
                        <div class="input-box">
                            <label for="proximo_cambio_fecha">Próximo Mantenimiento (Fecha):*</label>
                            <div class="input_field_proximo_cambio_fecha" id="grupo_proximo_cambio_fecha">
                                <input type="date" name="proximo_cambio_fecha" id="proximo_cambio_fecha" value="<?php echo htmlspecialchars($_POST['proximo_cambio_fecha'] ?? ''); ?>">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <p class="validacion_proximo_cambio_fecha" id="validacion_proximo_cambio_fecha">Fecha no puede ser pasada.</p>
                        </div>

                        <div class="input-box" style="flex: 1 1 65%;">
                            <label for="observaciones">Observaciones:*</label>
                            <div class="input_field_observaciones" id="grupo_observaciones">
                                <textarea name="observaciones" id="observaciones" rows="4"><?php echo htmlspecialchars($_POST['observaciones'] ?? ''); ?></textarea>
                                <i class="bi bi-pencil"></i>
                            </div>
                            <p class="validacion_observaciones" id="validacion_observaciones">Máximo 500 caracteres, solo letras, números y puntuación básica.</p>
                        </div>
                    </div>
                
                </div>

                <!-- Error general -->
                <div>
                    <p class="formulario_error" id="formulario_error"><b>Error:</b> Por favor complete todos los campos correctamente.</p>
                </div>

                <div class="btn-field">
                    <button type="submit" class="btn btn-primary">Registrar Mantenimiento</button>
                </div>

                <!-- Mensaje de éxito -->
                <p class="formulario_exito" id="formulario_exito">Mantenimiento registrado correctamente.</p>

            </form>
        </div>

        <script src="../js/scriptmantenimiento.js"></script>

         <?php
            include('../../../includes/auto_logout_modal.php');
        ?>
    </body>
    </html>