/**
 * SCRIPT DE REGISTRO - VALIDACIÓN Y ENVÍO DE FORMULARIO
 * 
 * Este archivo maneja la validación del lado del cliente y el envío del formulario de registro.
 * Incluye validación en tiempo real, verificación de contraseñas coincidentes, y comunicación AJAX
 * con el servidor para procesar el registro de nuevos usuarios.
 */

// ===== REFERENCIAS A ELEMENTOS DEL DOM =====
// Obtiene referencia al formulario principal de registro
const formulario = document.getElementById('formulario');
// Obtiene todos los campos de entrada del formulario para aplicar validaciones
const inputs = document.querySelectorAll('#formulario input');

// ===== EXPRESIONES REGULARES PARA VALIDACIÓN =====
/**
 * Objeto que contiene todas las expresiones regulares utilizadas para validar
 * los diferentes campos del formulario de registro
 */
const expresiones = {
    // Valida documento: entre 6 y 10 dígitos numéricos
    validadocumento: /^\d{6,10}$/,
    // Valida nombre: letras (incluye acentos y ñ), espacios, entre 2 y 50 caracteres
    validanombre: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/,
    // Valida correo: formato específico para Gmail únicamente
    validacorreo: /^[a-zA-Z0-9._%+-]+@gmail\.com$/,
    // Valida contraseña: 8-14 caracteres, debe incluir mayúscula, minúscula, número y carácter especial
    validapassword: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,14}$/,
    // Valida celular: exactamente 10 dígitos numéricos
    validacelular: /^\d{10}$/
};

// ===== FUNCIÓN REUTILIZABLE PARA VALIDACIÓN DE CAMPOS =====
/**
 * Función genérica que valida un campo específico usando una expresión regular
 * y proporciona retroalimentación visual al usuario
 * 
 * @param {RegExp} expresion - Expresión regular para validar el campo
 * @param {HTMLElement} input - Elemento de entrada a validar
 * @param {string} grupo - Identificador del grupo de elementos relacionados
 * @param {string} mensaje - ID del elemento que muestra el mensaje de validación
 */
const validarcampo = (expresion, input, grupo, mensaje) => {
    // Obtiene el contenedor del grupo de elementos (input + mensaje)
    const grupoElemento = document.getElementById(`grupo_${grupo}`);
    // Obtiene el elemento que muestra el mensaje de validación
    const validacionMensaje = document.getElementById(mensaje);

    // Verifica si el valor del input cumple con la expresión regular
    if (expresion.test(input.value)) {
        // CAMPO VÁLIDO: Remueve clases de error y aplica clase de éxito
        grupoElemento.classList.remove(`input_field_${grupo}`);
        grupoElemento.classList.remove(`input_field_${grupo}_incorrecto`);
        grupoElemento.classList.add(`input_field_${grupo}_correcto`);
        // Oculta el mensaje de error
        validacionMensaje.style.opacity = 0;
    } else {
        // CAMPO INVÁLIDO: Remueve clase de éxito y aplica clase de error
        grupoElemento.classList.remove(`input_field_${grupo}_correcto`);
        grupoElemento.classList.add(`input_field_${grupo}_incorrecto`);
        // Muestra el mensaje de error
        validacionMensaje.style.opacity = 1;
    }
};

// ===== VALIDACIÓN ESPECÍFICA PARA CONFIRMACIÓN DE CONTRASEÑA =====
/**
 * Función especializada para validar que las dos contraseñas coincidan
 * Compara el valor de ambos campos de contraseña y proporciona retroalimentación
 */
const validarPassword2 = () => {
    // Obtiene referencias a ambos campos de contraseña
    const inputPassword1 = document.getElementById('con');
    const inputPassword2 = document.getElementById('con2');
    // Obtiene el contenedor del grupo de confirmación de contraseña
    const grupo = document.getElementById('grupo_con2');
    // Obtiene el elemento del mensaje de validación
    const mensaje = document.getElementById('validacion4');

    // Verifica si las contraseñas no coinciden o si el campo está vacío
    if (inputPassword1.value !== inputPassword2.value || inputPassword2.value.length === 0) {
        // CONTRASEÑAS NO COINCIDEN: Aplica estilos de error
        grupo.classList.add('input_field_con2_incorrecto');
        grupo.classList.remove('input_field_con2_correcto');
        // Muestra mensaje de error específico
        mensaje.style.opacity = 1;
        mensaje.textContent = "Las contraseñas no coinciden...";
    } else {
        // CONTRASEÑAS COINCIDEN: Aplica estilos de éxito
        grupo.classList.remove('input_field_con2_incorrecto');
        grupo.classList.add('input_field_con2_correcto');
        // Oculta el mensaje de error
        mensaje.style.opacity = 0;
    }
};

// ===== FUNCIÓN PRINCIPAL DE VALIDACIÓN DEL FORMULARIO =====
/**
 * Función que maneja la validación de todos los campos del formulario
 * Utiliza un switch para determinar qué validación aplicar según el campo
 * 
 * @param {Event} e - Evento disparado por el input (keyup o blur)
 */
