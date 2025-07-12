/**
 * Sistema de Alertas - Frontend JavaScript
 * Maneja todas las interacciones del usuario con el sistema de alertas
 */

class AlertasManager {
    constructor() {
        this.alertas = [];
        this.filtros = {
            tipo: '',
            estado: '',
            vehiculo: '',
            prioridad: ''
        };
        this.alertaActual = null;
        this.apiUrl = 'ajax/alertas_api.php';
        
        this.init();
    }

    // Inicializar el sistema
    init() {
        this.cargarAlertas();
        this.setupEventListeners();
        this.setupAutoRefresh();
        this.setupAnimations();
    }

    // Configurar event listeners
    setupEventListeners() {
        // Filtros
        document.getElementById('filtroTipo')?.addEventListener('change', () => this.aplicarFiltros());
        document.getElementById('filtroEstado')?.addEventListener('change', () => this.aplicarFiltros());
        document.getElementById('filtroVehiculo')?.addEventListener('input', () => this.aplicarFiltros());
        document.getElementById('filtroPrioridad')?.addEventListener('change', () => this.aplicarFiltros());

        // Botones de acción
        document.querySelector('.btn-outline-primary')?.addEventListener('click', () => this.marcarTodasLeidas());
        document.querySelector('.btn-primary')?.addEventListener('click', () => this.actualizarAlertas());

        // Tarjetas de resumen
        document.querySelectorAll('.summary-card').forEach(card => {
            card.addEventListener('click', (e) => {
                const estado = e.currentTarget.classList.contains('criticas') ? 'critica' :
                             e.currentTarget.classList.contains('pendientes') ? 'pendiente' :
                             e.currentTarget.classList.contains('al-dia') ? 'informativa' : '';
                
                if (estado) {
                    this.filtrarPorEstado(estado);
                } else {
                    this.mostrarTodas();
                }
            });
        });
    }

    // Configurar auto-refresh
    setupAutoRefresh() {
        // Verificar nuevas alertas cada 5 minutos
        setInterval(() => {
            this.verificarNuevasAlertas();
        }, 300000);
    }

