<?php
// Apertura del bloque PHP para procesar el registro de usuarios

// Incluir el archivo de conexión a la base de datos
require_once('../conecct/conex.php');

// Crear una nueva instancia de la clase Database
$db = new Database();

// Establecer la conexión con la base de datos
$con = $db->conectar();

// Iniciar la sesión PHP para manejo de variables de sesión
session_start();

// Definir el estado por defecto para nuevos usuarios (1 = activo)
$estado = 1;

// Definir el rol por defecto para nuevos usuarios (2 = usuario regular)
$rol = 2;

// Establecer el tipo de contenido de la respuesta como JSON
header('Content-Type: application/json');

// Inicializar el array de respuesta
$response = [];

// Verificar si la petición HTTP es de tipo POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Capturar y sanitizar los datos del formulario usando null coalescing operator
    $doc = $_POST['doc'] ?? '';        // Documento de identidad
    $nom = $_POST['nom'] ?? '';        // Nombre completo
    $correo = $_POST['correo'] ?? '';  // Dirección de correo electrónico
    $cont = $_POST['con'] ?? '';       // Contraseña
    $con2 = $_POST['con2'] ?? '';      // Confirmación de contraseña
    $cel = $_POST['cel'] ?? '';        // Número de teléfono celular

    // Validar que todos los campos obligatorios estén completos
    if (empty($doc) || empty($nom) || empty($correo) || empty($cont) || empty($con2) || empty($cel)) {
        // Retornar error si algún campo está vacío
        echo json_encode(['status' => 'error', 'message' => 'Campos vacíos']);
        exit; // Terminar la ejecución del script
    }

    // Verificar que las contraseñas coincidan
    if ($cont !== $con2) {
        // Retornar error si las contraseñas no son iguales
        echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden']);
        exit; // Terminar la ejecución del script
    }

    // Encriptar la contraseña usando el algoritmo PASSWORD_DEFAULT con costo 12
    $cont_enc = password_hash($cont, PASSWORD_DEFAULT, array("cost" => 12));

    // Validar que el documento no esté ya registrado en la base de datos
    $sql1 = $con->prepare("SELECT * FROM usuarios WHERE documento = ?");
    $sql1->execute([$doc]); // Ejecutar la consulta con el documento como parámetro
    if ($sql1->fetch()) {
        // Si encuentra un registro, el documento ya existe
        echo json_encode(['status' => 'error', 'message' => 'Documento ya registrado']);
        exit; // Terminar la ejecución del script
    }

    // Validar que el nombre completo no esté ya registrado
    $sql3 = $con->prepare("SELECT * FROM usuarios WHERE nombre_completo = ?");
    $sql3->execute([$nom]); // Ejecutar la consulta con el nombre como parámetro
    if ($sql3->fetch()) {
        // Si encuentra un registro, el nombre ya existe
        echo json_encode(['status' => 'error', 'message' => 'Nombre ya registrado']);
        exit; // Terminar la ejecución del script
    }

    // Validar que el correo electrónico no esté ya registrado
    $sql2 = $con->prepare("SELECT * FROM usuarios WHERE email = ?");
    $sql2->execute([$correo]); // Ejecutar la consulta con el correo como parámetro
    if ($sql2->fetch()) {
        // Si encuentra un registro, el correo ya existe
        echo json_encode(['status' => 'error', 'message' => 'Correo ya registrado']);
        exit; // Terminar la ejecución del script
    }

    // Validar que el número de teléfono no esté ya registrado
    $sql4 = $con->prepare("SELECT * FROM usuarios WHERE telefono = ?");
    $sql4->execute([$cel]); // Ejecutar la consulta con el teléfono como parámetro
    if ($sql4->fetch()) {
        // Si encuentra un registro, el teléfono ya existe
        echo json_encode(['status' => 'error', 'message' => 'Celular ya registrado']);
        exit; // Terminar la ejecución del script
    }

    // Definir la ruta de la imagen de perfil por defecto
    $foto = '/roles/usuario/css/img/perfil.jpg'; // Ruta relativa a la raíz del proyecto
    
    // Preparar la consulta SQL para insertar el nuevo usuario
    $inserto = $con->prepare("INSERT INTO usuarios(documento, nombre_completo, email, password, telefono, foto_perfil, id_estado_usuario, id_rol) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Ejecutar la inserción con todos los parámetros
    if ($inserto->execute([$doc, $nom, $correo, $cont_enc, $cel, $foto, $estado, $rol])) {
        // Si la inserción es exitosa, retornar mensaje de éxito
        echo json_encode(['status' => 'success', 'message' => 'Registro exitoso']);

    } else {
        // Si hay error en la inserción, retornar mensaje de error
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar']);
    }
}
// Cierre del bloque PHP
?>