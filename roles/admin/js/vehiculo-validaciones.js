/**
 * VALIDACIONES PARA MODALES DE VEHÍCULOS
 * 
 * Este archivo maneja la validación del lado del cliente para los modales
 * de agregar y editar vehículos. Incluye validación en tiempo real y
 * verificación de campos obligatorios antes del envío.
 * 
 * NOTA: Este archivo solo maneja validaciones visuales en tiempo real.
 * Las validaciones finales del formulario se manejan en el archivo principal.
 */

// ===== EXPRESIONES REGULARES PARA VALIDACIÓN =====
const expresionesVehiculo = {
    // Valida placa: 3 letras + 3 números (formato colombiano)
    validaplaca: /^[A-Z]{3}[0-9]{3}$/,
    // Valida año: entre 1900 y año actual + 1
    validaanio: /^(19[0-9]{2}|20[0-9]{2}|202[0-9]|203[0-9])$/,
    // Valida modelo: letras, números, espacios, guiones, entre 2 y 50 caracteres
    validamodelo: /^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s\-]{2,50}$/,
    // Valida kilometraje: números positivos hasta 999999
    validakilometraje: /^[0-9]{1,6}$/
};

// ===== FUNCIÓN REUTILIZABLE PARA VALIDACIÓN DE CAMPOS =====
const validarCampoVehiculo = (expresion, input, grupo, mensaje) => {
    const grupoElemento = document.getElementById(`grupo_${grupo}`);
    const validacionMensaje = document.getElementById(mensaje);

    if (!grupoElemento || !validacionMensaje) {
        console.warn(`Elementos no encontrados: grupo_${grupo} o ${mensaje}`);
        return;
    }

    if (expresion.test(input.value)) {
        // CAMPO VÁLIDO
        grupoElemento.classList.remove(`input_field_${grupo}_incorrecto`);
        grupoElemento.classList.add(`input_field_${grupo}_correcto`);
        validacionMensaje.style.opacity = 0;
    } else {
        // CAMPO INVÁLIDO
        grupoElemento.classList.remove(`input_field_${grupo}_correcto`);
        grupoElemento.classList.add(`input_field_${grupo}_incorrecto`);
        validacionMensaje.style.opacity = 1;
    }
};

// ===== VALIDACIÓN ESPECÍFICA PARA PLACA =====
const validarPlaca = (input) => {
    const valor = input.value.toUpperCase();
    input.value = valor; // Convierte a mayúsculas automáticamente
    
    const grupo = input.id.includes('edit') ? 'placa_edit' : 'placa';
    const mensaje = input.id.includes('edit') ? 'validacion_placa_edit' : 'validacion_placa';
    
    if (expresionesVehiculo.validaplaca.test(valor)) {
        validarCampoVehiculo(expresionesVehiculo.validaplaca, input, grupo, mensaje);
    } else {
        const grupoElemento = document.getElementById(`grupo_${grupo}`);
        const validacionMensaje = document.getElementById(mensaje);
        
        if (grupoElemento && validacionMensaje) {
            grupoElemento.classList.remove(`input_field_${grupo}_correcto`);
            grupoElemento.classList.add(`input_field_${grupo}_incorrecto`);
            validacionMensaje.style.opacity = 1;
        }
    }
};

// ===== VALIDACIÓN ESPECÍFICA PARA AÑO =====
const validarAnio = (input) => {
    const valor = parseInt(input.value);
    const anioActual = new Date().getFullYear();
    const anioMaximo = anioActual + 1;
    
    const grupo = input.id.includes('edit') ? 'anio_edit' : 'anio';
    const mensaje = input.id.includes('edit') ? 'validacion_anio_edit' : 'validacion_anio';
    
    // Validar rango de años
    if (valor >= 1900 && valor <= anioMaximo) {
        validarCampoVehiculo(/^\d{4}$/, input, grupo, mensaje);
    } else {
        const grupoElemento = document.getElementById(`grupo_${grupo}`);
        const validacionMensaje = document.getElementById(mensaje);
        
        if (grupoElemento && validacionMensaje) {
            grupoElemento.classList.remove(`input_field_${grupo}_correcto`);
            grupoElemento.classList.add(`input_field_${grupo}_incorrecto`);
            validacionMensaje.style.opacity = 1;
            validacionMensaje.textContent = `El año debe estar entre 1900 y ${anioMaximo}`;
        }
    }
};

