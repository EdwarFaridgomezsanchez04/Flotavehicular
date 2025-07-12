<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <!-- Iconos de Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- CSS de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Favicon -->
    <link rel="shortcut icon" href="css/img/logo_sinfondo.png">
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="css/stylebody.css">
</head>
<body>
    <?php
    // Incluye el encabezado del sitio (barra de navegación, logo, etc.)
    include ('header.html');
    ?>
 
    <!-- Carrusel de imágenes principales -->
    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <!-- Botones para navegar entre slides -->
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <!-- Primer slide activo -->
            <div class="carousel-item active">
                <img src="css/img/slider3.png" class="d-block w-100" alt="Slide 1">
                <div class="carousel-caption d-none d-md-block">
                    <!-- Espacio para texto o botones sobre la imagen -->
                </div>
            </div>
            <!-- Segundo slide -->
            <div class="carousel-item">
                <img src="css/img/slider6.jpg" class="d-block w-100" alt="Slide 2">
                <div class="carousel-caption d-none d-md-block">
                </div>
            </div>
            <!-- Tercer slide -->
            <div class="carousel-item">
                <img src="css/img/slider7.png" class="d-block w-100" alt="Slide 3">
                <div class="carousel-caption d-none d-md-block">
                </div>
            </div>
        </div>
        <!-- Controles para avanzar o retroceder el carrusel -->
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
        </button>
    </div>

    <!-- Sección de bienvenida y descripción -->
    <div class="contenido">
        <!-- Logo principal -->
        <img src="css/img/logo.png" alt="logo">
        <div class="parrafo">
            <h3>Bienvenido</h3>
            <p>Gestiona tu flota vehicular de manera eficiente y segura. Nuestro software te permite:
                    -Realizar un seguimiento completo del mantenimiento de tus vehículos.
                    -Optimizar costos operativos mediante análisis detallados.
                <br>
                    -Acceso rápido a la documentación de cada vehículo.
                <br>
                ¡Simplifica la gestión de tu flota y aumenta la productividad con nuestra solución integral!
                <br>
            </p>
        </div>

    </div>
<?php
    // Incluye el pie de página del sitio
    include('footer.html');
?>
    
    
    
</body>
</html>