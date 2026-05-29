<?php
require_once '../includes/conexion.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("UPDATE emergencias SET estado = 'finalizado', fecha_finalizacion = NOW() WHERE id = ?");
$stmt->execute([$id]);

echo json_encode(['success' => true]);
?>