<?php
session_start();
require_once('../../conecct/conex.php');
include '../../includes/validarsession.php';
$db = new Database();
$con = $db->conectar();
$code = $_SESSION['documento'];
$sql = $con->prepare("SELECT*FROM usuarios
    INNER JOIN roles ON usuarios.id_rol = roles.id_rol 
    INNER JOIN estado_usuario ON usuarios.id_estado_usuario = estado_usuario.id_estado
    WHERE documento= :code");
$sql->bindParam(':code', $code);
$sql->execute();
$fila = $sql->fetch();

// Check for documento in session
$documento = $_SESSION['documento'] ?? null;
if (!$documento) {
    header('Location: ../../login.php');
    exit;
}

// Fetch nombre_completo and foto_perfil if not in session
$nombre_completo = $_SESSION['nombre_completo'] ?? null;
$foto_perfil = $_SESSION['foto_perfil'] ?? null;
if (!$nombre_completo || !$foto_perfil) {
    $user_query = $con->prepare("SELECT nombre_completo, foto_perfil FROM usuarios WHERE documento = :documento");
    $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
    $user_query->execute();
    $user = $user_query->fetch(PDO::FETCH_ASSOC);
    $nombre_completo = $user['nombre_completo'] ?? 'Usuario';
    $foto_perfil = $user['foto_perfil'] ?: '/Flotavehicular/roles/user/css/img/perfil.jpg';
    $_SESSION['nombre_completo'] = $nombre_completo;
    $_SESSION['foto_perfil'] = $foto_perfil;
}

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
  <title>Perfil de Usuario</title>
  <link rel="shortcut icon" href="../../css/img/logo_sinfondo.png">
  <link rel="stylesheet" href="css/perfil.css" />
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include 'menu.php'; ?> <!-- Sidebar fuera del contenido principal -->

  <div class="content">
    <div class="modal-content">
      <!-- Cabecera del perfil -->
      <div class="profile-header">
        <button class="close" onclick="closeModal()">
          <i class="bi bi-x"></i>
        </button>
        <h2><i class="bi bi-person-circle"></i> Información del Usuario</h2>
        
        <!-- Contenedor de la imagen de perfil -->
        <div class="profile-image-container">
          <img src="<?php echo $imagePath; ?>" alt="Foto de Perfil" class="usu_imagen">
        </div>
      </div>
      
      <!-- Cuerpo del perfil -->
      <div class="profile-body">
        <!-- Información del usuario -->
        <div class="user-info">
          <div class="info-item">
            <i class="bi bi-person-badge"></i>
            <div class="info-label">Documento:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['documento']); ?></div>
          </div>
          
          <div class="info-item">
            <i class="bi bi-person"></i>
            <div class="info-label">Nombre:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['nombre_completo']); ?></div>
          </div>
          
          <div class="info-item">
            <i class="bi bi-envelope"></i>
            <div class="info-label">Email:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
          </div>
          
          <div class="info-item">
            <i class="bi bi-telephone"></i>
            <div class="info-label">Teléfono:</div>
            <div class="info-value"><?php echo htmlspecialchars($user['telefono']); ?></div>
          </div>
        </div>
        
        <!-- Sección de actualización de foto -->
        <div class="update-photo-section">
          <div class="section-title">
            <i class="bi bi-camera"></i> Actualizar foto de perfil
          </div>
          
          <p class="upload-instructions">
            <i class="bi bi-info-circle"></i> Formatos: JPEG, PNG, GIF. Máximo 5MB. Recomendado: 512x512 píxeles.
          </p>
          
          <form action="actualizar_foto.php" method="post" enctype="multipart/form-data">
            <div class="input-file-custom">
              <button type="button" class="input-file-btn">
                <i class="bi bi-cloud-upload"></i> Elegir archivo
              </button>
              <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif" onchange="updateFileName(this)">
            </div>
            <div class="file-name" id="file-name-display">Ningún archivo seleccionado</div>
            
            <div class="action-buttons">
              <button type="submit" class="boton btn-update">
                <i class="bi bi-check-circle"></i> Actualizar Foto
              </button>
              
              <button type="button" class="boton btn-delete" onclick="confirmDelete()">
                <i class="bi bi-trash"></i> Borrar Imagen
              </button>
            </div>
          </form>
          
          <!-- Formulario oculto para borrar imagen -->
          <form id="delete-form" action="actualizar_foto.php" method="post" style="display: none;">
            <input type="hidden" name="reset_image" value="1">
          </form>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    // Función para mostrar el nombre del archivo seleccionado
    function updateFileName(input) {
      const fileNameDisplay = document.getElementById('file-name-display');
      if (input.files && input.files[0]) {
        fileNameDisplay.textContent = input.files[0].name;
      } else {
        fileNameDisplay.textContent = 'Ningún archivo seleccionado';
      }
    }
    
    // Función para confirmar eliminación de imagen
    function confirmDelete() {
      if (confirm('¿Estás seguro de que deseas eliminar tu foto de perfil?')) {
        document.getElementById('delete-form').submit();
      }
    }
    
    // Función para cerrar el modal
    function closeModal() {
      window.history.back();
    }
  </script>
</body>
</html>
