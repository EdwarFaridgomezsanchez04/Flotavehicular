<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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
    case 'crear_empresa':
        crearEmpresa($conexion);
        break;
    case 'crear_licencia':
        crearLicencia($conexion);
        break;
    case 'actualizar_licencia':
        actualizarLicencia($conexion);
        break;
    case 'suspender_licencia':
        suspenderLicencia($conexion);
        break;
    case 'renovar_licencia':
        renovarLicencia($conexion);
        break;
    case 'obtener_licencia':
        obtenerLicencia($conexion);
        break;
    case 'verificar_licencias':
        verificarLicencias($conexion);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

// =================================================
// CREAR EMPRESA
// =================================================
function crearEmpresa($conexion)
{
    try {
        $nombre_empresa = $_POST['nombre_empresa'] ?? '';
        $nit = $_POST['nit'] ?? '';
        $direccion = $_POST['direccion'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $email = $_POST['email'] ?? '';

        if (empty($nombre_empresa) || empty($nit) || empty($telefono) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            return;
        }

        $stmt = $conexion->prepare("SELECT COUNT(*) FROM empresas WHERE nit = ?");
        $stmt->execute([$nit]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'El NIT ya está registrado']);
            return;
        }

        $stmt = $conexion->prepare("
            INSERT INTO empresas (nombre_empresa, nit, direccion, telefono, email, fecha_registro, estado) 
            VALUES (?, ?, ?, ?, ?, NOW(), 1)
        ");

        $stmt->execute([$nombre_empresa, $nit, $direccion, $telefono, $email]);

        echo json_encode(['success' => true, 'message' => 'Empresa registrada correctamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al registrar empresa: ' . $e->getMessage()]);
    }
}

// =================================================
// CREAR LICENCIA
// =================================================
function crearLicencia($conexion)
{
    try {
        $id_empresa = $_POST['id_empresa'] ?? '';
        $tipo_licencia = $_POST['tipo_licencia'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';

        if (empty($id_empresa) || empty($tipo_licencia) || empty($fecha_inicio) || empty($fecha_vencimiento)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            return;
        }

        $limites = [
            'basica' => ['usuarios' => 5, 'vehiculos' => 10],
            'profesional' => ['usuarios' => 20, 'vehiculos' => 50],
            'empresarial' => ['usuarios' => 100, 'vehiculos' => 500],
        ];

        if (!isset($limites[$tipo_licencia])) {
            echo json_encode(['success' => false, 'message' => 'Tipo de licencia inválido']);
            return;
        }

        $max_usuarios = $limites[$tipo_licencia]['usuarios'];
        $max_vehiculos = $limites[$tipo_licencia]['vehiculos'];
        $clave_licencia = 'FLOTAX-' . strtoupper(bin2hex(random_bytes(4)));

        $stmt = $conexion->prepare("SELECT id_empresa FROM empresas WHERE id_empresa = ? LIMIT 1");
        $stmt->execute([$id_empresa]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Empresa no encontrada']);
            return;
        }

        $stmt = $conexion->prepare("SELECT COUNT(*) FROM sistema_licencias WHERE id_empresa = ? AND estado = 'activa'");
        $stmt->execute([$id_empresa]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'message' => 'La empresa ya tiene una licencia activa']);
            return;
        }

        $stmt = $conexion->prepare("
            INSERT INTO sistema_licencias (id_empresa, tipo_licencia, fecha_inicio, fecha_vencimiento, max_usuarios, max_vehiculos, clave_licencia, estado, fecha_creacion)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'activa', NOW())
        ");
        $stmt->execute([
            $id_empresa,
            $tipo_licencia,
            $fecha_inicio,
            $fecha_vencimiento,
            $max_usuarios,
            $max_vehiculos,
            $clave_licencia
        ]);

        echo json_encode(['success' => true, 'message' => 'Licencia creada exitosamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear licencia: ' . $e->getMessage()]);
    }
}