const validarformulario = (e) => {
    // Determina qué campo se está validando basándose en su atributo 'name'
    switch (e.target.name) {
        case "doc":
            // Valida el campo de documento
            validarcampo(expresiones.validadocumento, e.target, 'doc', 'validacion');
            break;
        case "nom":
            // Valida el campo de nombre
            validarcampo(expresiones.validanombre, e.target, 'nom', 'validacion1');
            break;
        case "correo":
            // Valida el campo de correo electrónico
            validarcampo(expresiones.validacorreo, e.target, 'correo', 'validacion2');
            break;
        case "con":
            // Valida el campo de contraseña y también verifica la confirmación
            validarcampo(expresiones.validapassword, e.target, 'con', 'validacion3');
            validarPassword2(); // Revalida la confirmación cuando cambia la contraseña principal
            break;
        case "con2":
            // Valida únicamente la confirmación de contraseña
            validarPassword2();
            break;
        case "cel":
            // Valida el campo de celular
            validarcampo(expresiones.validacelular, e.target, 'cel', 'validacion5');
            break;
    }
};

// ===== CONFIGURACIÓN DE EVENT LISTENERS =====
/**
 * Aplica event listeners a todos los campos de entrada del formulario
 * para validación en tiempo real durante la escritura y al perder el foco
 */
inputs.forEach((input) => {
    // Valida mientras el usuario escribe (keyup)
    input.addEventListener('keyup', validarformulario);
    // Valida cuando el campo pierde el foco (blur)
    input.addEventListener('blur', validarformulario);
});

// ===== MANEJO DEL ENVÍO DEL FORMULARIO =====
/**
 * Event listener para el envío del formulario
 * Realiza validación final, envía datos via AJAX y maneja la respuesta
 */
formulario.addEventListener('submit', (e) => {
    // Previene el envío tradicional del formulario
    e.preventDefault();

    // ===== VALIDACIÓN FINAL DE TODOS LOS CAMPOS =====
    // Verifica cada campo individualmente antes del envío
    const docvalido = expresiones.validadocumento.test($('#doc').val());
    const nomvalido = expresiones.validanombre.test($('#nom').val());
    const corrvalido = expresiones.validacorreo.test($('#correo').val());
    const passvalido = expresiones.validapassword.test($('#con').val());
    // Valida que la confirmación de contraseña sea válida Y que coincida con la original
    const pass2valido = expresiones.validapassword.test($('#con2').val()) && $('#con').val() === $('#con2').val();
    const celvalido = expresiones.validacelular.test($('#cel').val());

    // ===== ENVÍO AJAX SI TODOS LOS CAMPOS SON VÁLIDOS =====
    if (docvalido && nomvalido && corrvalido && passvalido && pass2valido && celvalido) {
        // Realiza petición AJAX para registrar el usuario
        $.ajax({
            type: "POST",
            url: "../ajax/datos_registro.php", // Endpoint para procesar el registro
            data: {
                // Envía todos los datos del formulario
                doc: $('#doc').val(),
                nom: $('#nom').val(),
                correo: $('#correo').val(),
                con: $('#con').val(),
                con2: $('#con2').val(),
                cel: $('#cel').val(),
            },
            success: function(response) {
                // Log de la respuesta para debugging
                console.log("Respuesta del servidor:", response);
                
                if (response.status === "success") {
                    // ===== REGISTRO EXITOSO =====
                    // Muestra mensaje de éxito en color verde
                    document.getElementById('formulario_exito').style.opacity = 1;
                    document.getElementById('formulario_exito').style.color = "#158000";
                    // Redirecciona al login después de 1 segundo
                    setTimeout(() => {
                        window.location.href = "../login/login";
                    }, 1000);
                } else {
                    // ===== ERROR EN EL REGISTRO =====
                    // Muestra mensaje de error específico del servidor
                    document.getElementById('formulario_error').style.opacity = 1;
                    document.getElementById('formulario_error').textContent = "Error: " + response.message;
                    document.getElementById('formulario_error').style.color = "#d32f2f";
                    // Oculta el mensaje de error después de 3 segundos
                    setTimeout(() => {
                        document.getElementById('formulario_error').style.opacity = 0;
                    }, 3000);
                }
            }
        });
    } else {
        // ===== MANEJO DE ERRORES DE VALIDACIÓN =====
        // Muestra mensaje de error general
        document.getElementById('formulario_error').style.opacity = 1;
        document.getElementById('formulario_error').style.color = "#d32f2f";

        // Oculta el mensaje de error después de 3 segundos
        setTimeout(() => {
            document.getElementById('formulario_error').style.opacity = 0;
        }, 3000);

        // ===== ENFOQUE EN EL PRIMER CAMPO INVÁLIDO =====
        // Determina cuál es el primer campo que no cumple la validación
        // y enfoca en él para mejorar la experiencia del usuario
        if (!docvalido) {
            $('#doc').focus();
            validarcampo(expresiones.validadocumento, document.getElementById('doc'), 'doc', 'validacion');
        } else if (!nomvalido) {
            $('#nom').focus();
            validarcampo(expresiones.validanombre, document.getElementById('nom'), 'nom', 'validacion1');
        } else if (!corrvalido) {
            $('#correo').focus();
            validarcampo(expresiones.validacorreo, document.getElementById('correo'), 'correo', 'validacion2');
        } else if (!passvalido) {
            $('#con').focus();
            validarcampo(expresiones.validapassword, document.getElementById('con'), 'con', 'validacion3');
        } else if (!pass2valido) {
            $('#con2').focus();
            validarcampo(expresiones.validapassword, document.getElementById('con2'), 'con2', 'validacion4');
        } else if (!celvalido) {
            $('#cel').focus();
            validarcampo(expresiones.validacelular, document.getElementById('cel'), 'cel', 'validacion5');
        }
    }
});
  