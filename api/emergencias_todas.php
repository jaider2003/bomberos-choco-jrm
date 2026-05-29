<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../includes/conexion.php';

$stmt = $pdo->query("SELECT id, tipo, latitud, longitud, gravedad, estado, descripcion FROM emergencias WHERE latitud IS NOT NULL AND longitud IS NOT NULL");
$emergencias = $stmt->fetchAll();
echo json_encode($emergencias);
?>