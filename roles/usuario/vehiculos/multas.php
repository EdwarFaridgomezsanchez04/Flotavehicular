<?php
session_start();
if (!isset($_SESSION['documento'])) {
    echo "No hay una cédula registrada en la sesión.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $documentNumber = $_SESSION['documento'];
    $documentType = 'CC';
    $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjbGllbnRJZCI6IjY4MWUwZWQ5MDA3OWM4ZjkxZGE5ZmY3YiIsInAiOiJ2ayIsIkpXVFBocmFzZSI6IjY3ZjljYTg3MjQ5ZDVmOTFhNmE0NjA4ZiIsImV4cGlyZXNBdCI6MTc1MzQwMzA2NCwiaWF0IjoxNzUwODExMDY0fQ.ECvK-j7s8ZIIv1m9zLG34PeuAeXutz9midEbmnNy7pM';

    $url = "https://api.verifik.co/v2/co/simit/consultar?documentType=$documentType&documentNumber=$documentNumber";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        // Guardamos temporalmente los datos en sesión o pasamos por GET
        file_put_contents("respuesta.json", $response);
        header("Location: resultado.php");
        exit;
    } else {
        echo "Error HTTP $httpCode:<br>$response";
    }
} else {
    echo "Acceso no permitido.";
}
