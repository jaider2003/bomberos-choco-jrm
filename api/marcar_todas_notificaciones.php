<?php
header('Content-Type: application/json');
require_once '../includes/conexion.php';
session_start();

if(!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$stmt = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario_id']]);

echo json_encode(['success' => true]);
?>