// ===== VALIDACIÓN ESPECÍFICA PARA KILOMETRAJE =====
const validarKilometraje = (input) => {
    const valor = parseInt(input.value);
    const grupo = input.id.includes('edit') ? 'kilometraje_edit' : 'kilometraje';
    const mensaje = input.id.includes('edit') ? 'validacion_kilometraje_edit' : 'validacion_kilometraje';
    
    if (valor >= 0 && valor <= 999999) {
        validarCampoVehiculo(expresionesVehiculo.validakilometraje, input, grupo, mensaje);
    } else {
        const grupoElemento = document.getElementById(`grupo_${grupo}`);
        const validacionMensaje = document.getElementById(mensaje);
        
        if (grupoElemento && validacionMensaje) {
            grupoElemento.classList.remove(`input_field_${grupo}_correcto`);
            grupoElemento.classList.add(`input_field_${grupo}_incorrecto`);
            validacionMensaje.style.opacity = 1;
            validacionMensaje.textContent = "El kilometraje debe estar entre 0 y 999,999";
        }
    }
};

// ===== FUNCIÓN PRINCIPAL DE VALIDACIÓN PARA MODAL AGREGAR =====
const validarFormularioAgregar = (e) => {
    switch (e.target.name) {
        case "placa":
            validarPlaca(e.target);
            break;
        case "anio":
            validarAnio(e.target);
            break;
        case "modelo":
            validarCampoVehiculo(expresionesVehiculo.validamodelo, e.target, 'modelo', 'validacion_modelo');
            break;
        case "kilometraje_actual":
            validarKilometraje(e.target);
            break;
    }
};

// ===== FUNCIÓN PRINCIPAL DE VALIDACIÓN PARA MODAL EDITAR =====
const validarFormularioEditar = (e) => {
    switch (e.target.name) {
        case "placa":
            validarPlaca(e.target);
            break;
        case "anio":
            validarAnio(e.target);
            break;
        case "modelo":
            validarCampoVehiculo(expresionesVehiculo.validamodelo, e.target, 'modelo_edit', 'validacion_modelo_edit');
            break;
        case "kilometraje_actual":
            validarKilometraje(e.target);
            break;
    }
};

// ===== CONFIGURACIÓN DE EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Validaciones de vehículos cargadas');
    
    // Event listeners para modal agregar
    const inputsAgregar = document.querySelectorAll('#formAgregarVehiculo input[type="text"], #formAgregarVehiculo input[type="number"]');
    inputsAgregar.forEach((input) => {
        input.addEventListener('keyup', validarFormularioAgregar);
        input.addEventListener('blur', validarFormularioAgregar);
    });

    // Event listeners para modal editar
    const inputsEditar = document.querySelectorAll('#editarVehiculoForm input[type="text"], #editarVehiculoForm input[type="number"]');
    inputsEditar.forEach((input) => {
        input.addEventListener('keyup', validarFormularioEditar);
        input.addEventListener('blur', validarFormularioEditar);
    });

    // Validación de selects obligatorios
    const selectsObligatorios = document.querySelectorAll('select[required]');
    selectsObligatorios.forEach((select) => {
        select.addEventListener('change', function() {
            if (this.value === '') {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });

    // Limpiar validaciones al abrir modales
    const modales = document.querySelectorAll('.modal');
    modales.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            limpiarValidaciones(this);
        });
    });
});

// ===== FUNCIÓN PARA LIMPIAR VALIDACIONES =====
function limpiarValidaciones(modal) {
    // Limpiar clases de validación de inputs
    const inputs = modal.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.classList.remove('is-valid', 'is-invalid');
        input.classList.remove('input_field_placa_correcto', 'input_field_placa_incorrecto');
        input.classList.remove('input_field_anio_correcto', 'input_field_anio_incorrecto');
        input.classList.remove('input_field_modelo_correcto', 'input_field_modelo_incorrecto');
        input.classList.remove('input_field_kilometraje_correcto', 'input_field_kilometraje_incorrecto');
    });

    // Ocultar mensajes de validación
    const mensajes = modal.querySelectorAll('[id^="validacion_"]');
    mensajes.forEach(mensaje => {
        mensaje.style.opacity = 0;
    });

    // Limpiar mensaje de error general
    const mensajeError = modal.querySelector('#mensaje-error-vehiculo');
    if (mensajeError) {
        mensajeError.style.display = 'none';
    }
}

// ===== FUNCIÓN PARA PREVIEW DE IMAGEN =====
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
} 