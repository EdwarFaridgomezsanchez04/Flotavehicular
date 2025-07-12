<?php
/**
 * Configuración del Sistema de Alertas
 * Contiene todas las configuraciones y constantes del sistema de alertas
 */

// Configuración de la base de datos
define('ALERTAS_TABLE', 'notificaciones');
define('USUARIOS_TABLE', 'usuarios');

// Tipos de alertas disponibles
define('TIPOS_ALERTAS', [
    'soat' => [
        'nombre' => 'SOAT',
        'icono' => 'bi-shield-check',
        'color' => 'primary',
        'descripcion' => 'Seguro Obligatorio de Accidentes de Tránsito'
    ],
    'tecnomecanica' => [
        'nombre' => 'Revisión Técnico-Mecánica',
        'icono' => 'bi-gear',
        'color' => 'warning',
        'descripcion' => 'Revisión técnica obligatoria del vehículo'
    ],
    'mantenimiento' => [
        'nombre' => 'Mantenimiento',
        'icono' => 'bi-tools',
        'color' => 'info',
        'descripcion' => 'Mantenimiento programado del vehículo'
    ],
    'licencia' => [
        'nombre' => 'Licencia',
        'icono' => 'bi-person-badge',
        'color' => 'success',
        'descripcion' => 'Licencia de conducción'
    ],
    'llantas' => [
        'nombre' => 'Llantas',
        'icono' => 'bi-circle',
        'color' => 'secondary',
        'descripcion' => 'Cambio o revisión de llantas'
    ],
    'pico_placa' => [
        'nombre' => 'Pico y Placa',
        'icono' => 'bi-car-front',
        'color' => 'dark',
        'descripcion' => 'Restricción de circulación'
    ],
    'multa' => [
        'nombre' => 'Multas',
        'icono' => 'bi-exclamation-triangle',
        'color' => 'danger',
        'descripcion' => 'Multas de tránsito'
    ],
    'registro' => [
        'nombre' => 'Registros',
        'icono' => 'bi-plus-circle',
        'color' => 'success',
        'descripcion' => 'Registros del sistema'
    ],
    'general' => [
        'nombre' => 'General',
        'icono' => 'bi-bell',
        'color' => 'secondary',
        'descripcion' => 'Alertas generales del sistema'
    ]
]);

// Estados de alertas
define('ESTADOS_ALERTAS', [
    'critica' => [
        'nombre' => 'Crítica',
        'icono' => 'bi-exclamation-triangle-fill',
        'color' => 'danger',
        'descripcion' => 'Requiere atención inmediata'
    ],
    'pendiente' => [
        'nombre' => 'Pendiente',
        'icono' => 'bi-clock-fill',
        'color' => 'warning',
        'descripcion' => 'Requiere atención pronto'
    ],
    'informativa' => [
        'nombre' => 'Informativa',
        'icono' => 'bi-info-circle-fill',
        'color' => 'info',
        'descripcion' => 'Información general'
    ]
]);

// Prioridades de alertas
define('PRIORIDADES_ALERTAS', [
    'alta' => [
        'nombre' => 'Alta',
        'color' => 'danger',
        'icono' => 'bi-arrow-up',
        'descripcion' => 'Requiere acción inmediata'
    ],
    'media' => [
        'nombre' => 'Media',
        'color' => 'warning',
        'icono' => 'bi-dash',
        'descripcion' => 'Requiere atención pronto'
    ],
    'baja' => [
        'nombre' => 'Baja',
        'color' => 'secondary',
        'icono' => 'bi-arrow-down',
        'descripcion' => 'Información general'
    ]
]);

// Configuración de filtros
define('FILTROS_DISPONIBLES', [
    'tipo' => [
        'label' => 'Tipo de Alerta',
        'options' => [
            '' => 'Todas las alertas',
            'soat' => 'SOAT',
            'tecnomecanica' => 'Revisión Técnico-Mecánica',
            'mantenimiento' => 'Mantenimiento',
            'licencia' => 'Licencia',
            'llantas' => 'Llantas',
            'pico_placa' => 'Pico y Placa',
            'multa' => 'Multas',
            'registro' => 'Registros'
        ]
    ],
    'estado' => [
        'label' => 'Estado',
        'options' => [
            '' => 'Todos los estados',
            'critica' => '🔴 Crítica',
            'pendiente' => '🟡 Pendiente',
            'informativa' => '🔵 Informativa'
        ]
    ],
    'prioridad' => [
        'label' => 'Prioridad',
        'options' => [
            '' => 'Todas las prioridades',
            'alta' => 'Alta',
            'media' => 'Media',
            'baja' => 'Baja'
        ]
    ]
]);

// Configuración de paginación
define('ALERTAS_POR_PAGINA', 50);
define('ALERTAS_MAXIMAS', 100);

// Configuración de auto-refresh
define('AUTO_REFRESH_INTERVAL', 300000); // 5 minutos en milisegundos
define('NOTIFICACION_TIMEOUT', 5000); // 5 segundos

