// Obtiene la referencia al elemento textarea con ID "mensa" para el mensaje
const mensaje = document.getElementById("mensa")

// Obtiene la referencia al formulario principal
const form = document.getElementById('form')
// Obtiene todos los elementos input dentro del formulario
const inputs = document.querySelectorAll('#form input')

// Define las expresiones regulares para validar cada campo del formulario
const expresiones = {
    // Valida nombres: solo letras, acentos, ñ y espacios, entre 2 y 50 caracteres
    validanombre:  /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/,
    // Valida apellidos: misma regla que nombres
    validaapellido: /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/,
    // Valida correos: solo acepta direcciones de Gmail
    validacorreo:  /^[a-zA-Z0-9._%+-]+@gmail\.com$/,
    // Valida párrafos: cualquier carácter entre 10 y 500 caracteres
    valodaparrafo: /^[\s\S]{10,500}$/
}

// Función principal para validar los campos del formulario en tiempo real
const validarform = (e) => {
    // Evalúa qué campo se está validando según su atributo 'name'
    switch (e.target.name) {
        case "nom": // Validación del campo nombre
            // Si el nombre cumple con la expresión regular
            if(expresiones.validanombre.test(e.target.value)){
                // Oculta el mensaje de advertencia
                document.getElementById('warnings').style.opacity = 0;
                // Cambia el borde a verde para indicar validez
                document.getElementById('nom').style.border = "solid 3px green";;
                // Restaura el color del texto a negro
                document.getElementById('nom').style.color = "black";

            }else{
                // Si no es válido, muestra advertencia con fondo rojo
                document.getElementById('warnings').style.background = "#d32f2f";
                document.getElementById('warnings').style.opacity = 1;
                // Cambia el borde a rojo para indicar error
                document.getElementById('nom').style.border = "solid 3px #d32f2f";
                // Cambia el color del texto a rojo
                document.getElementById('nom').style.color = "#d32f2f";
            }
        break
        case "ape": // Validación del campo apellido
            // Si el apellido cumple con la expresión regular
            if(expresiones.validaapellido.test(e.target.value)){
                // Oculta el mensaje de advertencia específico para apellidos
                document.getElementById('warnings1').style.opacity = 0;
                // Cambia el borde a verde para indicar validez
                document.getElementById('ape').style.border = "solid 3px green";;
                // Restaura el color del texto a negro
                document.getElementById('ape').style.color = "black";
            }else{
                // Si no es válido, muestra advertencia con fondo rojo
                document.getElementById('warnings1').style.background = "#d32f2f";
                document.getElementById('warnings1').style.opacity = 1;
                // Cambia el borde a rojo para indicar error
                document.getElementById('ape').style.border = "solid 3px #d32f2f";
                // Cambia el color del texto a rojo
                document.getElementById('ape').style.color = "#d32f2f";
            }
        break
        case "corre": // Validación del campo correo electrónico
            // Si el correo cumple con la expresión regular (solo Gmail)
            if(expresiones.validacorreo.test(e.target.value)){
                // Oculta el mensaje de advertencia específico para correos
                document.getElementById('warnings2').style.opacity = 0;
                // Cambia el borde a verde para indicar validez
                document.getElementById('corre').style.border = "solid 3px green";
                // Restaura el color del texto a negro
                document.getElementById('corre').style.color = "black";

            }else{
                // Si no es válido, muestra advertencia con fondo rojo
                document.getElementById('warnings2').style.background = "#d32f2f";
                document.getElementById('warnings2').style.opacity = 1;
                // Cambia el borde a rojo para indicar error
                document.getElementById('corre').style.border = "solid 3px #d32f2f";
                // Cambia el color del texto a rojo
                document.getElementById('corre').style.color = "#d32f2f";
            }
        break
        
    }
}

// Obtiene referencias específicas para la validación del textarea
const textarea = document.getElementById('mensa');
const warnings = document.getElementById('warnings3');

// Agrega un listener para validar el textarea en tiempo real
textarea.addEventListener('input', () => {
    // Obtiene el valor del textarea sin espacios al inicio y final
    const value = textarea.value.trim();
    // Valida si el mensaje tiene menos de 10 caracteres
    if (value.length < 10) {
        // Muestra mensaje de error específico para longitud mínima
        warnings.textContent = "El mensaje debe tener al menos 10 caracteres.";
        warnings.style.background = "#d32f2f";
        textarea.style.color = "#d32f2f";
        textarea.style.border = "solid 3px #d32f2f";
        warnings.style.opacity = 1;
        
        
    } else if (value.length > 500) {
        // Valida si el mensaje excede los 500 caracteres
        warnings.textContent = "El mensaje no debe exceder los 500 caracteres.";
        warnings.style.background = "#d32f2f";
        textarea.style.color = "#d32f2f";
        textarea.style.border = "solid 3px #d32f2f";
        warnings.style.opacity = 1;
    } else {
        // Si la longitud es válida, oculta advertencias y marca como válido
        warnings.style.background = "none";
        warnings.style.opacity = 0;
        textarea.style.color = "black";
        textarea.style.border = "solid 3px green";
        
    }
});

