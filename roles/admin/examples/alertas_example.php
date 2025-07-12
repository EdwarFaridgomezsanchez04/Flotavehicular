<?php
/**
 * Ejemplo de uso de la API de Alertas
 * Este archivo muestra cómo usar los diferentes endpoints de la API
 */

// Incluir configuración
require_once('../includes/alertas_config.php');

// Simular una sesión de usuario
session_start();
$_SESSION['documento'] = '1234567890';

// URL base de la API
$apiBase = 'ajax/alertas_api.php';

echo "<h1>Ejemplos de uso de la API de Alertas</h1>";

// Función para hacer peticiones HTTP
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Ejemplo 1: Obtener todas las alertas
echo "<h2>1. Obtener todas las alertas</h2>";
$response = makeRequest($apiBase . '?action=listar');
echo "<pre>";
print_r($response);
echo "</pre>";

// Ejemplo 2: Obtener alertas con filtros
echo "<h2>2. Obtener alertas con filtros</h2>";
$response = makeRequest($apiBase . '?action=listar&tipo=soat&limite=5');
echo "<pre>";
print_r($response);
echo "</pre>";

// Ejemplo 3: Obtener estadísticas
echo "<h2>3. Obtener estadísticas</h2>";
$response = makeRequest($apiBase . '?action=estadisticas');
echo "<pre>";
print_r($response);
echo "</pre>";

// Ejemplo 4: Marcar alerta como leída (POST)
echo "<h2>4. Marcar alerta como leída</h2>";
$response = makeRequest($apiBase . '?action=marcar_leida', 'POST', 'id=1');
echo "<pre>";
print_r($response);
echo "</pre>";

// Ejemplo 5: Marcar todas como leídas
echo "<h2>5. Marcar todas como leídas</h2>";
$response = makeRequest($apiBase . '?action=marcar_todas_leidas', 'POST');
echo "<pre>";
print_r($response);
echo "</pre>";

// Ejemplo 6: JavaScript para usar la API
echo "<h2>6. Ejemplo de JavaScript</h2>";
?>

<script>
// Ejemplo de cómo usar la API desde JavaScript
class AlertasExample {
    constructor() {
        this.apiUrl = 'ajax/alertas_api.php';
    }

    // Obtener todas las alertas
    async obtenerAlertas() {
        try {
            const response = await fetch(`${this.apiUrl}?action=listar`);
            const data = await response.json();
            console.log('Alertas obtenidas:', data);
            return data;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Obtener alertas con filtros
    async obtenerAlertasFiltradas(filtros = {}) {
        const params = new URLSearchParams(filtros);
        try {
            const response = await fetch(`${this.apiUrl}?action=listar&${params}`);
            const data = await response.json();
            console.log('Alertas filtradas:', data);
            return data;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Obtener una alerta específica
    async obtenerAlerta(id) {
        try {
            const response = await fetch(`${this.apiUrl}?action=obtener&id=${id}`);
            const data = await response.json();
            console.log('Alerta obtenida:', data);
            return data;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Marcar alerta como leída
    async marcarComoLeida(id) {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=marcar_leida&id=${id}`
            });
            const data = await response.json();
            console.log('Alerta marcada como leída:', data);
            return data;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Marcar todas como leídas
    async marcarTodasLeidas() {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=marcar_todas_leidas'
            });
            const data = await response.json();
            console.log('Todas marcadas como leídas:', data);
            return data;
        } catch (error) {
            console.error('Error:', error);
        }
    }

    // Obtener estadísticas
    async obtenerEstadisticas() {
        try {
            const response = await fetch(`${this.apiUrl}?action=estadisticas`);
            const data = await response.json();
            console.log('Estadísticas:', data);
            return data;
        } catch (error) {
            console.error('Error:', error);
        }
    }
}

// Ejemplo de uso
const ejemplo = new AlertasExample();

// Ejecutar ejemplos cuando se cargue la página
document.addEventListener('DOMContentLoaded', async () => {
    console.log('=== Ejemplos de uso de la API de Alertas ===');
    
    // Obtener todas las alertas
    await ejemplo.obtenerAlertas();
    
    // Obtener alertas filtradas
    await ejemplo.obtenerAlertasFiltradas({
        tipo: 'soat',
        limite: 5
    });
    
    // Obtener estadísticas
    await ejemplo.obtenerEstadisticas();
    
    console.log('=== Fin de ejemplos ===');
});
</script>

<?php
// Ejemplo 7: Mostrar configuración
echo "<h2>7. Configuración del sistema</h2>";
echo "<h3>Tipos de alertas disponibles:</h3>";
echo "<ul>";
foreach (TIPOS_ALERTAS as $tipo => $config) {
    echo "<li><strong>{$tipo}:</strong> {$config['nombre']} - {$config['descripcion']}</li>";
}
echo "</ul>";

echo "<h3>Estados disponibles:</h3>";
echo "<ul>";
foreach (ESTADOS_ALERTAS as $estado => $config) {
    echo "<li><strong>{$estado}:</strong> {$config['nombre']} - {$config['descripcion']}</li>";
}
echo "</ul>";

echo "<h3>Prioridades disponibles:</h3>";
echo "<ul>";
foreach (PRIORIDADES_ALERTAS as $prioridad => $config) {
    echo "<li><strong>{$prioridad}:</strong> {$config['nombre']} - {$config['descripcion']}</li>";
}
echo "</ul>";

// Ejemplo 8: Funciones de utilidad
echo "<h2>8. Funciones de utilidad</h2>";

$mensajeEjemplo = "El SOAT del vehículo ABC123 vence en 15 días";
echo "<p><strong>Mensaje de ejemplo:</strong> {$mensajeEjemplo}</p>";

$tipo = categorizarNotificacion($mensajeEjemplo);
echo "<p><strong>Tipo categorizado:</strong> {$tipo}</p>";

$placa = extraerPlaca($mensajeEjemplo);
echo "<p><strong>Placa extraída:</strong> {$placa}</p>";

$prioridad = determinarPrioridad($mensajeEjemplo, $tipo);
echo "<p><strong>Prioridad determinada:</strong> {$prioridad}</p>";

$estado = determinarEstado($mensajeEjemplo, false);
echo "<p><strong>Estado determinado:</strong> {$estado}</p>";

// Ejemplo 9: Configuración de seguridad
echo "<h2>9. Configuración de seguridad</h2>";
echo "<ul>";
foreach (SECURITY_CONFIG as $key => $value) {
    echo "<li><strong>{$key}:</strong> " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "</li>";
}
echo "</ul>";

// Ejemplo 10: Configuración de logs
echo "<h2>10. Configuración de logs</h2>";
echo "<ul>";
foreach (LOG_CONFIG as $key => $value) {
    echo "<li><strong>{$key}:</strong> " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "</li>";
}
echo "</ul>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h1 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

h2 {
    color: #007bff;
    margin-top: 30px;
}

h3 {
    color: #555;
}

pre {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    overflow-x: auto;
    font-size: 12px;
}

ul {
    background-color: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

li {
    margin-bottom: 5px;
}

strong {
    color: #007bff;
}

p {
    background-color: white;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 10px 0;
}
</style> 