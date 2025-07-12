<?php
// Clase principal para validar y gestionar licencias del sistema
class LicenseValidator {
    // Propiedad privada para almacenar la conexión a la base de datos
    private $conexion;
    // Propiedad privada para almacenar los datos de la licencia activa actual
    private $licencia_actual;
    
    // Constructor de la clase que recibe la conexión a la base de datos
    public function __construct($conexion) {
        // Asigna la conexión recibida a la propiedad de la clase
        $this->conexion = $conexion;
        // Llama al método privado para cargar la licencia actual al instanciar la clase
        $this->cargarLicenciaActual();
    }
    
    // Método privado para cargar la licencia activa desde la base de datos
    private function cargarLicenciaActual() {
        try {
            // Prepara la consulta SQL para obtener la licencia activa más reciente
            $stmt = $this->conexion->prepare("
                SELECT * FROM sistema_licencias 
                WHERE estado = 'activa' 
                ORDER BY fecha_creacion DESC 
                LIMIT 1
            ");
            // Ejecuta la consulta preparada
            $stmt->execute();
            // Almacena el resultado como array asociativo en la propiedad licencia_actual
            $this->licencia_actual = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // En caso de error, establece licencia_actual como null
            $this->licencia_actual = null;
        }
    }
    
    // Método público para validar si la licencia actual es válida
    public function validarLicencia() {
        // Verifica si existe una licencia cargada
        if (!$this->licencia_actual) {
            // Retorna array con estado inválido y mensaje explicativo
            return ['valida' => false, 'mensaje' => 'No hay licencia configurada'];
        }
        
        // Obtiene la fecha actual en formato Y-m-d para comparación
        $fecha_actual = date('Y-m-d');
        // Compara si la fecha actual es posterior a la fecha de vencimiento
        if ($fecha_actual > $this->licencia_actual['fecha_vencimiento']) {
            // Actualiza el estado de la licencia a 'vencida' en la base de datos
            $this->actualizarEstadoLicencia('vencida');
            // Retorna array indicando que la licencia está vencida
            return ['valida' => false, 'mensaje' => 'Licencia vencida'];
        }
        
        // Verifica si la licencia está en estado suspendido
        if ($this->licencia_actual['estado'] === 'suspendida') {
            // Retorna array indicando que la licencia está suspendida
            return ['valida' => false, 'mensaje' => 'Licencia suspendida'];
        }
        
        // Si pasa todas las validaciones, retorna que la licencia es válida
        return ['valida' => true, 'mensaje' => 'Licencia válida'];
    }
    
    // Método público para validar si se puede agregar más usuarios
    public function validarLimiteUsuarios() {
        // Si no hay licencia cargada, retorna false
        if (!$this->licencia_actual) return false;
        
        // Prepara consulta para contar el total de usuarios registrados
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM usuarios");
        // Ejecuta la consulta de conteo
        $stmt->execute();
        // Obtiene el número actual de usuarios del resultado
        $usuarios_actuales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Compara si los usuarios actuales son menores al límite permitido
        return $usuarios_actuales < $this->licencia_actual['max_usuarios'];
    }
    
    // Método público para validar si se puede agregar más vehículos
    public function validarLimiteVehiculos() {
        // Si no hay licencia cargada, retorna false
        if (!$this->licencia_actual) return false;
        
        // Prepara consulta para contar el total de vehículos registrados
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM vehiculos");
        // Ejecuta la consulta de conteo
        $stmt->execute();
        // Obtiene el número actual de vehículos del resultado
        $vehiculos_actuales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Compara si los vehículos actuales son menores al límite permitido
        return $vehiculos_actuales < $this->licencia_actual['max_vehiculos'];
    }
    
    // Método público para obtener toda la información de la licencia actual
    public function obtenerInfoLicencia() {
        // Retorna el array completo con los datos de la licencia
        return $this->licencia_actual;
    }
    
    // Método público para obtener estadísticas detalladas de uso del sistema
    public function obtenerEstadisticasUso() {
        // Si no hay licencia cargada, retorna null
        if (!$this->licencia_actual) return null;
        
        // Consulta para obtener el total de usuarios actuales
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM usuarios");
        $stmt->execute();
        $usuarios_actuales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Consulta para obtener el total de vehículos actuales
        $stmt = $this->conexion->prepare("SELECT COUNT(*) as total FROM vehiculos");
        $stmt->execute();
        $vehiculos_actuales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Retorna array estructurado con estadísticas completas
        return [
            'usuarios' => [
                'actuales' => $usuarios_actuales, // Número actual de usuarios
                'limite' => $this->licencia_actual['max_usuarios'], // Límite máximo permitido
                'porcentaje' => ($usuarios_actuales / $this->licencia_actual['max_usuarios']) * 100 // Porcentaje de uso
            ],
            'vehiculos' => [
                'actuales' => $vehiculos_actuales, // Número actual de vehículos
                'limite' => $this->licencia_actual['max_vehiculos'], // Límite máximo permitido
                'porcentaje' => ($vehiculos_actuales / $this->licencia_actual['max_vehiculos']) * 100 // Porcentaje de uso
            ]
        ];
    }
    
    // Método privado para actualizar el estado de la licencia en la base de datos
    private function actualizarEstadoLicencia($nuevo_estado) {
        try {
            // Prepara consulta UPDATE para cambiar el estado de la licencia
            $stmt = $this->conexion->prepare("
                UPDATE sistema_licencias 
                SET estado = ? 
                WHERE id = ?
            ");
            // Ejecuta la actualización con el nuevo estado y el ID de la licencia actual
            $stmt->execute([$nuevo_estado, $this->licencia_actual['id']]);
        } catch (Exception $e) {
            // Registra cualquier error en el log del sistema
            error_log("Error actualizando estado de licencia: " . $e->getMessage());
        }
    }
    
    // Método público para calcular los días restantes de la licencia
    public function diasRestantesLicencia() {
        // Si no hay licencia cargada, retorna 0 días
        if (!$this->licencia_actual) return 0;
        
        // Crea objeto DateTime con la fecha actual
        $fecha_actual = new DateTime();
        // Crea objeto DateTime con la fecha de vencimiento de la licencia
        $fecha_vencimiento = new DateTime($this->licencia_actual['fecha_vencimiento']);
        // Calcula la diferencia entre las dos fechas
        $diferencia = $fecha_actual->diff($fecha_vencimiento);
        
        // Si invert es true, significa que ya venció (fecha actual > vencimiento)
        // Retorna 0 si ya venció, o los días restantes si aún es válida
        return $diferencia->invert ? 0 : $diferencia->days;
    }
}
// Cierre del bloque PHP
?>