<?php
require_once __DIR__ . '/LicenseValidator.php';
require_once __DIR__ . '/../conecct/conex.php';

function validarLicenciaMiddleware($redirigir_en_error = true) {
    $database = new Database();
    $conexion = $database->conectar();
    
    $validator = new LicenseValidator($conexion);
    $resultado = $validator->validarLicencia();
    
    if (!$resultado['valida']) {
        if ($redirigir_en_error) {
            // Redirigir a página de error de licencia
            header('Location: /Flotavehicular/license_error.php?error=' . urlencode($resultado['mensaje']));
            exit;
        } else {
            return $resultado;
        }
    }
    
    return $resultado;
}

function validarLimiteUsuarios() {
    $database = new Database();
    $conexion = $database->conectar();
    
    $validator = new LicenseValidator($conexion);
    return $validator->validarLimiteUsuarios();
}

function validarLimiteVehiculos() {
    $database = new Database();
    $conexion = $database->conectar();
    
    $validator = new LicenseValidator($conexion);
    return $validator->validarLimiteVehiculos();
}

function obtenerInfoLicencia() {
    $database = new Database();
    $conexion = $database->conectar();
    
    $validator = new LicenseValidator($conexion);
    return $validator->obtenerInfoLicencia();
}
?>