// Agrega event listeners a todos los inputs para validación en tiempo real
inputs.forEach((input) => {
    // Valida cuando el usuario suelta una tecla
    input.addEventListener('keyup', validarform)
    // Valida cuando el campo pierde el foco
    input.addEventListener('blur', validarform)
})

// Obtiene referencias para mostrar mensajes de resultado del envío
const mensajeerror = document.getElementById('warnings4')
const mensajecorrecto = document.getElementById('warnings5')

// Agrega listener para el evento de envío del formulario
form.addEventListener('submit', function(event) {
    // Previene el envío tradicional del formulario (recarga de página)
    event.preventDefault();

    // Realiza validaciones finales antes de enviar usando jQuery
    const nombreValido = expresiones.validanombre.test($('#nom').val());
    const apellidoValido = expresiones.validaapellido.test($('#ape').val());
    const correoValido = expresiones.validacorreo.test($('#corre').val());
    // Obtiene el texto del mensaje sin espacios extras
    const mensajeTexto = $('#mensa').val().trim();
    // Valida que el mensaje esté dentro del rango permitido
    const mensajeValido = mensajeTexto.length >= 10 && mensajeTexto.length <= 500;

    // Si todas las validaciones son exitosas
    if (nombreValido && apellidoValido && correoValido && mensajeValido) {
        // Realiza petición AJAX para enviar los datos
        $.ajax({
            type: "POST", // Método HTTP POST
            url: "../ajax/datos_contacto.php", // Archivo PHP que procesará los datos
            // Datos a enviar al servidor
            data: {
                nom: $('#nom').val(),
                ape: $('#ape').val(),
                corre: $('#corre').val(),
                mensa: $('#mensa').val()
            },
            // Función que se ejecuta si el envío es exitoso
            success: function(response) {
                // Registra la respuesta del servidor en la consola
                console.log("Respuesta del servidor:", response);
                // Muestra mensaje de éxito al usuario
                mensajecorrecto.textContent = "Formulario enviado correctamente. Tu mensaje nos sera de mucha ayuda.";
                mensajecorrecto.style.opacity = 1;
                mensajecorrecto.style.background = "#388e3c";

                // Limpia el formulario después del envío exitoso
                $('#form')[0].reset();
                // Restaura los estilos originales de los campos
                $('#nom, #ape, #corre, #mensa').css({
                    border: "1px solid #ccc",
                    color: "black"
                });

                // Oculta el mensaje de éxito después de 3 segundos
                setTimeout(() => {
                    mensajecorrecto.style.opacity = 0;
                }, 3000);
            },
            // Función que se ejecuta si hay un error en el envío
            error: function(xhr, status, error) {
                // Registra el error en la consola para debugging
                console.error("Error al enviar el formulario:", error);
                // Muestra mensaje de error al usuario
                mensajeerror.textContent = "Ocurrió un error al enviar el formulario.";
                mensajeerror.style.opacity = 1;
                mensajeerror.style.background = "#d32f2f";
                
                // Oculta el mensaje de error después de 3 segundos
                setTimeout(() => {
                    mensajeerror.style.opacity = 0;
                }, 3000);
            }
        });
    } else {
        // Si las validaciones fallan, muestra mensaje de error
        mensajeerror.textContent = "Por favor llena el formulario correctamente.";
        mensajeerror.style.opacity = 1;
        mensajeerror.style.background = "#d32f2f";
        // Enfoca el primer campo (nombre) para que el usuario corrija
        document.getElementById('nom').focus();
        // Marca el campo nombre como inválido visualmente
        document.getElementById('warnings').style.background = "#d32f2f";
        document.getElementById('warnings').style.opacity = 1;
        document.getElementById('nom').style.border = "solid 3px #d32f2f";
        document.getElementById('nom').style.color = "#d32f2f";
        // Oculta el mensaje de error después de 3 segundos
        setTimeout(() => {
            mensajeerror.style.opacity = 0;
        }, 3000);
    }
});
