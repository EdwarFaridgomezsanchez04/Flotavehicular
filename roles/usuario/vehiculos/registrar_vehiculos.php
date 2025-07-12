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

// Fetch user's full name and foto_perfil for the profile section
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?? '/roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Fetch vehicle types from the tipo_vehiculo table using PDO
$query_tipos = "SELECT id_tipo_vehiculo, vehiculo FROM tipo_vehiculo";
$stmt_tipos = $con->prepare($query_tipos);
$stmt_tipos->execute();
$result_tipos = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);

// Fetch states from the estado_vehiculo table using PDO
$query_estados = "SELECT id_estado, estado FROM estado_vehiculo";
$stmt_estados = $con->prepare($query_estados);
$stmt_estados->execute();
$result_estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Vehiculos - Flotax AGC</title>
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../css/styles_registro_general.css">
</head>
<body onload="form_vehiculo.tipo_vehiculo.focus()">
    <?php
        include('../header.php')
    ?>

    <div class="contenido">
        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data" class="form" id="form_vehiculo" autocomplete="off">
                <h2>Registrar Vehículo</h2>
                <div class="input-group">

                    <!-- Tipo de Vehículo -->
                    <div>
                        <div class="input_field_tipo" id="grupo_tipo">
                            <label for="tipo_vehiculo">Tipo de vehiculo:*</label>
                            <i class="bi bi-truck"></i>
                            <select id="tipo_vehiculo" name="tipo_vehiculo" >
                                <option value="">Tipo de Vehículo</option>
                                <?php foreach ($result_tipos as $row) { ?>
                                    <option value="<?php echo htmlspecialchars($row['id_tipo_vehiculo']); ?>">
                                        <?php echo htmlspecialchars($row['vehiculo']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="formulario_error_tipo" id="formulario_correcto_tipo">
                            <p class="validacion" id="validacion">Seleccione un tipo de vehículo válido.</p>
                        </div>
                    </div>

                    <!-- Marca -->
                    <div>
                        <div class="input_field_marca" id="grupo_marca">
                            <label for="id_marca">Marca del vehiculo:*</label>
                            <i class="bi bi-tags"></i>
                            <select name="id_marca" id="id_marca" >
                                <option value="">Seleccione una marca</option>
                            </select>
                        </div>
                        <div class="formulario_error_marca" id="formulario_correcto_marca">
                            <p class="validacion" id="validacion1">Seleccione una marca válida.</p>
                        </div>
                    </div>

                    <!-- Placa -->
                    <div>
                        <div class="input_field_placa" id="grupo_placa">
                            <label for="placa">Placa del vehiculo:*</label>
                            <i class="bi bi-car-front"></i>
                            <input type="text" name="placa" id="placa" placeholder="Placa del vehículo" >
                        </div>
                        <div class="formulario_error_placa" id="formulario_correcto_placa">
                            <p class="validacion" id="validacion2">Ingrese una placa válida (ej: ABC123).</p>
                        </div>
                    </div>

                    <!-- Modelo -->
                    <div>
                        <div class="input_field_modelo" id="grupo_modelo">
                            <label for="modelo">Modelo del vehiculo:*</label>
                            <i class="bi bi-calendar-range"></i>
                            <input type="number" name="modelo" id="modelo" placeholder="Modelo" >
                        </div>
                        <div class="formulario_error_modelo" id="formulario_correcto_modelo">
                            <p class="validacion" id="validacion3">Ingrese un año valido.</p>
                        </div>
                    </div>

                    <!-- Kilometraje -->
                    <div>
                        <div class="input_field_km" id="grupo_km">
                            <label for="kilometraje">Kilometraje del vehiculo:*</label>
                            <i class="bi bi-speedometer2"></i>
                            <input type="number" name="kilometraje" id="kilometraje" placeholder="Kilometraje actual" >
                        </div>
                        <div class="formulario_error_km" id="formulario_correcto_km">
                            <p class="validacion" id="validacion4">Ingrese un kilometraje válido.</p>
                        </div>
                    </div>

                    <!-- Estado -->
                    <div>
                        <div class="input_field_estado" id="grupo_estado">
                            <label for="estado">Estado del vehiculo:*</label>
                            <i class="bi bi-clipboard-check"></i>
                            <select name="estado" id="estado">
                                <option value="">Seleccione estado</option>
                                <?php foreach ($result_estados as $row) { ?>
                                    <option value="<?php echo htmlspecialchars($row['id_estado']); ?>">
                                        <?php echo htmlspecialchars($row['estado']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="formulario_error_estado" id="formulario_correcto_estado">
                            <p class="validacion" id="validacion5">Seleccione un estado válido.</p>
                        </div>
                    </div>

                    <!-- Fecha -->
                    <div>
                        <div class="input_field_fecha" id="grupo_fecha">
                            <label for="fecha">Fecha registro:*</label>
                            <i class="bi bi-calendar-event"></i>
                            <input type="date" name="fecha" id="fecha" placeholder="Fecha registro" readonly>
                        </div>
                        <div class="formulario_error_fecha" id="formulario_correcto_fecha">
                            <p class="validacion" id="validacion6">Seleccione una fecha válida.</p>
                        </div>
                    </div>

                    <!-- Foto -->
                    <div>
                        <div class="input_field_foto" id="grupo_foto">
                            <label for="foto_vehiculo">Foto del Vehiculo:(Opcional)</label>
                            <i class="bi bi-camera"></i>
                            <input type="file" name="foto_vehiculo" id="foto_vehiculo" accept="image/*">
                        </div>
                        <div class="formulario_error_foto" id="formulario_correcto_foto">
                            <p class="validacion" id="validacion7">Solo se permiten imágenes (JPG, PNG).</p>
                        </div>
                    </div>

                    
                </div>

                <!-- Mensaje general de error -->
                <div>
                    <p class="formulario_error" id="formulario_error"><b>Error:</b> Por favor rellena el formulario correctamente.</p>
                </div>

                <!-- Botón -->
                <div class="btn-field">
                    <button type="submit" class="btn btn-success">Guardar Vehículo</button>
                </div>

                <!-- Mensaje de éxito -->
                <p class="formulario_exito" id="formulario_exito">Vehículo registrado correctamente.</p>
            </form>
        </div>
    </div>

    

    <script>
        document.getElementById('tipo_vehiculo').addEventListener('change', function() {
            const id_tipo = this.value;
            const marcas = document.getElementById('id_marca');

            if (id_tipo) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../AJAX/obtener_marcas.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status === 200) {
                        marcas.innerHTML = this.responseText;
                    } else {
                        marcas.innerHTML = '<option value="">Error al cargar marcas</option>';
                    }
                };
                xhr.onerror = function() {
                    marcas.innerHTML = '<option value="">Error al cargar marcas</option>';
                };
                xhr.send('id_tipo=' + encodeURIComponent(id_tipo));
            } else {
                marcas.innerHTML = '<option value="">Seleccione un tipo primero</option>';
            }
        });
    </script>

    <script src="../js/vehiculos_registro.js"></script>
    <?php
      include('../../../includes/auto_logout_modal.php');
    ?>
</body>
</html>