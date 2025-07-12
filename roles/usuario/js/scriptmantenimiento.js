const formulario = document.getElementById('formulario');
const inputs = document.querySelectorAll('#formulario input');
const selects = document.querySelectorAll('#formulario select');
const textarea = document.querySelector('#observaciones');

// Expresiones regulares para validaciones
const expresiones = {
    placa: /^[A-Z0-9]{3,8}$/, // Letras y números, entre 3 y 8 caracteres
    kilometraje: /^\d{1,7}$/, // Solo números, máximo 7 dígitos
    observaciones: /^[a-zA-Z0-9\s.,!?'-]{1,500}$/, // Letras, números y puntuación básica, máximo 500 caracteres
};

// Objeto para rastrear el estado de los campos (inicializamos todos como no válidos)
const campos = {
    placa: false,
    id_tipo_mantenimiento: false,
    fecha_programada: false,
    fecha_realizada: false, // Opcional, pero inicia como no válido
    kilometraje_actual: false, // Opcional, pero inicia como no válido
    proximo_cambio_km: false, // Opcional, pero inicia como no válido
    proximo_cambio_fecha: false, // Opcional, pero inicia como no válido
    observaciones: false, // Opcional, pero inicia como no válido
    trabajos: false
};

// Función para validar el formulario
const validarFormulario = (e) => {
    switch (e.target.name) {
        case "placa":
            validarSelect(e.target, 'placa', 'Seleccione un vehículo.');
            break;
        case "id_tipo_mantenimiento":
            validarSelect(e.target, 'id_tipo_mantenimiento', 'Seleccione un tipo de mantenimiento.');
            break;
        case "fecha_programada":
            validarFecha(e.target, 'fecha_programada', 'Seleccione una fecha válida.', false);
            break;
        case "fecha_realizada":
            validarFecha(e.target, 'fecha_realizada', 'Fecha no puede ser futura.', true);
            break;
        case "kilometraje_actual":
            validarCampo(expresiones.kilometraje, e.target, 'kilometraje_actual', 'Ingrese un número positivo.', true);
            break;
        case "proximo_cambio_km":
            validarCampo(expresiones.kilometraje, e.target, 'proximo_cambio_km', 'Ingrese un número positivo.', true);
            break;
        case "proximo_cambio_fecha":
            validarFechaFutura(e.target, 'proximo_cambio_fecha', 'Fecha no puede ser pasada.', true);
            break;
        case "observaciones":
            validarCampo(expresiones.observaciones, e.target, 'observaciones', 'Máximo 500 caracteres, solo letras, números y puntuación básica.', true);
            break;
    }
};

// Validar campos de texto o número
const validarCampo = (expresion, input, campo, mensaje, opcional = false) => {
    const grupo = document.getElementById(`grupo_${campo}`);
    const validacion = document.getElementById(`validacion_${campo}`);

    if (input.value.trim() === '') {
        if (opcional) {
            // Si es opcional y está vacío, se considera válido para el envío, pero visualmente se muestra como incorrecto
            grupo.classList.remove(`input_field_${campo}_correcto`);
            grupo.classList.add(`input_field_${campo}_incorrecto`);
            validacion.textContent = mensaje;
            validacion.style.opacity = '1';
            campos[campo] = true; // Válido para el envío, pero visualmente incorrecto
        } else {
            // Si no es opcional y está vacío, es inválido
            grupo.classList.remove(`input_field_${campo}_correcto`);
            grupo.classList.add(`input_field_${campo}_incorrecto`);
            validacion.textContent = mensaje;
            validacion.style.opacity = '1';
            campos[campo] = false;
        }
    } else if (expresion.test(input.value.trim())) {
        grupo.classList.remove(`input_field_${campo}_incorrecto`);
        grupo.classList.add(`input_field_${campo}_correcto`);
        validacion.style.opacity = '0';
        campos[campo] = true;
    } else {
        grupo.classList.remove(`input_field_${campo}_correcto`);
        grupo.classList.add(`input_field_${campo}_incorrecto`);
        validacion.textContent = mensaje;
        validacion.style.opacity = '1';
        campos[campo] = false;
    }
};

// Validar selects
const validarSelect = (select, campo, mensaje) => {
    const grupo = document.getElementById(`grupo_${campo}`);
    const validacion = document.getElementById(`validacion_${campo}`);

    if (select.value === '') {
        grupo.classList.remove(`input_field_${campo}_correcto`);
        grupo.classList.add(`input_field_${campo}_incorrecto`);
        validacion.textContent = mensaje;
        validacion.style.opacity = '1';
        campos[campo] = false;
    } else {
        grupo.classList.remove(`input_field_${campo}_incorrecto`);
        grupo.classList.add(`input_field_${campo}_correcto`);
        validacion.style.opacity = '0';
        campos[campo] = true;
    }
};

// Validar fechas (no futuras para fecha_realizada)
const validarFecha = (input, campo, mensaje, opcional = false) => {
    const grupo = document.getElementById(`grupo_${campo}`);
    const validacion = document.getElementById(`validacion_${campo}`);
    const fecha = new Date(input.value);
    const hoy = new Date();

    if (input.value.trim() === '') {
        if (opcional) {
            grupo.classList.remove(`input_field_${campo}_correcto`);
            grupo.classList.add(`input_field_${campo}_incorrecto`);
            validacion.textContent = mensaje;
            validacion.style.opacity = '1';
            campos[campo] = true; // Válido para el envío, pero visualmente incorrecto
        } else {
            grupo.classList.remove(`input_field_${campo}_correcto`);
            grupo.classList.add(`input_field_${campo}_incorrecto`);
            validacion.textContent = mensaje;
            validacion.style.opacity = '1';
            campos[campo] = false;
        }
    } else if (campo === 'fecha_realizada' && fecha > hoy) {
        grupo.classList.remove(`input_field_${campo}_correcto`);
        grupo.classList.add(`input_field_${campo}_incorrecto`);
        validacion.textContent = mensaje;
        validacion.style.opacity = '1';
        campos[campo] = false;
    } else {
        grupo.classList.remove(`input_field_${campo}_incorrecto`);
        grupo.classList.add(`input_field_${campo}_correcto`);
        validacion.style.opacity = '0';
        campos[campo] = true;
    }
};

// Validar fechas futuras (para proximo_cambio_fecha)
const validarFechaFutura = (input, campo, mensaje, opcional = false) => {
    const grupo = document.getElementById(`grupo_${campo}`);
    const validacion = document.getElementById(`validacion_${campo}`);
    const fecha = new Date(input.value);
    const hoy = new Date();

    if (input.value.trim() === '') {
        if (opcional) {
            grupo.classList.remove(`input_field_${campo}_correcto`);
            grupo.classList.add(`input_field_${campo}_incorrecto`);
            validacion.textContent = mensaje;
            validacion.style.opacity = '1';
            campos[campo] = true; // Válido para el envío, pero visualmente incorrecto
        } else {
            grupo.classList.remove(`input_field_${campo}_correcto`);
            grupo.classList.add(`input_field_${campo}_incorrecto`);
            validacion.textContent = mensaje;
            validacion.style.opacity = '1';
            campos[campo] = false;
        }
    } else if (fecha < hoy) {
        grupo.classList.remove(`input_field_${campo}_correcto`);
        grupo.classList.add(`input_field_${campo}_incorrecto`);
        validacion.textContent = mensaje;
        validacion.style.opacity = '1';
        campos[campo] = false;
    } else {
        grupo.classList.remove(`input_field_${campo}_incorrecto`);
        grupo.classList.add(`input_field_${campo}_correcto`);
        validacion.style.opacity = '0';
        campos[campo] = true;
    }
};
// Añadir eventos a los inputs
inputs.forEach((input) => {
    input.addEventListener('blur', validarFormulario);
    input.addEventListener('input', validarFormulario);
});

selects.forEach((select) => {
    select.addEventListener('blur', validarFormulario);
    select.addEventListener('change', validarFormulario);
});

textarea.addEventListener('blur', validarFormulario);
textarea.addEventListener('input', validarFormulario);

// Validar al enviar el formulario
formulario.addEventListener('submit', function (e) {
    e.preventDefault(); // Prevenir envío por defecto

    // Verificar si todos los campos requeridos están validados
    if (campos.placa && campos.id_tipo_mantenimiento && campos.fecha_programada) {
        const formdatos = new FormData(formulario);

        $.ajax({
            type: "POST",
            url: "../AJAX/guardar_mantenimiento.php",
            data: formdatos,
            contentType: false,
            processData: false,
            success: function (response) {
                console.log("Respuesta del servidor:", response);
                
                if (response.status === "success") {
                    document.getElementById('formulario_exito').style.opacity = 1;
                    document.getElementById('formulario_exito').style.color = "#158000";
                    // Limpiar el formulario antes de redirigir
                    formulario.reset();
                    // Eliminar clases de validación visual
                    Object.keys(campos).forEach(campo => {
                        const grupo = document.getElementById(`grupo_${campo}`);
                        if (grupo) {
                            grupo.classList.remove(`input_field_${campo}_correcto`);
                            grupo.classList.remove(`input_field_${campo}_incorrecto`);
                        }
                        campos[campo] = false;
                    });
                    setTimeout(() => {
                        window.location.href = '../historiales/ver_mantenimiento.php';
                    }, 3000);
                } else {
                    document.getElementById('formulario_error').style.opacity = 1;
                    document.getElementById('formulario_error').textContent = "Error: " + response.message;
                    document.getElementById('formulario_error').style.color = "#d32f2f";
                    setTimeout(() => {
                        document.getElementById('formulario_error').style.opacity = 0;
                    }, 3000);
                }
            },
            error: function () {
                document.getElementById('formulario_error').style.opacity = 1;
                document.getElementById('formulario_error').textContent = "Error en la conexión con el servidor.";
                document.getElementById('formulario_error').style.color = "#d32f2f";
                setTimeout(() => {
                    document.getElementById('formulario_error').style.opacity = 0;
                }, 3000);
            }
        });
    } else {
        document.getElementById('formulario_error').style.opacity = 1;
        document.getElementById('formulario_error').style.color = "#d32f2f";
        document.getElementById('formulario_error').textContent = "Debe completar correctamente todos los campos obligatorios.";

        setTimeout(() => {
            document.getElementById('formulario_error').style.opacity = 0;
        }, 3000);
    }
});
