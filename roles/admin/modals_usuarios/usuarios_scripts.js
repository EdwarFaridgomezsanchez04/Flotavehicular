document.addEventListener('DOMContentLoaded', function () {
  // Validaciones
  function validarNombre(nombre) {
    const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{4,90}$/;
    return regex.test(nombre.trim());
  }

  function validarCorreo(correo) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Z]{2,}$/i;
    return regex.test(correo.trim());
  }

  function validarTelefono(telefono) {
    const regex = /^\d{7,15}$/;
    return regex.test(telefono.trim());
  }

  document.getElementById('btnAgregarUsuario').addEventListener('click', function (e) {
    e.preventDefault();
    document.getElementById('agregarUsuarioForm').reset();
    new bootstrap.Modal(document.getElementById('agregarUsuarioModal')).show();
  });

  document.querySelectorAll('.edit-user').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const documento = this.getAttribute('data-id');
      if (!documento) {
        alert('Error: No se pudo obtener el documento del usuario');
        return false;
      }

      fetch(`modals_usuarios/get_usuario_data.php?documento=${encodeURIComponent(documento)}`)
        .then(response => {
          if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
          return response.json();
        })
        .then(data => {
          if (data.success) {
            const usuario = data.data;
            document.getElementById('documentoEditar').value = documento;
            document.getElementById('nombreCompletoEditar').value = usuario.nombre_completo || '';
            document.getElementById('emailEditar').value = usuario.email || '';
            document.getElementById('telefonoEditar').value = usuario.telefono || '';
            document.getElementById('estadoEditar').value = usuario.id_estado_usuario || '';
            document.getElementById('rolEditar').value = usuario.id_rol || '';
            new bootstrap.Modal(document.getElementById('editarUsuarioModal')).show();
          } else {
            alert('Error al cargar los datos del usuario');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error de conexión al cargar los datos del usuario');
        });
    });
  });

  document.getElementById('actualizarUsuario').addEventListener('click', function (e) {
    e.preventDefault();
    const form = document.getElementById('editarUsuarioForm');

    const nombre = document.getElementById('nombreCompletoEditar').value;
    const email = document.getElementById('emailEditar').value;
    const telefono = document.getElementById('telefonoEditar').value;

    if (!validarNombre(nombre)) {
      alert('El nombre solo puede contener letras y espacios minimo 4 caracteres (máx. 90 caracteres)');
      return false;
    }
    if (!validarCorreo(email)) {
      alert('El correo no tiene un formato válido');
      return false;
    }
    if (!validarTelefono(telefono)) {
      alert('El teléfono debe contener solo números (entre 7 y 15 dígitos)');
      return false;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const formData = new FormData(form);
    fetch('modals_usuarios/actualizar_usuario.php', {
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
          bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal')).hide();
          setTimeout(() => location.reload(), 1500);
        } else {
          alert(result);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al actualizar el usuario');
      });
  });

  document.querySelectorAll('.delete-user').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const documento = this.getAttribute('data-id');
      if (!documento) {
        alert('Error: No se pudo obtener el documento del usuario');
        return false;
      }
      document.getElementById('documentoEliminar').textContent = documento;
      document.getElementById('documentoEliminarInput').value = documento;
      new bootstrap.Modal(document.getElementById('eliminarUsuarioModal')).show();
    });
  });

  document.getElementById('confirmarEliminar').addEventListener('click', function (e) {
    e.preventDefault();
    const documento = document.getElementById('documentoEliminarInput').value;
    if (!documento) {
      alert('Error: No se pudo obtener el documento del usuario');
      return false;
    }

    fetch('modals_usuarios/eliminar_usuario.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `documento=${encodeURIComponent(documento)}`
    })
      .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
      })
      .then(data => {
        if (data.success) {
          alert(data.message);
          bootstrap.Modal.getInstance(document.getElementById('eliminarUsuarioModal')).hide();
          setTimeout(() => location.reload(), 1500);
        } else {
          alert(data.error || 'Error al eliminar el usuario');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al eliminar el usuario');
      });
  });

  document.getElementById('guardarUsuario').addEventListener('click', function (e) {
    e.preventDefault();

    const form = document.getElementById('agregarUsuarioForm');
    const documento = document.getElementById('documentoAgregar').value;
    const nombre = document.getElementById('nombreCompletoAgregar').value;
    const email = document.getElementById('emailAgregar').value;
    const telefono = document.getElementById('telefonoAgregar').value;
    const password = document.getElementById('passwordAgregar').value;

    if (!validarNombre(nombre)) {
      alert('El nombre solo puede contener letras y espacios (máx. 90 caracteres)');
      return;
    }
    if (!validarCorreo(email)) {
      alert('El correo no tiene un formato válido');
      return;
    }
    if (!validarTelefono(telefono)) {
      alert('El teléfono debe contener solo números (entre 7 y 15 dígitos)');
      return;
    }
    if (password.trim().length < 8) {
      alert('La contraseña debe tener al menos 8 caracteres');
      return;
    }

    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const formData = new FormData(form);
    fetch('modals_usuarios/agregar_usuario.php', {
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
          bootstrap.Modal.getInstance(document.getElementById('agregarUsuarioModal')).hide();
          setTimeout(() => location.reload(), 1500);
        } else {
          alert(result);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al agregar el usuario');
      });
  });
});
