<?php
/**
 * CAMBIO DE CONTRASEÑA - RECUPERACIÓN DE CUENTA
 * 
 * Este archivo maneja el proceso de cambio de contraseña mediante token de recuperación.
 * Permite a los usuarios restablecer su contraseña después de solicitar una recuperación
 * a través del sistema de tokens temporales con expiración.
 */

// ===== CONFIGURACIÓN INICIAL =====
// Inicia la sesión para manejar datos temporales del usuario
session_start();
// Incluye la clase de conexión a la base de datos
require_once('../conecct/conex.php');
// Crea una instancia de la base de datos y establece la conexión
$db = new Database();
$con = $db->conectar();
// Establece la zona horaria para Colombia (importante para validación de tokens)
date_default_timezone_set('America/Bogota');

// ===== VALIDACIÓN DE TOKEN EN URL =====
/**
 * Verifica que se haya proporcionado un token en la URL
 * Si no existe, redirige al usuario a la página de recuperación
 */
if (!isset($_GET['token'])) {
    echo '<script>alert("Acceso no autorizado.");</script>';
    echo '<script>window.location = "recovery";</script>';
    exit;
}

// ===== VERIFICACIÓN DE TOKEN Y EXPIRACIÓN =====
// Obtiene el token de la URL
$token = $_GET['token'];
// Obtiene la fecha y hora actual en formato de base de datos
$now = date("Y-m-d H:i:s");
// Consulta para verificar si el token existe y no ha expirado
$query = $con->prepare("SELECT * FROM usuarios WHERE reset_token = ? AND reset_expira >= ?");
$query->execute([$token, $now]);
$user = $query->fetch(PDO::FETCH_ASSOC);

/**
 * Si no se encuentra el usuario o el token ha expirado,
 * redirige a la página de recuperación con mensaje de error
 */
if (!$user) {
    echo '<script>alert("El token es inválido o ha expirado.");</script>';
    echo '<script>window.location = "recovery";</script>';
    exit;
}

// ===== EXTRACCIÓN DE DATOS DEL USUARIO =====
// Obtiene el documento (ID) del usuario para futuras operaciones
$id_usuario = $user['documento'];
// Obtiene el email del usuario (puede ser útil para logs o confirmaciones)
$email = $user['email'];

// ===== PROCESAMIENTO DEL FORMULARIO =====
/**
 * Maneja el envío del formulario de cambio de contraseña
 * Incluye múltiples validaciones de seguridad
 */