// =================================================
// ACTUALIZAR LICENCIA
// =================================================
function actualizarLicencia($conexion)
{
    try {
        $id_licencia = $_POST['id_licencia'] ?? '';
        $tipo_licencia = $_POST['tipo_licencia'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
        $max_usuarios = $_POST['max_usuarios'] ?? '';
        $max_vehiculos = $_POST['max_vehiculos'] ?? '';

        if (empty($id_licencia) || empty($tipo_licencia) || empty($fecha_inicio) || empty($fecha_vencimiento) || empty($max_usuarios) || empty($max_vehiculos)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            return;
        }

        $stmt = $conexion->prepare("UPDATE sistema_licencias SET tipo_licencia = ?, fecha_inicio = ?, fecha_vencimiento = ?, max_usuarios = ?, max_vehiculos = ? WHERE id_licencia = ?");
        $stmt->execute([
            $tipo_licencia,
            $fecha_inicio,
            $fecha_vencimiento,
            $max_usuarios,
            $max_vehiculos,
            $id_licencia
        ]);

        echo json_encode(['success' => true, 'message' => 'Licencia actualizada exitosamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar licencia: ' . $e->getMessage()]);
    }
}

// =================================================
// SUSPENDER LICENCIA
// =================================================
function suspenderLicencia($conexion)
{
    try {
        $id_licencia = $_POST['id_licencia'] ?? '';
        if (empty($id_licencia)) {
            echo json_encode(['success' => false, 'message' => 'ID de licencia requerido']);
            return;
        }

        $conexion->prepare("UPDATE sistema_licencias SET estado = 'suspendida' WHERE id_licencia = ?")->execute([$id_licencia]);
        $conexion->prepare("UPDATE usuarios SET id_estado_usuario = 2 WHERE id_estado_usuario = 1")->execute();

        echo json_encode(['success' => true, 'message' => 'Licencia suspendida y usuarios desactivados']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al suspender licencia: ' . $e->getMessage()]);
    }
}

// =================================================
// RENOVAR LICENCIA
// =================================================
function renovarLicencia($conexion)
{
    try {
        $id_licencia = $_POST['id_licencia'] ?? '';
        $nueva_fecha_vencimiento = $_POST['nueva_fecha_vencimiento'] ?? '';

        if (empty($id_licencia) || empty($nueva_fecha_vencimiento)) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos para renovar']);
            return;
        }

        $conexion->prepare("UPDATE sistema_licencias SET fecha_vencimiento = ?, estado = 'activa' WHERE id_licencia = ?")
            ->execute([$nueva_fecha_vencimiento, $id_licencia]);

        $conexion->prepare("UPDATE usuarios SET id_estado_usuario = 1 WHERE id_estado_usuario = 2")->execute();

        echo json_encode(['success' => true, 'message' => 'Licencia renovada correctamente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al renovar licencia: ' . $e->getMessage()]);
    }
}

// =================================================
// OBTENER LICENCIA
// =================================================
function obtenerLicencia($conexion)
{
    try {
        $id_licencia = $_POST['id_licencia'] ?? '';
        if (empty($id_licencia)) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $stmt = $conexion->prepare("SELECT * FROM sistema_licencias WHERE id_licencia = ?");
        $stmt->execute([$id_licencia]);
        $licencia = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$licencia) {
            echo json_encode(['success' => false, 'message' => 'Licencia no encontrada']);
            return;
        }

        echo json_encode(['success' => true, 'licencia' => $licencia]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener licencia: ' . $e->getMessage()]);
    }
}

// =================================================
// VERIFICAR LICENCIAS (vencidas / próximas)
// =================================================
function verificarLicencias($conexion)
{
    try {
        $stmt = $conexion->prepare("
            SELECT COUNT(*) as vencidas 
            FROM sistema_licencias 
            WHERE fecha_vencimiento < CURDATE() AND estado = 'activa'
        ");
        $stmt->execute();
        $vencidas = $stmt->fetch(PDO::FETCH_ASSOC)['vencidas'];

        $stmt = $conexion->prepare("
            SELECT COUNT(*) as proximas 
            FROM sistema_licencias 
            WHERE fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
              AND estado = 'activa'
        ");
        $stmt->execute();
        $proximas = $stmt->fetch(PDO::FETCH_ASSOC)['proximas'];

        if ($vencidas > 0) {
            $conexion->prepare("UPDATE sistema_licencias SET estado = 'vencida' WHERE fecha_vencimiento < CURDATE() AND estado = 'activa'")->execute();
            $conexion->prepare("UPDATE usuarios SET id_estado_usuario = 2 WHERE id_estado_usuario = 1")->execute();
        }

        echo json_encode([
            'success' => true,
            'vencidas' => $vencidas,
            'proximas' => $proximas,
            'message' => 'Verificación completada'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error en verificación: ' . $e->getMessage()]);
    }
}
