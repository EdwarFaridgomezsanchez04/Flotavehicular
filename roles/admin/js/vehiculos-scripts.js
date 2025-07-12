fetch('procesar_vehiculo.php', {
    method: 'POST',
    body: formData
})
.then(response => {
    // Verificar si la respuesta es JSON válido
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
        throw new Error('Respuesta no válida del servidor');
    }
    return response.json();
})
.then(data => {
    if (data.success) {
        mostrarAlerta('Vehículo agregado exitosamente', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalAgregarVehiculo')).hide();
        setTimeout(() => location.reload(), 1500);
    } else {
        // Verificar si es un error de sesión
        if (data.redirect) {
            alert(data.message);
            window.location.href = data.redirect;
            return;
        }
        mostrarAlerta(data.message || 'Error al agregar el vehículo', 'danger');
    }
})
.catch(error => {
    console.error('Error:', error);
    mostrarAlerta('Error de conexión o sesión expirada', 'danger');
})