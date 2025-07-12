<?php
session_start();

if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../../conecct/conex.php';
$database = new Database();
$conexion = $database->conectar();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'crear_vehiculo':
        crearVehiculo($conexion);
        break;
    case 'actualizar_vehiculo':
        actualizarVehiculo($conexion);
        break;
    case 'eliminar_vehiculo':
        eliminarVehiculo($conexion);
        break;
    case 'obtener_vehiculo':
        obtenerVehiculo($conexion);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

function crearVehiculo($conexion)
{
    try {
        $placa = $_POST['placa'] ?? '';
        $Documento = $_POST['Documento'] ?? '';
        $id_marca = $_POST['id_marca'] ?? '';
        $modelo = $_POST['modelo'] ?? '';
        $año = $_POST['año'] ?? '';
        $id_tipo = $_POST['tipo_vehiculo'] ?? '';
        $id_color = $_POST['id_color'] ?? '';
        $id_estado = $_POST['id_estado'] ?? '1';
        $kilometraje = $_POST['kilometraje_actual'] ?? '0';
        $foto = '';
        $registrado_por = $_SESSION['documento'];

        if (empty($placa) || empty($Documento) || empty($id_marca) || empty($modelo) || empty($año) || empty($id_tipo) || empty($id_color)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            return;
        }

        // Validación de placa única
        $stmt = $conexion->prepare("SELECT placa FROM vehiculos WHERE placa = ?");
        $stmt->execute([$placa]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'La placa ya está registrada']);
            return;
        }

        // Validación del propietario
        $stmt = $conexion->prepare("SELECT documento FROM usuarios WHERE documento = ?");
        $stmt->execute([$Documento]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El propietario no existe']);
            return;
        }

        // Validar y subir la imagen (si viene)
        if (isset($_FILES['foto_vehiculo']) && $_FILES['foto_vehiculo']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['foto_vehiculo']['tmp_name'];
            $archivoNombre = $_FILES['foto_vehiculo']['name'];
            $archivoTamaño = $_FILES['foto_vehiculo']['size'];
            $archivoTipo = mime_content_type($archivoTmp);

            $extensionesPermitidas = ['image/jpeg', 'image/png', 'image/jpg'];
            $tamanoMaximo = 2 * 1024 * 1024; // 2MB

            if (!in_array($archivoTipo, $extensionesPermitidas)) {
                echo json_encode(['success' => false, 'message' => 'Solo se permiten imágenes JPG o PNG']);
                return;
            }

            if ($archivoTamaño > $tamanoMaximo) {
                echo json_encode(['success' => false, 'message' => 'La imagen debe pesar menos de 2MB']);
                return;
            }

            $nombreArchivo = time() . '_' . basename($archivoNombre);
            $directorio = '../../uploads/vehiculos/';
            $rutaDestino = $directorio . $nombreArchivo;

            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            if (!move_uploaded_file($archivoTmp, $rutaDestino)) {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
                return;
            }

            $foto = 'uploads/vehiculos/' . $nombreArchivo;
        }

        // Inserción en la base de datos
        $stmt = $conexion->prepare("
            INSERT INTO vehiculos 
            (placa, tipo_vehiculo, año, Documento, id_marca, modelo, id_color, kilometraje_actual, id_estado, fecha_registro, foto_vehiculo, registrado_por)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)
        ");

        $stmt->execute([
            $placa,
            $id_tipo,
            $año,
            $Documento,
            $id_marca,
            $modelo,
            $id_color,
            $kilometraje,
            $id_estado,
            $foto,
            $registrado_por
        ]);

        echo json_encode(['success' => true, 'message' => 'Vehículo creado exitosamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear vehículo: ' . $e->getMessage()]);
    }
}


