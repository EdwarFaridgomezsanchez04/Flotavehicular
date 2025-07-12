<?php
// Apertura del bloque PHP para la clase de conexión a base de datos

// Definición de la clase Database para manejar conexiones a MySQL
class Database
{
    // Propiedades privadas para almacenar los parámetros de conexión
    private $hostname;   // Servidor de base de datos (host)
    private $database;   // Nombre de la base de datos
    private $username;   // Usuario de la base de datos
    private $password;   // Contraseña del usuario
    private $charset = "utf8"; // Codificación de caracteres por defecto

    // Constructor de la clase - se ejecuta automáticamente al crear una instancia
    public function __construct()
    {
        // Detectar el entorno de ejecución (local vs producción)
        if (
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['DOCUMENT_ROOT'], 'htdocs') !== false
        ) {
            // 🔧 Configuración para entorno local (XAMPP)
            $this->hostname = 'localhost';           // Servidor local
            $this->database = 'proyecto_flota';      // Nombre de la base de datos local
            $this->username = 'root';                // Usuario por defecto de XAMPP
            $this->password = '';                    // Sin contraseña en XAMPP local
        } else {
            // 🌐 Configuración para entorno de producción (Hostinger u otro hosting)
            $this->hostname = 'localhost';                    // Servidor de producción
            $this->database = 'u148394603_flota_agc';        // Nombre de la base de datos en producción
            $this->username = 'u148394603_flota_agc';        // Usuario de la base de datos en producción
            $this->password = 'Faridgomez04';                // Contraseña de la base de datos en producción
        }
    }

    // Método público para establecer la conexión con la base de datos
    public function conectar()
    {
        try {
            // Construir la cadena de conexión DSN (Data Source Name) para MySQL
            $conexion = "mysql:host=" . $this->hostname . "; dbname=" . $this->database . "; charset=" . $this->charset;
            
            // Configurar opciones de PDO para mejorar seguridad y manejo de errores
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Lanzar excepciones en caso de error
                PDO::ATTR_EMULATE_PREPARES => false           // Usar prepared statements nativos del servidor
            ];

            // Crear nueva instancia de PDO con los parámetros configurados
            $pdo = new PDO($conexion, $this->username, $this->password, $options);

            // Establecer la zona horaria de la base de datos a GMT-5 (Colombia)
            $pdo->exec("SET time_zone = '-05:00'");

            // Retornar el objeto PDO para uso en otras partes del sistema
            return $pdo;
        } catch (PDOException $e) {
            // Capturar y mostrar errores de conexión
            echo 'Error de conexión: ' . $e->getMessage();
            exit; // Terminar la ejecución si no se puede conectar
        }
    }
}

// Cierre del bloque PHP
?>