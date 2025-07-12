# Sistema de Alertas - Backend

## Descripción General

El sistema de alertas ha sido refactorizado para proporcionar una arquitectura más robusta y mantenible. El nuevo backend incluye:

- **API RESTful** para manejo de alertas
- **Clase AlertasAPI** para lógica de negocio
- **Configuración centralizada** 
- **JavaScript modular** para el frontend
- **Sistema de notificaciones** mejorado

## Estructura de Archivos

```
roles/admin/
├── ajax/
│   └── alertas_api.php          # API RESTful para alertas
├── includes/
│   └── alertas_config.php       # Configuración centralizada
├── js/
│   └── alertas.js               # JavaScript modular
├── docs/
│   └── ALERTAS_BACKEND.md       # Esta documentación
└── alertas.php                  # Página principal (actualizada)
```

## API Endpoints

### GET Endpoints

#### 1. Listar Alertas
```
GET ajax/alertas_api.php?action=listar
```

**Parámetros opcionales:**
- `tipo`: Filtrar por tipo de alerta
- `estado`: Filtrar por estado (leidas/no_leidas)
- `fecha_desde`: Fecha de inicio (YYYY-MM-DD)
- `fecha_hasta`: Fecha de fin (YYYY-MM-DD)
- `limite`: Número máximo de resultados

**Ejemplo:**
```javascript
fetch('ajax/alertas_api.php?action=listar&tipo=soat&limite=10')
  .then(response => response.json())
  .then(data => console.log(data));
```

#### 2. Obtener Alerta Específica
```
GET ajax/alertas_api.php?action=obtener&id={id}
```

**Ejemplo:**
```javascript
fetch('ajax/alertas_api.php?action=obtener&id=123')
  .then(response => response.json())
  .then(data => console.log(data));
```

#### 3. Obtener Estadísticas
```
GET ajax/alertas_api.php?action=estadisticas
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total": 50,
    "leidas": 30,
    "no_leidas": 20,
    "este_mes": 15,
    "criticas": 5,
    "porcentaje_leidas": 60.0
  }
}
```

### POST Endpoints

#### 1. Marcar Alerta como Leída
```
POST ajax/alertas_api.php?action=marcar_leida
Content-Type: application/x-www-form-urlencoded

id={alerta_id}
```

#### 2. Marcar Todas como Leídas
```
POST ajax/alertas_api.php?action=marcar_todas_leidas
```

#### 3. Eliminar Alerta
```
POST ajax/alertas_api.php?action=eliminar
Content-Type: application/x-www-form-urlencoded

id={alerta_id}
```

## Clase AlertasAPI

### Métodos Principales

#### `obtenerAlertas($filtros = [])`
Obtiene las alertas del usuario con filtros opcionales.

**Parámetros:**
- `$filtros`: Array con filtros (tipo, estado, fecha_desde, fecha_hasta, limite)

**Retorna:**
```php
[
    'success' => true,
    'data' => [...], // Array de alertas
    'total' => 50
]
```

#### `obtenerAlerta($id)`
Obtiene una alerta específica por ID.

#### `marcarComoLeida($id)`
Marca una alerta como leída.

#### `marcarTodasLeidas()`
Marca todas las alertas no leídas como leídas.

#### `eliminarAlerta($id)`
Elimina una alerta específica.

#### `obtenerEstadisticas()`
Obtiene estadísticas de alertas del usuario.

## Configuración

### Tipos de Alertas
```php
define('TIPOS_ALERTAS', [
    'soat' => [
        'nombre' => 'SOAT',
        'icono' => 'bi-shield-check',
        'color' => 'primary',
        'descripcion' => 'Seguro Obligatorio de Accidentes de Tránsito'
    ],
    // ... más tipos
]);
```

### Estados de Alertas
```php
define('ESTADOS_ALERTAS', [
    'critica' => [
        'nombre' => 'Crítica',
        'icono' => 'bi-exclamation-triangle-fill',
        'color' => 'danger',
        'descripcion' => 'Requiere atención inmediata'
    ],
    // ... más estados
]);
```

### Prioridades
```php
define('PRIORIDADES_ALERTAS', [
    'alta' => [
        'nombre' => 'Alta',
        'color' => 'danger',
        'icono' => 'bi-arrow-up',
        'descripcion' => 'Requiere acción inmediata'
    ],
    // ... más prioridades
]);
```

## JavaScript Frontend

### Clase AlertasManager

El archivo `js/alertas.js` contiene la clase `AlertasManager` que maneja toda la lógica del frontend.

#### Métodos Principales

- `cargarAlertas()`: Carga alertas desde la API
- `renderizarAlertas()`: Renderiza las alertas en el DOM
- `aplicarFiltros()`: Aplica filtros a las alertas
- `verDetalles(id)`: Muestra detalles de una alerta
- `resolverAlerta(id)`: Marca una alerta como resuelta
- `marcarTodasLeidas()`: Marca todas como leídas

