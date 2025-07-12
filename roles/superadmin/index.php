<?php
session_start();

// Verificar que el usuario esté autenticado como superadmin
if (!isset($_SESSION['superadmin_logged']) || $_SESSION['superadmin_logged'] !== true) {
    // Si no está autenticado como superadmin, verificar si está autenticado como usuario normal
    if (isset($_SESSION['documento']) && isset($_SESSION['tipo']) && $_SESSION['tipo'] == 3) {
        // Convertir sesión normal a sesión de superadmin
        require_once('../../conecct/conex.php');
        $db = new Database();
        $con = $db->conectar();
        
        $documento = $_SESSION['documento'];
        $sql = $con->prepare("SELECT * FROM usuarios WHERE documento = ? AND id_rol = 3");
        $sql->execute([$documento]);
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            // Crear sesión de superadmin
            $_SESSION['superadmin_documento'] = $usuario['documento'];
            $_SESSION['superadmin_nombre'] = $usuario['nombre_completo'];
            $_SESSION['superadmin_email'] = $usuario['email'];
            $_SESSION['superadmin_rol'] = $usuario['id_rol'];
            $_SESSION['superadmin_logged'] = true;
        } else {
            header('Location: ../../login/login.php');
            exit;
        }
    } else {
        header('Location: ../../login/login.php');
        exit;
    }
}

// Redirigir al dashboard
header('Location: dashboard.php');
exit;
?> 