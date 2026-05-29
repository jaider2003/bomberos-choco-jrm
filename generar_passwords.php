<?php
// Archivo: C:\xampp\htdocs\bomberos_choco\generar_passwords.php

echo "<h2>Generador de Contraseñas para Bomberos Chocó JRM</h2>";

$passwords = [
    'admin' => 'admin123',
    'bombero' => 'bombero123',
    'ciudadano' => 'ciudadano123',
    '123456' => '123456'
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Usuario</th><th>Contraseña</th><th>Hash (copia esto)</th></tr>";

foreach($passwords as $usuario => $pass) {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    echo "<tr>";
    echo "<td>$usuario</td>";
    echo "<td>$pass</td>";
    echo "<td><code>$hash</code></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>📋 Para insertar en tu base de datos, copia y pega este SQL:</h3>";
echo "<pre>";
echo "DELETE FROM usuarios WHERE email LIKE '%@prueba.com';\n\n";
echo "-- Administrador\n";
echo "INSERT INTO usuarios (nombre_completo, email, password, rol) VALUES \n";
echo "('Admin Sistema', 'admin@prueba.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'administrador');\n\n";
echo "-- Bombero\n";
echo "INSERT INTO usuarios (nombre_completo, email, password, rol) VALUES \n";
echo "('Bombero Carlos', 'bombero@prueba.com', '" . password_hash('bombero123', PASSWORD_DEFAULT) . "', 'bombero');\n\n";
echo "-- Ciudadano\n";
echo "INSERT INTO usuarios (nombre_completo, email, password, rol) VALUES \n";
echo "('Ciudadano Pedro', 'ciudadano@prueba.com', '" . password_hash('ciudadano123', PASSWORD_DEFAULT) . "', 'ciudadano');";
echo "</pre>";
?>