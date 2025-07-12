<?php
// Inicializar sesión para validar el usuario autenticado
session_start();

// Incluir archivo de conexión a la base de datos
require_once('../../../conecct/conex.php');

// Validación de sesión activa
if (!isset($_SESSION['documento'])) {
    echo "Sesión no válida. Por favor, inicie sesión nuevamente.";
    exit();
}

// Crear instancia de la base de datos y obtener la conexión
$db = new Database();
$con = $db->conectar();

// Verificar que la petición sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $documento = $_POST['documento'];              // Documento de identidad del usuario (clave primaria)
    $nombre_completo = $_POST['nombre_completo'];  // Nombre completo del usuario
    $email = $_POST['email'];                      // Correo electrónico del usuario
    $telefono = $_POST['telefono'];                // Número de teléfono del usuario
    $estado = $_POST['estado'];                    // ID del estado del usuario (activo/inactivo)
    $rol = $_POST['rol'];                          // ID del rol del usuario (admin/usuario)

    // Preparar consulta SQL para actualizar los datos del usuario
    // Se actualiza por documento ya que es la clave primaria
    $query = $con->prepare("UPDATE usuarios SET nombre_completo = :nombre_completo, email = :email, telefono = :telefono, id_estado_usuario = :estado, id_rol = :rol WHERE documento = :documento");
    
    // Vincular parámetros para prevenir inyección SQL
    $query->bindParam(':documento', $documento, PDO::PARAM_STR);           // Documento como string
    $query->bindParam(':nombre_completo', $nombre_completo, PDO::PARAM_STR); // Nombre como string
    $query->bindParam(':email', $email, PDO::PARAM_STR);                   // Email como string
    $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);             // Teléfono como string
    $query->bindParam(':estado', $estado, PDO::PARAM_INT);                 // Estado como entero
    $query->bindParam(':rol', $rol, PDO::PARAM_INT);                       // Rol como entero

    // Ejecutar la consulta y mostrar resultado
    if ($query->execute()) {
        echo "Usuario actualizado exitosamente";
    } else {
        echo "Error al actualizar el usuario";
    }
}
?>