/**
 * Sistema de Validaciones para Mantenimiento
 * Maneja todas las validaciones de los formularios de agregar y editar mantenimiento
 */

document.addEventListener('DOMContentLoaded', function () {
  
  // ============================================
  // FUNCIONES DE VALIDACIÓN
  // ============================================
  
  // Validar placa de vehículo (mayúsculas, sin espacios, formato válido)
  function validarPlaca(placa) {
    if (!placa || placa.trim() === '') {
      return { valido: false, mensaje: 'Debe seleccionar un vehículo' };
    }
    const formato = /^([A-Z]{3}\d{3}|[A-Z]{3}\d{2}[A-Z]|[A-Z]{2}\d{4})$/;
    if (!formato.test(placa.trim().toUpperCase())) {
      return { valido: false, mensaje: 'Formato de placa inválido (Ej: ABC123, ABC12D, AB1234)' };
    }
    return { valido: true, mensaje: '' };
  }

  // Validar tipo de mantenimiento
  function validarTipo(tipo) {
    if (!tipo || tipo.trim() === '') {
      return { valido: false, mensaje: 'Debe seleccionar un tipo de mantenimiento' };
    }
    return { valido: true, mensaje: '' };
  }

  // Validar fecha programada
  function validarFechaProgramada(fecha) {
    if (!fecha || fecha.trim() === '') {
      return { valido: false, mensaje: 'Debe ingresar una fecha programada' };
    }
    
    const fechaProg = new Date(fecha);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    
    if (fechaProg < hoy) {
      return { valido: false, mensaje: 'La fecha programada no puede ser anterior a hoy' };
    }
    
    // Validar que no sea más de 2 años en el futuro
    const maxFecha = new Date();
    maxFecha.setFullYear(maxFecha.getFullYear() + 2);
    
    if (fechaProg > maxFecha) {
      return { valido: false, mensaje: 'La fecha programada no puede ser más de 2 años en el futuro' };
    }
    
    return { valido: true, mensaje: '' };
  }

  // Validar fecha realizada
  function validarFechaRealizada(fechaRealizada, fechaProgramada) {
    if (!fechaRealizada || fechaRealizada.trim() === '') {
      return { valido: true, mensaje: '' }; // Es opcional
    }
    
    const fechaReal = new Date(fechaRealizada);
    const fechaProg = new Date(fechaProgramada);
    const hoy = new Date();
    
    // Normalizar todas las fechas para comparar solo el día (sin hora)
    fechaReal.setHours(0, 0, 0, 0);
    fechaProg.setHours(0, 0, 0, 0);
    hoy.setHours(0, 0, 0, 0);
    
    if (fechaReal < fechaProg) {
      return { valido: false, mensaje: 'La fecha realizada no puede ser anterior a la programada' };
    }
    
    if (fechaReal > hoy) {
      return { valido: false, mensaje: 'La fecha realizada no puede ser futura' };
    }
    
    return { valido: true, mensaje: '' };
  }

  // Validar kilometraje (entero positivo, sin decimales)
  function validarKilometraje(kilometraje) {
    if (!kilometraje || kilometraje.trim() === '') {
      return { valido: true, mensaje: '' }; // Es opcional
    }
    if (!/^[0-9]+$/.test(kilometraje)) {
      return { valido: false, mensaje: 'El kilometraje debe ser un número entero positivo' };
    }
    const km = Number(kilometraje);
    if (km < 0) {
      return { valido: false, mensaje: 'El kilometraje no puede ser negativo' };
    }
    if (km > 999999) {
      return { valido: false, mensaje: 'El kilometraje no puede ser mayor a 999,999 km' };
    }
    return { valido: true, mensaje: '' };
  }

  // Validar observaciones (sin solo espacios, sin caracteres peligrosos, contador)
  function validarObservaciones(observaciones) {
    if (!observaciones || observaciones.trim() === '') {
      return { valido: true, mensaje: '' }; // Es opcional
    }
    if (observaciones.length > 500) {
      return { valido: false, mensaje: 'Las observaciones no pueden superar 500 caracteres' };
    }
    if (observaciones.trim().length === 0 && observaciones.length > 0) {
      return { valido: false, mensaje: 'Las observaciones no pueden ser solo espacios en blanco' };
    }
    if (/[$<>]/.test(observaciones)) {
      return { valido: false, mensaje: 'No se permiten caracteres especiales como <, > o $' };
    }
    return { valido: true, mensaje: '' };
  }

  // ============================================
  // FUNCIONES DE INTERFAZ
  // ============================================
  
  // Mostrar error en un campo
  function mostrarError(input, mensaje) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    
    let feedback = input.parentElement.querySelector('.invalid-feedback');
    if (!feedback) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback';
      input.parentElement.appendChild(feedback);
    }
    feedback.textContent = mensaje;
  }

  // Mostrar éxito en un campo
  function mostrarExito(input) {
    input.classList.add('is-valid');
    input.classList.remove('is-invalid');
    
    let feedback = input.parentElement.querySelector('.invalid-feedback');
    if (feedback) {
      feedback.remove();
    }
  }

  // Limpiar errores de un formulario
  function limpiarErrores(form) {
    form.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
      el.classList.remove('is-invalid', 'is-valid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
  }

  // Validar campo en tiempo real (input y blur)
  function validarCampoEnTiempoReal(input, validacionFunc, dependencias = {}) {
    function ejecutarValidacion() {
      let resultado;
      if (validacionFunc.name === 'validarFechaRealizada') {
        const fechaProgramada = dependencias.fechaProgramada ? dependencias.fechaProgramada.value : '';
        resultado = validacionFunc(input.value, fechaProgramada);
      } else {
        resultado = validacionFunc(input.value);
      }
      if (resultado.valido) {
        mostrarExito(input);
      } else {
        mostrarError(input, resultado.mensaje);
      }
    }
    input.addEventListener('input', ejecutarValidacion);
    input.addEventListener('blur', ejecutarValidacion);
  }

  // Contador de caracteres en observaciones
  function agregarContadorCaracteres(textarea, max) {
    let counter = textarea.parentElement.querySelector('.char-counter');
    if (!counter) {
      counter = document.createElement('div');
      counter.className = 'char-counter';
      textarea.parentElement.appendChild(counter);
    }
    function actualizarContador() {
      const restante = max - textarea.value.length;
      counter.textContent = `${restante} caracteres restantes`;
      counter.className = 'char-counter' + (restante < 50 ? ' warning' : '') + (restante < 10 ? ' danger' : '');
    }
    textarea.addEventListener('input', actualizarContador);
    actualizarContador();
  }

  // Resaltar el primer error al enviar
  function resaltarPrimerError(form) {
    const error = form.querySelector('.is-invalid');
    if (error) {
      error.focus();
      error.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  // ============================================
  // CONFIGURACIÓN DE VALIDACIONES EN TIEMPO REAL
  // ============================================
  
  // Formulario de Agregar
  const formAgregar = document.getElementById('agregarMantenimientoForm');
  if (formAgregar) {
    const placaAgregar = document.getElementById('placaAgregar');
    const tipoAgregar = document.getElementById('tipoMantenimientoAgregar');
    const fechaProgAgregar = document.getElementById('fechaProgramadaAgregar');
    const fechaRealAgregar = document.getElementById('fechaRealizadaAgregar');
    const kilometrajeAgregar = document.getElementById('kilometrajeAgregar');
    const observacionesAgregar = document.getElementById('observacionesAgregar');

    // Configurar validaciones en tiempo real
    validarCampoEnTiempoReal(placaAgregar, validarPlaca);
    validarCampoEnTiempoReal(tipoAgregar, validarTipo);
    validarCampoEnTiempoReal(fechaProgAgregar, validarFechaProgramada);
    validarCampoEnTiempoReal(fechaRealAgregar, validarFechaRealizada, { fechaProgramada: fechaProgAgregar });
    validarCampoEnTiempoReal(kilometrajeAgregar, validarKilometraje);
    validarCampoEnTiempoReal(observacionesAgregar, validarObservaciones);
    agregarContadorCaracteres(observacionesAgregar, 500);

    // Actualizar validación de fecha realizada cuando cambie la fecha programada
    fechaProgAgregar.addEventListener('change', function() {
      if (fechaRealAgregar.value) {
        const resultado = validarFechaRealizada(fechaRealAgregar.value, fechaProgAgregar.value);
        if (resultado.valido) {
          mostrarExito(fechaRealAgregar);
        } else {
          mostrarError(fechaRealAgregar, resultado.mensaje);
        }
      }
    });
  }

  // Formulario de Editar
  const formEditar = document.getElementById('editarMantenimientoForm');
  if (formEditar) {
    const placaEditar = document.getElementById('placaEditar');
    const tipoEditar = document.getElementById('tipoMantenimientoEditar');
    const fechaProgEditar = document.getElementById('fechaProgramadaEditar');
    const fechaRealEditar = document.getElementById('fechaRealizadaEditar');
    const kilometrajeEditar = document.getElementById('kilometrajeEditar');
    const observacionesEditar = document.getElementById('observacionesEditar');

    // Configurar validaciones en tiempo real
    validarCampoEnTiempoReal(placaEditar, validarPlaca);
    validarCampoEnTiempoReal(tipoEditar, validarTipo);
    validarCampoEnTiempoReal(fechaProgEditar, validarFechaProgramada);
    validarCampoEnTiempoReal(fechaRealEditar, validarFechaRealizada, { fechaProgramada: fechaProgEditar });
    validarCampoEnTiempoReal(kilometrajeEditar, validarKilometraje);
    validarCampoEnTiempoReal(observacionesEditar, validarObservaciones);
    agregarContadorCaracteres(observacionesEditar, 500);

    // Actualizar validación de fecha realizada cuando cambie la fecha programada
    fechaProgEditar.addEventListener('change', function() {
      if (fechaRealEditar.value) {
        const resultado = validarFechaRealizada(fechaRealEditar.value, fechaProgEditar.value);
        if (resultado.valido) {
          mostrarExito(fechaRealEditar);
        } else {
          mostrarError(fechaRealEditar, resultado.mensaje);
        }
      }
    });
  }

  // ============================================
  // VALIDACIÓN COMPLETA DE FORMULARIOS
  // ============================================
  
  // Validar formulario completo
  function validarFormularioCompleto(form) {
    limpiarErrores(form);
    
    const campos = {
      placa: form.querySelector('[name="placa"]'),
      tipo: form.querySelector('[name="id_tipo_mantenimiento"]'),
      fechaProgramada: form.querySelector('[name="fecha_programada"]'),
      fechaRealizada: form.querySelector('[name="fecha_realizada"]'),
      kilometraje: form.querySelector('[name="kilometraje_actual"]'),
      observaciones: form.querySelector('[name="observaciones"]')
    };

    let esValido = true;

    // Validar placa
    const validacionPlaca = validarPlaca(campos.placa.value);
    if (!validacionPlaca.valido) {
      mostrarError(campos.placa, validacionPlaca.mensaje);
      esValido = false;
    }

    // Validar tipo
    const validacionTipo = validarTipo(campos.tipo.value);
    if (!validacionTipo.valido) {
      mostrarError(campos.tipo, validacionTipo.mensaje);
      esValido = false;
    }

    // Validar fecha programada
    const validacionFechaProg = validarFechaProgramada(campos.fechaProgramada.value);
    if (!validacionFechaProg.valido) {
      mostrarError(campos.fechaProgramada, validacionFechaProg.mensaje);
      esValido = false;
    }

    // Validar fecha realizada
    const validacionFechaReal = validarFechaRealizada(campos.fechaRealizada.value, campos.fechaProgramada.value);
    if (!validacionFechaReal.valido) {
      mostrarError(campos.fechaRealizada, validacionFechaReal.mensaje);
      esValido = false;
    }

    // Validar kilometraje
    const validacionKilometraje = validarKilometraje(campos.kilometraje.value);
    if (!validacionKilometraje.valido) {
      mostrarError(campos.kilometraje, validacionKilometraje.mensaje);
      esValido = false;
    }

    // Validar observaciones
    const validacionObservaciones = validarObservaciones(campos.observaciones.value);
    if (!validacionObservaciones.valido) {
      mostrarError(campos.observaciones, validacionObservaciones.mensaje);
      esValido = false;
    }

    return esValido;
  }

  // ============================================
  // EVENTOS DE FORMULARIOS
  // ============================================
  
  // Botón Agregar Mantenimiento
  const btnAgregar = document.getElementById('btnAgregarMantenimiento');
  if (btnAgregar) {
    btnAgregar.addEventListener('click', function (e) {
      e.preventDefault();
      limpiarErrores(formAgregar);
      formAgregar.reset();
      new bootstrap.Modal(document.getElementById('agregarMantenimientoModal')).show();
    });
  }

  // Botón Guardar Mantenimiento
  const btnGuardar = document.getElementById('guardarMantenimiento');
  if (btnGuardar) {
    btnGuardar.addEventListener('click', function (e) {
      e.preventDefault();
      
      if (!validarFormularioCompleto(formAgregar)) {
        resaltarPrimerError(formAgregar);
        return;
      }

      // Mostrar indicador de carga
      btnGuardar.disabled = true;
      btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

      const formData = new FormData(formAgregar);
      
      fetch('modals_mantenimiento/agregar_mantenimiento.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
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
        alert('Error de conexión al guardar el mantenimiento');
      })
      .finally(() => {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = 'Guardar';
      });
    });
  }

  // Botones Editar Mantenimiento
  document.querySelectorAll('.edit-mantenimiento').forEach(button => {
    button.addEventListener('click', function (e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      if (!id) {
        alert('Error: No se pudo obtener el ID del mantenimiento');
        return false;
      }

      // Mostrar indicador de carga
      button.disabled = true;
      const originalText = button.innerHTML;
      button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...';

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
            
            limpiarErrores(formEditar);
            new bootstrap.Modal(document.getElementById('editarMantenimientoModal')).show();
          } else {
            alert('Error al cargar los datos del mantenimiento');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error de conexión al cargar los datos del mantenimiento');
        })
        .finally(() => {
          button.disabled = false;
          button.innerHTML = originalText;
        });
    });
  });

  // Botón Actualizar Mantenimiento
  const btnActualizar = document.getElementById('actualizarMantenimiento');
  if (btnActualizar) {
    btnActualizar.addEventListener('click', function (e) {
      e.preventDefault();
      
      if (!validarFormularioCompleto(formEditar)) {
        resaltarPrimerError(formEditar);
        return;
      }

      // Mostrar indicador de carga
      btnActualizar.disabled = true;
      btnActualizar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...';

      const formData = new FormData(formEditar);
      
      fetch('modals_mantenimiento/actualizar_mantenimiento.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
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
      })
      .finally(() => {
        btnActualizar.disabled = false;
        btnActualizar.innerHTML = 'Actualizar';
      });
    });
  }

  // ============================================
  // EVENTOS ADICIONALES
  // ============================================
  
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
  const btnConfirmarEliminar = document.getElementById('confirmarEliminarMantenimiento');
  if (btnConfirmarEliminar) {
    btnConfirmarEliminar.addEventListener('click', function (e) {
      e.preventDefault();
      const id = document.getElementById('elimIdMantenimiento').value;
      
      if (!id) {
        alert('Error: No se pudo obtener el ID del mantenimiento');
        return;
      }

      // Mostrar indicador de carga
      btnConfirmarEliminar.disabled = true;
      btnConfirmarEliminar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...';

      const formData = new FormData();
      formData.append('id_mantenimiento', id);

      fetch('modals_mantenimiento/eliminar_mantenimiento.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.text())
      .then(result => {
        if (result.includes('exitosamente')) {
          alert(result);
          bootstrap.Modal.getInstance(document.getElementById('eliminarMantenimientoModal')).hide();
          setTimeout(() => location.reload(), 1500);
        } else {
          alert(result);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al eliminar el mantenimiento');
      })
      .finally(() => {
        btnConfirmarEliminar.disabled = false;
        btnConfirmarEliminar.innerHTML = 'Eliminar';
      });
    });
  }

  // ============================================
  // FUNCIONES UTILITARIAS
  // ============================================
  
  // Formatear fecha para mostrar
  function formatearFecha(fecha) {
    if (!fecha) return '';
    const fechaObj = new Date(fecha);
    return fechaObj.toLocaleDateString('es-ES', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    });
  }

  // Formatear kilometraje
  function formatearKilometraje(km) {
    if (!km) return '';
    return Number(km).toLocaleString('es-ES');
  }

  // Mostrar notificación
  function mostrarNotificacion(mensaje, tipo = 'info') {
    const alertClass = tipo === 'success' ? 'alert-success' : 
                      tipo === 'error' ? 'alert-danger' : 
                      tipo === 'warning' ? 'alert-warning' : 'alert-info';
    
    const notificacion = document.createElement('div');
    notificacion.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notificacion.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notificacion.innerHTML = `
      ${mensaje}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notificacion);
    
    setTimeout(() => {
      if (notificacion.parentNode) {
        notificacion.parentNode.removeChild(notificacion);
      }
    }, 5000);
  }

});
