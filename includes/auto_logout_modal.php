<?php
// Bloque PHP para configuración de URL base del sistema

// Verificar si la constante BASE_URL ya está definida para evitar redefinición
if (!defined('BASE_URL')) {
    // Detectar el entorno de ejecución para configurar la URL base apropiada
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
        // Configuración para entorno local (XAMPP) - incluye la carpeta del proyecto
        define('BASE_URL', '/Flotavehicular');
    } else {
        // Configuración para entorno de producción - raíz del dominio
        define('BASE_URL', ''); // O '/subcarpeta' si tu proyecto está en una subcarpeta en el hosting
    }
}
?>

<!-- ESTRUCTURA HTML DEL MODAL DE INACTIVIDAD -->
<!-- Modal principal con overlay de fondo difuminado -->
<div id="modalInactividad" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: none; backdrop-filter:blur(4px); z-index:9999; display:flex; align-items:center; justify-content:center;">
    <!-- Contenedor del contenido del modal -->
    <div id="apa" style="display:none;background: rgba(255, 255, 255, 0.95); padding: 25px; border-radius: 12px; text-align: center; max-width: 320px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3); font-family: 'Poppins', sans-serif;">
        <!-- Título del modal -->
        <h3 class="titumo" style="margin-bottom: 10px; font-weight: 600;">¿Sigues ahí?</h3>
        <!-- Mensaje con contador dinámico -->
        <p class="pmo" style="margin-bottom: 20px; font-size: 15px;">Por inactividad, la sesión se cerrará en <span id="tiempoRestante" style="font-weight: bold;">10</span> segundos.</p>
        <!-- Botón para cancelar el cierre automático -->
        <button onclick="cancelarCierre()" class="btn-grad">Seguir aquí</button>
    </div>
</div>

<!-- ESTILOS CSS PARA EL MODAL -->
<style>
    /* Estilos para el botón con gradiente animado */
    .btn-grad {
        padding: 10px 20px;
        background:  #0072ff;
        background-size: 600% 600%;
        border: none;
        border-radius: 25px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 0 15px rgba(0, 114, 255, 0.4);
        animation: gradientMove 5s ease infinite;
        transition: transform 0.2s;
    }

    /* Efecto hover para el botón */
    .btn-grad:hover {
        transform: scale(1.05);
        box-shadow: 0 0 20px rgba(0, 114, 255, 0.7);
    }
</style>

<!-- FUNCIONALIDAD JAVASCRIPT -->
<script>
    // Transferir la constante PHP BASE_URL al contexto JavaScript
    const BASE_URL = "<?= BASE_URL ?>";

    // Configuración de tiempos para el sistema de logout automático
    let tiempoInactividad = 240000; // 4 minutos de inactividad total
    let advertenciaTiempo = 10000;  // Mostrar advertencia 10 segundos antes del logout
    let temporizadorInactividad;    // Variable para el temporizador principal
    let temporizadorAdvertencia;    // Variable para el temporizador de advertencia
    let tiempoRestante = 10;        // Contador regresivo del modal
    let cuentaRegresiva;            // Variable para el intervalo de cuenta regresiva

    // Función principal para reiniciar todos los temporizadores
    function reiniciarTemporizador() {
        // Ocultar el modal si está visible
        document.getElementById("modalInactividad").style.display = "none";
        // Limpiar temporizadores existentes
        clearTimeout(temporizadorInactividad);
        clearTimeout(temporizadorAdvertencia);
        cerrarModal();

        // Configurar temporizador para mostrar advertencia
        temporizadorAdvertencia = setTimeout(() => {
            mostrarModal();
        }, tiempoInactividad - advertenciaTiempo);

        // Configurar temporizador para logout automático
        temporizadorInactividad = setTimeout(() => {
            window.location.href = BASE_URL + "/includes/salir";
        }, tiempoInactividad);
    }

    // Función para mostrar el modal de advertencia
    function mostrarModal() {
        // Configurar el fondo del modal
        document.getElementById("modalInactividad").style.background = "rgba(0,0,0,0.5)";
        document.getElementById("modalInactividad").style.display = "flex";
        document.getElementById("apa").style.display = "block";
        
        // Inicializar cuenta regresiva
        tiempoRestante = 10;
        document.getElementById("tiempoRestante").textContent = tiempoRestante;
        
        // Crear intervalo para actualizar el contador cada segundo
        cuentaRegresiva = setInterval(() => {
            tiempoRestante--;
            document.getElementById("tiempoRestante").textContent = tiempoRestante;
            if (tiempoRestante <= 0) {
                clearInterval(cuentaRegresiva);
            }
        }, 1000);
    }

    // Función para cerrar el modal
    function cerrarModal() {
        document.getElementById("modalInactividad").style.display = "none";
        clearInterval(cuentaRegresiva);
    }

    // Función llamada por el botón "Seguir aquí"
    function cancelarCierre() {
        reiniciarTemporizador();
    }

    // Event listeners para detectar actividad del usuario
    window.onload = reiniciarTemporizador;        // Al cargar la página
    document.onmousemove = reiniciarTemporizador; // Movimiento del mouse
    document.onkeypress = reiniciarTemporizador;  // Pulsación de teclas
    document.onscroll = reiniciarTemporizador;    // Desplazamiento de página
    document.onclick = reiniciarTemporizador;     // Clics del mouse
</script>
