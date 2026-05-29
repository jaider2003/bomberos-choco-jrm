
<?php
// Iniciar sesión siempre
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set('America/Bogota');

// Configuración del sitio
define('SITE_NAME', 'BOMBEROS CHOCÓ JRM');
define('SITE_URL', 'https://bombero.infinityfreeapp.com/');  // ← CAMBIADO

// Verificar si el usuario está logueado
function usuarioLogueado() {
    return isset($_SESSION['usuario_id']);
}

// Verificar rol específico
function verificarRol($rol) {
    if(!usuarioLogueado()) {
        header("Location: " . SITE_URL . "auth/login.php");
        exit();
    }
    if($_SESSION['rol'] != $rol && $_SESSION['rol'] != 'administrador') {
        header("Location: " . SITE_URL . "auth/login.php");
        exit();
    }
}

// Obtener nombre del rol
function getRolNombre($rol) {
    $roles = [
        'ciudadano' => 'Ciudadano',
        'bombero' => 'Bombero',
        'administrador' => 'Administrador'
    ];
    return $roles[$rol] ?? $rol;
}

// Mensajes flash
function setMensaje($tipo, $mensaje) {
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $mensaje];
}

function getMensaje() {
    if(isset($_SESSION['mensaje'])) {
        $mensaje = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
        return $mensaje;
    }
    return null;
}
?>