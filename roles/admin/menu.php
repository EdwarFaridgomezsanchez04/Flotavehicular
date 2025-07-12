
<?php 

// Obtener información del usuario
$user_query = $con->prepare("SELECT documento, nombre_completo, email, telefono FROM usuarios WHERE documento = :documento");
$user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
$user_query->execute();
$user = $user_query->fetch(PDO::FETCH_ASSOC);

// Ruta de la imagen con timestamp para evitar caché
$imagePath = htmlspecialchars($foto_perfil) . '?v=' . time();
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>

  <div class="sidebar" id="sidebar">
    <!-- Header del sidebar -->
    <div class="sidebar-header">
      <div class="logo-container">
        <img src="../../css/img/blanco.png" alt="Logo" class="logo">
        <div class="brand-text">
          <h1 class="brand-title">FLOTAX</h1>
          <span class="brand-subtitle">AGC</span>
        </div>
      </div>
      <button class="toggle-btn" id="toggleBtn">
        <i class="bi bi-list"></i>
      </button>
    </div>

    <!-- Navegación principal -->
    <nav class="sidebar-nav">
      <div class="nav-section">
        <span class="section-title">Principal</span>
        <a href="index.php" class="nav-link active">
          <i class="bi bi-speedometer2"></i>
          <span>Dashboard</span>
          <div class="nav-indicator"></div>
        </a>
        <a href="Vehiculos.php" class="nav-link">
          <i class="bi bi-truck"></i>
          <span>Gestión de Vehículos</span>
          <div class="nav-indicator"></div>
        </a>
        <a href="documentos.php" class="nav-link">
          <i class="bi bi-folder"></i>
          <span>Control de Documentos</span>
          <div class="nav-indicator"></div>
        </a>
      </div>

      <div class="nav-section">
        <span class="section-title">Operaciones</span>
        <a href="mantenimiento.php" class="nav-link">
          <i class="bi bi-tools"></i>
          <span>Mantenimientos</span>
          <div class="nav-indicator"></div>
        </a>
        <a href="alertas.php" class="nav-link">
          <i class="bi bi-bell"></i>
          <span>Alertas</span>
          <div class="nav-badge">3</div>
          <div class="nav-indicator"></div>
        </a>
        <a href="usuarios.php" class="nav-link">
          <i class="bi bi-people"></i>
          <span>Usuarios</span>
          <div class="nav-indicator"></div>
        </a>
      </div>

      <div class="nav-section">
        <span class="section-title">Historial</span>
        <a href="historial.php" class="nav-link">
          <i class="bi bi-clock-history"></i>
          <span>Historial</span>
          <div class="nav-indicator"></div>
        </a>
        <a href="reportes.php" class="nav-link">
                <i class="bi bi-graph-up"></i>
          <span>Reportes</span>
          <div class="nav-indicator"></div>
        </a>
      </div>
    </nav>

    <!-- Footer del sidebar -->
    <div class="sidebar-footer">
      <a href="perfil.php" class="nav-link profile-link">
        <div class="profile-avatar">
          <img src="<?php echo $imagePath; ?>" alt="Foto de Perfil" class="usu_imagen">
        </div>
        <div class="profile-info">
        <span class="profile-name">
          <?php
            $nombre = htmlspecialchars($user['nombre_completo']);
            echo (mb_strlen($nombre) > 10) ? mb_substr($nombre, 0, 10) . '...' : $nombre;
          ?>
        </span>          <span class="profile-role">Administrador</span>
        </div>
        <i class="bi bi-chevron-right profile-arrow"></i>
      </a>
      
      <div class="footer-actions">
        <a href="#" class="action-btn" title="Configuración">
          <i class="bi bi-gear"></i>
          <span>Configuración</span>
        </a>
                <a href="logout.php" class="action-btn logout-btn" title="Cerrar Sesión">
            <i class="bi bi-box-arrow-right"></i>
            <span>Cerrar Sesión</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Overlay para móvil -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <script src="js/sidebar.js"></script>
</body>
</html>
