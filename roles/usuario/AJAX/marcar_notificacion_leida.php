<?php
// filepath: c:\xampp\htdocs\Flotavehicular\roles\usuario\AJAX\marcar_notificacion_leida.php
session_start();
require_once '../../../conecct/conex.php';

if (!isset($_POST['id'])) exit;
$id = intval($_POST['id']);
$documento = $_SESSION['documento'];

$db = new Database();
$con = $db->conectar();

// Solo marca como leída si la notificación pertenece al usuario logueado
$stmt = $con->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND documento_usuario = ?");
$stmt->execute([$id, $documento]);

echo "ok";