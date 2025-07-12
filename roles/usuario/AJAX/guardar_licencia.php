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
    $documento = $_POST['documento_usuario'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $fecha_exp = $_POST['fecha_expedicion'] ?? '';
    $fecha_ven = $_POST['fecha_vencimiento'] ?? '';
    $servicio = $_POST['tipo_servicio'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';

    if (empty($documento) || empty($fecha_exp) || empty($fecha_ven) || empty($servicio) || empty($categoria)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    try {
        // Obtener categorías ya registradas por el usuario que NO estén vencidas
        $query = $con->prepare("SELECT cl.id_categoria, cat.nombre_categoria 
                                FROM licencias cl 
                                JOIN categoria_licencia cat ON cl.id_categoria = cat.id_categoria 
                                WHERE cl.id_documento = ? 
                                AND cl.fecha_vencimiento >= CURRENT_DATE()");
        $query->execute([$documento]);
        $categorias_existentes = $query->fetchAll(PDO::FETCH_ASSOC);


        // Obtener nombre de la categoría
        $stmt = $con->prepare("SELECT cl.nombre_categoria 
                               FROM categoria_licencia cl 
                               WHERE cl.id_categoria = ?");
        $stmt->execute([$categoria]);
        $categoria_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$categoria_info) {
            echo json_encode(['status' => 'error', 'message' => 'Categoría no válida.']);
            exit;
        }

        $categoria_nueva = $categoria_info['nombre_categoria'];
        $codigo_categoria_actual = strtoupper(substr($categoria_nueva, 0, 2)); // Ej: A1, B2...
        $servicio_usuario = strtolower(trim($servicio));

        // Traducir servicio si llega como número
        if ($servicio_usuario === '1') $servicio_usuario = 'particular';
        elseif ($servicio_usuario === '2') $servicio_usuario = 'publico';

        // Validar que el servicio corresponda según la categoría
        $servicios_permitidos_por_categoria = [
            'A1' => 'particular',
            'A2' => 'particular',
            'B1' => 'particular',
            'B2' => 'particular',
            'B3' => 'particular',
            'C1' => 'publico',
            'C2' => 'publico',
            'C3' => 'publico',
        ];

        if (isset($servicios_permitidos_por_categoria[$codigo_categoria_actual])) {
            $servicio_permitido = strtolower($servicios_permitidos_por_categoria[$codigo_categoria_actual]);
            if ($servicio_usuario !== $servicio_permitido) {
                echo json_encode([
                    'status' => 'error',
                    'message' => "La categoría $codigo_categoria_actual solo se puede registrar con el servicio '$servicio_permitido'."
                ]);
                exit;
            }
        }

        // Obtener categorías ya registradas por el usuario que NO estén vencidas
        $query = $con->prepare("SELECT cl.id_categoria, cat.nombre_categoria 
                                FROM licencias cl 
                                JOIN categoria_licencia cat ON cl.id_categoria = cat.id_categoria 
                                WHERE cl.id_documento = ? 
                                AND cl.fecha_vencimiento >= CURRENT_DATE()");
        $query->execute([$documento]);
        $categorias_existentes = $query->fetchAll(PDO::FETCH_ASSOC);

        // Validar conflictos entre categorías solo con licencias vigentes
        $conflictos = [
            'A1' => ['A2'],
            'A2' => ['A1'],
            'B1' => ['B2', 'B3'],
            'B2' => ['B1', 'B3'],
            'B3' => ['B1', 'B2'],
            'C1' => ['C2', 'C3'],
            'C2' => ['C1', 'C3'],
            'C3' => ['C1', 'C2']
        ];

        foreach ($categorias_existentes as $cat) {
            $codigo_existente = substr($cat['nombre_categoria'], 0, 2);
            if (isset($conflictos[$codigo_categoria_actual]) &&
                in_array($codigo_existente, $conflictos[$codigo_categoria_actual])) {
                echo json_encode(['status' => 'error', 
                    'message' => "No se puede registrar la categoría $codigo_categoria_actual porque existe una licencia vigente de categoría $codigo_existente."]);
                exit;
            }
        }

        // Validaciones de fechas
        $hoy = date('Y-m-d');

        if ($fecha_exp > $hoy) {
            echo json_encode(['status' => 'error', 'message' => 'La fecha de expedición no puede ser una fecha futura.']);
            exit;
        }

        if ($fecha_ven <= $fecha_exp) {
            echo json_encode(['status' => 'error', 'message' => 'La fecha de vencimiento debe ser posterior a la de expedición.']);
            exit;
        }

        // Validar solapamiento de fechas en la misma categoría
        $stmt = $con->prepare("SELECT * FROM licencias 
                               WHERE id_documento = ? AND id_categoria = ? 
                               AND (fecha_expedicion <= ? AND fecha_vencimiento >= ?)");
        $stmt->execute([$documento, $categoria, $fecha_ven, $fecha_exp]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Ya existe una licencia con fechas que se cruzan para esta categoría.']);
            exit;
        }

        // Insertar en la base de datos
        $sql = $con->prepare("INSERT INTO licencias (id_documento, id_categoria, fecha_expedicion, fecha_vencimiento, id_servicio) VALUES (?, ?, ?, ?, ?)");
        $resultado = $sql->execute([$documento, $categoria, $fecha_exp, $fecha_ven, $servicio]);

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Licencia guardada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar la licencia.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
}
?>
