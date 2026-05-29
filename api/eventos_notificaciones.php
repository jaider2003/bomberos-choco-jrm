<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('X-Accel-Buffering: no');
set_time_limit(0);
ignore_user_abort(true);

require_once '../includes/conexion.php';
session_start();

if(!isset($_SESSION['usuario_id'])) {
    echo "event: error\n";
    echo "data: No autorizado\n\n";
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$ultimo_id = 0;

// Limitar el tiempo de vida de la conexión (30 segundos máximo)
$tiempo_inicio = time();
$max_tiempo = 25; // 25 segundos

// Obtener el último ID
$stmt = $pdo->prepare("SELECT MAX(id) as ultimo FROM notificaciones WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$resultado = $stmt->fetch();
if($resultado && $resultado['ultimo']) {
    $ultimo_id = $resultado['ultimo'];
}

// Enviar keep-alive cada 10 segundos
$contador = 0;

while(true) {
    // Verificar si el cliente cerró la conexión
    if(connection_aborted()) {
        break;
    }
    
    // Limitar tiempo de vida
    if(time() - $tiempo_inicio > $max_tiempo) {
        echo "event: ping\n";
        echo "data: keepalive\n\n";
        ob_flush();
        flush();
        $tiempo_inicio = time(); // Reiniciar contador
    }
    
    // Buscar notificaciones nuevas (solo una vez por ciclo)
    $stmt = $pdo->prepare("
        SELECT * FROM notificaciones 
        WHERE usuario_id = ? AND id > ? 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->execute([$usuario_id, $ultimo_id]);
    $notificacion = $stmt->fetch();
    
    if($notificacion) {
        $ultimo_id = $notificacion['id'];
        
        echo "event: nueva_notificacion\n";
        echo "data: " . json_encode([
            'id' => $notificacion['id'],
            'titulo' => $notificacion['titulo'],
            'mensaje' => $notificacion['mensaje'],
            'tipo' => $notificacion['tipo'],
            'fecha' => $notificacion['fecha']
        ]) . "\n\n";
        ob_flush();
        flush();
    }
    
    // Enviar contador cada 5 ciclos (10 segundos)
    $contador++;
    if($contador >= 3) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM notificaciones 
            WHERE usuario_id = ? AND leido = 0
        ");
        $stmt->execute([$usuario_id]);
        $total = $stmt->fetch();
        
        echo "event: contador\n";
        echo "data: " . json_encode(['total_no_leidas' => $total['total']]) . "\n\n";
        ob_flush();
        flush();
        $contador = 0;
    }
    
    // Pausa más larga para reducir carga (5 segundos)
    sleep(5);
}
?>