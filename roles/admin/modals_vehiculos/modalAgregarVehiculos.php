<?php
require_once('../../conecct/conex.php');
$db = new Database();
$con = $db->conectar();

// Obtener marcas únicas
$marcas_query = $con->prepare("SELECT id_marca, nombre_marca FROM marca ORDER BY nombre_marca");
$marcas_query->execute();
$marcas_raw = $marcas_query->fetchAll(PDO::FETCH_ASSOC);

// Eliminar duplicados por nombre
$marcas = [];
$nombres_unicos = [];
foreach ($marcas_raw as $marca) {
    $nombre = strtolower(trim($marca['nombre_marca']));
    if (!in_array($nombre, $nombres_unicos)) {
        $marcas[] = $marca;
        $nombres_unicos[] = $nombre;
    }
}

// Obtener colores 
$color_query = $con->prepare("SELECT id_color, color FROM colores ORDER BY color");
$color_query->execute();
$color_raw = $color_query->fetchAll(PDO::FETCH_ASSOC);

// Eliminar duplicados por nombre
$colores = [];
$nombres_unicos_color = [];
foreach ($color_raw as $color_item) {
    $nombre_color = strtolower(trim($color_item['color']));
    if (!in_array($nombre_color, $nombres_unicos_color)) {
        $colores[] = $color_item;
        $nombres_unicos_color[] = $nombre_color;
    }
}

$estados = $con->query("SELECT id_estado, estado FROM estado_vehiculo ORDER BY estado")->fetchAll(PDO::FETCH_ASSOC);

