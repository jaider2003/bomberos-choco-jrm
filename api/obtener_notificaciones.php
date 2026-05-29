<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../includes/conexion.php';
session_start();

if(!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener notificaciones
$stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 30");
$stmt->execute([$usuario_id]);
$notificaciones = $stmt->fetchAll();

// Contar no leídas
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leido = 0");
$stmt->execute([$usuario_id]);
$no_leidas = $stmt->fetch();

echo json_encode([
    'success' => true,
    'notificaciones' => $notificaciones,
    'total_no_leidas' => $no_leidas['total']
]);
?>