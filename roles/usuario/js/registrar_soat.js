const formulario = document.getElementById('formsoat');
const inputs = document.querySelectorAll('#formsoat input, #formsoat select');

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


const validarfecha = (input, grupo, mensaje) => {
    const fechaValor = new Date(input.value);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0); // Eliminar la hora para comparación precisa
    fechaValor.setHours(0, 0, 0, 0);

    if (input.value !== "") {
        if (input.name === "fechaExpedicion" && fechaValor > hoy) {
            document.getElementById(grupo).classList.remove(`${grupo}_correcto`);
            document.getElementById(grupo).classList.add(`${grupo}_incorrecto`);
            document.getElementById(mensaje).style.opacity = 1;
            document.getElementById(mensaje).innerText = "La fecha de expedición no puede ser futura al día de hoy.";
            return false;
        }

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

const validarFechasRelacionadas = () => {
    const fechaExpedicionInput = document.getElementById('fechaExpedicion');
    const fechaVencimientoInput = document.getElementById('fechaVencimiento');
    const grupoVencimiento = 'grupo_vencimiento';
    const mensajeVencimiento = 'validacion2';

    const fechaExpedicion = new Date(fechaExpedicionInput.value);
    const fechaVencimiento = new Date(fechaVencimientoInput.value);

    // Normalizar horas
    fechaExpedicion.setHours(0, 0, 0, 0);
    fechaVencimiento.setHours(0, 0, 0, 0);

    // Calcular fecha esperada de vencimiento (1 año después)
    const fechaEsperada = new Date(fechaExpedicion);
    fechaEsperada.setFullYear(fechaEsperada.getFullYear() + 1);

    if (fechaVencimiento.getTime() === fechaEsperada.getTime()) {
        document.getElementById(grupoVencimiento).classList.remove(`${grupoVencimiento}_incorrecto`);
        document.getElementById(grupoVencimiento).classList.add(`${grupoVencimiento}_correcto`);
        document.getElementById(mensajeVencimiento).style.opacity = 0;
        return true;
    } else {
        document.getElementById(grupoVencimiento).classList.remove(`${grupoVencimiento}_correcto`);
        document.getElementById(grupoVencimiento).classList.add(`${grupoVencimiento}_incorrecto`);
        document.getElementById(mensajeVencimiento).style.opacity = 1;
        document.getElementById(mensajeVencimiento).innerText = "La fecha de vencimiento debe ser exactamente 1 año después de la expedición.";
        return false;
    }
};



const validarformulario = (e) => {
    switch (e.target.name) {
        case "placa":
            validarSelect(e.target, 'grupo_placa', 'validacion');
            break;
        case "fechaExpedicion":
            validarfecha(e.target, 'grupo_expedicion', 'validacion1');
            validarFechasRelacionadas()
            break;
        case "fechaVencimiento":
            validarfecha(e.target, 'grupo_vencimiento', 'validacion2');
            validarFechasRelacionadas()
            break;

        case "aseguradora":
            validarSelect(e.target, 'grupo_aseguradora', 'validacion3');
            break;

        case "estado":
            validarSelect(e.target, 'grupo_estado', 'validacion4');
            break;
    }
}


inputs.forEach((input) => {
    input.addEventListener('keyup', validarformulario);
    input.addEventListener('blur', validarformulario);
});


formulario.addEventListener('submit', function (e) {
    e.preventDefault(); // Evita envío por defecto

    const placa = document.getElementById('placa').value;
    const fechaExpedicion = document.getElementById('fechaExpedicion').value;
    const fechaVencimiento = document.getElementById('fechaVencimiento').value;
    const aseguradora = document.getElementById('aseguradora').value;
    const estado = document.getElementById('estado').value;

    // Validación de todos los campos
    const validPlaca = validarSelect(document.getElementById('placa'), 'grupo_placa', 'validacion');
    const validAseguradora = validarSelect(document.getElementById('aseguradora'), 'grupo_aseguradora', 'validacion3');
    const validFechaex = validarfecha(document.getElementById('fechaExpedicion'), 'grupo_expedicion', 'validacion1');
    const validFechaven = validarfecha(document.getElementById('fechaVencimiento'), 'grupo_vencimiento', 'validacion2');
    const validestado = validarSelect(document.getElementById('estado'), 'grupo_estado', 'validacion4');

    if (validPlaca && validAseguradora && validFechaex && validFechaven && validestado) {
        const formdatos = new FormData(formulario)
        
        $.ajax({
            type: "POST",
            url: "../AJAX/guardar_soat.php",
            data: formdatos,
            contentType: false,
            processData: false,
             success: function(response) {
                console.log("Respuesta del servidor:", response);
                if (response.status === "success") {
                    document.getElementById('formulario_exito').style.opacity = 1;
                    document.getElementById('formulario_exito').style.color = "#158000";
                    setTimeout(() => {
                        window.location.href = '../historiales/ver_soat.php';
                    }, 3000);
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