$usuarios = $con->query("
  SELECT u.documento, u.nombre_completo, u.id_rol 
  FROM usuarios u
  WHERE u.id_rol = 2
  ORDER BY u.nombre_completo
")->fetchAll(PDO::FETCH_ASSOC);

$tipos_vehiculo = $con->query("SELECT id_tipo_vehiculo, vehiculo FROM tipo_vehiculo ORDER BY vehiculo")->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST['guardarVehiculoModal'])) {
    // Recibir valores
    $tipo_vehiculo = $_POST['tipo_vehiculo'] ?? '';
    $id_marca = $_POST['id_marca'] ?? '';
    $placa = strtoupper(trim($_POST['placa'] ?? ''));
    $anio = trim($_POST['anio'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $id_color = $_POST['id_color'] ?? '';
    $kilometraje_actual = trim($_POST['kilometraje_actual'] ?? '');
    $id_estado = $_POST['id_estado'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $foto_vehiculo = null;
    $fecha_registro = date('Y-m-d H:i:s');
    $registrado_por = $_SESSION['usuario_id'] ?? 1;
    $conteo = 0;

    // Validaciones en PHP con alert y salida inmediata si falla
    if (empty($tipo_vehiculo)) {
        echo "<script>alert('Debe seleccionar un tipo de vehículo.'); window.history.back();</script>";
        $conteo = 0;
        exit;
    }

    if (empty($id_marca)) {
        echo "<script>alert('Debe seleccionar una marca.'); window.history.back();</script>";
        $conteo = 0;
        exit;
    }

    if (!preg_match('/^[A-Z]{3}[0-9]{2,3}$/', $placa)) {
        $conteo = 0;
        echo "<script>alert('Placa inválida. Debe tener 3 letras y 2 o 3 números (ej: ABC12 o ABC123).'); window.location.href='vehiculos.php';</script>";
        exit;
    }
    
    
    $anio_num = (int)$anio;
    $anio_actual = (int)date('Y') + 1;
    if ($anio_num < 1900 || $anio_num > $anio_actual) {
        $conteo = 0;
        echo "<script>alert('El año debe estar entre 1900 y $anio_actual.'); window.history.back();</script>";
        exit;
    }

    if (strlen($modelo) > 50 || strlen($modelo) < 2 || !preg_match('/^[A-Za-z0-9\s\-]+$/', $modelo)) {
        echo "<script>alert('Modelo inválido. Solo se permiten letras, números, espacios o guiones (2 a 50 caracteres).'); window.history.back();</script>";
        exit;
    }

    if (empty($id_color)) {
        echo "<script>alert('Debe seleccionar un color.'); window.history.back();</script>";
        exit;
    }

    if (!is_numeric($kilometraje_actual) || $kilometraje_actual < 0 || $kilometraje_actual > 999999) {
        echo "<script>alert('Kilometraje inválido. Debe estar entre 0 y 999999.'); window.history.back();</script>";
        exit;
    }

    if (empty($id_estado)) {
        echo "<script>alert('Debe seleccionar un estado del vehículo.'); window.history.back();</script>";
        exit;
    }

    if (empty($documento)) {
        echo "<script>alert('Debe seleccionar un propietario.'); window.history.back();</script>";
        exit;
    }

    // Procesar imagen si existe
    if (isset($_FILES['foto_vehiculo']) && $_FILES['foto_vehiculo']['error'] === 0) {
        $foto_temp = $_FILES['foto_vehiculo']['tmp_name'];
        $foto_nombre = $_FILES['foto_vehiculo']['name'];
        $foto_extension = pathinfo($foto_nombre, PATHINFO_EXTENSION);

        $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($foto_extension), $extensiones_permitidas)) {
            echo "<script>alert('Formato de imagen no permitido. Solo JPG, PNG o GIF.'); window.history.back();</script>";
            exit;
        }

        $foto_nuevo_nombre = 'vehiculo_' . $placa . '_' . time() . '.' . $foto_extension;
        $foto_ruta = 'uploads/vehiculos/' . $foto_nuevo_nombre;

        if (!move_uploaded_file($foto_temp, $foto_ruta)) {
            echo "<script>alert('Error al subir la imagen del vehículo.'); window.history.back();</script>";
            exit;
        }

        $foto_vehiculo = $foto_ruta;
    }

    if ($conteo == 1) {


        // Preparar la consulta INSERT
        $sql = "INSERT INTO vehiculos (
        placa, 
        tipo_vehiculo, 
        año, 
        Documento, 
        id_marca, 
        modelo, 
        id_color, 
        kilometraje_actual, 
        id_estado, 
        foto_vehiculo, 
        fecha_registro, 
        registrado_por
    ) VALUES (
        :placa, 
        :tipo_vehiculo, 
        :anio, 
        :documento, 
        :id_marca, 
        :modelo, 
        :id_color, 
        :kilometraje_actual, 
        :id_estado, 
        :foto_vehiculo, 
        :fecha_registro, 
        :registrado_por
    )";

        // Preparar la sentencia
        $stmt = $pdo->prepare($sql);

        // Ejecutar con los parámetros
        $resultado = $stmt->execute([
            ':placa' => $placa,
            ':tipo_vehiculo' => $tipo_vehiculo,
            ':anio' => $anio,
            ':documento' => $documento,
            ':id_marca' => $id_marca,
            ':modelo' => $modelo,
            ':id_color' => $id_color,
            ':kilometraje_actual' => $kilometraje_actual,
            ':id_estado' => $id_estado,
            ':foto_vehiculo' => $foto_vehiculo,
            ':fecha_registro' => $fecha_registro,
            ':registrado_por' => $registrado_por
        ]);

        if ($resultado) {
            $vehiculo_id = $pdo->lastInsertId();

            // Respuesta exitosa
            $response = [
                'success' => true,
                'message' => 'VEHICULO AGREGADO EXITOSAMENTE',
                'vehiculo_id' => $vehiculo_id,
                'placa' => $placa
            ];

            echo json_encode($response);
        } else {
            throw new Exception('Error al insertar el vehículo');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modal Agregar Vehículo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-red: #ff6b6b;
            --light-red: #ffe0e0;
            --dark-red: #e74c3c;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-gray: #dee2e6;
            --text-dark: #343a40;
            --success-green: #28a745;
            --danger-red: #dc3545;
        }

        .modal-content {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background: var(--white);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-red), var(--dark-red));
            color: var(--white);
            border-radius: 15px 15px 0 0;
            padding: 20px;
            border-bottom: none;
        }

        .modal-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .modal-body {
            padding: 30px;
            background: var(--light-red);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 2px solid var(--border-gray);
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-red);
            box-shadow: 0 0 0 0.2rem rgba(255, 107, 107, 0.25);
            background: var(--white);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .validation-message {
            font-size: 0.85rem;
            margin-top: 5px;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: none;
        }

        .validation-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        .validation-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            color: var(--primary-red);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-red), var(--dark-red));
            border: none;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary {
            background: var(--white);
            border: 2px solid var(--border-gray);
            color: var(--text-dark);
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--light-gray);
            border-color: var(--primary-red);
        }

        .modal-footer {
            background: var(--white);
            border-top: 1px solid var(--border-gray);
            padding: 20px 30px;
            border-radius: 0 0 15px 15px;
        }

        .preview-container {
            text-align: center;
            margin-top: 15px;
        }

        .preview-image {
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: none;
        }

        .required-field::after {
            content: '*';
            color: var(--danger-red);
            margin-left: 3px;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid var(--white);
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .form-row {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <!-- MODAL AGREGAR VEHÍCULO -->
    <div class="modal fade" id="modalAgregarVehiculo" tabindex="-1" aria-labelledby="modalAgregarVehiculoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarVehiculoLabel">
                        <i class="bi bi-plus-circle"></i> Agregar Nuevo Vehículo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="formAgregarVehiculo" enctype="multipart/form-data" method="post">
                    <div class="modal-body">

                        <!-- Primera fila: Tipo de Vehículo y Marca -->
                        <div class="form-row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tipoVehiculoAgregar" class="form-label required-field">Tipo de Vehículo</label>
                                        <select class="form-select" id="tipoVehiculoAgregar" name="tipo_vehiculo" required>
                                            <option value="">Seleccionar tipo...</option>
                                            <?php foreach ($tipos_vehiculo as $tipo): ?>
                                                <option value="<?= $tipo['id_tipo_vehiculo'] ?>"><?= htmlspecialchars($tipo['vehiculo']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="validation-message" id="error-tipoVehiculo">
                                            <i class="bi bi-exclamation-triangle"></i> Seleccione un tipo de vehículo
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="marcaAgregar" class="form-label required-field">Marca</label>
                                        <select class="form-select" id="marcaAgregar" name="id_marca" required>
                                            <?php foreach ($marcas as $marca): ?>
                                                <option value="<?= $marca['id_marca'] ?>"><?= htmlspecialchars($marca['nombre_marca']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="validation-message" id="error-marca">
                                            <i class="bi bi-exclamation-triangle"></i> Seleccione una marca
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Segunda fila: Placa y Año -->
                        <div class="form-row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="placaAgregar" class="form-label required-field">Placa</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="placaAgregar" name="placa"
                                                required maxlength="6" placeholder="ABC123"
                                                style="text-transform: uppercase;">
                                            <span class="input-icon" id="placa-icon"></span>
                                        </div>
                                        <div class="validation-message" id="error-placa">
                                            <i class="bi bi-exclamation-triangle"></i> Formato: 3 letras + 3 números (ej: ABC123)
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="anioAgregar" class="form-label required-field">Año</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="anioAgregar" name="anio"
                                                min="1900" max="2026" required>
                                            <span class="input-icon" id="anio-icon"></span>
                                        </div>
                                        <div class="validation-message" id="error-anio">
                                            <i class="bi bi-exclamation-triangle"></i> El año debe estar entre 1900 y 2026
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tercera fila: Modelo y Color -->
                        <div class="form-row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="modeloAgregar" class="form-label required-field">Modelo</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="modeloAgregar" name="modelo"
                                                required maxlength="50" placeholder="Ej: Corolla, Civic">
                                            <span class="input-icon" id="modelo-icon"></span>
                                        </div>
                                        <div class="validation-message" id="error-modelo">
                                            <i class="bi bi-exclamation-triangle"></i> Solo letras, números, espacios y guiones (2-50 caracteres)
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="colorAgregar" class="form-label required-field">Color</label>
                                        <select class="form-select" id="colorAgregar" name="id_color" required>
                                            <?php foreach ($colores as $color_item): ?>
                                                <option value="<?= $color_item['id_color'] ?>"><?= htmlspecialchars($color_item['color']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="validation-message" id="error-color">
                                            <i class="bi bi-exclamation-triangle"></i> Seleccione un color
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cuarta fila: Kilometraje y Estado -->
                        <div class="form-row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kilometrajeAgregar" class="form-label required-field">Kilometraje Actual</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="kilometrajeAgregar"
                                                name="kilometraje_actual" min="0" max="999999" required>
                                            <span class="input-icon" id="kilometraje-icon"></span>
                                        </div>
                                        <div class="validation-message" id="error-kilometraje">
                                            <i class="bi bi-exclamation-triangle"></i> El kilometraje debe estar entre 0 y 999,999
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="estadoAgregar" class="form-label required-field">Estado</label>
                                        <select class="form-select" id="estadoAgregar" name="id_estado" required>
                                            <?php foreach ($estados as $estado): ?>
                                                <option value="<?= $estado['id_estado'] ?>"><?= htmlspecialchars($estado['estado']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="validation-message" id="error-estado">
                                            <i class="bi bi-exclamation-triangle"></i> Seleccione un estado
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quinta fila: Propietario y Foto -->
                        <div class="form-row">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="documentoAgregar" class="form-label required-field">Propietario</label>
                                        <select class="form-select" id="documentoAgregar" name="documento" required>
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <option value="<?= $usuario['documento'] ?>" data-rol="<?= $usuario['id_rol'] ?>">
                                                    <?= htmlspecialchars($usuario['nombre_completo']) ?> (<?= $usuario['documento'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="validation-message" id="error-documento">
                                            <i class="bi bi-exclamation-triangle"></i> Seleccione un propietario
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fotoVehiculoAgregar" class="form-label">Foto del Vehículo</label>
                                        <input type="file" class="form-control" id="fotoVehiculoAgregar"
                                            name="foto_vehiculo" accept="image/*">
                                        <div class="validation-message" id="error-foto">
                                            <i class="bi bi-exclamation-triangle"></i> Formato de imagen no válido
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vista previa de la imagen -->
                        <div class="preview-container">
                            <img id="fotoPreviewAgregar" src="" alt="Vista previa" class="preview-image">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="guardarVehiculoModal" id="btnGuardarVehiculo">
                            <span class="loading-spinner"></span>
                            <i class="bi bi-check-circle"></i> Guardar Vehículo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formAgregarVehiculo');
            const submitBtn = document.getElementById('btnGuardarVehiculo');
            const spinner = submitBtn.querySelector('.loading-spinner');

            // Validaciones en tiempo real
            const validators = {
                tipoVehiculo: {
                    element: document.getElementById('tipoVehiculoAgregar'),
                    validate: function(value) {
                        return value !== '';
                    },
                    message: 'Seleccione un tipo de vehículo'
                },

                marca: {
                    element: document.getElementById('marcaAgregar'),
                    validate: function(value) {
                        return value !== '';
                    },
                    message: 'Seleccione una marca'
                },

                placa: {
                    element: document.getElementById('placaAgregar'),
                    validate: function(value) {
                        const regex = /^[A-Z]{3}[0-9]{3}$/;
                        return regex.test(value);
                    },
                    message: 'Formato: 3 letras + 3 números (ej: ABC123)'
                },

                anio: {
                    element: document.getElementById('anioAgregar'),
                    validate: function(value) {
                        const year = parseInt(value);
                        const currentYear = new Date().getFullYear();
                        return year >= 1900 && year <= currentYear + 1;
                    },
                    message: `El año debe estar entre 1900 y ${new Date().getFullYear() + 1}`
                },

                modelo: {
                    element: document.getElementById('modeloAgregar'),
                    validate: function(value) {
                        const regex = /^[A-Za-z0-9\s\-]{2,50}$/;
                        return regex.test(value);
                    },
                    message: 'Solo letras, números, espacios y guiones (2-50 caracteres)'
                },

                color: {
                    element: document.getElementById('colorAgregar'),
                    validate: function(value) {
                        return value !== '';
                    },
                    message: 'Seleccione un color'
                },

                kilometraje: {
                    element: document.getElementById('kilometrajeAgregar'),
                    validate: function(value) {
                        const km = parseInt(value);
                        return km >= 0 && km <= 999999;
                    },
                    message: 'El kilometraje debe estar entre 0 y 999,999'
                },

                estado: {
                    element: document.getElementById('estadoAgregar'),
                    validate: function(value) {
                        return value !== '';
                    },
                    message: 'Seleccione un estado'
                },

                documento: {
                    element: document.getElementById('documentoAgregar'),
                    validate: function(value) {
                        return value !== '';
                    },
                    message: 'Seleccione un propietario'
                },

                foto: {
                    element: document.getElementById('fotoVehiculoAgregar'),
                    validate: function(value) {
                        if (!value) return true; // Es opcional
                        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        const file = this.element.files[0];
                        return file && allowedTypes.includes(file.type);
                    },
                    message: 'Formato de imagen no válido'
                }
            };

            // Función para mostrar/ocultar mensajes de validación
            function showValidationMessage(fieldName, isValid, customMessage = null) {
                const errorElement = document.getElementById(`error-${fieldName}`);
                const iconElement = document.getElementById(`${fieldName}-icon`);

                if (!errorElement) return;

                if (isValid) {
                    errorElement.classList.remove('error');
                    errorElement.classList.add('success');
                    errorElement.innerHTML = '<i class="bi bi-check-circle"></i> Correcto';
                    if (iconElement) {
                        iconElement.innerHTML = '<i class="bi bi-check-circle text-success"></i>';
                    }
                } else {
                    errorElement.classList.remove('success');
                    errorElement.classList.add('error');
                    errorElement.innerHTML = `<i class="bi bi-exclamation-triangle"></i> ${customMessage || validators[fieldName].message}`;
                    if (iconElement) {
                        iconElement.innerHTML = '<i class="bi bi-x-circle text-danger"></i>';
                    }
                }
            }

            // Validar campo individual
            function validateField(fieldName) {
                const validator = validators[fieldName];
                if (!validator) return true;

                const value = validator.element.value.trim();
                const isValid = validator.validate(value);

                showValidationMessage(fieldName, isValid);
                return isValid;
            }

            // Agregar event listeners para validación en tiempo real
            Object.keys(validators).forEach(fieldName => {
                const validator = validators[fieldName];
                const element = validator.element;

                if (element) {
                    // Validar cuando el campo pierde el foco
                    element.addEventListener('blur', () => {
                        validateField(fieldName);
                        hideGeneralError(); // Ocultar error general si el usuario está corrigiendo
                    });

                    // Validación inmediata para algunos campos
                    element.addEventListener('input', () => {
                        hideGeneralError(); // Ocultar error general cuando el usuario escribe

                        if (['placa', 'anio', 'modelo', 'kilometraje'].includes(fieldName)) {
                            setTimeout(() => validateField(fieldName), 500);
                        }
                    });

                    // Para selects, validar inmediatamente al cambiar
                    if (element.tagName === 'SELECT') {
                        element.addEventListener('change', () => {
                            validateField(fieldName);
                            hideGeneralError();
                        });
                    }
                }
            });

            // Funciones para mostrar/ocultar error general
            function showGeneralError(message) {
                const generalError = document.getElementById('generalError');
                const generalErrorMessage = document.getElementById('generalErrorMessage');

                generalErrorMessage.textContent = message;
                generalError.classList.add('show');

                // Scroll al inicio del modal
                generalError.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }

            function hideGeneralError() {
                const generalError = document.getElementById('generalError');
                generalError.classList.remove('show');
            }

            // Validar formulario completo
            function validateForm() {
                let isValid = true;
                let firstInvalidField = null;

                Object.keys(validators).forEach(fieldName => {
                    const fieldValid = validateField(fieldName);
                    if (!fieldValid) {
                        isValid = false;
                        if (!firstInvalidField) {
                            firstInvalidField = fieldName;
                        }
                    }
                });

                return isValid;
            }

            // Validar campo individual con mayor robustez
            function validateField(fieldName) {
                const validator = validators[fieldName];
                if (!validator || !validator.element) return true;

                let value = validator.element.value;

                // Para campos de texto, hacer trim
                if (validator.element.type === 'text' || validator.element.type === 'number') {
                    value = value.trim();
                }

                const isValid = validator.validate(value);

                showValidationMessage(fieldName, isValid);
                return isValid;
            }

            // Manejar preview de imagen
            document.getElementById('fotoVehiculoAgregar').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const preview = document.getElementById('fotoPreviewAgregar');

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                    validateField('foto');
                } else {
                    preview.style.display = 'none';
                }
            });

            // Convertir placa a mayúsculas automáticamente
            document.getElementById('placaAgregar').addEventListener('input', function(e) {
                e.target.value = e.target.value.toUpperCase();
            });

            // Manejar envío del formulario
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Validar todo el formulario antes de continuar
                const isFormValid = validateForm();

                if (!isFormValid) {
                    // Mostrar mensaje de error general
                    showGeneralError('Por favor, corrija los errores antes de continuar.');

                    // Scroll al primer error
                    const firstError = document.querySelector('.validation-message.error');
                    if (firstError) {
                        firstError.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }

                    // Asegurar que el botón esté habilitado
                    submitBtn.disabled = false;
                    spinner.style.display = 'none';

                    return false; // Detener completamente el envío
                }

                // Si llegamos aquí, el formulario es válido
                hideGeneralError();

                // Mostrar spinner
                spinner.style.display = 'inline-block';
                submitBtn.disabled = true;

                // Aquí iría la lógica de envío al servidor
                // Por ahora simulamos el proceso
                setTimeout(() => {
                    alert('Vehículo agregado exitosamente!');

                    // Resetear formulario
                    form.reset();
                    document.getElementById('fotoPreviewAgregar').style.display = 'none';

                    // Ocultar todos los mensajes de validación
                    document.querySelectorAll('.validation-message').forEach(msg => {
                        msg.classList.remove('error', 'success');
                    });

                    // Ocultar spinner
                    spinner.style.display = 'none';
                    submitBtn.disabled = false;

                    // Cerrar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalAgregarVehiculo'));
                    modal.hide();
                }, 2000);
            });

            // Resetear formulario cuando se abre el modal
            document.getElementById('modalAgregarVehiculo').addEventListener('show.bs.modal', function() {
                form.reset();
                document.getElementById('fotoPreviewAgregar').style.display = 'none';
                document.querySelectorAll('.validation-message').forEach(msg => {
                    msg.classList.remove('error', 'success');
                });
                document.querySelectorAll('.input-icon').forEach(icon => {
                    icon.innerHTML = '';
                });
                hideGeneralError();

                // Asegurar que el botón esté habilitado
                submitBtn.disabled = false;
                spinner.style.display = 'none';
            });
        });
    </script>

</body>

</html>