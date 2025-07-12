<?php
// Iniciar sesión PHP para manejar variables de sesión
session_start();

// Incluir el archivo de conexión a la base de datos
require_once('../conecct/conex.php');

// Crear una nueva instancia de la clase Database y establecer conexión
$db = new Database();
$con = $db->conectar();

// Procesar el formulario cuando se envía por método POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Obtener y limpiar el correo electrónico del formulario
    $correo = trim($_POST['email']);

    // Validar que el campo de correo no esté vacío
    if (empty($correo)) {
        echo '<script>alert("Ningún dato puede estar vacío");</script>';
    } else {
        // Verificar si el usuario existe en la base de datos
        $sql = $con->prepare("SELECT email FROM usuarios WHERE email = :email");
        $sql->bindParam(':email', $correo, PDO::PARAM_STR);
        $sql->execute();

        // Obtener el resultado de la consulta
        $fila = $sql->fetch(PDO::FETCH_ASSOC);

        // Si el usuario existe en la base de datos
        if ($fila) {
            // Guardar el email en la sesión para uso posterior
            $_SESSION['email'] = $fila['email'];

            // Crear un formulario oculto para redirigir a enviar_recuperacion.php
            // con los datos del correo de forma segura
            echo '<form id="sendForm" action="enviar_recuperacion" method="POST">
                      <input type="hidden" name="email" value="' . htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') . '">
                  </form>
                  <script>document.getElementById("sendForm").submit();</script>';
            exit; // Terminar la ejecución del script
        } else {
            // Mostrar mensaje de error si el correo no está registrado
            echo '<script>alert("Correo no registrado");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>
    
    <!-- Favicon del sitio web -->
    <link rel="shortcut icon" href="../css/img/logo_sinfondo.png">
    
    <!-- Bootstrap Icons para iconografía -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Bootstrap CSS para estilos y componentes responsivos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos adicionales -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    
    <!-- jQuery para manipulación del DOM -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Estilos CSS personalizados para login y registro -->
    <link rel="stylesheet" href="../css/stylelog_re.css">
</head>

<!-- El body se carga con focus automático en el campo email -->
<body onload="formulario_olvidate_con.email.focus()">
    <div class="content">
        <!-- Botón de regreso al login -->
        <div class="regresar">
            <a href="login" class="re">
                <i class="bi bi-house-door-fill"></i>
            </a>
        </div>

        <!-- Contenedor principal del formulario -->
        <div class="conten_form">
            <div class="form-infor">
                <!-- Logo de la aplicación con enlace al inicio -->
                <a href="index">
                    <img src="../css/img/logo_sinfondo.png" alt="logo" class="logo">
                </a>
                
                <!-- Título y descripción del proceso de recuperación -->
                <h2>¿Olvidaste tu contraseña?</h2>
                <p>No te preocupes, restableceremos tu contraseña.</p>
                <p>Solo dinos con qué dirección de email te registraste al sistema.</p>
                
                <!-- Formulario de recuperación de contraseña -->
                <!-- action: vacío para procesar en la misma página -->
                <!-- method: POST para envío seguro -->
                <!-- autocomplete: off para mayor seguridad -->
                <form action="" method="POST" id="formulario_olvidate_con" autocomplete="off">
                    <div>
                        <!-- Campo de correo electrónico -->
                        <div class="input_field_correo" id="input_field_correo">
                            <label for="correo"></label>
                            <!-- Icono de sobre para el campo email -->
                            <i class="bi bi-envelope-fill"></i>
                            <!-- Input de email con validación HTML5 -->
                            <input type="email" name="email" id="email" placeholder="Correo">
                        </div>
                        
                        <!-- Mensaje de validación para el correo -->
                        <div>
                            <p class="formulario_error_olv_con" id="vali_correo">
                                Ingrese un correo electrónico válido (ejemplo@gmail.com).
                            </p>
                        </div>
                    </div>
                    
                    <!-- Mensaje de error general del formulario -->
                    <p class="formulario_error" id="formulario_error">
                        <b>Error:</b> Por favor coloca el correo correctamente.
                    </p>
                    
                    <!-- Botón de envío del formulario -->
                    <div class="btn-field">
                        <button type="submit" class="re" name="submit">Enviar</button>
                    </div>
                    
                    <!-- Mensaje de éxito -->
                    <p class="formulario_exito" id="formulario_exito">Enviando correo...</p>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript para validación del formulario -->
    <script>
        // Obtener referencias a elementos del DOM
        const formulario_con = document.getElementById('formulario_olvidate_con');
        const inputs = document.querySelectorAll('#formulario_olvidate_con input');

        // Expresiones regulares para validación
        const expresion = {
            // Validar que el correo sea específicamente de Gmail
            validacorreo: /^[a-zA-Z0-9._%+-]+@gmail\.com$/
        };

        /**
         * Función para validar el campo de correo en tiempo real
         * @param {Event} e - Evento del input (keyup o blur)
         */
        const validcorreo = (e) => {
            switch (e.target.name) {
                case "email":
                    // Verificar si el correo cumple con el patrón de Gmail
                    if (expresion.validacorreo.test(e.target.value)) {
                        // Aplicar estilos de campo correcto
                        document.getElementById('input_field_correo').classList.remove('input_field_correo');
                        document.getElementById('input_field_correo').classList.add('input_field_correo_correcto');
                        // Ocultar mensaje de error
                        document.getElementById('vali_correo').style.opacity = 0;
                    } else {
                        // Aplicar estilos de campo incorrecto
                        document.getElementById('input_field_correo').classList.remove('input_field_correo');
                        document.getElementById('input_field_correo').classList.add('input_field_correo_incorrecto');
                        // Mostrar mensaje de error
                        document.getElementById('vali_correo').style.opacity = 1;
                    }
                    break;
            }
        };

        // Agregar event listeners para validación en tiempo real
        inputs.forEach((input) => {
            // Validar mientras el usuario escribe
            input.addEventListener('keyup', validcorreo);
            // Validar cuando el campo pierde el foco
            input.addEventListener('blur', validcorreo);
        });

        /**
         * Manejar el envío del formulario
         * Realizar validación final antes del envío
         */
        formulario_con.addEventListener('submit', (e) => {
            // Verificar si el correo es válido antes del envío
            if (!expresion.validacorreo.test(inputs[0].value)) {
                // Prevenir el envío del formulario
                e.preventDefault();
                
                // Mostrar estilos de error
                document.getElementById('input_field_correo').classList.add('input_field_correo_incorrecto');
                document.getElementById('vali_correo').style.opacity = 1;
                document.getElementById('formulario_error').style.opacity = 1;
                document.getElementById('formulario_error').style.color = "#d32f2f";
                
                // Enfocar el campo de email para corrección
                document.getElementById('email').focus();

                // Ocultar mensaje de error después de 3 segundos
                setTimeout(() => {
                    document.getElementById('formulario_error').style.opacity = 0;
                }, 3000);
            } else {
                // Mostrar mensaje de éxito si la validación pasa
                document.getElementById('formulario_exito').style.opacity = 1;
                document.getElementById('formulario_exito').style.color = "#158000";

                // Ocultar mensaje de éxito después de 3 segundos
                setTimeout(() => {
                    document.getElementById('formulario_exito').style.opacity = 0;
                }, 3000);
            }
        });
    </script>
</body>
</html>
