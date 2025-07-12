<?php
// Inicia una sesión de PHP
session_start();

// Incluye el archivo de conexión a la base de datos
require_once '../../../../conecct/conex.php';

// Incluye el archivo para validar la sesión del usuario
include '../../../../includes/validarsession.php';

// Habilita la visualización de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Instancia la clase Database y obtiene la conexión PDO
$database = new Database();
$con = $database->conectar();

// Verifica si la conexión fue exitosa
if (!$con) {
    die("Error: No se pudo conectar a la base de datos.");
}

// Obtiene el documento desde la sesión, si no existe redirige al login
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Obtiene el nombre completo y foto de perfil desde la sesión si están disponibles
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;

// Si no están en la sesión, se consultan en la base de datos
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ? $user['foto_perfil'] . '?v=' . time() : 'css/img/default.jpg';

    // Guarda los datos en la sesión
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

// Obtiene la placa del vehículo desde GET
$placa = $_GET['vehiculo'] ?? '';

// Inicializa variables
$vehicle_data = null;
$error = '';
$success_message = '';
$upload_error = '';
$reset_message = '';

// Si se envió un formulario por POST con imagen nueva y hay una placa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['new_foto_vehiculo']) && $placa) {
    $foto_vehiculo = null;

    // Log para depuración
    error_log("Image update submitted for placa: $placa");

    // Si se cargó una imagen
    if ($_FILES['new_foto_vehiculo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_error = $_FILES['new_foto_vehiculo']['error'];
        $file_tmp = $_FILES['new_foto_vehiculo']['tmp_name'];
        $file_name = $_FILES['new_foto_vehiculo']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        // Log del intento de carga
        error_log("File upload attempt: name=$file_name, error=$file_error, tmp_name=$file_tmp");

        // Verifica errores de subida
        if ($file_error !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => "El archivo excede el tamaño máximo permitido por el servidor.",
                UPLOAD_ERR_FORM_SIZE => "El archivo excede el tamaño máximo permitido por el formulario.",
                UPLOAD_ERR_PARTIAL => "El archivo se subió parcialmente.",
                UPLOAD_ERR_NO_TMP_DIR => "Falta un directorio temporal.",
                UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco.",
                UPLOAD_ERR_EXTENSION => "Una extensión de PHP detuvo la subida del archivo."
            ];
            $upload_error = $upload_errors[$file_error] ?? "Error desconocido al subir el archivo.";
            error_log("Upload error: $upload_error");
        } else {
            // Valida la extensión del archivo
            if (in_array($file_ext, $allowed_exts)) {
                $new_file_name = uniqid('vehiculo_') . '.' . $file_ext;
                $upload_dir = 'guardar_foto_vehiculo/';
                $upload_path = $upload_dir . $new_file_name;

                // Verifica que exista el directorio
                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0755, true)) {
                        $upload_error = "No se pudo crear el directorio de subida.";
                        error_log("Failed to create directory: $upload_dir");
                    }
                }

                // Verifica permisos de escritura
                if (!$upload_error && !is_writable($upload_dir)) {
                    $upload_error = "El directorio de subida no tiene permisos de escritura.";
                    error_log("Directory not writable: $upload_dir");
                }

                // Mueve la imagen al servidor
                if (!$upload_error && move_uploaded_file($file_tmp, $upload_path)) {
                    $foto_vehiculo = 'vehiculos/listar/guardar_foto_vehiculo/' . $new_file_name;
                    error_log("File successfully uploaded to: $upload_path");

                    // Obtiene la imagen actual
                    $current_image_query = $con->prepare("SELECT foto_vehiculo FROM vehiculos WHERE placa = :placa AND Documento = :documento");
                    $current_image_query->bindParam(':placa', $placa, PDO::PARAM_STR);
                    $current_image_query->bindParam(':documento', $documento, PDO::PARAM_STR);
                    $current_image_query->execute();
                    $current_image = $current_image_query->fetchColumn();

                    // Elimina la imagen anterior si no es la predeterminada
                    if ($current_image && file_exists($current_image) && $current_image !== 'vehiculos/listar/guardar_foto_vehiculo/sin_foto_carro.png') {
                        if (unlink($current_image)) {
                            error_log("Old image deleted: $current_image");
                        } else {
                            error_log("Failed to delete old image: $current_image");
                        }
                    }

                    // Actualiza la base de datos con la nueva imagen
                    $update_query = $con->prepare("UPDATE vehiculos SET foto_vehiculo = :foto_vehiculo WHERE placa = :placa AND Documento = :documento");
                    $update_query->bindParam(':foto_vehiculo', $foto_vehiculo, PDO::PARAM_STR);
                    $update_query->bindParam(':placa', $placa, PDO::PARAM_STR);
                    $update_query->bindParam(':documento', $documento, PDO::PARAM_STR);

                    // Ejecuta la actualización
                    if ($update_query->execute()) {
                        $success_message = "Imagen del vehículo actualizada exitosamente.";
                        error_log("Database updated with new image: $foto_vehiculo");
                    } else {
                        $upload_error = "Error al actualizar la imagen en la base de datos.";
                        error_log("Database update failed: " . print_r($con->errorInfo(), true));
                        unlink($upload_path); // Elimina la nueva imagen si falla la actualización
                    }
                } else {
                    $upload_error = "Error al mover la imagen al servidor.";
                    error_log("Failed to move file to: $upload_path");
                }
            } else {
                $upload_error = "Formato de imagen no permitido. Use JPG, JPEG, PNG o GIF.";
                error_log("Invalid file extension: $file_ext");
            }
        }
    } else {
        $upload_error = "No se seleccionó ninguna imagen.";
        error_log("No file selected for update.");
    }
}