// Configuración de animaciones
define('ANIMATION_DELAY_BASE', 100); // milisegundos entre animaciones

// Patrones para extracción de información
define('PATRONES_PLACA', [
    '/\b[A-Z]{3}[0-9]{3}\b/', // ABC123
    '/\b[A-Z]{3}[0-9]{2}[A-Z]\b/', // ABC12A
    '/\b[A-Z]{2}[0-9]{4}\b/' // AB1234
]);

// Palabras clave para categorización
define('PALABRAS_CLAVE', [
    'soat' => ['soat', 'seguro obligatorio'],
    'tecnomecanica' => ['técnico-mecánica', 'tecnomecanica', 'revisión técnica'],
    'mantenimiento' => ['mantenimiento', 'servicio', 'reparación'],
    'licencia' => ['licencia', 'conducción', 'permiso'],
    'llantas' => ['llantas', 'neumáticos', 'gomas'],
    'pico_placa' => ['pico y placa', 'restricción', 'circulación'],
    'multa' => ['multa', 'infracción', 'sanción'],
    'registro' => ['registrado', 'nuevo', 'agregado']
]);

// Palabras clave para prioridad
define('PALABRAS_PRIORIDAD', [
    'alta' => ['vencido', 'vence', 'urgente', 'crítico', 'inmediato'],
    'media' => ['próximo', 'programado', 'faltan', 'días'],
    'baja' => ['registrado', 'nuevo', 'información']
]);

// Palabras clave para estado
define('PALABRAS_ESTADO', [
    'critica' => ['vencido', 'urgente', 'crítico', 'inmediato'],
    'pendiente' => ['vence', 'próximo', 'faltan', 'días'],
    'informativa' => ['registrado', 'nuevo', 'información']
]);

// Configuración de notificaciones
define('NOTIFICACION_CONFIG', [
    'position' => 'top-right',
    'timeout' => 5000,
    'animation' => 'fade',
    'types' => [
        'success' => [
            'icon' => 'bi-check-circle',
            'class' => 'alert-success'
        ],
        'error' => [
            'icon' => 'bi-exclamation-triangle',
            'class' => 'alert-danger'
        ],
        'warning' => [
            'icon' => 'bi-exclamation-triangle',
            'class' => 'alert-warning'
        ],
        'info' => [
            'icon' => 'bi-info-circle',
            'class' => 'alert-info'
        ]
    ]
]);

// Configuración de la API
define('API_ENDPOINTS', [
    'listar' => 'ajax/alertas_api.php?action=listar',
    'obtener' => 'ajax/alertas_api.php?action=obtener',
    'estadisticas' => 'ajax/alertas_api.php?action=estadisticas',
    'marcar_leida' => 'ajax/alertas_api.php?action=marcar_leida',
    'marcar_todas_leidas' => 'ajax/alertas_api.php?action=marcar_todas_leidas',
    'eliminar' => 'ajax/alertas_api.php?action=eliminar'
]);

// Configuración de seguridad
define('SECURITY_CONFIG', [
    'csrf_protection' => true,
    'rate_limiting' => true,
    'max_requests_per_minute' => 60,
    'session_timeout' => 3600 // 1 hora
]);

// Configuración de logs
define('LOG_CONFIG', [
    'enabled' => true,
    'level' => 'info', // debug, info, warning, error
    'file' => 'logs/alertas.log',
    'max_size' => 10485760, // 10MB
    'max_files' => 5
]);

/**
 * Función para obtener configuración de tipo de alerta
 */
function getTipoConfig($tipo) {
    return TIPOS_ALERTAS[$tipo] ?? TIPOS_ALERTAS['general'];
}

/**
 * Función para obtener configuración de estado
 */
function getEstadoConfig($estado) {
    return ESTADOS_ALERTAS[$estado] ?? ESTADOS_ALERTAS['informativa'];
}

/**
 * Función para obtener configuración de prioridad
 */
function getPrioridadConfig($prioridad) {
    return PRIORIDADES_ALERTAS[$prioridad] ?? PRIORIDADES_ALERTAS['media'];
}

/**
 * Función para validar tipo de alerta
 */
function esTipoValido($tipo) {
    return array_key_exists($tipo, TIPOS_ALERTAS);
}

/**
 * Función para validar estado de alerta
 */
function esEstadoValido($estado) {
    return array_key_exists($estado, ESTADOS_ALERTAS);
}

/**
 * Función para validar prioridad de alerta
 */
function esPrioridadValida($prioridad) {
    return array_key_exists($prioridad, PRIORIDADES_ALERTAS);
}

/**
 * Función para obtener todos los tipos de alertas
 */
function getTodosLosTipos() {
    return array_keys(TIPOS_ALERTAS);
}

/**
 * Función para obtener todos los estados
 */
function getTodosLosEstados() {
    return array_keys(ESTADOS_ALERTAS);
}

/**
 * Función para obtener todas las prioridades
 */
function getTodasLasPrioridades() {
    return array_keys(PRIORIDADES_ALERTAS);
}
?> 