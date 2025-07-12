
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Configuración - Modo Oscuro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    /* Estilos del switch */
    .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 28px;
      margin-right: 10px;
    }
    .switch input { display: none; }
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 28px;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 22px; width: 22px;
      left: 3px; bottom: 3px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    input:checked + .slider {
      background-color: #222;
    }
    input:checked + .slider:before {
      transform: translateX(32px);
    }
    body.dark-mode {
      background: #181818 !important;
      color: #f1f1f1 !important;
    }
  </style>
</head>
<body>
  <div style="margin: 2rem;">
    <label class="switch">
      <input type="checkbox" id="darkModeSwitch">
      <span class="slider"></span>
    </label>
    Activar modo oscuro
  </div>

  <script>
    const switchBtn = document.getElementById('darkModeSwitch');
    const body = document.body;

    // Guardar preferencia en localStorage
    if(localStorage.getItem('darkMode') === 'enabled') {
      body.classList.add('dark-mode');
      switchBtn.checked = true;
    }

    switchBtn.addEventListener('change', function() {
      if(this.checked) {
        body.classList.add('dark-mode');
        localStorage.setItem('darkMode', 'enabled');
      } else {
        body.classList.remove('dark-mode');
        localStorage.setItem('darkMode', 'disabled');
      }
    });
  </script>
</body>
</html>