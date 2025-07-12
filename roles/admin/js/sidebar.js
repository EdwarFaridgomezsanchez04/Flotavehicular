/**
 * Clase ModernSidebar
 * Sistema de navegación lateral moderno y responsivo para la administración de flota
 * Maneja la expansión/colapso del sidebar, eventos de hover, y adaptación móvil
 */
class ModernSidebar {
  /**
   * Constructor de la clase ModernSidebar
   * Inicializa las propiedades principales y configura el sidebar
   */
  constructor() {
    // Elementos del DOM necesarios para el funcionamiento del sidebar
    this.sidebar = document.getElementById("sidebar");           // Elemento principal del sidebar
    this.toggleBtn = document.getElementById("toggleBtn");       // Botón para alternar el sidebar
    this.overlay = document.getElementById("sidebarOverlay");   // Overlay para dispositivos móviles
    
    // Estados del sidebar
    this.isExpanded = false;                                    // Estado de expansión del sidebar
    this.isMobile = window.innerWidth <= 768;                  // Detección de dispositivo móvil (breakpoint 768px)

    // Inicializar el sidebar
    this.init();
  }

  /**
   * Método de inicialización principal
   * Configura todos los componentes necesarios del sidebar
   */
  init() {
    this.setupEventListeners();  // Configurar eventos de interacción
    this.handleResize();         // Manejar redimensionamiento inicial
    this.setActiveLink();        // Establecer enlace activo según la página actual
  }

  /**
   * Configuración de todos los event listeners del sidebar
   * Maneja interacciones de usuario, eventos de teclado y redimensionamiento
   */
  setupEventListeners() {
    // Event listener para el botón de toggle (hamburguesa)
    if (this.toggleBtn) {
      this.toggleBtn.addEventListener("click", () => this.toggle());
    }

    // Eventos de hover para escritorio - expansión automática al pasar el mouse
    if (!this.isMobile) {
      // Expandir al entrar con el mouse
      this.sidebar.addEventListener("mouseenter", () => this.expand());
      // Colapsar al salir con el mouse
      this.sidebar.addEventListener("mouseleave", () => this.collapse());
    }

    // Event listener para el overlay en dispositivos móviles
    // Permite cerrar el sidebar tocando fuera de él
    if (this.overlay) {
      this.overlay.addEventListener("click", () => this.collapse());
    }

    // Event listener para redimensionamiento de ventana
    // Adapta el comportamiento según el tamaño de pantalla
    window.addEventListener("resize", () => this.handleResize());

    // Event listener para la tecla Escape
    // Permite cerrar el sidebar con la tecla Escape en móviles
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && this.isExpanded && this.isMobile) {
        this.collapse();
      }
    });

    // Event listeners para enlaces de navegación en móviles
    // Cierra automáticamente el sidebar al hacer clic en un enlace
    const navLinks = this.sidebar.querySelectorAll(".nav-link");
    navLinks.forEach((link) => {
      link.addEventListener("click", () => {
        if (this.isMobile) {
          this.collapse();
        }
      });
    });
  }

  /**
   * Método para expandir el sidebar
   * Muestra el contenido completo del sidebar y activa el overlay en móviles
   */
  expand() {
    // Agregar clase CSS para expandir el sidebar
    this.sidebar.classList.add("expanded");
    this.isExpanded = true;

    // En dispositivos móviles, activar overlay y bloquear scroll del body
    if (this.isMobile && this.overlay) {
      this.overlay.classList.add("active");           // Mostrar overlay semitransparente
      document.body.style.overflow = "hidden";        // Prevenir scroll del contenido principal
    }
  }

  /**
   * Método para colapsar el sidebar
   * Oculta el contenido del sidebar y desactiva el overlay
   */
  collapse() {
    // Remover clase CSS de expansión
    this.sidebar.classList.remove("expanded");
    this.isExpanded = false;

    // Desactivar overlay y restaurar scroll del body
    if (this.overlay) {
      this.overlay.classList.remove("active");        // Ocultar overlay
      document.body.style.overflow = "";              // Restaurar scroll normal
    }
  }

  /**
   * Método para alternar el estado del sidebar
   * Cambia entre expandido y colapsado según el estado actual
   */
  toggle() {
    if (this.isExpanded) {
      this.collapse();  // Si está expandido, colapsar
    } else {
      this.expand();    // Si está colapsado, expandir
    }
  }

  /**
   * Manejo de redimensionamiento de ventana
   * Adapta el comportamiento del sidebar según el tamaño de pantalla
   */
  handleResize() {
    const wasMobile = this.isMobile;                    // Estado móvil anterior
    this.isMobile = window.innerWidth <= 768;          // Nuevo estado móvil

    // Si cambió de móvil a escritorio
    if (wasMobile !== this.isMobile) {
      if (!this.isMobile) {
        // Cambió de móvil a desktop - resetear estado
        this.collapse();                                // Colapsar sidebar
        document.body.style.overflow = "";             // Restaurar scroll
      }
    }
  }

  /**
   * Establecer el enlace activo según la página actual
   * Resalta el enlace de navegación correspondiente a la página actual
   */
  setActiveLink() {
    const currentPath = window.location.pathname;       // Ruta actual de la página
    const navLinks = this.sidebar.querySelectorAll(".nav-link"); // Todos los enlaces de navegación

    navLinks.forEach((link) => {
      // Remover clase activa de todos los enlaces
      link.classList.remove("active");
      const href = link.getAttribute("href");

      // Si el href coincide con la ruta actual, marcar como activo
      if (href && currentPath.includes(href.replace(".php", ""))) {
        link.classList.add("active");
      }
    });
  }

  /**
   * Método para actualizar badges dinámicamente
   * Permite mostrar contadores de notificaciones en los enlaces del sidebar
   * @param {string} linkSelector - Selector CSS del enlace
   * @param {number} count - Número a mostrar en el badge
   */
  updateBadge(linkSelector, count) {
    const link = this.sidebar.querySelector(linkSelector);
    if (link) {
      let badge = link.querySelector(".nav-badge");

      if (count > 0) {
        // Si no existe el badge, crearlo
        if (!badge) {
          badge = document.createElement("div");
          badge.className = "nav-badge";
          link.appendChild(badge);
        }
        // Actualizar contenido del badge (máximo 99+)
        badge.textContent = count > 99 ? "99+" : count;
      } else if (badge) {
        // Si el count es 0 y existe badge, eliminarlo
        badge.remove();
      }
    }
  }

  /**
   * Método para mostrar notificaciones en el sidebar
   * Muestra mensajes temporales al usuario dentro del sidebar
   * @param {string} message - Mensaje a mostrar
   * @param {string} type - Tipo de notificación (info, success, warning, error)
   */
  showNotification(message, type = "info") {
    // Crear elemento de notificación
    const notification = document.createElement("div");
    notification.className = `sidebar-notification ${type}`;
    notification.innerHTML = `
      <i class="bi bi-info-circle"></i>
      <span>${message}</span>
    `;

    // Agregar notificación al sidebar
    this.sidebar.appendChild(notification);

    // Remover notificación después de 3 segundos
    setTimeout(() => {
      notification.remove();
    }, 3000);
  }
}

