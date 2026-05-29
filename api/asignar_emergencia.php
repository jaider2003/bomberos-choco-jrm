<?php
require_once '../includes/conexion.php';

$id = $_GET['id'] ?? 0;
$unidad = $_GET['unidad'] ?? '';

$stmt = $pdo->prepare("UPDATE emergencias SET estado = 'en_proceso', fecha_asignacion = NOW() WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
?>