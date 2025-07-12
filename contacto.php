<?php
// Conexión a la base de datos (si se requiere para futuras funcionalidades)
require_once('conecct/conex.php');
$db = new Database();
$con = $db->conectar();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Flotax AGC</title>
    <!-- Iconos de Bootstrap y FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="css/img/logo_sinfondo.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
      /* Estilos generales y responsivos para la página de contacto */
      * {
        margin: 0;
        padding: 0;
        font-family: "Inter", sans-serif;
        list-style: none;
        text-decoration: none;
        box-sizing: border-box;
      }

      body {
        line-height: 1.6;
        color: #333;
          background-color: var(--bs-body-bg);
        min-height: 100vh;
      }

      /* Contact Hero Section */
      .contact-hero {
        background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
        color: white;
        text-align: center;
        padding: 80px 20px;
        position: relative;
        overflow: hidden;
      }

      .contact-hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
      }

      .hero-content {
        position: relative;
        z-index: 2;
        max-width: 600px;
        margin: 0 auto;
      }

      .hero-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        margin-bottom: 20px;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.2);
      }

      .hero-icon i {
        font-size: 2rem;
      }

      .contact-hero h1 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 15px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }

      .contact-hero p {
        font-size: 1.2rem;
        opacity: 0.95;
      }

      /* Enhanced Contact Info */
      .contenido_info {
        display: flex;
        justify-content: center;
        width: 100%;
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
        gap: 40px;
      }

      .informa {
        width: 50%;
        height: 100%;
      }

      .info-card {
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        border-left: 4px solid #e53e3e;
      }

      .info-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: #e53e3e;
        border-radius: 50%;
        color: white;
        margin-bottom: 15px;
      }

      .info-card h2 {
        color: #2d3748;
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1.4rem;
      }

      .info-card p {
        color: #4a5568;
        line-height: 1.7;
      }

      .contact-details {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 30px;
      }

      .contact-item {
        display: flex;
        align-items: center;
        gap: 15px;
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f1f5f9;
      }

      .contact-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(229, 62, 62, 0.1);
        border-color: white;
      }

      .contact-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        color: white;
        background: #e53e3e;
      }

      .contact-info h3 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d3748;
      }

      .contact-info a {
        color: #4a5568;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.3s ease;
      }

      .contact-info a:hover {
        color: #e53e3e;
      }

      .support-message {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 30px;
        border: 1px solid #e2e8f0;
      }

      .support-message i {
        color: #e53e3e;
        margin-right: 8px;
      }

      .support-message p {
        margin: 0;
        color: #4a5568;
      }

      /* Enhanced Form Styles */
      .contenido_form {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        width: 50%;
        padding-top: 10px;
        padding-bottom: 10px;
      }

      .formulario {
        width: 100%;
        max-width: 450px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        border: 1px solid #e2e8f0;
      }

      .form-header {
        background: #e53e3e;
        padding: 30px;
        text-align: center;
        color: white;
      }

      .form-header h3 {
        margin: 0 0 10px 0;
        font-size: 1.5rem;
        font-weight: 700;
      }

      .form-header p {
        margin: 0;
        opacity: 0.95;
      }

      .form {
        padding: 40px;
      }

      .form label {
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        margin-bottom: 8px;
      }

      .form label i {
        color: #e53e3e;
      }

      .form input,
      .form textarea {
        border: 2px solid #e2e8f0;
        outline: none;
        width: 100%;
        padding: 12px 15px;
        border-radius: 10px;
        background: #f8f9fa;
        transition: all 0.3s ease;
        font-family: "Inter", sans-serif;
        color: #2d3748;
      }

      .form input:focus,
      .form textarea:focus {
        border-color: #e53e3e;
        box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
        background: white;
      }

      .input_field,
      .input_fiel {
        padding-top: 15px;
      }

      .input_mensa {
        height: 120px;
        resize: vertical;
      }

      .btn-send {
        background: #e53e3e;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
        width: 100%;
      }

      .btn-send:hover {
        background: #c53030;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(229, 62, 62, 0.3);
      }

      .warnings {
        width: 100%;
        font-size: 12px;
        text-align: left;
        margin: 5px 0 0 0;
        background: none  !important;
        color: red !important;
        padding-top: 5px;
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .warnings.success {
        color: #38a169 !important;
        background: none !important;
      }

      .boton {
        padding-top: 20px;
      }

      /* Image Container */
      .img2 {
        padding: 0;
        width: 100%;
      }

      .image-container-contact {
        position: relative;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
      }

      .image-container-contact:hover {
        transform: scale(1.02);
      }

      .image-container-contact img {
        width: 100%;
        height: auto;
        display: block;
      }

      .image-overlay-contact {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(229, 62, 62, 0.2);
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .image-container-contact:hover .image-overlay-contact {
        opacity: 1;
      }

      /* Developers Section */
      .developers-section {
        background: #f8f9fa;
        padding: 80px 20px;
        margin-top: 60px;
      }

      .developers-container {
        max-width: 1200px;
        margin: 0 auto;
      }

      .section-header-dev {
        text-align: center;
        margin-bottom: 60px;
      }

      .dev-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        background: #e53e3e;
        border-radius: 50%;
        color: white;
        margin-bottom: 20px;
      }

      .dev-icon i {
        font-size: 1.8rem;
      }

      .section-header-dev h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 15px;
      }

      .section-header-dev p {
        font-size: 1.2rem;
        color: #4a5568;
      }

      .developers-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
        margin-top: 50px;
      }

      .developer-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        border: 1px solid #f1f5f9;
      }

      .developer-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 50px rgba(229, 62, 62, 0.1);
        border-color: #e53e3e;
      }

      .developer-image {
        position: relative;
        height: 300px;
        overflow: hidden;
      }

      .developer-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
      }

      .developer-card:hover .developer-image img {
        transform: scale(1.1);
      }

      .developer-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(229, 62, 62, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .developer-card:hover .developer-overlay {
        opacity: 1;
      }

      .social-linkse {
        display: flex;
        gap: 15px;
      }

      .social-linki {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
      }

      .social-linki:hover {
        background: white;
        color: #e53e3e;
        transform: scale(1.1);
      }

      .developer-info {
        padding: 30px;
        text-align: center;
      }

      .developer-info h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
      }

      .role {
        color: #e53e3e;
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1rem;
      }

      .description {
        color: #4a5568;
        line-height: 1.6;
        margin-bottom: 20px;
      }

      .skills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
      }

      .skill {
        background: #f1f5f9;
        color: #2d3748;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        border: 1px solid #e2e8f0;
      }

      /* Contact CTA */
      .contact-cta {
        background: #e53e3e;
        color: white;
        text-align: center;
        padding: 80px 20px;
      }

      .contact-cta .cta-content {
        max-width: 600px;
        margin: 0 auto;
      }

      .contact-cta h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 20px;
      }

      .contact-cta p {
        font-size: 1.2rem;
        opacity: 0.95;
        margin-bottom: 40px;
      }

      .cta-buttons {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
      }

      .btn-primary,
      .btn-secondary {
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
      }

      .btn-primary {
        background: white;
        color: #e53e3e;
      }

      .btn-primary:hover {
        background: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
      }

      .btn-secondary {
        background: transparent;
        color: white;
        border: 2px solid white;
      }

      .btn-secondary:hover {
        background: white;
        color: #e53e3e;
        transform: translateY(-2px);
      }

      /* Responsive Design */
      @media (max-width: 767px) {
        .contact-hero {
          padding: 60px 20px;
        }

        .contact-hero h1 {
          font-size: 2rem;
        }

        .contenido_info {
          flex-direction: column;
          align-items: center;
          padding: 10px;
          gap: 20px;
        }

        .informa,
        .contenido_form {
          width: 100%;
        }

        .formulario {
          width: 90%;
          max-width: none;
          margin: 0 auto;
        }

        .form {
          padding: 30px 20px;
        }

        .developers-grid {
          grid-template-columns: 1fr;
          gap: 30px;
        }

        .cta-buttons {
          flex-direction: column;
          align-items: center;
        }

        .btn-primary,
        .btn-secondary {
          width: 200px;
          justify-content: center;
        }

        .section-header-dev h2 {
          font-size: 2rem;
        }
      }

      @media (min-width: 768px) and (max-width: 1199px) {
        .contenido_info {
          flex-direction: column;
          align-items: center;
        }

        .informa,
        .contenido_form {
          width: 80%;
        }

        .developers-grid {
          grid-template-columns: repeat(2, 1fr);
        }

        .formulario {
          padding: 40px;
        }
      }

      @media (min-width: 1200px) {
        .contenido_info {
          flex-direction: row;
          justify-content: center;
          gap: 40px;
          padding: 20px;
        }

        .informa,
        .contenido_form {
          width: 45%;
        }

        .formulario {
          padding: 0;
        }
      }

      /* Animations */
      @keyframes fadeInUp {
        from {
          opacity: 0;
          transform: translateY(30px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .info-card,
      .contact-item,
      .developer-card {
        animation: fadeInUp 0.6s ease-out;
      }
  </style>
</head>
<body>
<?php
    // Incluye el encabezado del sitio (barra de navegación, logo, etc.)
    include ('header.html');
?>

<!-- Sección de información de contacto y formulario -->
<div class="contenido_info">
    <div class="informa">
        <!-- Tarjeta informativa principal -->
        <div class="info-card">
            <div class="info-icon">
                <i class="fas fa-question-circle"></i>
            </div>
            <h2>¿Tienes dudas o necesitas más información?</h2>
            <p>Si tienes alguna pregunta o necesitas más detalles sobre nuestros servicios, no dudes en
                ponerte en contacto con nosotros. Para todas las consultas, envíanos un correo electrónico
                a:
            </p>
        </div>

        <!-- Detalles de contacto: correo y teléfono -->
        <div class="contact-details">
            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="contact-info">
                    <h3>Correo Electrónico:</h3>
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=flotavehicularagc@gmail.com">flotavehicularagc@gmail.com</a>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="contact-info">
                    <h3>Teléfono:</h3>
                    <a href="falta">falta</a>
                </div>
            </div>
        </div>

        <!-- Mensaje de soporte -->
        <div class="support-message">
            <p><i class="fas fa-heart"></i> Estamos aquí para ayudarte y responder a todas tus inquietudes.</p>
        </div>

        <!-- Imagen ilustrativa de contacto -->
        <div class="img2">
            <div class="image-container-contact">
                <img src="css/img/contacto.png" alt="Contacto Flotax AGC">
                <div class="image-overlay-contact"></div>
            </div>
        </div>
    </div>

    <!-- Formulario de contacto -->
    <div class="contenido_form">
        <div class="formulario">
            <div class="form-header">
                <h3>Envíanos un mensaje</h3>
                <p>Completa el formulario y te responderemos pronto</p>
            </div>
            
            <form action="" method="post" class="form" id="form" enctype="multipart/form-data" autocomplete="off">
                <div class="input-gruop">
                    <div class="input_field" id="grupo_nom">
                        <label for="nom" class="input_label">
                            <i class="fas fa-user"></i> Nombre:*
                        </label>
                        <input type="text" name="nom" id="nom" placeholder="Juan" >
                        <p class="warnings" id="warnings">Ingrese el nombre sin caracteres especiales</p>
                    </div>

                    <div class="input_field">
                        <label for="ape">
                            <i class="fas fa-user"></i> Apellido:*
                        </label>
                        <input type="text" name="ape" id="ape" placeholder="López">
                        <p class="warnings" id="warnings1">Ingrese el apellido sin caracteres especiales</p>
                    </div>

                    <div class="input_field">
                        <label for="corre">
                            <i class="fas fa-envelope"></i> Correo Electrónico (Solo Gmail):*
                        </label>
                        <input type="email" name="corre" id="corre" placeholder="ejemplo@gmail.com">
                        <p class="warnings" id="warnings2">Ingrese un correo electrónico válido (ejemplo@gmail.com).</p>
                    </div>

                    <div class="input_fiel">
                        <label for="mensa">
                            <i class="fas fa-comment"></i> Mensaje:*
                        </label>
                        <textarea class="input_mensa" type="text" name="mensa" id="mensa" placeholder="Escribe tu mensaje aquí..."></textarea>
                        <p class="warnings" id="warnings3"></p>
                    </div>
                    
                    <div>
                        <p class="warnings" id="warnings4">Rellena el formulario correctamente</p>
                    </div>
                </div>
                
                <div class="boton">
                    <button type="submit" name="enviar" id="enviar" value="guardar" class="btn-send">
                        <i class="fas fa-paper-plane"></i> Enviar mensaje
                    </button>
                </div>

                <div>
                    <p class="warnings success" id="warnings5">Mensaje enviado correctamente</p>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Sección de desarrolladores del proyecto -->
<section class="developers-section">
    <div class="developers-container">
        <div class="section-header-dev">
            <div class="dev-icon">
                <i class="fas fa-code"></i>
            </div>
            <h2>Nuestro Equipo de Desarrollo</h2>
            <p>Conoce a los expertos detrás de Flotax AGC</p>
        </div>

        <div class="developers-grid">
            <!-- Tarjeta de desarrollador 1 -->
            <div class="developer-card">
                <div class="developer-image">
                    <img src="css/img/adrian.jpg" alt="Desarrollador 1">
                    <div class="developer-overlay">
                        <div class="social-linkse">
                            <a href="https://www.linkedin.com/in/adrian-camargo-rodriguez-6bb23b364/" class="social-linki"><i class="fab fa-linkedin"></i></a>
                            <a href="https://github.com/ADRIANCHO207" class="social-linki"><i class="fab fa-github"></i></a>
                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=adriancamargo69@gmail.com" class="social-linki"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="developer-info">
                    <h3>Adrian Camargo</h3>
                    <p class="role">Full Stack Developer</p>
                    <p class="description"></p>
                    <div class="skills">
                        <span class="skill">HTML/CSS/JAVASCRIPT</span>
                        <span class="skill">MySQL</span>
                        <span class="skill">PHP</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de desarrollador 2 -->
            <div class="developer-card">
                <div class="developer-image">
                    <img src="css/img/edwar.jpg" alt="Desarrollador 2">
                    <div class="developer-overlay">
                        <div class="social-linkse">
                            <a href="https://www.linkedin.com/in/edwar-farid-gomez-sanchez-9ab07732a/" class="social-linki"><i class="fab fa-linkedin"></i></a>
                            <a href="https://github.com/EdwarFaridgomezsanchez04" class="social-linki"><i class="fab fa-github"></i></a>
                            <a href="mailto:edwardfaridg@gmail.com" class="social-linki"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="developer-info">
                    <h3>Edwar Gomez</h3>
                    <p class="role">Developer Web Full Stack </p>
                    <p class="description"></p>
                    <div class="skills">
                        <span class="skill">Laravel</span>
                        <span class="skill">MySQL</span>
                        <span class="skill">PHP</span>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de desarrollador 3 -->
            <div class="developer-card">
                <div class="developer-image">
                    <img src="css/img/carlos.jpg" alt="Desarrollador 3">
                    <div class="developer-overlay">
                        <div class="social-linkse">
                            <a href="#" class="social-linki"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="social-linki"><i class="fab fa-github"></i></a>
                            <a href="#" class="social-linki"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                <div class="developer-info">
                    <h3>Carlos Guevara</h3>
                    <p class="role">Full Stack Developer</p>
                    <p class="description"></p>
                    <div class="skills">
                        <span class="skill">Figma</span>
                        <span class="skill">Adobe XD</span>
                        <span class="skill">CSS</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<?php
    // Incluye el pie de página del sitio
    include ('footer.html');
?>
<script src="js/scriptcontacto.js"></script>

</body>
</html>
