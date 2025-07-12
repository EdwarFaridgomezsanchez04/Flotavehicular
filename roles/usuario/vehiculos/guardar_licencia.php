<?php
session_start();
require_once('../../../conecct/conex.php');
require_once('../../../includes/validarsession.php');
include('../../../includes/auto_logout_modal.php');
$db = new Database();
$con = $db->conectar();

$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    $_SESSION['errors'] = ["Sesión no válida."];
    header('Location: ../../login/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['errors'] = ["Método no válido."];
    header('Location: registrar_licencia.php');
    exit;
}

$categoria = trim($_POST['categoria'] ?? '');
$fecha_expedicion = trim($_POST['fecha_expedicion'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$fecha_nacimiento = trim($_POST['fecha_nacimiento'] ?? '');

$errors = [];

if (!is_numeric($categoria) || !$categoria) {
    $errors[] = "Debe seleccionar una categoría válida.";
}
if (!$fecha_expedicion) {
    $errors[] = "La fecha de expedición es requerida.";
} elseif (strtotime($fecha_expedicion) > time()) {
    $errors[] = "La fecha de expedición no puede ser futura.";
}
if (strlen($observaciones) > 500) {
    $errors[] = "Las observaciones no pueden exceder 500 caracteres.";
}
if (!$fecha_nacimiento) {
    $errors[] = "La fecha de nacimiento es obligatoria.";
} else {
    $dob = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    $now = new DateTime();
    if (!$dob || $dob->format('Y-m-d') !== $fecha_nacimiento) {
        $errors[] = "Formato de fecha de nacimiento inválido.";
    } elseif ($dob > $now) {
        $errors[] = "La fecha de nacimiento no puede ser futura.";
    } elseif ($now->diff($dob)->y < 16) {
        $errors[] = "Debe tener mínimo 16 años para registrar la licencia.";
    }
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: registrar_licencia.php');
    exit;
}

// Consultar el servicio correspondiente a la categoría
try {
    $stmt_servicio = $con->prepare("SELECT id_servicio FROM categoria_licencia WHERE id_categoria = :categoria");
    $stmt_servicio->bindParam(':categoria', $categoria, PDO::PARAM_INT);
    $stmt_servicio->execute();
    $id_servicio = $stmt_servicio->fetchColumn();

    if (!$id_servicio) {
        $errors[] = "No se encontró un tipo de servicio para la categoría seleccionada.";
    }
} catch (PDOException $e) {
    $errors[] = "Error al consultar el servicio: " . $e->getMessage();
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: registrar_licencia.php');
    exit;
}

// Calcular edad actual del usuario
$edad = $now->diff($dob)->y;

// Obtener vigencia desde tabla vigencia_categoria_servicio
try {
    $stmt_vigencia = $con->prepare("SELECT vigencia_años FROM vigencia_categoria_servicio 
        WHERE id_categoria = :categoria AND id_servicio = :servicio AND :edad >= edad_minima 
        ORDER BY edad_minima DESC LIMIT 1");
    $stmt_vigencia->bindParam(':categoria', $categoria, PDO::PARAM_INT);
    $stmt_vigencia->bindParam(':servicio', $id_servicio, PDO::PARAM_INT);
    $stmt_vigencia->bindParam(':edad', $edad, PDO::PARAM_INT);
    $stmt_vigencia->execute();
    $vigencia_anios = $stmt_vigencia->fetchColumn();

    if (!$vigencia_anios) {
        $errors[] = "No se encontró una vigencia válida para la edad, categoría y servicio seleccionados.";
    }
} catch (PDOException $e) {
    $errors[] = "Error al consultar la vigencia: " . $e->getMessage();
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: registrar_licencia.php');
    exit;
}

// Calcular fecha de vencimiento con la vigencia obtenida
$fecha_exp = new DateTime($fecha_expedicion);
$fecha_exp->modify("+{$vigencia_anios} years");
$fecha_vencimiento = $fecha_exp->format('Y-m-d');

// Actualizar la fecha de nacimiento del usuario si ha cambiado
try {
    $stmt_usuario = $con->prepare("SELECT fecha_nacimiento FROM usuarios WHERE documento = :documento");
    $stmt_usuario->bindParam(':documento', $documento, PDO::PARAM_STR);
    $stmt_usuario->execute();
    $user = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['fecha_nacimiento'] !== $fecha_nacimiento) {
        $update_query = $con->prepare("UPDATE usuarios SET fecha_nacimiento = :fecha_nacimiento WHERE documento = :documento");
        $update_query->bindParam(':fecha_nacimiento', $fecha_nacimiento);
        $update_query->bindParam(':documento', $documento);
        $update_query->execute();
    }
} catch (PDOException $e) {
    $errors[] = "Error al actualizar fecha de nacimiento: " . $e->getMessage();
}

if ($errors) {
    $_SESSION['errors'] = $errors;
    header('Location: registrar_licencia.php');
    exit;
}

// Insertar la licencia
try {
    $insert = $con->prepare("INSERT INTO licencias (id_documento, id_categoria, fecha_expedicion, fecha_vencimiento, id_servicio, observaciones)
        VALUES (:documento, :categoria, :fecha_expedicion, :fecha_vencimiento, :id_servicio, :observaciones)");
    $insert->bindParam(':documento', $documento);
    $insert->bindParam(':categoria', $categoria);
    $insert->bindParam(':fecha_expedicion', $fecha_expedicion);
    $insert->bindParam(':fecha_vencimiento', $fecha_vencimiento);
    $insert->bindParam(':id_servicio', $id_servicio);
    $insert->bindParam(':observaciones', $observaciones);
    $insert->execute();

    $_SESSION['success_message'] = "Licencia registrada exitosamente.";
    header('Location: registrar_licencia.php');
    exit;
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error al registrar la licencia: " . $e->getMessage()];
    header('Location: registrar_licencia.php');
    exit;
}
?>