function actualizarVehiculo($conexion)
{
    try {
        $placa_original = $_POST['placa_original'] ?? '';

        if (empty($placa_original)) {
            echo json_encode(['success' => false, 'message' => 'Placa original requerida']);
            return;
        }

        // Obtener datos actuales del vehículo
        $stmt = $conexion->prepare("SELECT * FROM vehiculos WHERE placa = ?");
        $stmt->execute([$placa_original]);
        $vehiculo_actual = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehiculo_actual) {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
            return;
        }

        // Recolectar datos nuevos (o mantener existentes si no se envían)
        $placa = $vehiculo_actual['placa']; // no editable
        $Documento = $vehiculo_actual['Documento'];
        $id_marca = $vehiculo_actual['id_marca'];
        $modelo = $vehiculo_actual['modelo'];
        $año = $vehiculo_actual['año'];
        $id_tipo = $vehiculo_actual['tipo_vehiculo'];

        // Solo se pueden editar estos campos:
        $id_color = $_POST['id_color'] ?? $vehiculo_actual['id_color'];
        $id_estado = $_POST['id_estado'] ?? $vehiculo_actual['id_estado'];
        $kilometraje = $_POST['kilometraje_actual'] ?? $vehiculo_actual['kilometraje_actual'];
        $foto = $vehiculo_actual['foto_vehiculo'];

        // Procesar nueva foto si se subió
        if (isset($_FILES['foto_vehiculo']) && $_FILES['foto_vehiculo']['error'] === UPLOAD_ERR_OK) {
            $archivoTmp = $_FILES['foto_vehiculo']['tmp_name'];
            $archivoNombre = $_FILES['foto_vehiculo']['name'];
            $archivoTamaño = $_FILES['foto_vehiculo']['size'];
            $archivoTipo = mime_content_type($archivoTmp);

            $extensionesPermitidas = ['image/jpeg', 'image/png', 'image/jpg'];
            $tamanoMaximo = 2 * 1024 * 1024; // 2MB

            if (!in_array($archivoTipo, $extensionesPermitidas)) {
                echo json_encode(['success' => false, 'message' => 'Solo se permiten imágenes JPG o PNG']);
                return;
            }

            if ($archivoTamaño > $tamanoMaximo) {
                echo json_encode(['success' => false, 'message' => 'El archivo debe ser menor a 2MB']);
                return;
            }

            // Guardar archivo
            $nombreArchivo = time() . '_' . basename($archivoNombre);
            $directorio = '../../uploads/vehiculos/';
            $rutaDestino = $directorio . $nombreArchivo;

            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            if (move_uploaded_file($archivoTmp, $rutaDestino)) {
                // Eliminar imagen anterior si existía
                if (!empty($vehiculo_actual['foto_vehiculo']) && file_exists('../../' . $vehiculo_actual['foto_vehiculo'])) {
                    unlink('../../' . $vehiculo_actual['foto_vehiculo']);
                }

                $foto = 'uploads/vehiculos/' . $nombreArchivo;
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
                return;
            }
        }


        // Actualizar solo los campos editables
        $stmt = $conexion->prepare("
            UPDATE vehiculos 
            SET 
                id_color = ?, 
                kilometraje_actual = ?, 
                id_estado = ?, 
                foto_vehiculo = ?
            WHERE placa = ?
        ");

        $stmt->execute([
            $id_color,
            $kilometraje,
            $id_estado,
            $foto,
            $placa_original
        ]);

        echo json_encode(['success' => true, 'message' => 'Vehículo actualizado exitosamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar vehículo: ' . $e->getMessage()]);
    }
}


function eliminarVehiculo($conexion)
{
    try {
        $placa = $_POST['placa'] ?? '';
        if (empty($placa)) {
            echo json_encode(['success' => false, 'message' => 'Placa requerida']);
            return;
        }

        $stmt = $conexion->prepare("SELECT placa FROM vehiculos WHERE placa = ?");
        $stmt->execute([$placa]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
            return;
        }

        $stmt = $conexion->prepare("DELETE FROM vehiculos WHERE placa = ?");
        $stmt->execute([$placa]);

        echo json_encode(['success' => true, 'message' => 'Vehículo eliminado exitosamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar vehículo: ' . $e->getMessage()]);
    }
}

function obtenerVehiculo($conexion)
{
    try {
        $placa = $_POST['placa'] ?? '';
        if (empty($placa)) {
            echo json_encode(['success' => false, 'message' => 'Placa requerida']);
            return;
        }

        $stmt = $conexion->prepare("
            SELECT placa, tipo_vehiculo, año, Documento, id_marca, modelo, id_color, kilometraje_actual, id_estado, foto_vehiculo
            FROM vehiculos 
            WHERE placa = ?
        ");
        $stmt->execute([$placa]);
        $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehiculo) {
            echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
            return;
        }

        echo json_encode(['success' => true, 'vehiculo' => $vehiculo]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener vehículo: ' . $e->getMessage()]);
    }
}
