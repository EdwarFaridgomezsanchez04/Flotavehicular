    $(document).ready(function() {
        const formulario = $('#formulario');
        const inputs = $('#formulario input, #formulario select, #formulario textarea');
        let enviando = false;

        const expresiones = {
            placa: /.+/,
            estado: /^(Bueno|Regular|Malo)$/,
            fecha: /^\d{4}-\d{2}-\d{2}$/,
            presion: /^(?:[0-9]|[1-9][0-9]|100)(?:\.[0-9]{1,2})?$/,
            kilometraje: /^[0-9]+$/,
            notas: /^[a-zA-Z0-9\s.,!?'-]{0,500}$/
        };

        const validarCampo = (expresion, input, grupo, mensaje) => {
            const grupoElemento = $(`#grupo_${grupo}`);
            const validacionMensaje = $(`#validacion_${grupo}`);

            if (expresion.test(input.val())) {
                grupoElemento.removeClass(`input_field_${grupo}_incorrecto`).addClass(`input_field_${grupo}_correcto`);
                validacionMensaje.css('opacity', '0');
                return true;
            } else {
                grupoElemento.removeClass(`input_field_${grupo}_correcto`).addClass(`input_field_${grupo}_incorrecto`);
                validacionMensaje.css('opacity', '1');
                return false;
            }
        };

        const validarFormulario = (e) => {
            switch (e.target.id) {
                case "placa":
                    validarCampo(expresiones.placa, $(e.target), 'placa', 'placa');
                    break;
                case "estado":
                    validarCampo(expresiones.estado, $(e.target), 'estado', 'estado');
                    break;
                case "ultimo_cambio":
                    if ($(e.target).val()) {
                        const inputDate = new Date($(e.target).val());
                        const today = new Date();
                        if (inputDate > today) {
                            $(`#grupo_${e.target.id}`).removeClass('input_field_ultimo_cambio_correcto').addClass('input_field_ultimo_cambio_incorrecto');
                            $(`#validacion_${e.target.id}`).css('opacity', '1');
                        } else {
                            $(`#grupo_${e.target.id}`).removeClass('input_field_ultimo_cambio_incorrecto').addClass('input_field_ultimo_cambio_correcto');
                            $(`#validacion_${e.target.id}`).css('opacity', '0');
                        }
                    }
                    break;
                case "presion_llantas":
                    validarCampo(expresiones.presion, $(e.target), 'presion_llantas', 'presion_llantas');
                    break;
                case "kilometraje_actual":
                    validarCampo(expresiones.kilometraje, $(e.target), 'kilometraje_actual', 'kilometraje_actual');
                    break;
                case "proximo_cambio_km":
                    validarCampo(expresiones.kilometraje, $(e.target), 'proximo_cambio_km', 'proximo_cambio_km');
                    break;
                case "proximo_cambio_fecha":
                    if ($(e.target).val()) {
                        const inputDate = new Date($(e.target).val());
                        const today = new Date();
                        if (inputDate < today) {
                            $(`#grupo_${e.target.id}`).removeClass('input_field_proximo_cambio_fecha_correcto').addClass('input_field_proximo_cambio_fecha_incorrecto');
                            $(`#validacion_${e.target.id}`).css('opacity', '1');
                        } else {
                            $(`#grupo_${e.target.id}`).removeClass('input_field_proximo_cambio_fecha_incorrecto').addClass('input_field_proximo_cambio_fecha_correcto');
                            $(`#validacion_${e.target.id}`).css('opacity', '0');
                        }
                    }
                    break;
                case "notas":
                    validarCampo(expresiones.notas, $(e.target), 'notas', 'notas');
                    break;
            }
        };

        inputs.on('keyup blur', validarFormulario);

        formulario.on('submit', function(e) {
            e.preventDefault();

            if (enviando) return;

            const isPlacaValid = validarCampo(expresiones.placa, $('#placa'), 'placa', 'placa');
            const isEstadoValid = validarCampo(expresiones.estado, $('#estado'), 'estado', 'estado');
            const isUltimoCambioValid = $('#ultimo_cambio').val() ? (new Date($('#ultimo_cambio').val()) <= new Date()) : true;
            const isPresionValid = validarCampo(expresiones.presion, $('#presion_llantas'), 'presion_llantas', 'presion_llantas');
            const isKilometrajeActualValid = validarCampo(expresiones.kilometraje, $('#kilometraje_actual'), 'kilometraje_actual', 'kilometraje_actual');
            const isProximoKmValid = validarCampo(expresiones.kilometraje, $('#proximo_cambio_km'), 'proximo_cambio_km', 'proximo_cambio_km');
            const isProximoFechaValid = $('#proximo_cambio_fecha').val() ? (new Date($('#proximo_cambio_fecha').val()) >= new Date()) : true;
            const isNotasValid = validarCampo(expresiones.notas, $('#notas'), 'notas', 'notas');

            if (isPlacaValid && isEstadoValid && isUltimoCambioValid && isPresionValid && isKilometrajeActualValid && isProximoKmValid && isProximoFechaValid && isNotasValid) {
                enviando = true;
                const submitBtn = formulario.find('button[type="submit"]');
                submitBtn.prop('disabled', true);

                const formdatos = new FormData(formulario[0]);
                $.ajax({
                    type: "POST",
                    url: "../AJAX/guardar_llantas.php",
                    data: formdatos,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        console.log("Respuesta del servidor:", response);
                        if (response.status === "success") {
                            $('#formulario_exito').css({ opacity: 1, color: "#158000" });
                            // Limpiar campos y estilos
                            formulario[0].reset(); // Limpia los campos
                            inputs.each(function () {
                                const id = $(this).attr('id');
                                $(`#grupo_${id}`).removeClass().addClass(`input_field_${id}`);
                                $(`#validacion_${id}`).css('opacity', '0');
                            });
                            setTimeout(() => {
                                window.location.href = '../historiales/ver_llantas.php';
                            }, 3000);
                        } else {
                            enviando = false;
                            submitBtn.prop('disabled', false);
                            $('#formulario_error').css({ opacity: 1, color: "#d32f2f" }).text("Error: " + response.message);
                            setTimeout(() => $('#formulario_error').css('opacity', 0), 3000);
                        }
                    },
                    error: function () {
                        enviando = false;
                        submitBtn.prop('disabled', false);
                        $('#formulario_error').css({ opacity: 1, color: "#d32f2f" }).text("Error en la conexión con el servidor.");
                        setTimeout(() => $('#formulario_error').css('opacity', 0), 3000);
                    }
                });
            } else {
                $('#formulario_error').css({ opacity: 1, color: "#d32f2f" }).text("Debe completar correctamente todos los campos obligatorios.");
                setTimeout(() => $('#formulario_error').css('opacity', 0), 3000);
            }
        });
    });
