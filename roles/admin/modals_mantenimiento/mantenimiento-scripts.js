document.addEventListener('DOMContentLoaded', function () {
  // Validaciones
  function validarPlaca(placa) {
    return placa.trim() !== '';
  }

  function validarTipo(tipo) {
    return tipo.trim() !== '';
  }

  function validarFecha(fecha) {
    return fecha.trim() !== '';
  }

  function validarKilometraje(kilometraje) {
    if (kilometraje === '') return true; // Es opcional
    return !isNaN(kilometraje) && kilometraje >= 0;
  }

  // Botón Agregar Mantenimiento
  document.getElementById('btnAgregarMantenimiento').addEventListener('click', function (e) {
    e.preventDefault();
    document.getElementById('agregarMantenimientoForm').reset();
    new bootstrap.Modal(document.getElementById('agregarMantenimientoModal')).show();
  });

  // Botones Editar Mantenimiento
  document.querySelectorAll('.edit-mantenimiento').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      if (!id) {
        alert('Error: No se pudo obtener el ID del mantenimiento');
        return false;
      }

      fetch(`modals_mantenimiento/get_mantenimiento.php?id=${encodeURIComponent(id)}`)
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          return response.json();
        })
        .then(data => {
            if (data.success) {
            const mantenimiento = data.data;
            document.getElementById('idMantenimientoEditar').value = id;
            document.getElementById('placaEditar').value = mantenimiento.placa || '';
            document.getElementById('tipoMantenimientoEditar').value = mantenimiento.id_tipo_mantenimiento || '';
            document.getElementById('fechaProgramadaEditar').value = mantenimiento.fecha_programada || '';
            document.getElementById('fechaRealizadaEditar').value = mantenimiento.fecha_realizada || '';
            document.getElementById('kilometrajeEditar').value = mantenimiento.kilometraje_actual || '';
            document.getElementById('observacionesEditar').value = mantenimiento.observaciones || '';
            new bootstrap.Modal(document.getElementById('editarMantenimientoModal')).show();
            } else {
            alert('Error al cargar los datos del mantenimiento');
            }
        })
        .catch(error => {
            console.error('Error:', error);
          alert('Error de conexión al cargar los datos del mantenimiento');
        });
    });
  });

  // Botón Actualizar Mantenimiento
  document.getElementById('actualizarMantenimiento').addEventListener('click', function (e) {
    e.preventDefault();
    const form = document.getElementById('editarMantenimientoForm');

    const placa = document.getElementById('placaEditar').value;
    const tipo = document.getElementById('tipoMantenimientoEditar').value;
    const fechaProgramada = document.getElementById('fechaProgramadaEditar').value;
    const kilometraje = document.getElementById('kilometrajeEditar').value;

    if (!validarPlaca(placa)) {
      alert('Debe seleccionar un vehículo');
      return false;
    }
    if (!validarTipo(tipo)) {
      alert('Debe seleccionar un tipo de mantenimiento');
      return false;
    }
    if (!validarFecha(fechaProgramada)) {
      alert('Debe ingresar una fecha programada');
      return false;
    }
    if (!validarKilometraje(kilometraje)) {
      alert('El kilometraje debe ser un número válido mayor o igual a 0');
      return false;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const formData = new FormData(form);
    fetch('modals_mantenimiento/actualizar_mantenimiento.php', {
      method: 'POST',
      body: formData
    })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.text();
      })
      .then(result => {
        if (result.includes('exitosamente')) {
          alert(result);
          bootstrap.Modal.getInstance(document.getElementById('editarMantenimientoModal')).hide();
          setTimeout(() => location.reload(), 1500);
            } else {
          alert(result);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        alert('Error de conexión al actualizar el mantenimiento');
      });
  });

  // Botones Ver Mantenimiento
  document.querySelectorAll('.view-mantenimiento').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      if (!id) {
        alert('Error: No se pudo obtener el ID del mantenimiento');
        return false;
      }

      fetch(`modals_mantenimiento/get_mantenimiento.php?id=${encodeURIComponent(id)}`)
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          return response.json();
        })
        .then(data => {
            if (data.success) {
            const mantenimiento = data.data;
            document.getElementById('verIdMantenimiento').textContent = mantenimiento.id_mantenimiento || '';
            document.getElementById('verPlacaMantenimiento').textContent = mantenimiento.placa || '';
            document.getElementById('verTipoMantenimiento').textContent = mantenimiento.descripcion_tipo || '';
            document.getElementById('verEstadoMantenimiento').textContent = mantenimiento.estado || '';
            document.getElementById('verFechaProgramada').textContent = mantenimiento.fecha_programada || '';
            document.getElementById('verFechaRealizada').textContent = mantenimiento.fecha_realizada || 'No realizada';
            document.getElementById('verKilometraje').textContent = mantenimiento.kilometraje_actual || 'No registrado';
            document.getElementById('verObservaciones').textContent = mantenimiento.observaciones || 'Sin observaciones';
            new bootstrap.Modal(document.getElementById('verMantenimientoModal')).show();
            } else {
            alert('Error al cargar los datos del mantenimiento');
            }
        })
        .catch(error => {
            console.error('Error:', error);
          alert('Error de conexión al cargar los datos del mantenimiento');
        });
    });
  });

  // Botones Eliminar Mantenimiento
  document.querySelectorAll('.delete-mantenimiento').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      const placa = this.getAttribute('data-placa');
      const tipo = this.getAttribute('data-tipo');
      
      if (!id) {
        alert('Error: No se pudo obtener el ID del mantenimiento');
        return false;
      }
      
      document.getElementById('elimIdMantenimiento').value = id;
      document.getElementById('elimPlacaMantenimiento').textContent = placa || '';
      document.getElementById('elimTipoMantenimiento').textContent = tipo || '';
      new bootstrap.Modal(document.getElementById('eliminarMantenimientoModal')).show();
    });
  });

  // Botón Confirmar Eliminar
  document.getElementById('confirmarEliminarMantenimiento').addEventListener('click', function (e) {
    e.preventDefault();
    const id = document.getElementById('elimIdMantenimiento').value;
    if (!id) {
      alert('Error: No se pudo obtener el ID del mantenimiento');
      return false;
    }

    fetch('modals_mantenimiento/eliminar_mantenimiento.php', {
            method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `id=${encodeURIComponent(id)}`
    })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
      })
        .then(data => {
            if (data.success) {
          alert(data.message);
          bootstrap.Modal.getInstance(document.getElementById('eliminarMantenimientoModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
          alert(data.error || 'Error al eliminar el mantenimiento');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        alert('Error de conexión al eliminar el mantenimiento');
        });
    });
    
  // Botón Guardar Mantenimiento
  document.getElementById('guardarMantenimiento').addEventListener('click', function (e) {
    e.preventDefault();

    const form = document.getElementById('agregarMantenimientoForm');
    const placa = document.getElementById('placaAgregar').value;
    const tipo = document.getElementById('tipoMantenimientoAgregar').value;
    const fechaProgramada = document.getElementById('fechaProgramadaAgregar').value;
    const kilometraje = document.getElementById('kilometrajeAgregar').value;

    if (!validarPlaca(placa)) {
      alert('Debe seleccionar un vehículo');
      return;
    }
    if (!validarTipo(tipo)) {
      alert('Debe seleccionar un tipo de mantenimiento');
      return;
    }
    if (!validarFecha(fechaProgramada)) {
      alert('Debe ingresar una fecha programada');
      return;
    }
    if (!validarKilometraje(kilometraje)) {
      alert('El kilometraje debe ser un número válido mayor o igual a 0');
      return;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const formData = new FormData(form);
    fetch('modals_mantenimiento/agregar_mantenimiento.php', {
                method: 'POST',
                body: formData
            })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.text();
      })
      .then(result => {
        if (result.includes('exitosamente')) {
          alert(result);
          bootstrap.Modal.getInstance(document.getElementById('agregarMantenimientoModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                } else {
          alert(result);
                }
            })
            .catch(error => {
                console.error('Error:', error);
        alert('Error de conexión al agregar el mantenimiento');
            });
    });
});