// Obtiene la referencia al formulario de login principal
const formulario = document.getElementById('formulario');
// Obtiene todos los elementos input dentro del formulario
const inputs = document.querySelectorAll('#formulario input');

// Define las expresiones regulares para validar los campos del formulario de login
const expresiones = {
    // Valida documento: solo números entre 6 y 10 dígitos
    validadocumento: /^\d{6,10}$/,
    // Valida contraseña: mínimo 8, máximo 14 caracteres, debe incluir:
    // - Al menos una minúscula (?=.*[a-z])
    // - Al menos una mayúscula (?=.*[A-Z])
    // - Al menos un dígito (?=.*\d)
    // - Al menos un carácter especial (?=.*[\W_])
    validapassword: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,14}$/
};

// Función reutilizable para validar cualquier campo del formulario
const validarCampo = (expresion, input, grupo, mensaje) => {
    // Obtiene el elemento contenedor del grupo de input
    const grupoElemento = document.getElementById(`grupo_${grupo}`);
    // Obtiene el elemento que muestra el mensaje de validación
    const validacionMensaje = document.getElementById(mensaje);

    // Si el valor del input cumple con la expresión regular
    if (expresion.test(input.value)) {
        // Remueve las clases de estado incorrecto
        grupoElemento.classList.remove(`input_field_${grupo}`);
        grupoElemento.classList.remove(`input_field_${grupo}_incorrecto`);
        // Agrega la clase de estado correcto
        grupoElemento.classList.add(`input_field_${grupo}_correcto`);
        // Oculta el mensaje de validación
        validacionMensaje.style.opacity = 0;
    } else {
        // Si no cumple con la validación
        // Remueve la clase de estado correcto
        grupoElemento.classList.remove(`input_field_${grupo}_correcto`);
        // Agrega la clase de estado incorrecto
        grupoElemento.classList.add(`input_field_${grupo}_incorrecto`);
        // Muestra el mensaje de validación
        validacionMensaje.style.opacity = 1;
    }
};

// Función principal para validar el formulario según el campo que se está editando
const validarformulario = (e) => {
    // Evalúa qué campo se está validando según su atributo 'name'
    switch (e.target.name) {
        case "doc": // Validación del campo documento
            // Llama a la función reutilizable con los parámetros específicos para documento
            validarCampo(expresiones.validadocumento, e.target, 'doc', 'validacion');
            break;
        case "passw": // Validación del campo contraseña
            // Llama a la función reutilizable con los parámetros específicos para contraseña
            validarCampo(expresiones.validapassword, e.target, 'passw', 'validacion2');
            break;
    }
};

// Agrega event listeners a todos los inputs para validación en tiempo real
inputs.forEach((input) => {
    // Valida cuando el usuario suelta una tecla
    input.addEventListener('keyup', validarformulario);
    // Valida cuando el campo pierde el foco
    input.addEventListener('blur', validarformulario);
});



// Agrega listener para el evento de envío del formulario
formulario.addEventListener('submit', (e) => {
    // Previene el envío tradicional del formulario (recarga de página)
    e.preventDefault();

    // Realiza validaciones finales antes de enviar usando jQuery
    const docvalido = expresiones.validadocumento.test($('#doc').val());
    const passvalido = expresiones.validapassword.test($('#passw').val());

    // Si ambas validaciones son exitosas
    if (docvalido && passvalido) {
        // Realiza petición AJAX para autenticar al usuario
        $.ajax({
            type: "POST", // Método HTTP POST
            url: "../includes/inicio.php", // Archivo PHP que procesará la autenticación
            // Datos a enviar al servidor
            data: {
                doc: $('#doc').val(), // Documento del usuario
                passw: $('#passw').val(), // Contraseña del usuario
                log: true // Flag para indicar que es un intento de login
            },
            dataType: 'json', // Especifica que se espera una respuesta JSON
            // Función que se ejecuta si la petición es exitosa
            success: function(response) {
                // Registra la respuesta del servidor en la consola para debugging
                console.log("Respuesta del servidor:", response);

                // Si la autenticación fue exitosa
                if (response.status === "success") {
                    // Muestra mensaje de éxito
                    document.getElementById('formulario_exito').style.opacity = 1;
                    document.getElementById('formulario_exito').style.color = "#158000";

                    // Después de 2 segundos, redirige según el rol del usuario
                    setTimeout(() => {
                        // Oculta el mensaje de éxito
                        document.getElementById('formulario_exito').style.opacity = 0;

                        // Redirección dependiendo del rol del usuario autenticado
                        if (response.rol === "admin") {
                            // Redirige al panel de administrador
                            location.href = "../roles/admin/index";
                        } else if (response.rol === "superadmin") {
                            // Redirige al panel de superadmin
                            location.href = "../roles/superadmin/index";
                        } else if (response.rol === "usuario") {
                            // Redirige al panel de usuario
                            location.href ="../roles/usuario/index";
                        }

                    }, 2000);

                } else {
                    // Si la autenticación falló
                    // Muestra mensaje de error específico del servidor
                    document.getElementById('formulario_error').style.opacity = 1;
                    document.getElementById('formulario_error').style.color = "#d32f2f";
                    document.getElementById('formulario_error').innerText = response.message; 
                    // Enfoca el campo documento para que el usuario corrija
                    $('#doc').focus();
                    // Oculta el mensaje de error después de 3 segundos
                    setTimeout(() => {
                        document.getElementById('formulario_error').style.opacity = 0;
                    }, 3000);
                }
            },
            // Función que se ejecuta si hay un error en la petición AJAX
            error: function(xhr, status, error) {
                // Registra el error completo en la consola para debugging
                console.error("Error al enviar el formulario:", xhr.responseText);
                // Muestra mensaje de error de conexión
                document.getElementById('formulario_error').style.opacity = 1;
                document.getElementById('formulario_error').style.color = "#d32f2f";
                document.getElementById('formulario_error').innerText = "Error de conexión con el servidor";
                // Oculta el mensaje de error después de 3 segundos
                setTimeout(() => {
                    document.getElementById('formulario_error').style.opacity = 0;
                }, 3000);
            }
        });
    } else {
        // Si las validaciones del lado cliente fallan
        // Muestra mensaje de error general
        document.getElementById('formulario_error').style.opacity = 1;
        document.getElementById('formulario_error').style.color = "#d32f2f";

        // Oculta el mensaje de error después de 3 segundos
        setTimeout(() => {
            document.getElementById('formulario_error').style.opacity = 0;
        }, 3000);
        
        // Enfoca el primer campo inválido y aplica validación visual
        if (!docvalido) {
            // Si el documento no es válido, enfoca ese campo
            $('#doc').focus();
            // Aplica validación visual al campo documento
            validarCampo(expresiones.validadocumento, document.getElementById('doc'), 'doc', 'validacion1');
        } else if (!passvalido) {
            // Si la contraseña no es válida, enfoca ese campo
            $('#passw').focus();
            // Aplica validación visual al campo contraseña
            validarCampo(expresiones.validapassword, document.getElementById('passw'), 'passw', 'validacion2');
        }

        
    }
});
