<?php
session_start();
require_once('../../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();
include '../../../includes/validarsession.php';

header('Content-Type: application/json');

$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    echo json_encode(['status' => 'error', 'message' => 'No se encontró la sesión del usuario.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Recibir los datos del formulario
    $tipo_vehiculo = $_POST['tipo_vehiculo'] ?? '';
    $id_marca = $_POST['id_marca'] ?? '';
    $placa = $_POST['placa'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $kilometraje = $_POST['kilometraje'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $foto_vehiculo = null;

    // Validar campos vacíos
    if (empty($tipo_vehiculo) || empty($id_marca) || empty($placa) || empty($modelo) || empty($kilometraje) || empty($estado) || empty($fecha)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    // Validar existencia de placa duplicada
    $stmt = $con->prepare("SELECT * FROM vehiculos WHERE placa = ?");
    $stmt->execute([$placa]);
    if ($stmt->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'La placa ya está registrada.']);
        exit;
    }

    // Manejar la imagen del vehículo
    error_log("FILES array: " . print_r($_FILES, true));

    // Check if file input exists and was uploaded
    if (isset($_FILES['foto_vehiculo']) && $_FILES['foto_vehiculo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_error = $_FILES['foto_vehiculo']['error'];
        $file_tmp = $_FILES['foto_vehiculo']['tmp_name'];
        $file_name = $_FILES['foto_vehiculo']['name'];
        $file_size = $_FILES['foto_vehiculo']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

        // Log file details
        error_log("File upload attempt: name=$file_name, size=$file_size, error=$file_error, tmp_name=$file_tmp, ext=$file_ext");

        // Check for upload errors
        if ($file_error !== UPLOAD_ERR_OK) {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE => "El archivo excede el tamaño máximo permitido por el servidor.",
                UPLOAD_ERR_FORM_SIZE => "El archivo excede el tamaño máximo permitido por el formulario.",
                UPLOAD_ERR_PARTIAL => "El archivo se subió parcialmente.",
                UPLOAD_ERR_NO_TMP_DIR => "Falta un directorio temporal.",
                UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco.",
                UPLOAD_ERR_EXTENSION => "Una extensión de PHP detuvo la subida del archivo."
            ];
            $_SESSION['error'] = $upload_errors[$file_error] ?? "Error desconocido al subir el archivo (código: $file_error).";
            error_log("Upload error: " . $_SESSION['error']);
            header('Location: formulario.php');
            exit;
        }

        // Check if temporary file exists
        if (!file_exists($file_tmp) || !is_uploaded_file($file_tmp)) {
            $_SESSION['error'] = "El archivo temporal no existe o no es un archivo subido válido.";
            error_log("Invalid temporary file: $file_tmp");
            header('Location: formulario.php');
            exit;
        }

        // Validate file extension
        if (!in_array($file_ext, $allowed_exts)) {
            $_SESSION['error'] = "Formato de imagen no permitido. Use JPG, JPEG, PNG o GIF.";
            error_log("Invalid file extension: $file_ext");
            header('Location: formulario.php');
            exit;
        }

        // Generate unique file name
        $new_file_name = uniqid('vehiculo_') . '.' . $file_ext;
        $upload_dir = 'vehiculos/listar/guardar_foto_vehiculo/';
        $upload_path = $upload_dir . $new_file_name;

        // Resolve absolute path for logging
        $absolute_upload_path = realpath(__DIR__ . '/../../../') . '/' . $upload_dir . $new_file_name;
        error_log("Attempting to save file to: $absolute_upload_path");

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                $_SESSION['error'] = "No se pudo crear el directorio de subida: $upload_dir";
                error_log("Failed to create directory: $upload_dir");
                header('Location: formulario.php');
                exit;
            }
            error_log("Created directory: $upload_dir");
        }

        // Check directory permissions
        if (!is_writable($upload_dir)) {
            $_SESSION['error'] = "El directorio de subida no tiene permisos de escritura: $upload_dir";
            error_log("Directory not writable: $upload_dir");
            header('Location: formulario.php');
            exit;
        }

        // Move the uploaded file
        if (move_uploaded_file($file_tmp, $upload_path)) {
            $foto_vehiculo = '../vehiculos/listar/guardar_foto_vehiculo/' . $new_file_name;
            error_log("File successfully uploaded to: $absolute_upload_path, stored as: $foto_vehiculo");
        } else {
            $_SESSION['error'] = "Error al mover la imagen al servidor.";
            error_log("Failed to move file from $file_tmp to $absolute_upload_path");
            header('Location: formulario.php');
            exit;
        }
    } else {
        error_log("No file uploaded or file input was empty for foto_vehiculo.");
        $_SESSION['error'] = "No se seleccionó ninguna imagen.";
    }

    // If no image was uploaded or upload failed, use the default image
    if (!$foto_vehiculo) {
        $foto_vehiculo = '../vehiculos/listar/guardar_foto_vehiculo/sin_foto_carro.png';
        error_log("Using default image: $foto_vehiculo");
    }

    // Validar formato de la placa según el tipo de vehículo
    $sql = $con-> prepare("SELECT * FROM tipo_vehiculo");
    $res = $sql->execute();

    $placa = strtoupper($placa); // Convertir a mayúsculas para uniformidad

    if ($tipo_vehiculo == 2 && !preg_match('/^[A-Z]{3}[0-9]{2}[A-Z]{1}$/', $placa)) {
        echo json_encode(['status' => 'error', 'message' => 'Para Motocicleta, la placa debe tener 4 letras y 2 números. Ej: ABC12D']);
        exit;
    }

    if ($tipo_vehiculo == 1 && $tipo_vehiculo <= 3 && !preg_match('/^[A-Z]{3}[0-9]{3}$/', $placa)) {
        echo json_encode(['status' => 'error', 'message' => 'Para los vehiculos diferente a Motocicleta , la placa debe tener 3 letras y 3 números. Ej: ABC123']);
        exit;
    }


    // Insertar datos en la tabla de vehículos
    $sql = "INSERT INTO vehiculos (tipo_vehiculo, id_marca, placa, modelo, kilometraje_actual, id_estado, fecha_registro, foto_vehiculo, Documento)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $resultado = $stmt->execute([
        $tipo_vehiculo,
        $id_marca,
        strtoupper($placa),
        $modelo,
        $kilometraje,
        $estado,
        $fecha,
        $foto_vehiculo,
        $documento
    ]);

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Vehículo registrado exitosamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al registrar el vehículo.']);
    }
}
?>
