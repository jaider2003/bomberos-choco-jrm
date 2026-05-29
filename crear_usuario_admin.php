<?php
require_once 'includes/conexion.php';

$email = 'admin@bomberoschoco.gov.co';
$password = 'admin123';
$nombre = 'Administrador Principal';

// Verificar si ya existe
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$existe = $stmt->fetch();

if($existe) {
    // Actualizar contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, rol = 'administrador' WHERE email = ?");
    $stmt->execute([$hash, $email]);
    echo "✅ Usuario ADMINISTRADOR actualizado correctamente<br>";
} else {
    // Crear nuevo
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, email, password, rol) VALUES (?, ?, ?, 'administrador')");
    $stmt->execute([$nombre, $email, $hash]);
    echo "✅ Usuario ADMINISTRADOR creado correctamente<br>";
}

// Crear bombero
$email_b = 'bombero@bomberoschoco.gov.co';
$password_b = 'bombero123';
$nombre_b = 'Bombero Oficial';

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email_b]);
$existe_b = $stmt->fetch();

if($existe_b) {
    $hash = password_hash($password_b, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, rol = 'bombero' WHERE email = ?");
    $stmt->execute([$hash, $email_b]);
    echo "✅ Usuario BOMBERO actualizado correctamente<br>";
} else {
    $hash = password_hash($password_b, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, email, password, rol) VALUES (?, ?, ?, 'bombero')");
    $stmt->execute([$nombre_b, $email_b, $hash]);
    echo "✅ Usuario BOMBERO creado correctamente<br>";
}

echo "<hr>";
echo "<h3>📋 Credenciales para probar:</h3>";
echo "<ul>";
echo "<li><strong>ADMINISTRADOR:</strong> admin@bomberoschoco.gov.co / admin123</li>";
echo "<li><strong>BOMBERO:</strong> bombero@bomberoschoco.gov.co / bombero123</li>";
echo "<li><strong>CIUDADANO:</strong> ciudadano@test.com / ciudadano123</li>";
echo "</ul>";
echo "<a href='auth/login.php'>Ir al Login</a>";
?>