// Si se envió un formulario para restablecer imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_image']) && $placa) {
    // Obtiene la imagen actual
    $current_image_query = $con->prepare("SELECT foto_vehiculo FROM vehiculos WHERE placa = :placa AND Documento = :documento");
    $current_image_query->bindParam(':placa', $placa, PDO::PARAM_STR);
    $current_image_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $current_image_query->execute();
    $current_image = $current_image_query->fetchColumn();

    // Elimina la imagen actual si no es la predeterminada
    if ($current_image && file_exists($current_image) && $current_image !== 'vehiculos/listar/guardar_foto_vehiculo/sin_foto_carro.png') {
        if (unlink($current_image)) {
            error_log("Old image deleted: $current_image");
        } else {
            error_log("Failed to delete old image: $current_image");
        }
    }

    // Define la imagen por defecto
    $default_image = 'vehiculos/listar/guardar_foto_vehiculo/sin_foto_carro.png';

    // Actualiza la base de datos con la imagen por defecto
    $reset_query = $con->prepare("UPDATE vehiculos SET foto_vehiculo = :foto_vehiculo WHERE placa = :placa AND Documento = :documento");
    $reset_query->bindParam(':foto_vehiculo', $default_image, PDO::PARAM_STR);
    $reset_query->bindParam(':placa', $placa, PDO::PARAM_STR);
    $reset_query->bindParam(':documento', $documento, PDO::PARAM_STR);

    // Ejecuta la actualización
    if ($reset_query->execute()) {
        $reset_message = "Imagen del vehículo restablecida a la predeterminada.";
        error_log("Database updated to: $default_image");
    } else {
        $upload_error = "Error al restablecer la imagen en la base de datos.";
        error_log("Database update failed: " . print_r($con->errorInfo(), true));
    }
}

// Obtiene los detalles del vehículo
if ($placa) {
    $query = "
        SELECT 
            v.placa, v.modelo, v.kilometraje_actual, v.fecha_registro, v.foto_vehiculo,
            tv.vehiculo AS tipo_vehiculo, m.nombre_marca AS marca, ev.estado
        FROM vehiculos v
        INNER JOIN marca m ON v.id_marca = m.id_marca
        INNER JOIN tipo_vehiculo tv ON m.id_tipo_vehiculo = tv.id_tipo_vehiculo
        INNER JOIN estado_vehiculo ev ON v.id_estado = ev.id_estado
        WHERE v.placa = :placa AND v.Documento = :documento
    ";
    $stmt = $con->prepare($query);
    $stmt->bindParam(':placa', $placa, PDO::PARAM_STR);
    $stmt->bindParam(':documento', $documento, PDO::PARAM_STR);
    $stmt->execute();
    $vehicle_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra el vehículo
    if (!$vehicle_data) {
        $error = "No se encontró el vehículo con la placa seleccionada.";
        error_log("Vehicle not found for placa: $placa, documento: $documento");
    }
} else {
    // No se seleccionó ningún vehículo
    $error = "Por favor, seleccione un vehículo.";
    error_log("No vehicle selected.");
}


