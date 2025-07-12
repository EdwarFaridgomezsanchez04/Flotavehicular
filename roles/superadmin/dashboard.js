// Funciones principales del dashboard
class SuperAdminDashboard {
    constructor() {
        this.currentSection = 'dashboard';
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadDashboardData();
    }
    
    bindEvents() {
        // Navegación entre secciones
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = link.getAttribute('href').replace('#', '');
                if (section !== 'logout') {
                    this.showSection(section);
                }
            });
        });
    }
    
    showSection(sectionName) {
        // Ocultar todas las secciones
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = 'none';
        });
        
        // Mostrar la sección seleccionada
        const targetSection = document.getElementById(sectionName + '-section');
        if (targetSection) {
            targetSection.style.display = 'block';
            this.currentSection = sectionName;
            
            // Cargar datos específicos de la sección
            this.loadSectionData(sectionName);
        }
        
        // Actualizar navegación activa
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const activeLink = document.querySelector(`[href="#${sectionName}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }
    
    loadDashboardData() {
        // Cargar estadísticas del dashboard
        this.loadStats();
        this.loadCharts();
        this.loadRecentActivity();
    }
    
    loadSectionData(section) {
        switch (section) {
            case 'usuarios':
                this.loadUsuarios();
                break;
            case 'vehiculos':
                this.loadVehiculos();
                break;
            case 'reportes':
                this.loadReportes();
                break;
            case 'configuracion':
                this.loadConfiguracion();
                break;
            case 'logs':
                this.loadLogs();
                break;
        }
    }
    
    async loadUsuarios() {
        try {
            const response = await fetch('usuarios_backend.php?action=listar_usuarios');
            const data = await response.json();
            
            if (data.success) {
                this.renderUsuariosTable(data.data);
            }
        } catch (error) {
            console.error('Error cargando usuarios:', error);
        }
    }
    
    async loadVehiculos() {
        try {
            const response = await fetch('vehiculos_backend.php?action=listar_vehiculos');
            const data = await response.json();
            
            if (data.success) {
                this.renderVehiculosTable(data.data);
            }
        } catch (error) {
            console.error('Error cargando vehículos:', error);
        }
    }
    
    async loadReportes() {
        // Cargar diferentes tipos de reportes
        this.loadReporteUsuarios();
        this.loadReporteVehiculos();
        this.loadReporteMantenimientos();
    }
    
    async loadConfiguracion() {
        try {
            const response = await fetch('configuracion_backend.php?action=obtener_configuracion');
            const data = await response.json();
            
            if (data.success) {
                this.renderConfiguracion(data.data);
            }
        } catch (error) {
            console.error('Error cargando configuración:', error);
        }
    }
    
    async loadLogs() {
        try {
            const response = await fetch('logs_backend.php?action=obtener_logs');
            const data = await response.json();
            
            if (data.success) {
                this.renderLogsTable(data.data);
            }
        } catch (error) {
            console.error('Error cargando logs:', error);
        }
    }
    
    // Métodos de renderizado
    renderUsuariosTable(usuarios) {
        // Implementar renderizado de tabla de usuarios
        console.log('Renderizando usuarios:', usuarios);
    }
    
    renderVehiculosTable(vehiculos) {
        // Implementar renderizado de tabla de vehículos
        console.log('Renderizando vehículos:', vehiculos);
    }
    
    renderConfiguracion(config) {
        // Implementar renderizado de configuración
        console.log('Renderizando configuración:', config);
    }
    
    renderLogsTable(logs) {
        // Implementar renderizado de tabla de logs
        console.log('Renderizando logs:', logs);
    }
    
    // Métodos de utilidad
    showAlert(message, type = 'info') {
        // Implementar sistema de alertas
        console.log(`${type.toUpperCase()}: ${message}`);
    }
    
    showModal(title, content) {
        // Implementar sistema de modales
        console.log(`Modal: ${title}`, content);
    }
}

// Inicializar dashboard cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new SuperAdminDashboard();
});