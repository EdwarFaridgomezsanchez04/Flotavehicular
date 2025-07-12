<?php
// Incluir archivo de conexión a la base de datos
require_once('../../conecct/conex.php');

// Crear instancia de conexión a la base de datos para cargar datos dinámicos
$db = new Database();
$con = $db->conectar();
?>

<!-- Modal Agregar Usuario -->
<!-- Modal Bootstrap 5 para agregar nuevos usuarios al sistema -->
<div class="modal fade" id="agregarUsuarioModal" tabindex="-1" aria-labelledby="agregarUsuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Tamaño grande para acomodar todos los campos -->
    <div class="modal-content">
      <!-- Encabezado del modal con título y botón de cierre -->
      <div class="modal-header">
        <h5 class="modal-title" id="agregarUsuarioModalLabel">Agregar Nuevo Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Cuerpo del modal con formulario de registro -->
      <div class="modal-body">
        <form id="agregarUsuarioForm">
          <!-- Campo para documento de identidad (requerido) -->
          <div class="mb-3">
            <label for="documentoAgregar" class="form-label">Documento</label>
            <input type="text" class="form-control" id="documentoAgregar" name="documento" required 
                   pattern="[0-9]{6,12}" title="El documento debe tener entre 6 y 12 dígitos numéricos" 
                   maxlength="12">
            <div class="form-text">Ingrese entre 6 y 12 dígitos numéricos</div>
          </div>
          
          <!-- Campo para nombre completo del usuario -->
          <div class="mb-3">
            <label for="nombreCompletoAgregar" class="form-label">Nombre Completo</label>
            <input type="text" class="form-control" id="nombreCompletoAgregar" name="nombre_completo" required 
                   maxlength="200" title="El nombre completo no debe exceder los 200 caracteres">
            <div class="form-text">Máximo 200 caracteres</div>
          </div>
          
          <!-- Campo para dirección de correo electrónico -->
          <div class="mb-3">
            <label for="emailAgregar" class="form-label">Email <i class="fas fa-question-circle text-info" data-bs-toggle="tooltip" title="Ejemplos válidos: usuario@dominio.com, nombre.apellido@empresa.co, correo123@sitio.net"></i></label>
            <input type="email" class="form-control" id="emailAgregar" name="email" required 
                   maxlength="100" title="Ingrese un email válido">
            <small class="form-text text-muted">Formato: usuario@dominio.com</small>
          </div>
          
          <!-- Campo para contraseña inicial del usuario -->
          <div class="mb-3">
            <label for="passwordAgregar" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="passwordAgregar" name="password" required 
                   minlength="8" title="La contraseña debe tener al menos 8 caracteres">
            <div class="form-text">Mínimo 8 caracteres</div>
          </div>
          
          <!-- Campo para número de teléfono -->
          <div class="mb-3">
            <label for="telefonoAgregar" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="telefonoAgregar" name="telefono" required 
                   pattern="[0-9]{10}" maxlength="10" title="El teléfono debe tener 10 dígitos numéricos">
            <div class="form-text">Ingrese 10 dígitos numéricos</div>
          </div>
          
          <!-- Selector dinámico de estado del usuario -->
          <div class="mb-3">
            <label for="estadoAgregar" class="form-label">Estado</label>
            <select class="form-select" id="estadoAgregar" name="estado" required>
              <option value="">Seleccione</option>
              <?php
              // Consulta para obtener todos los estados disponibles desde la base de datos
              $estadoQuery = $con->prepare("SELECT id_estado, tipo_stade FROM estado_usuario");
              $estadoQuery->execute();
              $estados = $estadoQuery->fetchAll(PDO::FETCH_ASSOC);
              
              // Generar opciones dinámicamente desde la base de datos
              foreach ($estados as $estado) {
                echo "<option value='{$estado['id_estado']}'>{$estado['tipo_stade']}</option>";
              }
              ?>
            </select>
          </div>
          
          <!-- Selector dinámico de rol del usuario -->
          <div class="mb-3">
            <label for="rolAgregar" class="form-label">Rol</label>
            <select class="form-select" id="rolAgregar" name="rol" required>
              <option value="">Seleccione</option>
              <?php
              // Consulta para obtener solo el rol de usuarios (id 2)
              $rolQuery = $con->prepare("SELECT id_rol, tip_rol FROM roles WHERE id_rol = 2");
              $rolQuery->execute();
              $roles = $rolQuery->fetchAll(PDO::FETCH_ASSOC);
              
              // Generar opciones dinámicamente desde la base de datos
              foreach ($roles as $rol) {
                echo "<option value='{$rol['id_rol']}'>{$rol['tip_rol']}</option>";
              }
              ?>
            </select>
          </div>
        </form>
      </div>
      
      <!-- Pie del modal con botones de acción -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="guardarUsuario">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Script para validaciones de formularios -->
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap 5
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  // Referencias a los formularios
  const formAgregar = document.getElementById('agregarUsuarioForm');
  const formEditar = document.getElementById('editarUsuarioForm');
  
  // Prevenir envío directo de los formularios
  if (formAgregar) {
    formAgregar.addEventListener('submit', function(e) {
      // Solo permitir el envío si fue disparado por nuestro código
      if (!e.isTrusted || e._isCustomEvent) {
        return true;
      }
      e.preventDefault();
      // Redirigir al botón de guardar para que se ejecuten las validaciones
      document.getElementById('guardarUsuario').click();
    });
  }
  
  if (formEditar) {
    formEditar.addEventListener('submit', function(e) {
      // Solo permitir el envío si fue disparado por nuestro código
      if (!e.isTrusted || e._isCustomEvent) {
        return true;
      }
      e.preventDefault();
      // Redirigir al botón de actualizar para que se ejecuten las validaciones
      document.getElementById('actualizarUsuario').click();
    });
  }
  
  // Referencias a los campos del formulario Agregar
  const documentoAgregar = document.getElementById('documentoAgregar');
  const nombreCompletoAgregar = document.getElementById('nombreCompletoAgregar');
  const emailAgregar = document.getElementById('emailAgregar');
  const telefonoAgregar = document.getElementById('telefonoAgregar');
  const passwordAgregar = document.getElementById('passwordAgregar');
  
  // Referencias a los campos del formulario Editar
  const nombreCompletoEditar = document.getElementById('nombreCompletoEditar');
  const emailEditar = document.getElementById('emailEditar');
  const telefonoEditar = document.getElementById('telefonoEditar');
  
  // Expresiones regulares para validaciones
  // Expresión regular más permisiva para correos electrónicos
  const regexEmail = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
  const regexTelefono = /^[0-9]{10}$/;
  const regexDocumento = /^[0-9]{6,12}$/;
  
  // Función para mostrar mensajes de error
  function mostrarError(input, mensaje) {
    // Eliminar mensaje de error previo si existe
    const errorPrevio = input.parentElement.querySelector('.invalid-feedback');
    if (errorPrevio) {
      errorPrevio.remove();
    }
    
    // Crear y agregar nuevo mensaje de error
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = mensaje;
    errorDiv.style.display = 'block';
    
    // Si es un error de email, agregar ejemplos de correos válidos
    if (input.type === 'email' && mensaje.includes('email')) {
      const ayudaDiv = document.createElement('div');
      ayudaDiv.className = 'text-muted small mt-1';
      ayudaDiv.innerHTML = 'Ejemplos válidos: usuario@dominio.com, nombre.apellido@empresa.co, correo123@sitio.net';
      errorDiv.appendChild(ayudaDiv);
    }
    
    input.classList.add('is-invalid');
    input.parentElement.appendChild(errorDiv);
  }
  
  // Función para limpiar mensajes de error
  function limpiarError(input) {
    input.classList.remove('is-invalid');
    const errorDiv = input.parentElement.querySelector('.invalid-feedback');
    if (errorDiv) {
      errorDiv.remove();
    }
  }
  
  // Validación de documento
  function validarDocumento(input) {
    if (input.value.trim() === '') {
      mostrarError(input, 'El documento es obligatorio');
      return false;
    } else if (!regexDocumento.test(input.value)) {
      mostrarError(input, 'El documento debe tener entre 6 y 12 dígitos numéricos');
      return false;
    } else {
      limpiarError(input);
      return true;
    }
  }
  
  // Validación de nombre completo
  function validarNombreCompleto(input) {
    if (input.value.trim() === '') {
      mostrarError(input, 'El nombre completo es obligatorio');
      return false;
    } else if (input.value.length > 200) {
      mostrarError(input, 'El nombre completo no debe exceder los 200 caracteres');
      return false;
    } else {
      limpiarError(input);
      return true;
    }
  }
  
  // Función para normalizar el correo electrónico
  function normalizarEmail(email) {
    // Eliminar espacios y convertir a minúsculas
    return email.trim().toLowerCase();
  }
  
  // Función para validar el dominio del correo electrónico
  function validarDominioEmail(dominio) {
    // Lista de TLDs comunes (no exhaustiva)
    const tlds = ['com', 'co', 'net', 'org', 'edu', 'gov', 'mil', 'io', 'info', 'biz', 'mx', 'us', 'uk', 'ca', 'au', 'de', 'jp', 'fr', 'it', 'es', 'ru', 'cn', 'in', 'br'];
    
    // Verificar que el dominio termine con un TLD válido
    const partesDelDominio = dominio.split('.');
    const tld = partesDelDominio[partesDelDominio.length - 1];
    
    return tlds.includes(tld);
  }
  
  // Validación de email
  function validarEmail(input) {
    // Normalizar el valor del email
    const valorOriginal = input.value;
    const valor = normalizarEmail(valorOriginal);
    
    // Si el valor ha cambiado después de normalizar, actualizar el campo
    if (valorOriginal !== valor) {
      input.value = valor;
    }
    
    if (valor === '') {
      mostrarError(input, 'El email es obligatorio');
      return false;
    } 
    
    // Verificar longitud máxima
    if (valor.length > 100) {
      mostrarError(input, 'El email no debe exceder los 100 caracteres');
      return false;
    }
    
    // Verificar formato básico (contiene @ y al menos un punto después)
    if (valor.indexOf('@') === -1) {
      mostrarError(input, 'El email debe contener el símbolo @');
      return false;
    }
    
    const partes = valor.split('@');
    if (partes.length !== 2) {
      mostrarError(input, 'El email debe contener exactamente un símbolo @');
      return false;
    }
    
    const [nombreUsuario, dominio] = partes;
    
    // Verificar que el nombre de usuario no esté vacío
    if (nombreUsuario.length === 0) {
      mostrarError(input, 'El nombre de usuario del email no puede estar vacío');
      return false;
    }
    
    // Verificar que el dominio tenga al menos un punto
    if (dominio.indexOf('.') === -1) {
      mostrarError(input, 'El dominio del email debe contener al menos un punto');
      return false;
    }
    
    // Verificar que no termine en punto
    if (dominio.endsWith('.')) {
      mostrarError(input, 'El email no puede terminar en punto');
      return false;
    }
    
    // Verificar que el dominio tenga un TLD válido
    if (!validarDominioEmail(dominio)) {
      mostrarError(input, 'El dominio del email debe terminar con una extensión válida (com, co, net, org, etc.)');
      return false;
    }
    
    // Verificar con regex completa
    if (!regexEmail.test(valor)) {
      mostrarError(input, 'El formato del email no es válido');
      return false;
    }
    
    limpiarError(input);
    return true;
  }
  
  // Validación de teléfono
  function validarTelefono(input) {
    if (input.value.trim() === '') {
      mostrarError(input, 'El teléfono es obligatorio');
      return false;
    } else if (!regexTelefono.test(input.value)) {
      mostrarError(input, 'El teléfono debe tener 10 dígitos numéricos');
      return false;
    } else {
      limpiarError(input);
      return true;
    }
  }
  
  // Validación de contraseña
  function validarPassword(input) {
    if (input.value.trim() === '') {
      mostrarError(input, 'La contraseña es obligatoria');
      return false;
    } else if (input.value.length < 8) {
      mostrarError(input, 'La contraseña debe tener al menos 8 caracteres');
      return false;
    } else {
      limpiarError(input);
      return true;
    }
  }
  
  // Eventos para validación en tiempo real - Formulario Agregar
  if (documentoAgregar) {
    documentoAgregar.addEventListener('input', function() {
      validarDocumento(this);
    });
  }
  
  if (nombreCompletoAgregar) {
    nombreCompletoAgregar.addEventListener('input', function() {
      validarNombreCompleto(this);
    });
  }
  
  if (emailAgregar) {
    // Validar mientras escribe
    emailAgregar.addEventListener('input', function() {
      validarEmail(this);
    });
    
    // Normalizar cuando pierde el foco
    emailAgregar.addEventListener('blur', function() {
      this.value = normalizarEmail(this.value);
      validarEmail(this);
    });
  }
  
  if (telefonoAgregar) {
    telefonoAgregar.addEventListener('input', function() {
      validarTelefono(this);
    });
  }
  
  if (passwordAgregar) {
    passwordAgregar.addEventListener('input', function() {
      validarPassword(this);
    });
  }
  
  // Eventos para validación en tiempo real - Formulario Editar
  if (nombreCompletoEditar) {
    nombreCompletoEditar.addEventListener('input', function() {
      validarNombreCompleto(this);
    });
  }
  
  if (emailEditar) {
    // Validar mientras escribe
    emailEditar.addEventListener('input', function() {
      validarEmail(this);
    });
    
    // Normalizar cuando pierde el foco
    emailEditar.addEventListener('blur', function() {
      this.value = normalizarEmail(this.value);
      validarEmail(this);
    });
  }
  
  if (telefonoEditar) {
    telefonoEditar.addEventListener('input', function() {
      validarTelefono(this);
    });
  }
  
  // Validación al enviar el formulario Agregar
  const btnGuardarUsuario = document.getElementById('guardarUsuario');
  if (btnGuardarUsuario && formAgregar) {
    btnGuardarUsuario.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Validar todos los campos
      const docValido = validarDocumento(documentoAgregar);
      const nombreValido = validarNombreCompleto(nombreCompletoAgregar);
      const emailValido = validarEmail(emailAgregar);
      const telefonoValido = validarTelefono(telefonoAgregar);
      const passwordValido = validarPassword(passwordAgregar);
      
      // Si todos los campos son válidos, enviar el formulario
      if (docValido && nombreValido && emailValido && telefonoValido && passwordValido) {
        // Crear un evento personalizado con una propiedad para identificarlo
        const customEvent = new Event('submit');
        customEvent._isCustomEvent = true;
        
        // Disparar el evento personalizado
        formAgregar.dispatchEvent(customEvent);
      } else {
        // Mostrar mensaje de error general
        const alertaDiv = document.createElement('div');
        alertaDiv.className = 'alert alert-danger mt-3';
        alertaDiv.textContent = 'Por favor, corrija los errores en el formulario antes de continuar.';
        alertaDiv.id = 'alertaErrorGeneral';
        
        // Eliminar alerta previa si existe
        const alertaPrevia = document.getElementById('alertaErrorGeneral');
        if (alertaPrevia) {
          alertaPrevia.remove();
        }
        
        // Agregar la alerta al formulario
        formAgregar.appendChild(alertaDiv);
        
        // Hacer scroll al primer campo con error
        const primerCampoInvalido = formAgregar.querySelector('.is-invalid');
        if (primerCampoInvalido) {
          primerCampoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
  }
  
  // Prevenir envío del formulario con Enter
  if (formAgregar) {
    formAgregar.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        btnGuardarUsuario.click();
      }
    });
  }
  
  // Validación al enviar el formulario Editar
  const btnActualizarUsuario = document.getElementById('actualizarUsuario');
  if (btnActualizarUsuario && formEditar) {
    btnActualizarUsuario.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Validar todos los campos
      const nombreValido = validarNombreCompleto(nombreCompletoEditar);
      const emailValido = validarEmail(emailEditar);
      const telefonoValido = validarTelefono(telefonoEditar);
      
      // Si todos los campos son válidos, enviar el formulario
      if (nombreValido && emailValido && telefonoValido) {
        // Crear un evento personalizado con una propiedad para identificarlo
        const customEvent = new Event('submit');
        customEvent._isCustomEvent = true;
        
        // Disparar el evento personalizado
        formEditar.dispatchEvent(customEvent);
      } else {
        // Mostrar mensaje de error general
        const alertaDiv = document.createElement('div');
        alertaDiv.className = 'alert alert-danger mt-3';
        alertaDiv.textContent = 'Por favor, corrija los errores en el formulario antes de continuar.';
        alertaDiv.id = 'alertaErrorGeneralEditar';
        
        // Eliminar alerta previa si existe
        const alertaPrevia = document.getElementById('alertaErrorGeneralEditar');
        if (alertaPrevia) {
          alertaPrevia.remove();
        }
        
        // Agregar la alerta al formulario
        formEditar.appendChild(alertaDiv);
        
        // Hacer scroll al primer campo con error
        const primerCampoInvalido = formEditar.querySelector('.is-invalid');
        if (primerCampoInvalido) {
          primerCampoInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
  }
  
  // Prevenir envío del formulario de edición con Enter
  if (formEditar) {
    formEditar.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        btnActualizarUsuario.click();
      }
    });
  }
});
</script>