// Obtiene nombre completo y foto de perfil si no están en sesión
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: '/roles/usuario/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flotax AGC - Detalles del Vehículo</title>
    <link rel="shortcut icon" href="../css/img/logo_sinfondo.png">
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php
        include('../../header.php')
    ?>

    <div class="container">
        <h2>Detalles del Vehículo</h2>
        <?php if ($error): ?>
            <div class="notification">
                <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php elseif ($vehicle_data): ?>
            <?php if ($success_message): ?>
                <div class="notification">
                    <div class="alert success"><?php echo htmlspecialchars($success_message); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($upload_error): ?>
                <div class="notification">
                    <div class="alert error"><?php echo htmlspecialchars($upload_error); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($reset_message): ?>
                <div class="notification">
                    <div class="alert success"><?php echo htmlspecialchars($reset_message); ?></div>
                </div>
            <?php endif; ?>
            <div class="vehicle-details">
                <div class="vehicle-image">
                    <?php
                    // Construir la ruta relativa para la imagen
                    $relativeImagePath = $vehicle_data['foto_vehiculo'] 
                        ? 'guardar_foto_vehiculo/' . basename($vehicle_data['foto_vehiculo']) 
                        : 'guardar_foto_vehiculo/sin_foto_carro.png';
                    
                    // Construir la ruta absoluta para verificar existencia
                    $absoluteImagePath = realpath(__DIR__ . '/' . $relativeImagePath);
                    
                    // Ruta para la etiqueta <img> (relativa desde listar.php)
                    $imagePath = htmlspecialchars($relativeImagePath) . '?v=' . time();

                    // Verificar si el archivo existe
                    if (!$absoluteImagePath || !file_exists($absoluteImagePath)) {
                        error_log("Image file does not exist: $absoluteImagePath (relative: $relativeImagePath)");
                        $imagePath = 'guardar_foto_vehiculo/sin_foto_carro.png?v=' . time();
                        $absoluteImagePath = realpath(__DIR__ . '/guardar_foto_vehiculo/sin_foto_carro.png');
                    }

                    error_log("Generated image path: $imagePath (absolute: $absoluteImagePath)");
                    ?>
                    <img src="<?php echo $imagePath; ?>" alt="Foto del Vehículo" style="max-width: 100%; height: auto;">
                    <form action="listar.php?vehiculo=<?php echo urlencode($placa); ?>" method="post" enctype="multipart/form-data">
                        <label for="new_foto_vehiculo">Cambiar Imagen:</label>
                        <input type="file" id="new_foto_vehiculo" name="new_foto_vehiculo" accept="image/jpeg,image/png,image/gif">
                        <button type="submit" class="boton">Actualizar Imagen</button>
                    </form>
                    <form action="listar.php?vehiculo=<?php echo urlencode($placa); ?>" method="post">
                        <input type="hidden" name="reset_image" value="1">
                        <button type="submit" class="boton">Restablecer Imagen</button>
                    </form>
                </div>
                <div class="vehicle-info">
                    <p><strong>Placa:</strong> <?php echo htmlspecialchars($vehicle_data['placa']); ?></p>
                    <p><strong>Tipo de Vehículo:</strong> <?php echo htmlspecialchars($vehicle_data['tipo_vehiculo']); ?></p>
                    <p><strong>Marca:</strong> <?php echo htmlspecialchars($vehicle_data['marca']); ?></p>
                    <p><strong>Modelo:</strong> <?php echo htmlspecialchars($vehicle_data['modelo']); ?></p>
                    <p><strong>Kilometraje Actual:</strong> <?php echo htmlspecialchars($vehicle_data['kilometraje_actual']); ?> km</p>
                    <p><strong>Estado:</strong> <?php echo htmlspecialchars($vehicle_data['estado']); ?></p>
                    <p><strong>Fecha de Registro:</strong> <?php echo htmlspecialchars($vehicle_data['fecha_registro']); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
      include('../../../../includes/auto_logout_modal.php');
    ?>
</body>
</html>