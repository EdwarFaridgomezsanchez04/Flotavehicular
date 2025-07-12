<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['documento']) || $_SESSION['tipo'] != 3) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once '../../conecct/conex.php';
$database = new Database();
$pdo = $database->conectar();

$action = $_POST['action'] ?? null;

if ($action === 'obtener_usuario') {
    try {
        $documento = $_POST['documento'] ?? '';
        $stmt = $pdo->prepare("SELECT documento, nombre_completo, email, telefono, id_rol, id_estado_usuario, nit_empresa AS id_empresa FROM usuarios WHERE documento = ?");
        $stmt->execute([$documento]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            echo json_encode(['success' => true, 'usuario' => $usuario]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener usuario: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'crear_usuario') {
    try {
        $documento = $_POST['documento'] ?? '';
        $nombre = $_POST['nombre_completo'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $password = $_POST['password'] ?? '';
        $id_rol = $_POST['id_rol'] ?? '';
        $id_estado = $_POST['id_estado_usuario'] ?? '';
        $id_empresa = $_POST['id_empresa'] ?? null;

        $hashed = !empty($password) ? password_hash($password, PASSWORD_BCRYPT) : null;

        $nit_empresa = null;
        if ($id_empresa) {
            $stmtNit = $pdo->prepare("SELECT nit FROM empresas WHERE id_empresa = ?");
            $stmtNit->execute([$id_empresa]);
            $nit_empresa = $stmtNit->fetchColumn();
        }

        $stmt = $pdo->prepare("INSERT INTO usuarios (documento, nombre_completo, email, telefono, password, id_rol, id_estado_usuario, joined_at, nit_empresa)
                               VALUES (:documento, :nombre, :email, :telefono, :password, :id_rol, :id_estado, NOW(), :nit_empresa)");
        $stmt->execute([
            ':documento' => $documento,
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':password' => $hashed,
            ':id_rol' => $id_rol,
            ':id_estado' => $id_estado,
            ':nit_empresa' => $nit_empresa
        ]);

        echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al crear usuario: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'actualizar_usuario') {
    try {
        $documento_original = $_POST['documento_original'] ?? '';
        $email = $_POST['email'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $id_rol = $_POST['id_rol'] ?? '';
        $id_estado = $_POST['id_estado_usuario'] ?? '';
        $password = $_POST['password'] ?? '';
        $id_empresa = $_POST['id_empresa'] ?? null;

        $nit_empresa = null;
        if ($id_empresa) {
            $stmtNit = $pdo->prepare("SELECT nit FROM empresas WHERE id_empresa = ?");
            $stmtNit->execute([$id_empresa]);
            $nit_empresa = $stmtNit->fetchColumn();
        }

        $campos = "email = :email, telefono = :telefono, id_rol = :id_rol, id_estado_usuario = :id_estado, nit_empresa = :nit_empresa";
        $params = [
            ':email' => $email,
            ':telefono' => $telefono,
            ':id_rol' => $id_rol,
            ':id_estado' => $id_estado,
            ':nit_empresa' => $nit_empresa,
            ':documento' => $documento_original
        ];

        if (!empty($password)) {
            $campos .= ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET $campos WHERE documento = :documento");
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario: ' . $e->getMessage()]);
    }
    exit;
}

if ($action === 'eliminar_usuario') {
    try {
        $documento = $_POST['documento'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE documento = ?");
        $stmt->execute([$documento]);

        echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Acción no válida']);
