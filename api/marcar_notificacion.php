<?php
header('Content-Type: application/json');
require_once '../includes/conexion.php';
session_start();

if(!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id > 0) {
    $stmt = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>