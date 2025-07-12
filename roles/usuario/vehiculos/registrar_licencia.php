<?php
session_start();
require_once('../../../conecct/conex.php');
require_once('../../../includes/validarsession.php');
include('../../../includes/auto_logout_modal.php');
$db = new Database();
$con = $db->conectar();

$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login/login.php');
    exit;
}

// Obtener categorías
$sql_categoria = $con->prepare("SELECT id_categoria, nombre_categoria FROM categoria_licencia");
$sql_categoria->execute();
$categorias = $sql_categoria->fetchAll(PDO::FETCH_ASSOC);

// Datos del usuario
$user_query = $con->prepare("SELECT nombre_completo, foto_perfil, fecha_nacimiento FROM usuarios WHERE documento = :documento");
$user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
$user_query->execute();
$user = $user_query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header('Location: ../../login/login.php');
    exit;
}

$nombre_completo = $user['nombre_completo'] ?? 'Usuario';
$foto_perfil = $user['foto_perfil'] ?: '/Flotavehicular/roles/usuario/css/img/perfil.jpg';
$fecha_nacimiento = $user['fecha_nacimiento'] ?? '';
$_SESSION['nombre_completo'] = $nombre_completo;
$_SESSION['foto_perfil'] = $foto_perfil;

$errors = $_SESSION['errors'] ?? [];
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['errors']);
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flotax AGC - Registrar Licencia</title>
    <link rel="stylesheet" href="../css/styles_licencia.css">
    <link rel="shortcut icon" href="../../../css/img/logo_sinfondo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include('../header.php'); ?>

    <div class="container">
        <form action="guardar_licencia.php" method="post" class="license-form" id="license-form">
            <h1>Registrar Licencia</h1>
            <p class="instructions">Completa los detalles de la licencia.</p>
            
            <?php if (!empty($errors)): ?>
                <div class="form-error active">
                    <b>Error:</b>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <p class="form-success active"><?php echo htmlspecialchars($success_message); ?></p>
            <?php endif; ?>

            <div class="form-group">
                <div class="form-field full-width">
                    <label for="documento">Documento:</label>
                    <input type="text" name="documento" id="documento" value="<?php echo htmlspecialchars($documento); ?>" readonly>
                </div>
                <div class="form-field">
                    <label for="categoria">Categoría*:</label>
                    <select name="categoria" id="categoria" required>
                        <option value="">Seleccionar Categoría</option>
                        <?php foreach ($categorias as $row): ?>
                            <option value="<?php echo htmlspecialchars($row['id_categoria']); ?>">
                                <?php echo htmlspecialchars($row['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-text" id="error-categoria"></span>
                </div>
                <div class="form-field">
                    <label for="fecha-nacimiento">Fecha de Nacimiento*:</label>
                    <input type="date" name="fecha_nacimiento" id="fecha-nacimiento" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($fecha_nacimiento); ?>">
                    <span class="error-text" id="error-fecha-nacimiento"></span>
                </div>
                <div class="form-field">
                    <label for="fecha-expedicion">Fecha de Expedición*:</label>
                    <input type="date" name="fecha_expedicion" id="fecha-expedicion" required max="<?php echo date('Y-m-d'); ?>">
                    <span class="error-text" id="error-fecha-expedicion"></span>
                </div>

                <!-- Aquí mostramos la vigencia calculada -->
                <div class="form-field full-width">
                    <label>Vigencia Calculada:</label>
                    <div id="vigencia-resultado" style="font-weight: bold; color: green;">Seleccione categoría y fecha de nacimiento</div>
                </div>

                <div class="form-field full-width">
                    <label for="observaciones">Observaciones:</label>
                    <textarea name="observaciones" id="observaciones" rows="4" maxlength="500"></textarea>
                    <span class="error-text" id="error-observaciones"></span>
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn btn-primary">Registrar Licencia</button>
            </div>
        </form>
    </div>

    <script src="../js/scriptlicencia.js"></script>
</body>
</html>