/**
 * Inicialización del sidebar cuando el DOM esté completamente cargado
 * Crea una instancia global del ModernSidebar para uso en toda la aplicación
 */
document.addEventListener("DOMContentLoaded", () => {
  // Crear instancia global del sidebar
  window.modernSidebar = new ModernSidebar();

  // Ejemplos de uso de badges dinámicos (comentados)
  // window.modernSidebar.updateBadge('a[href="alertas.php"]', 5);           // Badge para alertas
  // window.modernSidebar.updateBadge('a[href="#"]:has(.bi-bell-fill)', 12);  // Badge para notificaciones
});

/**
 * Función global para actualizar el perfil del usuario en el sidebar
 * Permite actualizar dinámicamente la información del usuario mostrada
 * @param {string} name - Nombre del usuario
 * @param {string} role - Rol del usuario
 * @param {string} avatarUrl - URL de la imagen de avatar
 */
function updateUserProfile(name, role, avatarUrl) {
  // Obtener elementos del perfil de usuario
  const profileName = document.querySelector(".profile-name");  // Elemento del nombre
  const profileRole = document.querySelector(".profile-role");  // Elemento del rol
  const avatarImg = document.querySelector(".avatar-img");      // Elemento de la imagen

  // Actualizar contenido si los elementos existen
  if (profileName) profileName.textContent = name;              // Actualizar nombre
  if (profileRole) profileRole.textContent = role;              // Actualizar rol
  if (avatarImg && avatarUrl) avatarImg.src = avatarUrl;        // Actualizar avatar
}

// Exportar función para uso global en toda la aplicación
window.updateUserProfile = updateUserProfile;