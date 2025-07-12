const formulario = document.getElementById('form_vehiculo');
const inputs = document.querySelectorAll('#form_vehiculo input, #form_vehiculo select');

const expresiones = {
    placa_general: /^([A-Z]{3}\d{3}|[A-Z]{3}\d{2}[A-Z])$/,  // ABC123 o ABC12D
    modelo: /^\d{4}$/,
    kilometraje: /^\d+$/,
};


const validarCampo = (expresion, input, grupo, mensaje) => {
    if (expresion.test(input.value)) {
        document.getElementById(grupo).classList.remove(`${grupo}`);
        document.getElementById(grupo).classList.add(`${grupo}_correcto`);
        document.getElementById(mensaje).style.opacity = 0;
        return true;
    } else {
        document.getElementById(grupo).classList.remove(`${grupo}_correcto`);
        document.getElementById(grupo).classList.add(`${grupo}_incorrecto`);
        document.getElementById(mensaje).style.opacity = 1;
        return false;
    }
};

const validarSelect = (input, grupo, mensaje) => {
    if (input.value !== "") {
        document.getElementById(grupo).classList.remove(`${grupo}`);
        document.getElementById(grupo).classList.add(`${grupo}_correcto`);
        document.getElementById(mensaje).style.opacity = 0;
        return true;
    } else {
        document.getElementById(grupo).classList.remove(`${grupo}_correcto`);
        document.getElementById(grupo).classList.add(`${grupo}_incorrecto`);
        document.getElementById(mensaje).style.opacity = 1;
        return false;
    }
};

const validarFecha = (input, grupo, mensaje) => {
    const fechaSeleccionada = new Date(input.value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    if (input.value !== "" && fechaSeleccionada <= hoy) {
        document.getElementById(grupo).classList.remove(`${grupo}_incorrecto`);
        document.getElementById(grupo).classList.add(`${grupo}_correcto`);
        document.getElementById(mensaje).style.opacity = 0;
        return true;
    } else {
        document.getElementById(grupo).classList.remove(`${grupo}_correcto`);
        document.getElementById(grupo).classList.add(`${grupo}_incorrecto`);
        document.getElementById(mensaje).style.opacity = 1;
        return false;
    }
};

const validarImagen = (input, grupo, mensaje) => {
    const archivo = input.files[0];
    const extensionesPermitidas = /(\.jpg|\.jpeg|\.png)$/i;
    const maxSizeMB = 2;
    const maxSizeBytes = maxSizeMB * 1024 * 1024;
    if (archivo && extensionesPermitidas.test(archivo.name) && archivo.size <= maxSizeBytes) {
        document.getElementById(grupo).classList.remove(`${grupo}_incorrecto`);
        document.getElementById(grupo).classList.add(`${grupo}_correcto`);
        document.getElementById(mensaje).style.opacity = 0;
        return true;
    } else {
        document.getElementById(grupo).classList.remove(`${grupo}_correcto`);
        document.getElementById(grupo).classList.add(`${grupo}_incorrecto`);
        document.getElementById(mensaje).style.opacity = 1;
        return false;
    }
};

window.addEventListener('load', () => {
    const fechaInput = document.getElementById('fecha');
    if (fechaInput) {
        const hoy = new Date();
        // Formatear la fecha a YYYY-MM-DD para que sea compatible con input type="date"
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0'); // Enero es 0
        const dd = String(hoy.getDate()).padStart(2, '0');

        fechaInput.value = `${yyyy}-${mm}-${dd}`;
    }
});



const validarformulario = (e) => {
    switch (e.target.name) {
        case "tipo_vehiculo":
            validarSelect(e.target, 'grupo_tipo', 'validacion');
            break;
        case "id_marca":
            validarSelect(e.target, 'grupo_marca', 'validacion1');
            break;
        case "placa":
            validarCampo(expresiones.placa_general, e.target, 'grupo_placa', 'validacion2');
            break;

        case "modelo":
            const modeloInput = document.getElementById('modelo');
            const modeloValue = parseInt(modeloInput.value);

            let modeloValido = false;
            if (!isNaN(modeloValue) && modeloValue >= 1900 && modeloValue <= 2026) {
                document.getElementById('grupo_modelo').classList.remove(`grupo_modelo_incorrecto`);
                document.getElementById('grupo_modelo').classList.add(`grupo_modelo_correcto`);
                document.getElementById('validacion3').style.opacity = 0;
                modeloValido = true;
            } else {
                document.getElementById('grupo_modelo').classList.remove(`grupo_modelo_correcto`);
                document.getElementById('grupo_modelo').classList.add(`grupo_modelo_incorrecto`);
                document.getElementById('validacion3').style.opacity = 1;
            }

            break;
        case "kilometraje":
            validarCampo(expresiones.kilometraje, e.target, 'grupo_km', 'validacion4');
            break;
        case "estado":
            validarSelect(e.target, 'grupo_estado', 'validacion5');
            break;
        case "fecha":
            validarFecha(e.target, 'grupo_fecha', 'validacion6');
            break;
    }
};

inputs.forEach((input) => {
    input.addEventListener('keyup', validarformulario);
    input.addEventListener('blur', validarformulario);
});

const placaInput = document.getElementById('placa');

placaInput.addEventListener('input', () => {
    placaInput.value = placaInput.value.toUpperCase();
});


document.getElementById("foto_vehiculo").addEventListener('change', validarformulario);

formulario.addEventListener('submit', (e) => {
    e.preventDefault();
    const tipoValido = validarSelect(document.getElementById('tipo_vehiculo'), 'grupo_tipo', 'validacion');
    const marcaValido = validarSelect(document.getElementById('id_marca'), 'grupo_marca', 'validacion1');
    const placaValido = validarCampo(expresiones.placa_general, document.getElementById('placa'), 'grupo_placa', 'validacion2' )
    const modeloValido = validarCampo(expresiones.modelo, document.getElementById('modelo'), 'grupo_modelo', 'validacion3');
    const kmValido = validarCampo(expresiones.kilometraje, document.getElementById('kilometraje'), 'grupo_km', 'validacion4');
    const estadoValido = validarSelect(document.getElementById('estado'), 'grupo_estado', 'validacion5');
    const fechaValido = validarFecha(document.getElementById('fecha'), 'grupo_fecha', 'validacion6');

    if (tipoValido && marcaValido && placaValido && modeloValido && kmValido && estadoValido && fechaValido) {
        const formData = new FormData(formulario);
        
      
        $.ajax({
            type: "POST",
            url: "../AJAX/registro_vehiculo.php",
            data: formData,
            contentType: false,
            processData: false,
             success: function(response) {
                console.log("Respuesta del servidor:", response);
                if (response.status === "success") {
                    document.getElementById('formulario_exito').style.opacity = 1;
                    document.getElementById('formulario_exito').style.color = "#158000";
                    setTimeout(() => {
                        window.location.reload();
                    });
                } else {
                    document.getElementById('formulario_error').style.opacity = 1;
                    document.getElementById('formulario_error').textContent = "Error: " + response.message;
                    document.getElementById('formulario_error').style.color = "#d32f2f";
                    setTimeout(() => {
                        document.getElementById('formulario_error').style.opacity = 0;
                    }, 3000);
                }
            }
            
        });
    }else{
        document.getElementById('formulario_error').style.opacity = 1;
        document.getElementById('formulario_error').style.color = "#d32f2f";


        setTimeout(() => {
            document.getElementById('formulario_error').style.opacity = 0;
        }, 3000);
    }
});