    // Configurar animaciones
    setupAnimations() {
        const alertas = document.querySelectorAll('.alert-item');
        alertas.forEach((alerta, index) => {
            alerta.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // Cargar alertas desde la API
    async cargarAlertas() {
        try {
            this.mostrarLoading();
            
            const response = await fetch(`${this.apiUrl}?action=listar`);
            const data = await response.json();
            
            if (data.success) {
                this.alertas = data.data;
                this.renderizarAlertas();
                this.actualizarEstadisticas();
            } else {
                this.mostrarError('Error al cargar alertas: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error de conexión al cargar alertas');
        } finally {
            this.ocultarLoading();
        }
    }

    // Renderizar alertas en el DOM
    renderizarAlertas() {
        const alertasList = document.getElementById('alertasList');
        const noAlertas = document.getElementById('noAlertas');
        
        if (!alertasList) return;

        if (this.alertas.length === 0) {
            alertasList.innerHTML = `
                <li class="no-alerts-item">
                    <div class="text-center p-4">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No hay alertas</h5>
                        <p class="text-muted">No tienes alertas pendientes en este momento.</p>
                    </div>
                </li>
            `;
            noAlertas.style.display = 'block';
            alertasList.style.display = 'none';
        } else {
            noAlertas.style.display = 'none';
            alertasList.style.display = 'block';
            
            alertasList.innerHTML = this.alertas.map(alerta => this.crearElementoAlerta(alerta)).join('');
        }

        this.actualizarContador();
    }

    // Crear elemento HTML para una alerta
    crearElementoAlerta(alerta) {
        const iconClass = this.getAlertIcon(alerta.tipo);
        const estadoClass = alerta.estado;
        const prioridadClass = alerta.prioridad;
        
        return `
            <li class="alert-item ${estadoClass}"
                data-tipo="${alerta.tipo.toLowerCase()}"
                data-estado="${alerta.estado}"
                data-vehiculo="${alerta.vehiculo.toLowerCase()}"
                data-prioridad="${alerta.prioridad}"
                data-id="${alerta.id}">

                <div class="alert-priority ${prioridadClass}"></div>

                <div class="alert-icon ${estadoClass}">
                    <i class="${iconClass}"></i>
                </div>

                <div class="alert-content">
                    <div class="alert-type">
                        <i class="${iconClass}"></i>
                        ${this.escapeHtml(alerta.tipo)}
                        ${alerta.vehiculo !== 'N/A' ? `<span class="alert-vehicle">${this.escapeHtml(alerta.vehiculo)}</span>` : ''}
                        ${!alerta.leido ? '<span class="badge bg-danger ms-2">Nuevo</span>' : ''}
                    </div>
                    <div class="alert-description">${this.escapeHtml(alerta.descripcion)}</div>
                    <div class="alert-date">
                        <i class="bi bi-calendar"></i>
                        ${this.formatearFecha(alerta.fecha_alerta)}
                    </div>
                </div>

                <div class="alert-status">
                    <span class="status-badge ${estadoClass}">
                        ${this.getEstadoBadge(alerta.estado)}
                    </span>
                    <small class="text-muted d-block mt-1">
                        Prioridad: ${alerta.prioridad.charAt(0).toUpperCase() + alerta.prioridad.slice(1)}
                    </small>
                </div>

                <div class="alert-actions">
                    <a href="#" onclick="alertasManager.verDetalles(${alerta.id})" class="action-btn primary">
                        <i class="bi bi-eye"></i> Ver
                    </a>
                    ${!alerta.leido ? `
                        <a href="#" onclick="alertasManager.resolverAlerta(${alerta.id})" class="action-btn success">
                            <i class="bi bi-check"></i> Resolver
                        </a>
                    ` : `
                        <span class="action-btn disabled">
                            <i class="bi bi-check-circle"></i> Resuelta
                        </span>
                    `}
                </div>
            </li>
        `;
    }

    // Aplicar filtros
    aplicarFiltros() {
        this.filtros = {
            tipo: document.getElementById('filtroTipo')?.value.toLowerCase() || '',
            estado: document.getElementById('filtroEstado')?.value.toLowerCase() || '',
            vehiculo: document.getElementById('filtroVehiculo')?.value.toLowerCase() || '',
            prioridad: document.getElementById('filtroPrioridad')?.value.toLowerCase() || ''
        };

        const alertas = document.querySelectorAll('.alert-item');
        let alertasVisibles = 0;

        alertas.forEach(alerta => {
            const tipo = alerta.dataset.tipo || '';
            const estado = alerta.dataset.estado || '';
            const vehiculo = alerta.dataset.vehiculo || '';
            const prioridad = alerta.dataset.prioridad || '';

            let mostrar = true;

            if (this.filtros.tipo && !tipo.includes(this.filtros.tipo)) mostrar = false;
            if (this.filtros.estado && estado !== this.filtros.estado) mostrar = false;
            if (this.filtros.vehiculo && !vehiculo.includes(this.filtros.vehiculo)) mostrar = false;
            if (this.filtros.prioridad && prioridad !== this.filtros.prioridad) mostrar = false;

            alerta.style.display = mostrar ? 'flex' : 'none';
            if (mostrar) alertasVisibles++;
        });

        this.actualizarContador(alertasVisibles);
        this.mostrarOcultarNoAlertas(alertasVisibles === 0);
    }

    // Filtrar por estado desde las tarjetas
    filtrarPorEstado(estado) {
        document.getElementById('filtroEstado').value = estado;
        this.aplicarFiltros();
    }

    // Mostrar todas las alertas
    mostrarTodas() {
        this.limpiarFiltros();
    }

    // Limpiar filtros
    limpiarFiltros() {
        document.getElementById('filtroTipo').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroVehiculo').value = '';
        document.getElementById('filtroPrioridad').value = '';
        this.aplicarFiltros();
    }

    // Ver detalles de una alerta
    async verDetalles(id) {
        try {
            const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
            const detallesContenido = document.getElementById('detallesContenido');

            // Mostrar loading
            detallesContenido.innerHTML = this.getLoadingHTML();
            modal.show();
            this.alertaActual = id;

            // Obtener detalles de la API
            const response = await fetch(`${this.apiUrl}?action=obtener&id=${id}`);
            const data = await response.json();

            if (data.success) {
                detallesContenido.innerHTML = this.crearDetallesHTML(data.data);
            } else {
                detallesContenido.innerHTML = this.getErrorHTML(data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarError('Error al cargar detalles de la alerta');
        }
    }

    // Resolver una alerta
    async resolverAlerta(id) {
        if (!confirm('¿Está seguro de marcar esta alerta como resuelta?')) {
            return;
        }

        try {
            const alertaElement = document.querySelector(`[data-id="${id}"]`);
            const btnResolver = alertaElement?.querySelector('.action-btn.success');

            if (btnResolver) {
                btnResolver.innerHTML = '<i class="bi bi-hourglass-split"></i> Procesando...';
                btnResolver.style.pointerEvents = 'none';
            }

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=marcar_leida&id=${id}`
            });

            const data = await response.json();

            if (data.success) {
                this.actualizarInterfazAlerta(id, true);
                this.mostrarNotificacion('Alerta resuelta correctamente', 'success');
                
                // Cerrar modal si está abierto
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetalles'));
                if (modal) {
                    modal.hide();
                }

                // Recargar después de un delay
                setTimeout(() => {
                    this.cargarAlertas();
                }, 1500);
            } else {
                this.mostrarNotificacion(data.error || 'Error al resolver la alerta', 'error');
                if (btnResolver) {
                    btnResolver.innerHTML = '<i class="bi bi-check"></i> Resolver';
                    btnResolver.style.pointerEvents = 'auto';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarNotificacion('Error de conexión', 'error');
        }
    }

    // Marcar todas como leídas
    async marcarTodasLeidas() {
        if (!confirm('¿Está seguro de marcar todas las alertas como leídas?')) {
            return;
        }

        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=marcar_todas_leidas'
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarNotificacion('Todas las alertas han sido marcadas como leídas', 'success');
                setTimeout(() => {
                    this.cargarAlertas();
                }, 1500);
            } else {
                this.mostrarNotificacion(data.error || 'Error al marcar las alertas', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarNotificacion('Error de conexión', 'error');
        }
    }

    // Actualizar alertas
    actualizarAlertas() {
        this.cargarAlertas();
    }

    // Verificar nuevas alertas
    async verificarNuevasAlertas() {
        try {
            const response = await fetch(`${this.apiUrl}?action=estadisticas`);
            const data = await response.json();
            
            if (data.success) {
                const noLeidas = data.data.no_leidas;
                if (noLeidas > 0) {
                    this.mostrarNotificacion(`Tienes ${noLeidas} alerta(s) nueva(s)`, 'info');
                }
            }
        } catch (error) {
            console.error('Error al verificar nuevas alertas:', error);
        }
    }

    // Actualizar interfaz de alerta
    actualizarInterfazAlerta(id, resuelta) {
        const alertaElement = document.querySelector(`[data-id="${id}"]`);
        if (alertaElement) {
            alertaElement.style.opacity = '0.6';
            alertaElement.style.transform = 'translateX(10px)';

            const btnResolver = alertaElement.querySelector('.action-btn.success');
            if (btnResolver && resuelta) {
                btnResolver.outerHTML = '<span class="action-btn disabled"><i class="bi bi-check-circle"></i> Resuelta</span>';
            }

            const badgeContainer = alertaElement.querySelector('.alert-type');
            if (badgeContainer && !badgeContainer.querySelector('.badge.bg-success')) {
                badgeContainer.innerHTML += ' <span class="badge bg-success ms-2">Resuelta</span>';
            }
        }
    }

    // Actualizar contador
    actualizarContador(count = null) {
        const contador = document.getElementById('alertasCount');
        if (contador) {
            const total = count !== null ? count : this.alertas.length;
            contador.textContent = `${total} alertas`;
        }
    }

    // Mostrar/ocultar mensaje de no alertas
    mostrarOcultarNoAlertas(mostrar) {
        const noAlertas = document.getElementById('noAlertas');
        const alertasList = document.getElementById('alertasList');
        
        if (noAlertas && alertasList) {
            if (mostrar) {
                noAlertas.style.display = 'block';
                alertasList.style.display = 'none';
            } else {
                noAlertas.style.display = 'none';
                alertasList.style.display = 'block';
            }
        }
    }

    // Actualizar estadísticas
    async actualizarEstadisticas() {
        try {
            const response = await fetch(`${this.apiUrl}?action=estadisticas`);
            const data = await response.json();
            
            if (data.success) {
                this.actualizarResumenEstadisticas(data.data);
            }
        } catch (error) {
            console.error('Error al actualizar estadísticas:', error);
        }
    }

    // Actualizar resumen de estadísticas
    actualizarResumenEstadisticas(stats) {
        // Actualizar contadores en las tarjetas de resumen
        const criticas = document.querySelector('.summary-card.criticas .summary-number span');
        const pendientes = document.querySelector('.summary-card.pendientes .summary-number span');
        const alDia = document.querySelector('.summary-card.al-dia .summary-number span');
        const total = document.querySelector('.summary-card.total .summary-number span');

        if (criticas) criticas.textContent = stats.criticas;
        if (pendientes) pendientes.textContent = stats.no_leidas;
        if (alDia) alDia.textContent = stats.leidas;
        if (total) total.textContent = stats.total;
    }

    // Funciones auxiliares
    getAlertIcon(tipo) {
        const iconos = {
            'soat': 'bi-shield-check',
            'tecnomecanica': 'bi-gear',
            'revision': 'bi-gear',
            'mantenimiento': 'bi-tools',
            'licencia': 'bi-person-badge',
            'multa': 'bi-exclamation-triangle',
            'llantas': 'bi-circle',
            'pico_placa': 'bi-car-front',
            'registro': 'bi-plus-circle',
            'general': 'bi-bell'
        };
        return iconos[tipo.toLowerCase()] || 'bi-bell';
    }

    getEstadoBadge(estado) {
        const badges = {
            'critica': '<i class="bi bi-exclamation-triangle-fill"></i> Crítica',
            'pendiente': '<i class="bi bi-clock-fill"></i> Pendiente',
            'informativa': '<i class="bi bi-info-circle-fill"></i> Informativa'
        };
        return badges[estado] || '<i class="bi bi-info-circle-fill"></i> Informativa';
    }

    formatearFecha(fecha) {
        return new Date(fecha).toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    getLoadingHTML() {
        return `
            <div class="d-flex justify-content-center align-items-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `;
    }

    getErrorHTML(error) {
        return `
            <div class="container-fluid p-4">
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>
                        <strong>¡Atención!</strong> ${this.escapeHtml(error)}
                    </div>
                </div>
            </div>
        `;
    }

    crearDetallesHTML(alerta) {
        const fechaFormateada = new Date(alerta.fecha_alerta).toLocaleString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        let estadoBadge = 'bg-secondary';
        let prioridadBadge = 'bg-secondary';

        switch (alerta.estado) {
            case 'critica': estadoBadge = 'bg-danger'; break;
            case 'pendiente': estadoBadge = 'bg-warning text-dark'; break;
            case 'informativa': estadoBadge = 'bg-info'; break;
        }

        switch (alerta.prioridad) {
            case 'alta': prioridadBadge = 'bg-danger'; break;
            case 'media': prioridadBadge = 'bg-warning text-dark'; break;
            case 'baja': prioridadBadge = 'bg-secondary'; break;
        }

        return `
            <div class="container-fluid p-4">
                <!-- Header de la alerta -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="bi bi-bell text-primary" style="font-size: 2rem;"></i>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">Alerta #${alerta.id}</h4>
                                            <p class="text-muted mb-0">Tipo: ${alerta.tipo}</p>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge ${estadoBadge} fs-6 px-3 py-2 mb-2 d-block">
                                            ${alerta.estado.charAt(0).toUpperCase() + alerta.estado.slice(1)}
                                        </span>
                                        ${!alerta.leido ? '<span class="badge bg-danger">Nueva</span>' : '<span class="badge bg-success">Leída</span>'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información principal -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Información General
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">Fecha y Hora</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-calendar-event text-primary me-2"></i>
                                            <span>${fechaFormateada}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">Tipo de Alerta</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-tag text-primary me-2"></i>
                                            <span class="badge bg-info fs-6">${alerta.tipo}</span>
                                        </div>
                                    </div>
                                    ${alerta.vehiculo !== 'N/A' ? `
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">Vehículo</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-car-front text-primary me-2"></i>
                                            <span class="badge bg-secondary fs-6">${alerta.vehiculo}</span>
                                        </div>
                                    </div>
                                    ` : ''}
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">Usuario Responsable</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-person-circle text-primary me-2"></i>
                                            <span>${alerta.usuario}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Estado y Prioridad
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">Estado Actual</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-flag text-warning me-2"></i>
                                            <span class="badge ${estadoBadge} fs-6">${alerta.estado.charAt(0).toUpperCase() + alerta.estado.slice(1)}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">Prioridad</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-arrow-up text-danger me-2"></i>
                                            <span class="badge ${prioridadBadge} fs-6">${alerta.prioridad.charAt(0).toUpperCase() + alerta.prioridad.slice(1)}</span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-muted">Estado de Lectura</label>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-eye${alerta.leido ? '-fill' : '-slash'} text-${alerta.leido ? 'success' : 'danger'} me-2"></i>
                                            <span class="badge bg-${alerta.leido ? 'success' : 'danger'} fs-6">${alerta.leido ? 'Leída' : 'No leída'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Descripción completa -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-card-text me-2"></i>
                                    Descripción Completa
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-light border-start border-4 border-info">
                                    <p class="mb-0">${alerta.descripcion}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Acciones recomendadas -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bi bi-lightbulb me-2"></i>
                                    Acciones Recomendadas
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    ${!alerta.leido ? `
                                    <button class="btn btn-success btn-sm" onclick="alertasManager.resolverAlerta(${alerta.id})">
                                        <i class="bi bi-check-circle me-1"></i>
                                        Marcar como Resuelta
                                    </button>
                                    ` : ''}
                                    ${alerta.vehiculo !== 'N/A' ? `
                                    <button class="btn btn-outline-primary btn-sm" onclick="alertasManager.verVehiculo('${alerta.vehiculo}')">
                                        <i class="bi bi-car-front me-1"></i>
                                        Ver Vehículo
                                    </button>
                                    ` : ''}
                                    <button class="btn btn-outline-info btn-sm" onclick="alertasManager.compartirAlerta(${alerta.id})">
                                        <i class="bi bi-share me-1"></i>
                                        Compartir
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Funciones de utilidad
    verVehiculo(placa) {
        console.log('Ver vehículo:', placa);
        this.mostrarNotificacion(`Redirigiendo a detalles del vehículo: ${placa}`, 'info');
    }

    compartirAlerta(id) {
        if (navigator.share) {
            navigator.share({
                title: 'Alerta del Sistema Flotax',
                text: 'Alerta del sistema de gestión de flota',
                url: window.location.href + '?alerta=' + id
            });
        } else {
            const url = window.location.href + '?alerta=' + id;
            navigator.clipboard.writeText(url).then(() => {
                this.mostrarNotificacion('Enlace copiado al portapapeles', 'success');
            });
        }
    }

    // Sistema de notificaciones
    mostrarNotificacion(mensaje, tipo = 'info') {
        const alertClass = tipo === 'success' ? 'alert-success' : tipo === 'error' ? 'alert-danger' : 'alert-info';
        const iconClass = tipo === 'success' ? 'bi-check-circle' : tipo === 'error' ? 'bi-exclamation-triangle' : 'bi-info-circle';

        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="bi ${iconClass} me-2"></i>
            ${this.escapeHtml(mensaje)}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    mostrarError(mensaje) {
        this.mostrarNotificacion(mensaje, 'error');
    }

    mostrarLoading() {
        // Implementar loading si es necesario
    }

    ocultarLoading() {
        // Implementar ocultar loading si es necesario
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.alertasManager = new AlertasManager();
}); 