<!-- Modal Editar Usuario -->
<!-- Modal Bootstrap 5 para editar información de usuarios existentes -->
<div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg"> <!-- Tamaño grande para acomodar todos los campos -->
    <div class="modal-content">
      <!-- Encabezado del modal con título y botón de cierre -->
      <div class="modal-header">
        <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Cuerpo del modal con formulario de edición -->
      <div class="modal-body">
        <form id="editarUsuarioForm">
          <!-- Campo oculto para mantener el documento original (no editable) -->
          <input type="hidden" id="documentoEditar" name="documento">
          
          <!-- Campo para editar nombre completo -->
          <div class="mb-3">
            <label for="nombreCompletoEditar" class="form-label">Nombre Completo</label>
            <input type="text" class="form-control" id="nombreCompletoEditar" name="nombre_completo" required 
                   maxlength="200" title="El nombre completo no debe exceder los 200 caracteres">
            <div class="form-text">Máximo 200 caracteres</div>
          </div>
          
          <!-- Campo para editar email -->
          <div class="mb-3">
              <label for="emailEditar" class="form-label">Email <i class="fas fa-question-circle text-info" data-bs-toggle="tooltip" title="Ejemplos válidos: usuario@dominio.com, nombre.apellido@empresa.co, correo123@sitio.net"></i></label>
              <input type="email" class="form-control" id="emailEditar" name="email" required 
                     maxlength="100" title="Ingrese un email válido">
              <small class="form-text text-muted">Formato: usuario@dominio.com</small>
            </div>
          
          <!-- Campo para editar teléfono -->
          <div class="mb-3">
            <label for="telefonoEditar" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="telefonoEditar" name="telefono" required 
                   pattern="[0-9]{10}" maxlength="10" title="El teléfono debe tener 10 dígitos numéricos">
            <div class="form-text">Ingrese 10 dígitos numéricos</div>
          </div>
          
          <!-- Selector dinámico para cambiar estado del usuario -->
          <div class="mb-3">
            <label for="estadoEditar" class="form-label">Estado</label>
            <select class="form-select" id="estadoEditar" name="estado" required>
              <option value="">Seleccione</option>
              <?php
              // Reutilizar consulta de estados para el modal de edición
              $estadoQuery = $con->prepare("SELECT id_estado, tipo_stade FROM estado_usuario");
              $estadoQuery->execute();
              $estados = $estadoQuery->fetchAll(PDO::FETCH_ASSOC);
              foreach ($estados as $estado) {
                echo "<option value='{$estado['id_estado']}'>{$estado['tipo_stade']}</option>";
              }
              ?>
            </select>
          </div>
          
          <!-- Selector dinámico para cambiar rol del usuario -->
          <div class="mb-3">
            <label for="rolEditar" class="form-label">Rol</label>
            <select class="form-select" id="rolEditar" name="rol" required>
              <option value="">Seleccione</option>
              <?php
              // Reutilizar consulta de roles para el modal de edición (solo rol usuarios id 2)
              $rolQuery = $con->prepare("SELECT id_rol, tip_rol FROM roles WHERE id_rol = 2");
              $rolQuery->execute();
              $roles = $rolQuery->fetchAll(PDO::FETCH_ASSOC);
              foreach ($roles as $rol) {
                echo "<option value='{$rol['id_rol']}'>{$rol['tip_rol']}</option>";
              }
              ?>
            </select>
          </div>
        </form>
      </div>
      
      <!-- Pie del modal con botones de acción -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="actualizarUsuario">Actualizar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Eliminar Usuario -->
<!-- Modal Bootstrap 5 para confirmar eliminación de usuarios -->
<div class="modal fade" id="eliminarUsuarioModal" tabindex="-1" aria-labelledby="eliminarUsuarioModalLabel" aria-hidden="true">
  <div class="modal-dialog"> <!-- Tamaño estándar para confirmación simple -->
    <div class="modal-content">
      <!-- Encabezado del modal con título y botón de cierre -->
      <div class="modal-header">
        <h5 class="modal-title" id="eliminarUsuarioModalLabel">Eliminar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Cuerpo del modal con mensaje de confirmación -->
      <div class="modal-body">
        <!-- Mensaje de advertencia con documento del usuario a eliminar -->
        <p>¿Estás seguro de eliminar el usuario con documento <span id="documentoEliminar"></span>? Esta acción no se puede deshacer.</p>
        <!-- Campo oculto para almacenar el documento del usuario a eliminar -->
        <input type="hidden" id="documentoEliminarInput" name="documento">
      </div>
      
      <!-- Pie del modal con botones de confirmación -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="confirmarEliminar">Eliminar</button>
      </div>
    </div>
  </div>
</div>