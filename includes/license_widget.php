<?php
// Bloque PHP para widget de visualización del estado de licencia

// Incluir la clase LicenseValidator para validaciones y obtención de datos
require_once __DIR__ . '/LicenseValidator.php';

// Incluir la clase Database para conexión a la base de datos
require_once __DIR__ . '/../conecct/conex.php';

/**
 * Función para generar y mostrar el widget de estado de licencia
 * @return string - HTML del widget con información de licencia y estadísticas
 */
function mostrarWidgetLicencia() {
    // Crear instancia de la clase Database
    $database = new Database();
    
    // Establecer conexión con la base de datos
    $conexion = $database->conectar();
    
    // Crear instancia del validador de licencias
    $validator = new LicenseValidator($conexion);
    
    // Obtener información completa de la licencia actual
    $info_licencia = $validator->obtenerInfoLicencia();
    
    // Obtener estadísticas de uso (usuarios y vehículos actuales vs límites)
    $estadisticas = $validator->obtenerEstadisticasUso();
    
    // Calcular días restantes hasta vencimiento de la licencia
    $dias_restantes = $validator->diasRestantesLicencia();
    
    // Verificar si existe información de licencia válida
    if (!$info_licencia) return ''; // Retornar cadena vacía si no hay licencia
    
    // Determinar color de alerta según días restantes
    $color_alerta = $dias_restantes <= 30 ? 'danger' :     // Rojo: ≤ 30 días
                   ($dias_restantes <= 60 ? 'warning' :    // Amarillo: ≤ 60 días
                    'success');                             // Verde: > 60 días
    
    // Generar y retornar HTML del widget con información de licencia
    return '
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <!-- Título del widget con icono -->
            <h6 class="card-title"><i class="fas fa-certificate me-2"></i>Estado de Licencia</h6>
            
            <!-- Sección de estadísticas de uso -->
            <div class="row">
                <!-- Columna de estadísticas de usuarios -->
                <div class="col-6">
                    <small class="text-muted">Usuarios</small>
                    <!-- Barra de progreso para usuarios -->
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar" style="width: ' . $estadisticas['usuarios']['porcentaje'] . '%"></div>
                    </div>
                    <!-- Contador actual/límite de usuarios -->
                    <small>' . $estadisticas['usuarios']['actuales'] . '/' . $estadisticas['usuarios']['limite'] . '</small>
                </div>
                
                <!-- Columna de estadísticas de vehículos -->
                <div class="col-6">
                    <small class="text-muted">Vehículos</small>
                    <!-- Barra de progreso para vehículos -->
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar" style="width: ' . $estadisticas['vehiculos']['porcentaje'] . '%"></div>
                    </div>
                    <!-- Contador actual/límite de vehículos -->
                    <small>' . $estadisticas['vehiculos']['actuales'] . '/' . $estadisticas['vehiculos']['limite'] . '</small>
                </div>
            </div>
            
            <!-- Separador visual -->
            <hr>
            
            <!-- Información de días restantes con color dinámico -->
            <small class="text-' . $color_alerta . '">
                <i class="fas fa-clock me-1"></i>' . $dias_restantes . ' días restantes
            </small>
        </div>
    </div>';
}
// Cierre del bloque PHP
?>