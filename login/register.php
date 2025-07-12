<?php
// Incluir el archivo de conexión a la base de datos
require_once('../conecct/conex.php');

// Crear una nueva instancia de la clase Database y establecer conexión
$db = new Database();
$con = $db->conectar();

// Iniciar sesión PHP para manejar variables de sesión
session_start();

// Variables de configuración para nuevos usuarios
$estado = 1;  // Estado activo por defecto para nuevos usuarios
$rol = 2;     // Rol de usuario regular (no administrador)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    
    <!-- Favicon del sitio web -->
    <link rel="shortcut icon" href="../css/img/logo_sinfondo.png">
    
    <!-- Bootstrap Icons para iconografía -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Bootstrap CSS para estilos y componentes responsivos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome para iconos adicionales -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    
    <!-- jQuery para manipulación del DOM y AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Estilos CSS personalizados para login y registro -->
    <link rel="stylesheet" href="../css/stylelog_re.css">
</head>

<!-- El body se carga con focus automático en el campo documento -->
<body onload="formulario.doc.focus()">
    <div class="content">
        <!-- Botón de regreso al inicio -->
        <div class="regresar">
            <a href="../index" class="re">
                <i class="bi bi-house-door-fill"></i>
            </a>
        </div>
        
        <!-- Contenedor principal del formulario -->
        <div class="conten_form">
            <div class="form_infor">
                <!-- Logo de la aplicación -->
                <img src="../css/img/logo_sinfondo.png" alt="logo" class="logo">
                
                <!-- Título del formulario -->
                <h1 class="titulo">Registro</h1>
                
                <!-- Formulario de registro de usuarios -->
                <!-- action: vacío para procesar en la misma página -->
                <!-- method: POST para envío seguro de datos -->
                <!-- enctype: multipart/form-data para manejo de archivos -->
                <!-- autocomplete: off para mayor seguridad -->
                <form action="" method="post" id="formulario" enctype="multipart/form-data" autocomplete="off">
                    <div class="input_grupo">
                        
                        <!-- Campo de documento de identidad -->
                        <div>
                            <div class="input_field_doc" id="grupo_doc">
                                <label for="doc"></label>
                                <!-- Icono de persona con tarjeta -->
                                <i class="bi bi-person-vcard"></i>
                                <!-- Input numérico para documento de identidad -->
                                <input type="number" name="doc" id="doc" placeholder="Documento">
                            </div>
                            
                            <!-- Mensaje de validación para el documento -->
                            <div class="formulario_error_doc">
                                <p class="validacion" id="validacion">
                                    El documento solo debe contener numeros y el minimo son 6 digitos y el maximo son 10 dígitos.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Campo de nombre completo -->
                        <div>
                            <div class="input_field_nom" id="grupo_nom">
                                <label for="nom"></label>
                                <!-- Icono de tarjeta con encabezado -->
                                <i class="bi bi-card-heading"></i>
                                <!-- Input de texto para nombre completo -->
                                <input type="text" name="nom" id="nom" placeholder="Nombre">
                            </div>
                            
                            <!-- Mensaje de validación para el nombre -->
                            <div class="formulario_error_nom">
                                <p class="validacion1" id="validacion1">
                                    Ingrese el nombre completo sin caracteres especiales
                                </p>
                            </div>
                        </div>
                        
                        <!-- Campo de correo electrónico -->
                        <div>
                            <div class="input_field_correo" id="grupo_correo">
                                <label for="correo"></label>
                                <!-- Icono de sobre -->
                                <i class="bi bi-envelope-fill"></i>
                                <!-- Input de email con validación HTML5 -->
                                <input type="email" name="correo" id="correo" placeholder="Correo">
                            </div>
                            
                            <!-- Mensaje de validación para el correo -->
                            <div class="formulario_error_correo">
                                <p class="validacion2" id="validacion2">
                                    Ingrese un correo electrónico válido (ejemplo@gmail.com).
                                </p>
                            </div>
                        </div>
                        
                        <!-- Campo de contraseña -->
                        <div>
                            <div class="input_field_con" id="grupo_con">
                                <label for="con"></label>
                                <!-- Icono para mostrar/ocultar contraseña -->
                                <i class="bi bi-eye-slash" id="showpass1" onclick="showpass1()"></i>
                                <!-- Input de contraseña con límites de longitud -->
                                <input type="password" name="con" id="con" placeholder="Contraseña" value="" maxlength="14" minlength="8">
                            </div>
                            
                            <!-- Mensaje de validación para la contraseña -->
                            <div class="formulario_error_con">
                                <p class="validacion3" id="validacion3">
                                    La contraseña debe tener entre 8 a 14 caracteres, debe llevar una mayucula, minuscula y un caracter especial.
                                </p>
                            </div>
                        </div>
                        
                        <!-- Campo de confirmación de contraseña -->
                        <div>
                            <div class="input_field_con2" id="grupo_con2">
                                <label for="con2"></label>
                                <!-- Icono para mostrar/ocultar confirmación de contraseña -->
                                <i class="bi bi-eye-slash" id="showpass2" onclick="showpass2()"></i>
                                <!-- Input para confirmar contraseña -->
                                <input type="password" name="con2" id="con2" placeholder="Confirmar Contraseña" value="" maxlength="15" minlength="8">
                            </div>
                            
                            <!-- Mensaje de validación para confirmación de contraseña -->
                            <div class="formulario_error_con2">
                                <p class="validacion4" id="validacion4">
                                    Las contraseñas deben ser iguales...
                                </p>
                            </div>
                        </div>
                        
                        <!-- Campo de número telefónico -->
                        <div>
                            <div class="input_field_cel" id="grupo_cel">
                                <label for="cel"></label>
                                <!-- Icono de teléfono -->
                                <i class="bi bi-telephone-fill"></i>
                                <!-- Input numérico para teléfono -->
                                <input type="number" name="cel" id="cel" placeholder="Telefono">
                            </div>
                            
                            <!-- Mensaje de validación para el teléfono -->
                            <div class="formulario_error_cel">
                                <p class="validacion5" id="validacion5">
                                    El numero telefonico solo debe contener numeros y el maximo son 10 dígitos.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mensaje de error general del formulario -->
                    <div>
                        <p class="formulario_error" id="formulario_error">
                            <b>Error:</b> Por favor rellena el formulario correctamente.
                        </p>
                    </div>
                    
                    <!-- Botón de envío del formulario -->
                    <div class="btn-field">
                        <button type="submit" name="enviar" id="enviar" value="Guardar" class="btn btn-primary">
                            Registrarse
                        </button>
                    </div>
                    
                    <!-- Mensaje de éxito -->
                    <p class="formulario_exito" id="formulario_exito">Registro exitoso...</p>
                    
                    <!-- Enlace para usuarios que ya tienen cuenta -->
                    <p>¿Ya tienes una cuenta?<a class="res" href="login">Inicia Sesion</a></p>
                </form>
            </div>
        </div>
    </div>
    
    <!-- JavaScript para funcionalidades de mostrar/ocultar contraseñas -->
    <script>
        /**
         * Función para alternar la visibilidad de la contraseña principal
         * Cambia entre tipo 'password' y 'text' del input
         * Actualiza el icono correspondiente (ojo abierto/cerrado)
         */
        function showpass1() {
            // Obtener referencias a los elementos del DOM
            const passw = document.getElementById("con");
            const icon = document.getElementById("showpass1");

            // Verificar el tipo actual del input
            if (passw.type === "password") {
                // Mostrar contraseña como texto plano
                passw.type = "text";
                // Cambiar icono a ojo abierto
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                // Ocultar contraseña
                passw.type = "password";
                // Cambiar icono a ojo cerrado
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }

        /**
         * Función para alternar la visibilidad de la confirmación de contraseña
         * Funcionalidad idéntica a showpass1() pero para el campo de confirmación
         */
        function showpass2() {
            // Obtener referencias a los elementos del DOM
            const passw = document.getElementById("con2");
            const icon = document.getElementById("showpass2");

            // Verificar el tipo actual del input
            if (passw.type === "password") {
                // Mostrar contraseña como texto plano
                passw.type = "text";
                // Cambiar icono a ojo abierto
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                // Ocultar contraseña
                passw.type = "password";
                // Cambiar icono a ojo cerrado
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }
    </script>
    
    <!-- Incluir script de validación y manejo del formulario -->
    <script src="../js/scriptregistro.js"></script>
</body>
</html>
