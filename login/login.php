<?php
// Incluir el archivo de conexión a la base de datos
require_once('../conecct/conex.php');

// Crear una nueva instancia de la clase Database y establecer conexión
$db = new Database();
$con = $db->conectar();

// Iniciar sesión PHP para manejar variables de sesión
session_start();

// Variable de estado (posiblemente para control de estado de usuario)
$estado = 1;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    
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
            <div class="form-info">
                
                <div class="form-infor">
                    <!-- Logo de la aplicación -->
                    <img src="../css/img/logo_sinfondo.png" alt="logo" class="logo">
                    
                    <!-- Título del formulario -->
                    <h1 class="titu">Login</h1>
                
                    <!-- Formulario de inicio de sesión -->
                    <!-- action: envía datos a inicio.php para procesamiento -->
                    <!-- method: POST para envío seguro de credenciales -->
                    <!-- enctype: multipart/form-data para manejo de archivos (aunque no se usen aquí) -->
                    <!-- autocomplete: off para mayor seguridad -->
                    <form action="../includes/inicio.php" method="POST" id="formulario" enctype="multipart/form-data" autocomplete="off">

                        <div class="input-gruop">
                            <!-- Campo de documento -->
                            <div>
                                <div class="input_field_doc" id="grupo_doc">
                                    <label for="doc" class="input_label"></label>
                                    <!-- Icono de persona con tarjeta -->
                                    <i class="bi bi-person-vcard"></i>
                                    <!-- Input numérico para documento de identidad -->
                                    <input type="number" name="doc" id="doc" placeholder="Documento">
                                </div>
                                
                                <!-- Mensaje de validación para el documento -->
                                <div class="formulario_error_doc" id="formulario_correcto_doc">
                                    <p class="validacion" id="validacion">
                                        El documento solo debe contener numeros y el minimo son 6 digitos y el maximo son 10 dígitos.
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Campo de contraseña -->
                            <div>
                                <div class="input_field_passw" id="grupo_passw">
                                    <label for="passw"></label>
                                    <!-- Icono para mostrar/ocultar contraseña -->
                                    <i class="bi bi-eye-slash" id="showpass1" onclick="showpass1()"></i>
                                    <!-- Input de contraseña con límites de longitud -->
                                    <input type="password" name="passw" id="passw" placeholder="Contraseña" maxlength="14" minlength="8">
                                </div>
                                
                                <!-- Mensaje de validación para la contraseña -->
                                <div class="formulario_error_passw" id="formulario_correcto_passw">
                                    <p class="validacion2" id="validacion2">
                                        La contraseña debe tener entre 8 a 14 caracteres, debe llevar una mayucula, minuscula y un caracter especial.
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
                            <button type="submit" name="log" id="log" value="Log" class="btn btn-primary">
                                Log in
                            </button>
                        </div>
                        
                        <!-- Mensaje de éxito -->
                        <p class="formulario_exito" id="formulario_exito">Iniciando sesion...</p>
                        
                        <!-- Enlaces de navegación -->
                        <!-- Enlace para recuperación de contraseña -->
                        <a href="recovery">
                            <label>Olvidaste tu contraseña?</label>
                        </a> 
                        
                        <!-- Enlace para registro de nuevos usuarios -->
                        <a href="register">
                            <label class="col">No tienes cuenta, Registrate</label>
                        </a> 
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript para funcionalidad de mostrar/ocultar contraseña -->
    <script>
        /**
         * Función para alternar la visibilidad de la contraseña
         * Cambia entre tipo 'password' y 'text' del input
         * Actualiza el icono correspondiente (ojo abierto/cerrado)
         */
        function showpass1() {
            // Obtener referencias a los elementos del DOM
            const passw = document.getElementById("passw");
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
    </script>
</body>

<!-- Incluir script de validación y manejo del formulario -->
<script src="../js/scriptlogin.js"></script>
</html>
