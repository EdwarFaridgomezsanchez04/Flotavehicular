<?php
// Apertura del bloque PHP - Indica el inicio del código PHP en el archivo

require_once('../conecct/conex.php');
// Inclusión de archivo de conexión
// - require_once: Incluye el archivo una sola vez, evita inclusiones duplicadas
// - '../conecct/conex.php': Ruta relativa al archivo de conexión a la base de datos
// - Contiene la clase Database para manejar la conexión MySQL

$db = new Database();
// Instanciación de la clase Database
// - Crea un nuevo objeto de la clase Database
// - Este objeto manejará la conexión y operaciones con la base de datos

$con = $db->conectar(); // ← corregido aquí
// Establecimiento de conexión
// - Llama al método conectar() de la clase Database
// - Retorna un objeto PDO para interactuar con MySQL
// - El comentario indica que hubo una corrección en esta sección

$nom = $_POST['nom'] ?? '';
// Captura del nombre desde POST
// - $_POST['nom']: Obtiene el valor del campo 'nom' enviado por formulario
// - ?? '': Operador null coalescing, asigna cadena vacía si 'nom' no existe
// - Previene errores de variables indefinidas

$ape = $_POST['ape'] ?? '';
// Captura del apellido desde POST
// - $_POST['ape']: Obtiene el valor del campo 'ape' del formulario
// - ?? '': Valor por defecto de cadena vacía si el campo no existe
// - Manejo seguro de variables POST

$corre = $_POST['corre'] ?? '';
// Captura del email desde POST
// - $_POST['corre']: Obtiene el valor del campo 'corre' (correo)
// - ?? '': Asigna cadena vacía si el campo no está presente
// - Protección contra errores de índice indefinido

$mensa = $_POST['mensa'] ?? '';
// Captura del mensaje desde POST
// - $_POST['mensa']: Obtiene el valor del campo 'mensa' (mensaje)
// - ?? '': Valor por defecto si el campo no existe en POST
// - Consistencia en el manejo de variables de entrada

if ($nom && $ape && $corre && $mensa) {
// Validación de campos obligatorios
// - Estructura condicional if que verifica todos los campos
// - && (AND lógico): Todos los campos deben tener contenido
// - Evaluación truthy: campos vacíos ('') se evalúan como false
// - Solo procede si todos los campos tienen datos válidos

    $inserto = $con->prepare("INSERT INTO contacto(nom, apellido, email, mensaje) VALUES(?, ?, ?, ?)");
    // Preparación de consulta SQL
    // - $con->prepare(): Crea un prepared statement
    // - INSERT INTO contacto: Inserta datos en la tabla 'contacto'
    // - (nom, apellido, email, mensaje): Columnas de destino
    // - VALUES(?, ?, ?, ?): Placeholders para prevenir SQL injection
    // - Prepared statements son más seguros que concatenación directa

    $inserto->execute([$nom, $ape, $corre, $mensa]);
    // Ejecución de la consulta
    // - execute(): Ejecuta el prepared statement
    // - [$nom, $ape, $corre, $mensa]: Array con valores para los placeholders
    // - Los valores se insertan de forma segura en la consulta
    // - Orden de valores debe coincidir con los placeholders

    echo "Datos subidos";
    // Mensaje de éxito
    // - echo: Imprime texto de respuesta
    // - "Datos subidos": Mensaje confirmando inserción exitosa
    // - Respuesta simple para el frontend/AJAX

} else {
// Bloque else para validación fallida
// - Se ejecuta cuando la condición del if es false
// - Maneja el caso donde uno o más campos están vacíos

    echo "Datos no enviados";
    // Mensaje de error
    // - echo: Imprime mensaje de error
    // - "Datos no enviados": Indica que faltan campos obligatorios
    // - Respuesta para informar al usuario sobre el problema

}
// Cierre del bloque condicional - Finaliza la estructura if-else

?>
// Cierre del bloque PHP
// - Indica el final del código PHP
// - Opcional al final del archivo, pero buena práctica incluirlo
?>