#### Inicialización
```javascript
document.addEventListener('DOMContentLoaded', () => {
    window.alertasManager = new AlertasManager();
});
```

## Funciones de Utilidad

### Categorización de Alertas
```php
function categorizarNotificacion($mensaje) {
    $mensaje_lower = strtolower($mensaje);
    
    foreach (PALABRAS_CLAVE as $tipo => $palabras) {
        foreach ($palabras as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return $tipo;
            }
        }
    }
    
    return 'general';
}
```

### Extracción de Placas
```php
function extraerPlaca($mensaje) {
    foreach (PATRONES_PLACA as $patron) {
        if (preg_match($patron, $mensaje, $matches)) {
            return strtoupper($matches[0]);
        }
    }
    return 'N/A';
}
```

### Determinación de Prioridad
```php
function determinarPrioridad($mensaje, $tipo) {
    $mensaje_lower = strtolower($mensaje);

    foreach (PALABRAS_PRIORIDAD as $prioridad => $palabras) {
        foreach ($palabras as $palabra) {
            if (strpos($mensaje_lower, $palabra) !== false) {
                return $prioridad;
            }
        }
    }
    
    return 'media';
}
```

## Seguridad

### Validación de Sesión
```php
if (!isset($_SESSION['documento'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
```

### Sanitización de Datos
- Todas las consultas SQL usan prepared statements
- Los datos de entrada se validan antes de procesar
- Escape de HTML en el frontend

### Rate Limiting
```php
define('SECURITY_CONFIG', [
    'csrf_protection' => true,
    'rate_limiting' => true,
    'max_requests_per_minute' => 60,
    'session_timeout' => 3600
]);
```

## Logging

### Configuración de Logs
```php
define('LOG_CONFIG', [
    'enabled' => true,
    'level' => 'info',
    'file' => 'logs/alertas.log',
    'max_size' => 10485760, // 10MB
    'max_files' => 5
]);
```

## Mejoras Implementadas

### 1. Arquitectura Modular
- Separación clara entre backend y frontend
- API RESTful para comunicación
- Configuración centralizada

### 2. Mejor Manejo de Errores
- Respuestas JSON consistentes
- Códigos de estado HTTP apropiados
- Logging de errores

### 3. Filtros Avanzados
- Filtrado por tipo, estado, prioridad
- Filtrado por fechas
- Búsqueda por vehículo

### 4. Estadísticas en Tiempo Real
- Contadores dinámicos
- Porcentajes de resolución
- Métricas de rendimiento

### 5. Interfaz Mejorada
- Animaciones suaves
- Notificaciones toast
- Auto-refresh
- Modal de detalles mejorado

## Uso del Sistema

### 1. Cargar Alertas
```javascript
// Cargar todas las alertas
alertasManager.cargarAlertas();

// Cargar con filtros
alertasManager.cargarAlertas({
    tipo: 'soat',
    estado: 'no_leidas',
    limite: 20
});
```

### 2. Aplicar Filtros
```javascript
// Filtrar por estado
alertasManager.filtrarPorEstado('critica');

// Limpiar filtros
alertasManager.limpiarFiltros();
```

### 3. Gestionar Alertas
```javascript
// Ver detalles
alertasManager.verDetalles(123);

// Resolver alerta
alertasManager.resolverAlerta(123);

// Marcar todas como leídas
alertasManager.marcarTodasLeidas();
```

### 4. Notificaciones
```javascript
// Mostrar notificación
alertasManager.mostrarNotificacion('Mensaje', 'success');

// Mostrar error
alertasManager.mostrarError('Error de conexión');
```

## Mantenimiento

### Agregar Nuevos Tipos de Alertas
1. Agregar en `includes/alertas_config.php`:
```php
'nuvo_tipo' => [
    'nombre' => 'Nuevo Tipo',
    'icono' => 'bi-icon',
    'color' => 'primary',
    'descripcion' => 'Descripción del tipo'
]
```

2. Agregar palabras clave:
```php
'nuvo_tipo' => ['palabra1', 'palabra2']
```

### Modificar Comportamiento
- **Backend**: Editar `ajax/alertas_api.php`
- **Frontend**: Editar `js/alertas.js`
- **Configuración**: Editar `includes/alertas_config.php`

### Debugging
```javascript
// Habilitar logs en consola
console.log('Alertas cargadas:', alertasManager.alertas);

// Verificar estado de filtros
console.log('Filtros actuales:', alertasManager.filtros);
```

## Consideraciones de Rendimiento

1. **Paginación**: Límite de 50 alertas por defecto
2. **Caché**: Considerar implementar caché para estadísticas
3. **Indexación**: Asegurar índices en la base de datos
4. **Compresión**: Habilitar gzip en el servidor

## Próximas Mejoras

1. **WebSockets**: Para notificaciones en tiempo real
2. **Paginación**: Implementar paginación del lado del servidor
3. **Caché**: Sistema de caché para mejorar rendimiento
4. **Exportación**: Exportar alertas a PDF/Excel
5. **Notificaciones Push**: Integración con notificaciones del navegador 