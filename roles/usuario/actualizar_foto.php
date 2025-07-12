<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';
$db = new Database();
$con = $db->conectar();

// Check for documento in session
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    $_SESSION['error'] = "Por favor, inicia sesión para continuar.";
    header('Location: ../../login/login');
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Restablecer la imagen de perfil a la predeterminada
    if (isset($_POST['reset_image'])) {
        $default_image = '/roles/usuario/css/img/perfil.jpg';
        $reset_query = $con->prepare("UPDATE usuarios SET foto_perfil = :foto_perfil WHERE documento = :documento");
        $reset_query->bindParam(':foto_perfil', $default_image, PDO::PARAM_STR);
        $reset_query->bindParam(':documento', $documento, PDO::PARAM_STR);

        if ($reset_query->execute()) {
            $_SESSION['foto_perfil'] = $default_image;
            $_SESSION['success'] = "Imagen de perfil restablecida a la predeterminada.";
        } else {
            $_SESSION['error'] = "Error al restablecer la imagen de perfil.";
        }
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        header("Location: $redirect");
        exit;
    }

    // Subir una nueva imagen de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['foto_perfil'];
        // Use absolute path for reliability, save directly to css/img/
        $upload_dir = __DIR__ . '/css/img/';
        $relative_dir = '/css/img/';

        // Ensure directory exists and is writable
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                error_log("Failed to create upload directory: $upload_dir");
                $_SESSION['error'] = "No se pudo crear el directorio de carga.";
                $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
                header("Location: $redirect");
                exit;
            }
        }
        if (!is_writable($upload_dir)) {
            error_log("Upload directory not writable: $upload_dir");
            $_SESSION['error'] = "El directorio de carga no tiene permisos de escritura.";
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: $redirect");
            exit;
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => "El archivo excede el tamaño máximo permitido por el servidor (5MB).",
                UPLOAD_ERR_FORM_SIZE => "El archivo excede el tamaño máximo del formulario.",
                UPLOAD_ERR_PARTIAL => "El archivo se cargó parcialmente.",
                UPLOAD_ERR_NO_FILE => "No se seleccionó ningún archivo.",
                UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal del servidor.",
                UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco.",
                UPLOAD_ERR_EXTENSION => "Una extensión PHP detuvo la carga."
            ];
            $_SESSION['error'] = $errors[$file['error']] ?? "Error desconocido al cargar el archivo.";
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: $redirect");
            exit;
        }

        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error'] = "Solo se permiten archivos JPEG, PNG o GIF.";
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: $redirect");
            exit;
        }

        if ($file['size'] > $max_size) {
            $_SESSION['error'] = "El archivo no debe superar los 5MB.";
            $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
            header("Location: $redirect");
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $documento . '_' . time() . '.' . $ext;
        $destination = $upload_dir . $filename;
        $relative_path = '/roles/usuario/css/img/' . $filename;


        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Verify file exists
            if (!file_exists($destination)) {
                error_log("File not found after move: $destination");
                $_SESSION['error'] = "El archivo se movió pero no se encuentra en la ubicación esperada.";
                $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
                header("Location: $redirect");
                exit;
            }

            error_log("File successfully uploaded to: $destination");
            // Update database
            $query = $con->prepare("UPDATE usuarios SET foto_perfil = :foto_perfil WHERE documento = :documento");
            $query->bindParam(':foto_perfil', $relative_path, PDO::PARAM_STR);
            $query->bindParam(':documento', $documento, PDO::PARAM_STR);
            if ($query->execute()) {
                error_log("Database updated with new photo path: $relative_path");
                $_SESSION['foto_perfil'] = $relative_path;
                $_SESSION['success'] = "Foto de perfil actualizada correctamente.";
            } else {
                error_log("Failed to update database with new photo path: $relative_path");
                $_SESSION['error'] = "Error al actualizar la foto en la base de datos.";
            }
        } else {
            error_log("Failed to move uploaded file to: $destination");
            $_SESSION['error'] = "Error al mover el archivo. Verifica los permisos del directorio o el espacio en disco.";
        }
    } else {
        $_SESSION['error'] = "No se recibió ningún archivo válido.";
    }
}

    include('../../includes/auto_logout_modal.php');

$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $redirect");
exit;
?>