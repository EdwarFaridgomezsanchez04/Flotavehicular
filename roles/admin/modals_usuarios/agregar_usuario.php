<?php
// Incluir archivo de conexión a la base de datos
require_once('../../../conecct/conex.php');

// Crear instancia de la base de datos y obtener la conexión
$db = new Database();
$con = $db->conectar();

// Verificar que la petición sea de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos del formulario
    $documento = $_POST['documento'];              // Documento de identidad del usuario (clave primaria)
    $nombre_completo = $_POST['nombre_completo'];  // Nombre completo del usuario
    $email = $_POST['email'];                      // Correo electrónico del usuario
    // Encriptar la contraseña usando el algoritmo por defecto de PHP (bcrypt)
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $telefono = $_POST['telefono'];                // Número de teléfono del usuario
    $estado = $_POST['estado'];                    // ID del estado del usuario (activo/inactivo)
    $rol = $_POST['rol'];                          // ID del rol del usuario (admin/usuario)

    // Verificar si el documento ya existe en la base de datos para evitar duplicados
    $checkQuery = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE documento = :documento");
    $checkQuery->bindParam(':documento', $documento, PDO::PARAM_STR);
    $checkQuery->execute();
    
    // Si el documento ya existe, mostrar mensaje de error y terminar ejecución
    if ($checkQuery->fetchColumn() > 0) {
        echo "El documento ya está registrado";
        exit;
    }

    // Preparar consulta SQL para insertar el nuevo usuario
    $query = $con->prepare("INSERT INTO usuarios (documento, nombre_completo, email, password, telefono, id_estado_usuario, id_rol) VALUES (:documento, :nombre_completo, :email, :password, :telefono, :estado, :rol)");
    
    // Vincular parámetros para prevenir inyección SQL
    $query->bindParam(':documento', $documento, PDO::PARAM_STR);           // Documento como string
    $query->bindParam(':nombre_completo', $nombre_completo, PDO::PARAM_STR); // Nombre como string
    $query->bindParam(':email', $email, PDO::PARAM_STR);                   // Email como string
    $query->bindParam(':password', $password, PDO::PARAM_STR);             // Contraseña encriptada como string
    $query->bindParam(':telefono', $telefono, PDO::PARAM_STR);             // Teléfono como string
    $query->bindParam(':estado', $estado, PDO::PARAM_INT);                 // Estado como entero
    $query->bindParam(':rol', $rol, PDO::PARAM_INT);                       // Rol como entero

    // Ejecutar la consulta y mostrar resultado
    if ($query->execute()) {
        echo "Usuario agregado exitosamente";
    } else {
        echo "Error al agregar el usuario";
    }
}
?>