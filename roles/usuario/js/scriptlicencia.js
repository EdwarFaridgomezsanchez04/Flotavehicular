document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('license-form');

    if (!form) {
        console.error('Formulario no encontrado.');
        return;
    }

    const fields = {
        'categoria': form.querySelector('#categoria'),
        'fecha-nacimiento': form.querySelector('#fecha-nacimiento'),
        'fecha-expedicion': form.querySelector('#fecha-expedicion'),
        'observaciones': form.querySelector('#observaciones')
    };

    const errorElements = {
        'categoria': form.querySelector('#error-categoria'),
        'fecha-nacimiento': form.querySelector('#error-fecha-nacimiento'),
        'fecha-expedicion': form.querySelector('#error-fecha-expedicion'),
        'observaciones': form.querySelector('#error-observaciones')
    };

    const validators = {
        'categoria': (field) => !field.value ? 'Selecciona una categoría.' : '',
        'fecha-nacimiento': (field) => {
            if (!field.value) return 'Fecha de nacimiento requerida.';
            const dob = new Date(field.value);
            const today = new Date();
            if (isNaN(dob.getTime())) return 'Fecha inválida.';
            if (dob > today) return 'No puede ser futura.';
            return '';
        },
        'fecha-expedicion': (field) => {
            if (!field.value) return 'Fecha de expedición requerida.';
            const date = new Date(field.value);
            const today = new Date();
            if (isNaN(date.getTime())) return 'Fecha inválida.';
            if (date > today) return 'No puede ser futura.';
            return '';
        },
        'observaciones': (field) => field.value.length > 500 ? 'Máximo 500 caracteres.' : ''
    };

    function validateField(id) {
        const field = fields[id];
        const errorElement = errorElements[id];
        const error = validators[id](field);
        errorElement.textContent = error;
        return !error;
    }

    Object.keys(fields).forEach(id => {
        const field = fields[id];
        field.addEventListener('change', () => {
            validateField(id);
            calcularVigencia();
        });
        field.addEventListener('blur', () => {
            validateField(id);
        });
    });

    form.addEventListener('submit', (e) => {
        let isValid = true;
        Object.keys(fields).forEach(id => {
            if (!validateField(id)) isValid = false;
        });
        if (!isValid) e.preventDefault();
    });

    // ---- Cálculo de vigencia por AJAX ----
    function calcularVigencia() {
        const categoria = fields['categoria'].value;
        const fechaNacimiento = fields['fecha-nacimiento'].value;

        if (!categoria || !fechaNacimiento) {
            document.getElementById('vigencia-resultado').innerText = "Seleccione categoría y fecha de nacimiento";
            return;
        }

        fetch('calcular_vigencia.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id_categoria=${encodeURIComponent(categoria)}&fecha_nacimiento=${encodeURIComponent(fechaNacimiento)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById('vigencia-resultado').innerText = data.error;
                document.getElementById('vigencia-resultado').style.color = 'red';
            } else {
                document.getElementById('vigencia-resultado').innerText = `Vigencia: ${data.vigencia_años} años`;
                document.getElementById('vigencia-resultado').style.color = 'green';
            }
        })
        .catch(error => {
            console.error('Error en el cálculo de vigencia:', error);
            document.getElementById('vigencia-resultado').innerText = 'Error al calcular vigencia';
            document.getElementById('vigencia-resultado').style.color = 'red';
        });
    }
});