if (isset($_POST['enviar'])) {
    // Captura las contraseñas del formulario
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    // ===== VALIDACIÓN: CONTRASEÑA DIFERENTE A LA ANTERIOR =====
    /**
     * Verifica que la nueva contraseña no sea igual a la actual
     * Esto previene que los usuarios "cambien" por la misma contraseña
     */
    if (password_verify($password1, $user['password'])) {
        echo '<script>alert("La nueva contraseña no puede ser igual a la anterior.");</script>';
    } 
    // ===== VALIDACIÓN: LONGITUD MÍNIMA =====
    /**
     * Verifica que la contraseña tenga al menos 6 caracteres
     * (Nota: El frontend requiere 8-14 con complejidad, pero el backend es más permisivo)
     */
    elseif (strlen($password1) < 6) {
        echo '<script>alert("La contraseña debe tener al menos 6 caracteres.");</script>';
    } 
    // ===== VALIDACIÓN: CONFIRMACIÓN DE CONTRASEÑA =====
    /**
     * Verifica que ambas contraseñas coincidan
     * Previene errores de escritura del usuario
     */
    elseif ($password1 !== $password2) {
        echo '<script>alert("Las contraseñas no coinciden.");</script>';
    } 
    // ===== ACTUALIZACIÓN DE CONTRASEÑA =====
    else {
        // Genera hash seguro de la nueva contraseña con costo 12 (alta seguridad)
        $hashedPassword = password_hash($password2, PASSWORD_DEFAULT, array("cost" => 12));

        /**
         * Actualiza la contraseña en la base de datos y limpia los tokens de recuperación
         * Esto invalida el token actual y cualquier otro token pendiente
         */
        $update = $con->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expira = NULL WHERE documento = ?");
        $update->execute([$hashedPassword, $id_usuario]);

        // Verifica si la actualización fue exitosa
        if ($update->rowCount() > 0) {
            echo '<script>alert("Contraseña actualizada exitosamente.");</script>';
            echo '<script>window.location = "login";</script>';
        } else {
            echo '<script>alert("Error al actualizar la contraseña.");</script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <!-- Favicon del sistema -->
    <link rel="shortcut icon" href="../css/img/logo_sinfondo.png">
    <!-- Bootstrap Icons para iconos de mostrar/ocultar contraseña -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap CSS para estilos responsivos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Estilos personalizados para login y registro -->
    <link rel="stylesheet" href="../css/stylelog_re.css">
</head>
<!-- Auto-enfoque en el primer campo de contraseña al cargar la página -->
<body onload="form_con.password1.focus()">
<div class="contenido">
    <!-- ===== BOTÓN DE REGRESO ===== -->
    <!-- Permite al usuario regresar al login sin completar el cambio -->
    <div class="regresar">
        <a href="login" class="re">
            <i class="bi bi-house-door-fill"></i>
        </a>
    </div>
    
    <div class="conten_form">
        <div class="form-infor">
            <!-- ===== ENCABEZADO DEL FORMULARIO ===== -->
            <!-- Logo e información del proceso -->
            <img src="../css/img/logo_sinfondo.png" alt="logo" class="logo">
            <h2>Cambiar Contraseña</h2>
            <p>Por favor, ingresa tu nueva contraseña.</p>

            <!-- ===== FORMULARIO DE CAMBIO DE CONTRASEÑA ===== -->
            <form action="" method="POST" autocomplete="off" id="form_con">
                <div class="input-gruop">
                    <!-- ===== CAMPO: NUEVA CONTRASEÑA ===== -->
                    <div>
                        <div class="input_field_passw1" id="grupo_passw1">
                            <label for="password1" class="input_label"></label>
                            <!-- Icono para mostrar/ocultar contraseña -->
                            <i class="bi bi-eye-slash" id="showpass1" onclick="showpass1()"></i>
                            <input type="password" name="password1" id="password1" placeholder="Nueva contraseña">
                        </div>
                        <!-- Mensaje de validación para la primera contraseña -->
                        <div class="formulario_error_passw1" id="formulario_correcto_passw1">
                            <p class="validacion_passw1" id="validacion_passw1">La contraseña debe tener entre 8 a 14 caracteres, debe llevar una mayucula, minuscula y un caracter especial.</p>
                        </div>
                    </div>
                    
                    <!-- ===== CAMPO: CONFIRMAR CONTRASEÑA ===== -->
                    <div>
                        <div class="input_field_passw2" id="grupo_passw2">
                            <label for="password2"></label>
                            <!-- Icono para mostrar/ocultar confirmación de contraseña -->
                            <i class="bi bi-eye-slash" id="showpass2" onclick="showpass2()"></i>
                            <input type="password" name="password2" id="password2" placeholder="Confirmar contraseña">
                        </div>
                        <!-- Mensaje de validación para la confirmación -->
                        <div class="formulario_error_passw2" id="formulario_correcto_passw2">
                            <p class="validacion_passw2" id="validacion_passw2">Las contraseñas deben ser iguales...</p>
                        </div>
                    </div>
            
                    <!-- ===== MENSAJES DE ESTADO ===== -->
                    <!-- Mensaje de error general -->
                    <div>
                        <p class="formulario_error" id="formulario_error"><b>Error:</b> Existen campos vacios, asegurate de digitar la nueva contraseña correctamente.</p>
                    </div>
                    
                    <!-- ===== BOTÓN DE ENVÍO ===== -->
                    <div class="btn-field">
                        <button type="submit" name="enviar" id="enviar" value="Guardar" class="btn btn-primary">Cambiar contraseña</button>
                    </div>
                    
                    <!-- Mensaje de éxito -->
                    <p class="formulario_exito" id="formulario_exito">Cambio de contraseña exitoso...</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ===== REFERENCIAS A ELEMENTOS DEL DOM =====
        // Obtiene referencia al formulario principal
        const formulario = document.getElementById('form_con');
        // Obtiene todos los campos de entrada para aplicar validaciones
        const inputs = document.querySelectorAll('#form_con input')

        // ===== EXPRESIONES REGULARES PARA VALIDACIÓN =====
        /**
         * Define los patrones de validación para contraseñas seguras
         * Requiere: 8-14 caracteres, mayúscula, minúscula, número y carácter especial
         */
        const expresiones = {
            validapassword: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,14}$/
        }

        // ===== FUNCIÓN DE VALIDACIÓN DE CONTRASEÑAS =====
        /**
         * Maneja la validación en tiempo real de los campos de contraseña
         * Proporciona retroalimentación visual inmediata al usuario
         */
        const validpassw = (e) => {
            switch (e.target.name) {
                case "password1": 
                    // Valida la primera contraseña contra la expresión regular
                    if(expresiones.validapassword.test(e.target.value)){
                        // CONTRASEÑA VÁLIDA: Aplica estilos de éxito
                        document.getElementById('grupo_passw1').classList.remove('input_field_passw1_incorrecto')
                        document.getElementById('grupo_passw1').classList.add('input_field_passw1_correcto')
                        document.getElementById('validacion_passw1').style.opacity = 0;
                    }else{
                        // CONTRASEÑA INVÁLIDA: Aplica estilos de error
                        document.getElementById('grupo_passw1').classList.remove('input_field_passw1_correcto')
                        document.getElementById('grupo_passw1').classList.add('input_field_passw1_incorrecto')
                        document.getElementById('validacion_passw1').style.opacity = 1;
                    }
                    // Revalida la confirmación cuando cambia la contraseña principal
                    validarPassword2()
                break
                case "password2":
                    // Solo valida la confirmación para el segundo campo
                    validarPassword2()
                break
            }
        }

        // ===== VALIDACIÓN DE CONFIRMACIÓN DE CONTRASEÑA =====
        /**
         * Función especializada para verificar que ambas contraseñas coincidan
         * Se ejecuta cada vez que cambia cualquiera de los dos campos
         */
        const validarPassword2 = () => {
            // Obtiene referencias a ambos campos de contraseña
            const inputPassword1 = document.getElementById('password1');
            const inputPassword2 = document.getElementById('password2');
            const grupo = document.getElementById('grupo_passw2');
            const mensaje = document.getElementById('validacion_passw2');
            
            // Verifica si las contraseñas no coinciden o si el campo está vacío
            if (inputPassword1.value !== inputPassword2.value || inputPassword2.value.length === 0) {
                // CONTRASEÑAS NO COINCIDEN: Aplica estilos de error
                grupo.classList.add('input_field_passw2_incorrecto');
                grupo.classList.remove('input_field_passw2_correcto');
                mensaje.style.opacity = 1;
                mensaje.textContent = "Las contraseñas no coinciden...";
            } else {
                // CONTRASEÑAS COINCIDEN: Aplica estilos de éxito
                grupo.classList.remove('input_field_passw2_incorrecto');
                grupo.classList.add('input_field_passw2_correcto');
                mensaje.style.opacity = 0;
            }
        };

        // ===== CONFIGURACIÓN DE EVENT LISTENERS =====
        /**
         * Aplica validación en tiempo real a todos los campos
         * Valida durante la escritura (keyup) y al perder el foco (blur)
         */
        inputs.forEach((input) => {
            input.addEventListener('keyup', validpassw);
            input.addEventListener('blur', validpassw);
        });

        // ===== MANEJO DEL ENVÍO DEL FORMULARIO =====
        /**
         * Valida el formulario antes del envío y proporciona retroalimentación
         * Previene el envío si la validación falla
         */
        formulario.addEventListener('submit', (e) => {
            // Verifica si la primera contraseña cumple con los requisitos
            if (!expresiones.validapassword.test(inputs[0].value)) {
                // VALIDACIÓN FALLIDA: Previene el envío y muestra errores
                e.preventDefault();
                document.getElementById('grupo_passw1').classList.add('input_field_passw1_incorrecto')
                document.getElementById('validacion_passw1').style.opacity = 1;
                document.getElementById('formulario_error').style.opacity = 1;
                document.getElementById('formulario_error').style.color = "#d32f2f"
                document.getElementById('password1').focus();

                // Oculta el mensaje de error después de 3 segundos
                setTimeout(() => {
                document.getElementById('formulario_error').style.opacity = 0;
                }, 3000)
            }else{
                // VALIDACIÓN EXITOSA: Muestra mensaje de éxito
                document.getElementById('formulario_exito').style.opacity = 1;
                document.getElementById('formulario_exito').style.color = "#158000"

                // Oculta el mensaje de éxito después de 3 segundos
                setTimeout(() => {
                document.getElementById('formulario_exito').style.opacity = 0;
                }, 3000)
            }
        })   

        // ===== FUNCIONES PARA MOSTRAR/OCULTAR CONTRASEÑAS =====
        /**
         * Alterna la visibilidad de la primera contraseña
         * Cambia entre tipo 'password' y 'text' y actualiza el icono
         */
        function showpass1() {
            const passw = document.getElementById("password1");
            const icon = document.getElementById("showpass1");

            if (passw.type === "password") {
                // Muestra la contraseña
                passw.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                // Oculta la contraseña
                passw.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }

        /**
         * Alterna la visibilidad de la confirmación de contraseña
         * Funcionalidad idéntica a showpass1() pero para el segundo campo
         */
        function showpass2() {
            const passw = document.getElementById("password2");
            const icon = document.getElementById("showpass2");

            if (passw.type === "password") {
                // Muestra la contraseña
                passw.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                // Oculta la contraseña
                passw.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }
    </script>
</body>
</html>




  