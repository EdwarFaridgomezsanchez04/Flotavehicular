<?php
include '../../../conecct/conex.php';

// Instantiate the Database class and get the PDO connection
$database = new Database();
$conn = $database->conectar();

// Check if the connection is successful
if (!$conn) {
    echo '<option value="">Error: No se pudo conectar a la base de datos</option>';
    exit;
}

if (isset($_POST['id_tipo']) && !empty($_POST['id_tipo'])) {
    $id_tipo = $_POST['id_tipo'];
    
    // Query to fetch brands based on the selected vehicle type using PDO
    $query = "SELECT id_marca, nombre_marca FROM marca WHERE id_tipo_vehiculo = :id_tipo";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_tipo', $id_tipo, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if query returned results
    if ($result && count($result) > 0) {
        echo '<option value="">Seleccione una marca...</option>';
        foreach ($result as $row) {
            echo '<option value="' . htmlspecialchars($row['id_marca']) . '">' 
                 . htmlspecialchars($row['nombre_marca']) . '</option>';
        }
    } else {
        echo '<option value="">No hay marcas disponibles</option>';
    }
} else {
    echo '<option value="">Seleccione un tipo primero</option>';
}
?>