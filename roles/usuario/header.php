<?php
if (!defined('BASE_URL')) {
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        define('BASE_URL', '/Flotavehicular');
    } else {
        define('BASE_URL', '');
    }
}

$documento = $_SESSION['documento'];
$notif_stmt = $con->prepare("SELECT * FROM notificaciones WHERE documento_usuario = ? ORDER BY fecha DESC LIMIT 5");
$notif_stmt->execute([$documento]);
$notificaciones = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);
// Contar no leídas
$no_leidas = 0;
foreach ($notificaciones as $notif) {
    if (!$notif['leido']) $no_leidas++;
}
?>
<div class="header">
    <div class="logo">
        <a href="<?= BASE_URL ?>/roles/usuario/index">
            <img src="<?= BASE_URL ?>/roles/usuario/css/img/logo_sinfondo.png" alt="Logo">
            <span class="empresa">Flotax AGC</span>
        </a>
    </div>
    <div class="menu">
        <a href="<?= BASE_URL ?>/roles/usuario/index" class="boton">Inicio</a>
        <a href="<?= BASE_URL ?>/roles/usuario/vehiculos/registrar_vehiculos" class="boton">Registrar Vehículo</a>
        <div class="dropdown">
            <a href="#" class="boton">Historiales ▾</a>
            <div class="dropdown-content">
                <a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_soat">Historial de SOAT</a>
                <a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_tecnomecanica">Historial de Tecnomecánica</a>
                <a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_licencia">Historial de Licencia de Conducción</a>
                <a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_llantas">Historial de Llantas</a>
                <a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_mantenimiento">Historial de Mantenimiento</a>
            </div>
        </div> 
    </div>
    <!-- Notificaciones -->
    <div class="notificaciones">
        <button id="btnNotif" onclick="toggleNotificaciones()" class="campana">
            <?php if ($no_leidas > 0): ?>
                <i class="bi bi-bell-fill" style="color:#d32f2f"></i>
                <span id="notificacion-badge" class="badge badge-alert"><?= $no_leidas ?></span>
            <?php else: ?>
                <i class="bi bi-bell" style="color:#888"></i>
            <?php endif; ?>
        </button>
        <div id="contenedor-notificaciones" class="panel-notificaciones" style="display: none;">
            <?php
            if (count($notificaciones) > 0) {
                foreach ($notificaciones as $notif) {
                    echo '<div class="notificacion'.($notif['leido'] ? '' : ' no-leida').'" data-id="'.$notif['id'].'" onmouseenter="marcarLeida(this)">';
                    echo '<p>' . htmlspecialchars($notif['mensaje']) . '</p>';
                    echo '<small>' . $notif['fecha'] . '</small>';
                    echo '</div>';
                }
            } else {
                echo '<div class="notificacion vacia"><p><i class="bi bi-inbox"></i> No tienes notificaciones nuevas.</p></div>';
            }
            ?>
        </div>
    </div>
    <div class="perfil" onclick="openModal()">
        <img src="<?= htmlspecialchars(BASE_URL . $_SESSION['foto_perfil']) ?>" alt="Foto de perfil" class="imagen-usuario">
        <div class="info-usuario">
            <span><?php echo htmlspecialchars($nombre_completo); ?></span>
            <span>Perfil Usuario</span>
        </div>
    </div>
    <!-- Hamburguesa -->
    <button class="hamburger" id="hamburgerBtn" aria-label="Abrir menú">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <!-- Menú hamburguesa lateral -->
    <nav class="menu-hamburguesa" id="menuHamburguesa">
        <button class="close-hamburger" onclick="toggleHamburguesa()">&times;</button>
        <div class="perfil-hamburguesa" onclick="openModal()">
            <img src="<?= htmlspecialchars(BASE_URL . $_SESSION['foto_perfil']) ?>" alt="Foto de perfil" class="imagen-usuario">
            <div class="info-usuario">
                <span><?= htmlspecialchars($nombre_completo); ?></span>
                <span>Perfil Usuario</span>
            </div>
        </div>
        <ul>
            <li><a href="<?= BASE_URL ?>/roles/usuario/index"><i class="bi bi-house"></i> Inicio</a></li>
            <li><a href="<?= BASE_URL ?>/roles/usuario/vehiculos/registrar_vehiculos"><i class="bi bi-plus-circle"></i> Registrar Vehículo</a></li>
            <li class="submenu">
                <a href="#"><i class="bi bi-clock-history"></i> Historiales <span class="arrow">&#9662;</span></a>
                <ul class="submenu-content">
                    <li><a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_soat">Historial de SOAT</a></li>
                    <li><a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_tecnomecanica">Tecnomecánica</a></li>
                    <li><a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_licencia">Licencia</a></li>
                    <li><a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_llantas">Llantas</a></li>
                    <li><a href="<?= BASE_URL ?>/roles/usuario/historiales/ver_mantenimiento">Mantenimiento</a></li>
                </ul>
            </li>
            <li>
                <div class="notificaciones-hamburguesa" onclick="toggleNotificacionesHamburguesa(event)">
                    <span><i class="bi bi-bell<?= $no_leidas > 0 ? '-fill' : '' ?>" style="color:<?= $no_leidas > 0 ? '#d32f2f' : '#888' ?>"></i></span>
                    <?php if ($no_leidas > 0): ?>
                        <span class="badge badge-alert"><?= $no_leidas ?></span>
                    <?php endif; ?>
                    <span class="notif-txt">Notificaciones</span>
                </div>
                <div id="panel-notificaciones-hamburguesa" class="panel-notificaciones-hamburguesa">
                    <?php
                    if (count($notificaciones) > 0) {
                        foreach ($notificaciones as $notif) {
                            echo '<div class="notificacion'.($notif['leido'] ? '' : ' no-leida').'" data-id="'.$notif['id'].'" onmouseenter="marcarLeida(this)">';
                            echo '<p>' . htmlspecialchars($notif['mensaje']) . '</p>';
                            echo '<small>' . $notif['fecha'] . '</small>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="notificacion vacia"><p><i class="bi bi-inbox"></i> No tienes notificaciones nuevas.</p></div>';
                    }
                    ?>
                </div>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/includes/salir.php" class="logout-hamburguesa">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </nav>
</div>
<!-- Modal de perfil -->
<div id="profileModal" class="modal">
    <div class="modal-content">
        <button class="close" onclick="closeModal()">Cerrar</button>
        <h2>Información del Usuario</h2>
        <?php
        $imagePath = htmlspecialchars(BASE_URL . $foto_perfil) . '?v=' . time();
        ?>
        <img src="<?php echo $imagePath; ?>" alt="Foto de Perfil" class="usu_imagen" style="max-width: 100px; height: 100px;">
        <?php if ($foto_perfil === '/roles/usuario/css/img/perfil.jpg'): ?>
        <?php endif; ?>
        <?php
        $user_query = $con->prepare("SELECT documento, nombre_completo, email, telefono FROM usuarios WHERE documento = :documento");
        $user_query->bindParam(':documento', $documento, PDO::PARAM_STR);
        $user_query->execute();
        $user = $user_query->fetch(PDO::FETCH_ASSOC);
        ?>
        <p><strong>Documento:</strong> <?php echo htmlspecialchars($user['documento']); ?></p>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user['nombre_completo']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($user['telefono']); ?></p>
        <form action="<?= BASE_URL ?>/roles/usuario/actualizar_foto.php" method="post" enctype="multipart/form-data">
            <label for="foto_perfil">Cambiar Foto de Perfil:</label>
            <p class="upload-instructions">Formatos: JPEG, PNG, GIF. Máximo 5MB. Recomendado: 512x512 píxeles.</p>
            <div class="input-file-custom">
                <button class="input-file-btn">
                    <i class="bi bi-cloud-upload"></i> Elegir archivo
                </button>
                <input type="file" id="foto_perfil" name="foto_perfil" accept="image/jpeg,image/png,image/gif">
            </div>
            <br>
            <button type="submit" class="boton">Actualizar Foto</button>
        </form>
        <form action="<?= BASE_URL ?>/roles/usuario/actualizar_foto.php" method="post">
            <input type="hidden" name="reset_image" value="1">
            <button type="submit" class="boton">Borrar Imagen</button>
        </form>
    </div>
</div>
<div class="sidebar" id="sidebar">
    <a href="<?= BASE_URL ?>/includes/salir.php" class="logout" title="Cerrar Sesión">
        <i class="bi bi-box-arrow-right"></i>
    </a>
</div>
<style>
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: linear-gradient(135deg, #ffffff, #f1f1f1);
        padding: 20px 40px;
        border-bottom: 3px solid #d32f2f;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .logo {
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .logo:hover {
        transform: scale(1.05);
    }

    .logo img {
        width: 75px;
        height: 70px;
        border-radius: 50%;
        margin-right: 15px;
    }

    .empresa {
        font-size: 32px;
        font-weight: 700;
        color: #d32f2f;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .logo a{
        text-decoration: none;
        text-align:center;
        display: flex;
        align-items: center;
    }

    .menu {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .menu .boton {
        background: linear-gradient(135deg, #d32f2f, #b71c1c);
        color: #fff;
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: solid 3px #d32f2f;
        transition: transform 0.3s ease;
    }
    

    .menu .boton:hover {
        background: transparent;
        border: solid 3px #d32f2f;
        transform: scale(1.05);
        color: #333;
    }

    .perfil {
        display: flex;
        align-items: center;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .perfil:hover {
        transform: scale(1.05);
    }

    .imagen-usuario {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--primary-color);
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 5px;
        border: 2px solid #d32f2f;
        object-fit: cover;
    }

    .info-usuario {
        text-align: right;
    }

    .info-usuario span {
        display: block;
        color: #333;
        font-size: 16px;
        font-weight: 600;
    }

    .info-usuario span:last-child {
        font-size: 14px;
        font-weight: 400;
        color: #666;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .modal-content {
        background: linear-gradient(135deg, #ffffff, #f8f9fa);
        padding: 30px;
        border-radius: 15px;
        width: 90%;
        max-width: 650px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        position: relative;
    }

    .modal-content h2 {
        color: #d32f2f;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 20px;
        text-transform: uppercase;
    }

    .modal-content p {
        margin: 5px 0;
        font-size: 16px;
        color: #333;
    }

    .modal-content p strong {
        color: #d32f2f;
    }

    .modal-content form {
        margin-top: 6px;
    }

    .modal-content label{
        color: #d32f2f;
        font-weight: 600;
    }

    .modal-content input[type="file"] {
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        width: 100%;
        font-size: 16px;
    }

    .input-file-custom {
        position: relative;
        overflow: hidden;
        display: inline-block;
        width: 100%;
    }

    .input-file-custom input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        height: 100%;
        border-radius: 8px;
        width: 100%;
        cursor: pointer;
    }

    .input-file-btn {
        background-color: #d32f2f;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        border: none;
        width: 100%;
        justify-content: center;
        transition: background-color 0.3s ease;
    }


    .input-file-custom:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 15px rgba(211, 47, 47, 0.5);
    }



    .modal-content .boton {
        background: linear-gradient(135deg, #d32f2f, #b71c1c);
        color: #fff;
        padding: 10px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: solid 3px #d32f2f;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .modal-content .boton:hover {
        background: transparent;
        border: solid 3px #d32f2f;
        transform: scale(1.05);
        color: #333;
        box-shadow: 0 6px 15px rgba(211, 47, 47, 0.5);
    }

    .usu_imagen {
        width: 100px;
        border: solid 3px #d32f2f;
        display: block;
        border-radius: 50%;
        margin-left: auto;
        margin-right: auto;
        object-fit: cover;
    }

    .modal-content .close {
        position: absolute;
        bottom: 20px;
        right: 15px;
        padding: 8px 15px;
        background: #333;
        color: #fff;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .modal-content .close:hover {
        transform: scale(1.05);
    }
     .dropdown {
      position: relative;
      display: inline-block;
    }

    /* Estilo del enlace principal */
    .boton {
      background-color:rgb(255, 255, 255);
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      display: inline-block;
    }

    /* Submenú oculto */
    .dropdown-content {
      display: none;
      position: absolute;
      background-color: #f1f1f1;
      min-width: 180px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.2);
      z-index: 1;
    }

    /* Enlaces dentro del submenú */
    .dropdown-content a {
      color: black;
      padding: 10px 16px;
      text-decoration: none;
      display: block;
    }

    .dropdown-content a:hover {
      background-color: #ddd;
    }

    /* Mostrar submenú al pasar el mouse por el enlace principal */
    .dropdown:hover .dropdown-content {
      display: block;
    }

    .dropdown:hover .boton {
      background-color:rgb(255, 255, 255);
    }

    /* Sidebar */
    .sidebar {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 10;
    }

    .logout {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #d32f2f, #b71c1c);
        color: #fff;
        border-radius: 50%;
        text-decoration: none;
        font-size: 24px;
        box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
    }

    .logout:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 15px rgba(211, 47, 47, 0.5);
    }

    .logout::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        right: 0;
        background-color: #333;
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .logout:hover::after {
        opacity: 1;
        visibility: visible;
    }
    .notificaciones {
    position: relative;
    }
    
    .campana {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 25px;
        color: #d32f2f;
    }
    
    .panel-notificaciones {
        position: absolute;
        top: 60px;
        right: 0;
        width: 340px;
        max-height: 350px;
        overflow-y: auto;
        background: linear-gradient(135deg, #fff, #f8f9fa 80%);
        border: 1.5px solid #d32f2f;
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(211,47,47,0.08), 0 1.5px 4px #d32f2f22;
        z-index: 999;
        display: none;
        padding: 0;
    }
    
    .notificacion {
        padding: 14px 18px 10px 18px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
        cursor: pointer;
        position: relative;
    }

    .notificacion.no-leida {
        background: #ffeaea;
        font-weight: 600;
        border-left: 4px solid #d32f2f;
    }

    .notificacion.vacia {
        text-align: center;
        color: #888;
        font-size: 16px;
        background: #f9f9f9;
        border-radius: 0 0 14px 14px;
        border-bottom: none;
        padding: 30px 0 30px 0;
    }

    .notificacion p {
        margin: 0 0 4px 0;
        font-size: 15px;
        color: #333;
    }

    .notificacion small {
        color: #b71c1c;
        font-size: 12px;
        font-weight: 500;
    }

    .badge-alert {
        background: #d32f2f;
        color: #fff;
        font-size: 13px;
        padding: 2px 7px;
        border-radius: 50%;
        position: absolute;
        top: 2px;
        right: 2px;
        z-index: 2;
        font-weight: bold;
        box-shadow: 0 2px 6px rgba(211,47,47,0.2);
        animation: pulse 1s infinite alternate;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 #d32f2f44; }
        100% { box-shadow: 0 0 8px 4px #d32f2f44; }
    }

    /* Hamburguesa */
.hamburger {
    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 44px;
    height: 44px;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 1101;
    margin-left: auto;
}
.hamburger span {
    display: block;
    width: 28px;
    height: 4px;
    margin: 4px 0;
    background: #d32f2f;
    border-radius: 2px;
    transition: all 0.3s;
}
.menu-hamburguesa {
    position: fixed;
    top: 0;
    right: -320px;
    width: 320px;
    height: 100vh;
    background: #fff;
    box-shadow: -2px 0 16px rgba(0,0,0,0.12);
    z-index: 1100;
    transition: right 0.4s cubic-bezier(.77,0,.18,1), box-shadow 0.3s;
    padding: 30px 20px 20px 20px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    opacity: 0;
    pointer-events: none;
}
.menu-hamburguesa.open {
    right: 0;
    opacity: 1;
    pointer-events: auto;
    animation: slideInMenu 0.4s cubic-bezier(.77,0,.18,1);
}
@keyframes slideInMenu {
    from { right: -320px; opacity: 0; }
    to { right: 0; opacity: 1; }
}
.close-hamburger {
    background: none;
    border: none;
    font-size: 32px;
    color: #d32f2f;
    position: absolute;
    top: 12px;
    right: 18px;
    cursor: pointer;
}
.perfil-hamburguesa {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 10px;
    cursor: pointer;
}
.menu-hamburguesa .imagen-usuario {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 2px solid #d32f2f;
    object-fit: cover;
}
.menu-hamburguesa .info-usuario span {
    font-size: 15px;
    color: #333;
    font-weight: 600;
}
.menu-hamburguesa .info-usuario span:last-child {
    font-size: 12px;
    color: #666;
    font-weight: 400;
}
.menu-hamburguesa ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.menu-hamburguesa ul li {
    margin-bottom: 12px;
}
.menu-hamburguesa ul li a {
    color: #d32f2f;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color 0.2s;
}
.menu-hamburguesa ul li a:hover {
    color: #b71c1c;
}
.menu-hamburguesa .submenu > a {
    cursor: pointer;
    position: relative;
}
.menu-hamburguesa .submenu-content {
    display: none;
    margin-left: 18px;
    margin-top: 6px;
}
.menu-hamburguesa .submenu.open .submenu-content {
    display: block;
}
.menu-hamburguesa .arrow {
    font-size: 12px;
    margin-left: 6px;
}
.notificaciones-hamburguesa {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    position: relative;
}
.notificaciones-hamburguesa .badge-alert {
    position: static;
    margin-left: 2px;
    font-size: 12px;
    padding: 2px 6px;
    animation: pulse 1s infinite alternate;
}
.panel-notificaciones-hamburguesa {
    background: #f8f9fa;
    border-radius: 10px;
    margin-top: 10px;
    padding: 8px 8px 4px 8px;
    max-height: 180px;
    overflow-y: auto;
    box-shadow: 0 2px 8px #d32f2f22;
    display: none;
    opacity: 0;
    transition: opacity 0.3s;
}
.panel-notificaciones-hamburguesa.show {
    display: block;
    opacity: 1;
    animation: fadeInNotif 0.3s;
}
@keyframes fadeInNotif {
    from { opacity: 0; transform: translateY(-20px) scale(0.98);}
    to { opacity: 1; transform: translateY(0) scale(1);}
}
/* Responsivo */
@media (max-width: 1085px) {
    .hamburger { display: flex; }
    .menu, .perfil, .notificaciones { display: none !important; }
    .header { padding: 12px 8px; }
    .logo img { width: 38px; height: 36px; margin-right: 6px; }
    .empresa { font-size: 14px; }
    .sidebar { display: none; }
}
@media (max-width: 600px) {
    .menu-hamburguesa { width: 60vw; padding: 18px 4vw 10px 4vw; }
    .panel-notificaciones-hamburguesa { max-width: 60vw; }
    .panel-notificaciones { width: 95vw; min-width: 180px; }
    .modal-content { padding: 10px; max-width: 98vw; }
}
@media (max-width: 400px) {
    .menu-hamburguesa { width: 60vw; padding: 10px 2vw 6px 2vw; }
    .panel-notificaciones-hamburguesa { max-width: 99vw; }
    .panel-notificaciones { width: 99vw; }
}
</style>
<script>
function openModal() {
    document.getElementById('profileModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('profileModal').style.display = 'none';
}
function toggleNotificaciones() {
    const panel = document.getElementById('contenedor-notificaciones');
    if (panel.style.display === 'none' || panel.style.display === '') {
        panel.style.display = 'block';
    } else {
        panel.style.display = 'none';
    }
}
// Opcional: cerrar notificaciones si se hace clic fuera del panel
document.addEventListener('click', function(event) {
    const panel = document.getElementById('contenedor-notificaciones');
    const btn = document.getElementById('btnNotif');
    if (panel && !panel.contains(event.target) && !btn.contains(event.target)) {
        panel.style.display = 'none';
    }
});
function marcarLeida(element) {
    if (element.classList.contains('no-leida')) {
        const id = element.getAttribute('data-id');
        fetch('<?= BASE_URL ?>/roles/usuario/AJAX/marcar_notificacion_leida.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(id)
        })
        .then(response => response.text())
        .then(data => {
            element.classList.remove('no-leida');
            // Actualiza el badge y el icono
            const badge = document.getElementById('notificacion-badge');
            const icon = document.querySelector('#btnNotif i');
            if (badge) {
                let count = parseInt(badge.textContent) - 1;
                if (count <= 0) {
                    badge.style.display = 'none';
                    if (icon) {
                        icon.classList.remove('bi-bell-fill');
                        icon.classList.add('bi-bell');
                        icon.style.color = '#888';
                    }
                } else {
                    badge.textContent = count;
                }
            }
        });
    }
}

// Menú hamburguesa
function toggleHamburguesa() {
    const menu = document.getElementById('menuHamburguesa');
    menu.classList.toggle('open');
    const btn = document.getElementById('hamburgerBtn');
    if (menu.classList.contains('open')) {
        btn.setAttribute('aria-label', 'Cerrar menú');
        btn.style.display = 'none';
    } else {
        btn.setAttribute('aria-label', 'Abrir menú');
        btn.style.display = 'flex';
    }

}
document.getElementById('hamburgerBtn').onclick = function(e) {
    e.stopPropagation();
    toggleHamburguesa();
};

// Cerrar menú hamburguesa al hacer clic fuera
document.addEventListener('click', function(event) {
    const menu = document.getElementById('menuHamburguesa');
    const btn = document.getElementById('hamburgerBtn');
    if (menu.classList.contains('open') && !menu.contains(event.target) && !btn.contains(event.target)) {
        menu.classList.remove('open');
    }
});

// Submenú historial
document.querySelectorAll('.menu-hamburguesa .submenu > a').forEach(function(el) {
    el.onclick = function(e) {
        e.preventDefault();
        this.parentElement.classList.toggle('open');
    }
});

// Notificaciones en hamburguesa
function toggleNotificacionesHamburguesa(event) {
    event.stopPropagation();
    const panel = document.getElementById('panel-notificaciones-hamburguesa');
    panel.classList.toggle('show');
}
document.addEventListener('click', function(event) {
    const panel = document.getElementById('panel-notificaciones-hamburguesa');
    if (panel && panel.classList.contains('show') && !panel.contains(event.target)) {
        panel.classList.remove('show');
    }
});
</script>
