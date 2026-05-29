<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('bombero');

$bombero_id = $_SESSION['usuario_id'];

// ============================================
// FUNCIÓN PARA ENVIAR NOTIFICACIONES AL CIUDADANO
// ============================================

function enviarNotificacionCiudadano($pdo, $usuario_id, $titulo, $mensaje, $tipo = 'emergencia') {
    if(!$usuario_id) return false;
    $stmt = $pdo->prepare("
        INSERT INTO notificaciones (usuario_id, titulo, mensaje, tipo, leido, fecha) 
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    return $stmt->execute([$usuario_id, $titulo, $mensaje, $tipo]);
}

// ============================================
// PROCESAR ACCIONES CON NOTIFICACIONES
// ============================================
$mensaje = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ASIGNAR EMERGENCIA
    if(isset($_POST['asignar_emergencia'])) {
        $emergencia_id = $_POST['emergencia_id'];
        $unidad = $_POST['unidad'];
        
        // Obtener información de la emergencia para la notificación
        $stmt = $pdo->prepare("SELECT usuario_id, tipo, ubicacion_texto FROM emergencias WHERE id = ?");
        $stmt->execute([$emergencia_id]);
        $emergencia = $stmt->fetch();
        
        // Insertar asignación
        $stmt = $pdo->prepare("INSERT INTO asignaciones (emergencia_id, bombero_id, unidad, estado_asignacion) VALUES (?, ?, ?, 'asignado')");
        $stmt->execute([$emergencia_id, $bombero_id, $unidad]);
        
        // Actualizar estado de la emergencia
        $stmt = $pdo->prepare("UPDATE emergencias SET estado = 'en_proceso', fecha_asignacion = NOW() WHERE id = ?");
        $stmt->execute([$emergencia_id]);
        
        // 🔔 ENVIAR NOTIFICACIÓN EN TIEMPO REAL AL CIUDADANO
        if($emergencia && $emergencia['usuario_id']) {
            $tipos = ['incendio' => '🔥 Incendio', 'inundacion' => '🌊 Inundación', 'accidente' => '🚗 Accidente', 'otros' => '📞 Otros'];
            $tipo_texto = $tipos[$emergencia['tipo']] ?? $emergencia['tipo'];
            $ubicacion = substr($emergencia['ubicacion_texto'] ?? 'tu ubicación', 0, 50);
            
            enviarNotificacionCiudadano(
                $pdo,
                $emergencia['usuario_id'],
                "🚨 Unidad asignada a tu emergencia",
                "Tu reporte de {$tipo_texto} en {$ubicacion} ha sido asignado a la unidad {$unidad}. Los bomberos están en camino.",
                'emergencia'
            );
        }
        
        $mensaje = "✅ Emergencia #$emergencia_id asignada correctamente";
    }
    
    // ACTUALIZAR ESTADO DE LA UNIDAD
    if(isset($_POST['actualizar_estado_asignacion'])) {
        $asignacion_id = $_POST['asignacion_id'];
        $nuevo_estado = $_POST['estado_asignacion'];
        
        $stmt = $pdo->prepare("UPDATE asignaciones SET estado_asignacion = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $asignacion_id]);
        
        // Obtener información para notificación
        $stmt = $pdo->prepare("
            SELECT a.emergencia_id, a.unidad, e.usuario_id, e.tipo, e.ubicacion_texto
            FROM asignaciones a 
            JOIN emergencias e ON a.emergencia_id = e.id 
            WHERE a.id = ?
        ");
        $stmt->execute([$asignacion_id]);
        $data = $stmt->fetch();
        
        $estados_texto = [
            'asignado' => '📌 ha sido asignada a tu emergencia',
            'en_camino' => '🚗 está en camino hacia tu ubicación',
            'en_sitio' => '📍 ha llegado al lugar de la emergencia',
            'completado' => '✅ ha completado la atención en el lugar'
        ];
        
        // 🔔 NOTIFICAR AL CIUDADANO
        if($data && $data['usuario_id'] && isset($estados_texto[$nuevo_estado])) {
            enviarNotificacionCiudadano(
                $pdo,
                $data['usuario_id'],
                "🚒 Actualización de tu emergencia",
                "La unidad {$data['unidad']} {$estados_texto[$nuevo_estado]}.",
                'estado'
            );
        }
        
        $mensaje = "✅ Estado actualizado correctamente";
    }
    
    // FINALIZAR EMERGENCIA
    if(isset($_POST['finalizar_emergencia'])) {
        $emergencia_id = $_POST['emergencia_id'];
        $reporte_servicio = $_POST['reporte_servicio'] ?? '';
        
        // Obtener usuario_id
        $stmt = $pdo->prepare("SELECT usuario_id FROM emergencias WHERE id = ?");
        $stmt->execute([$emergencia_id]);
        $emergencia = $stmt->fetch();
        
        $stmt = $pdo->prepare("UPDATE emergencias SET estado = 'finalizado', fecha_finalizacion = NOW(), descripcion_atencion = ? WHERE id = ?");
        $stmt->execute([$reporte_servicio, $emergencia_id]);
        
        // 🔔 NOTIFICAR AL CIUDADANO
        if($emergencia && $emergencia['usuario_id']) {
            enviarNotificacionCiudadano(
                $pdo,
                $emergencia['usuario_id'],
                "✅ Emergencia finalizada",
                "Tu emergencia ha sido atendida exitosamente. Gracias por confiar en Bomberos Chocó JRM. ¿Podrías calificar nuestro servicio?",
                'estado'
            );
        }
        
        $mensaje = "✅ Emergencia #$emergencia_id finalizada correctamente";
    }
    
    // GUARDAR REPORTE DEL BOMBERO
    if(isset($_POST['guardar_reporte'])) {
        $reporte_texto = $_POST['reporte_texto'];
        $stmt = $pdo->prepare("INSERT INTO reportes_bomberos (bombero_id, titulo, contenido, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$bombero_id, $_POST['titulo_reporte'], $reporte_texto]);
        $mensaje = "✅ Reporte guardado correctamente";
    }
}

// ============================================
// ESTADÍSTICAS (igual que antes)
// ============================================
$total_emergencias = $pdo->query("SELECT COUNT(*) as total FROM emergencias")->fetch();
$pendientes = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE estado = 'pendiente'")->fetch();
$en_proceso = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE estado = 'en_proceso'")->fetch();
$finalizados = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE estado = 'finalizado'")->fetch();
$tiempo_promedio = $pdo->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_reporte, fecha_asignacion)) as promedio FROM emergencias WHERE fecha_asignacion IS NOT NULL")->fetch();

// Estadísticas del bombero actual
$mis_asignaciones = $pdo->prepare("SELECT COUNT(*) as total FROM asignaciones WHERE bombero_id = ?");
$mis_asignaciones->execute([$bombero_id]);
$mis_asignaciones_total = $mis_asignaciones->fetch();

$mis_finalizadas = $pdo->prepare("SELECT COUNT(*) as total FROM asignaciones a JOIN emergencias e ON a.emergencia_id = e.id WHERE a.bombero_id = ? AND e.estado = 'finalizado'");
$mis_finalizadas->execute([$bombero_id]);
$mis_finalizadas_total = $mis_finalizadas->fetch();

// ============================================
// OBTENER DATOS (igual que antes)
// ============================================

// Pendientes
$pendientes_list = $pdo->query("
    SELECT e.*, u.nombre_completo as reportado_por, u.telefono as reportado_telefono 
    FROM emergencias e 
    LEFT JOIN usuarios u ON e.usuario_id = u.id 
    WHERE e.estado = 'pendiente' 
    ORDER BY e.fecha_reporte ASC
")->fetchAll();

// En Proceso
$en_proceso_list = $pdo->query("
    SELECT e.*, u.nombre_completo as reportado_por, 
           a.id as asignacion_id, a.unidad, a.estado_asignacion, a.fecha_asignacion,
           b.nombre_completo as bombero_asignado
    FROM emergencias e 
    LEFT JOIN usuarios u ON e.usuario_id = u.id 
    LEFT JOIN asignaciones a ON e.id = a.emergencia_id 
    LEFT JOIN usuarios b ON a.bombero_id = b.id
    WHERE e.estado = 'en_proceso' 
    ORDER BY e.fecha_asignacion ASC
")->fetchAll();

// Historial COMPLETO (últimos 50)
$historial_completo = $pdo->query("
    SELECT e.*, u.nombre_completo as reportado_por,
           a.unidad, a.estado_asignacion, a.fecha_asignacion,
           b.nombre_completo as bombero_asignado
    FROM emergencias e 
    LEFT JOIN usuarios u ON e.usuario_id = u.id 
    LEFT JOIN asignaciones a ON e.id = a.emergencia_id 
    LEFT JOIN usuarios b ON a.bombero_id = b.id
    WHERE e.estado = 'finalizado' 
    ORDER BY e.fecha_finalizacion DESC 
    LIMIT 50
")->fetchAll();

// Reportes del bombero
$mis_reportes = $pdo->prepare("SELECT * FROM reportes_bomberos WHERE bombero_id = ? ORDER BY fecha DESC LIMIT 20");
$mis_reportes->execute([$bombero_id]);
$mis_reportes_list = $mis_reportes->fetchAll();

// Unidades disponibles
$unidades_disponibles = $pdo->query("SELECT * FROM unidades WHERE disponible = 1")->fetchAll();
if(count($unidades_disponibles) == 0) {
    $unidades_disponibles = [
        ['id' => 1, 'nombre' => 'Unidad 1 - Quibdó Centro', 'tipo' => 'Camión cisterna'],
        ['id' => 2, 'nombre' => 'Unidad 2 - Barrio la Playita', 'tipo' => 'Ambulancia'],
        ['id' => 3, 'nombre' => 'Unidad 3 - Aeropuerto', 'tipo' => 'Escalera'],
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Control - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ========== ESTILOS COMPLETOS (igual que tu código) ========== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-img {
            height: 55px;
            width: auto;
            border-radius: 12px;
            background: white;
            padding: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        
        .logo-img:hover {
            transform: scale(1.05);
        }
        
        .logo-area h1 {
            font-size: 22px;
        }
        
        .logo-area h1 span {
            font-size: 12px;
            color: #e74c3c;
            display: block;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            background: rgba(255,255,255,0.1);
            padding: 8px 20px;
            border-radius: 50px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 18px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .hero-banner {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .hero-banner::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background: url('../assets/images/imagen7.jpg') no-repeat center center;
            background-size: cover;
            opacity: 0.15;
            border-radius: 25px;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
            flex: 1;
        }
        
        .hero-content h2 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .hero-content h2 span {
            color: #e74c3c;
        }
        
        .hero-content p {
            opacity: 0.9;
            max-width: 500px;
        }
        
        .hero-image {
            position: relative;
            z-index: 1;
        }
        
        .hero-image img {
            width: 200px;
            height: 200px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 3px solid rgba(255,255,255,0.2);
            object-fit: cover;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 25px;
        }
        
        .mensaje-flotante {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #27ae60;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            z-index: 1000;
            animation: fadeOut 3s ease-in-out;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; transform: translateX(0); }
            70% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(100%); }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        .stat-card.pendiente::before { background: #f39c12; }
        .stat-card.proceso::before { background: #3498db; }
        .stat-card.finalizado::before { background: #27ae60; }
        .stat-card.primary::before { background: #e74c3c; }
        
        .stat-number {
            font-size: 36px;
            font-weight: bold;
        }
        
        .pendiente .stat-number { color: #f39c12; }
        .proceso .stat-number { color: #3498db; }
        .finalizado .stat-number { color: #27ae60; }
        .primary .stat-number { color: #e74c3c; }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            background: white;
            border: none;
            padding: 12px 28px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            font-family: 'Poppins', sans-serif;
        }
        
        .tab-btn:hover {
            transform: translateY(-2px);
        }
        
        .tab-btn.active {
            background: #e74c3c;
            color: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .map-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .map-container h3 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        #map { 
            height: 380px; 
            border-radius: 15px;
        }
        
        .legend {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .incidentes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .incidente-columna {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            max-height: 650px;
            overflow-y: auto;
        }
        
        .incidente-columna::-webkit-scrollbar {
            width: 6px;
        }
        
        .incidente-columna::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .incidente-columna::-webkit-scrollbar-thumb {
            background: #e74c3c;
            border-radius: 10px;
        }
        
        .incidente-columna h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            position: sticky;
            top: 0;
            background: white;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .contador {
            background: #f0f2f5;
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .incidente-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            border-left: 4px solid;
        }
        
        .incidente-card:hover { 
            transform: translateX(5px); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .incidente-card.pendiente { border-left-color: #f39c12; }
        .incidente-card.en_proceso { border-left-color: #3498db; }
        .incidente-card.finalizado { border-left-color: #27ae60; }
        
        .incidente-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .incidente-tipo {
            font-weight: 600;
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .tipo-incendio { background: #e74c3c; color: white; }
        .tipo-inundacion { background: #3498db; color: white; }
        .tipo-accidente { background: #f39c12; color: white; }
        .tipo-otros { background: #95a5a6; color: white; }
        
        .incidente-gravedad {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
        }
        
        .gravedad-alta { background: #e74c3c; color: white; }
        .gravedad-media { background: #f39c12; color: white; }
        .gravedad-baja { background: #27ae60; color: white; }
        
        .incidente-info {
            font-size: 12px;
            color: #666;
            margin: 6px 0;
        }
        
        .incidente-info i { 
            width: 22px; 
            color: #e74c3c;
        }
        
        .proceso-info {
            background: #e8f4f8;
            padding: 10px;
            border-radius: 10px;
            margin: 10px 0;
            font-size: 12px;
        }
        
        .btn-asignar, .btn-actualizar, .btn-finalizar {
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            margin-top: 8px;
            margin-right: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-asignar { background: #3498db; color: white; }
        .btn-asignar:hover { background: #2980b9; }
        .btn-actualizar { background: #f39c12; color: white; }
        .btn-actualizar:hover { background: #e67e22; }
        .btn-finalizar { background: #27ae60; color: white; }
        .btn-finalizar:hover { background: #229954; }
        
        select, textarea {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 12px;
            width: 100%;
            margin-top: 8px;
            font-family: 'Poppins', sans-serif;
        }
        
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            overflow-x: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .table-container h3 {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-incendio { background: #e74c3c; color: white; }
        .badge-inundacion { background: #3498db; color: white; }
        .badge-accidente { background: #f39c12; color: white; }
        
        .form-reporte {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
        }
        
        .form-reporte h4 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .reporte-item {
            background: white;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 10px;
            border-left: 4px solid #e74c3c;
            transition: all 0.3s;
        }
        
        .reporte-item:hover {
            transform: translateX(3px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 1000px) {
            .incidentes-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .hero-banner { flex-direction: column; text-align: center; }
            .hero-image { margin-top: 20px; }
            .tabs { justify-content: center; }
        }
        
        @media (max-width: 600px) {
            .header { flex-direction: column; gap: 15px; text-align: center; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-area">
            <img src="../assets/images/logo.png" alt="Logo" class="logo-img" onerror="this.style.display='none'">
            <h1>🚒 Centro de Control<br><span><?php echo SITE_NAME; ?></span></h1>
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
            </div>
            <span><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </div>
    
    <div class="container">
        <?php if($mensaje): ?>
            <div class="mensaje-flotante"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <div class="hero-banner">
            <div class="hero-content">
                <h2>¡Bienvenido, <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>! 👨‍🚒</h2>
                <p>Estás listo para proteger y servir a la comunidad de Quibdó. Aquí puedes gestionar todas las emergencias asignadas.</p>
            </div>
            <div class="hero-image">
                <img src="../assets/images/imagen7.jpg" alt="Bombero en acción" onerror="this.src='https://via.placeholder.com/200x200?text=Bombero'">
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card pendiente">
                <div class="stat-number"><?php echo $pendientes['total']; ?></div>
                <div class="stat-label">📌 Emergencias Pendientes</div>
            </div>
            <div class="stat-card proceso">
                <div class="stat-number"><?php echo $en_proceso['total']; ?></div>
                <div class="stat-label">⚙️ En Proceso</div>
            </div>
            <div class="stat-card finalizado">
                <div class="stat-number"><?php echo $finalizados['total']; ?></div>
                <div class="stat-label">✅ Finalizadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo round($tiempo_promedio['promedio'] ?? 0); ?> min</div>
                <div class="stat-label">⏱️ Tiempo Promedio</div>
            </div>
            <div class="stat-card primary">
                <div class="stat-number"><?php echo $mis_asignaciones_total['total']; ?></div>
                <div class="stat-label">📋 Mis Asignaciones</div>
            </div>
            <div class="stat-card primary">
                <div class="stat-number"><?php echo $mis_finalizadas_total['total']; ?></div>
                <div class="stat-label">✅ Mis Finalizadas</div>
            </div>
        </div>
        
        <div class="map-container">
            <h3><i class="fas fa-map-marked-alt"></i> Mapa en Tiempo Real</h3>
            <div id="map"></div>
            <div class="legend">
                <span><span style="color:#e74c3c;">🔴</span> Incendio</span>
                <span><span style="color:#3498db;">🔵</span> Inundación</span>
                <span><span style="color:#f39c12;">🟠</span> Accidente</span>
                <span><span style="color:#27ae60;">🟢</span> Finalizado</span>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab-btn active" onclick="mostrarTab('activas')"><i class="fas fa-fire"></i> Emergencias Activas</button>
            <button class="tab-btn" onclick="mostrarTab('historial')"><i class="fas fa-history"></i> Historial Completo</button>
            <button class="tab-btn" onclick="mostrarTab('reportes')"><i class="fas fa-file-alt"></i> Mis Reportes</button>
        </div>
        
        <!-- TAB 1: Emergencias Activas -->
        <div id="tab-activas" class="tab-content active">
            <div class="incidentes-grid">
                <!-- Pendientes -->
                <div class="incidente-columna">
                    <h3><i class="fas fa-clock"></i> ⏳ Pendientes</h3>
                    <div class="contador"><?php echo count($pendientes_list); ?> incidente(s)</div>
                    <?php if(count($pendientes_list) > 0): ?>
                        <?php foreach($pendientes_list as $e): ?>
                        <div class="incidente-card pendiente">
                            <div class="incidente-header">
                                <span class="incidente-tipo tipo-<?php echo $e['tipo']; ?>">
                                    <?php $iconos = ['incendio' => '🔥', 'inundacion' => '🌊', 'accidente' => '🚗', 'otros' => '📞']; echo $iconos[$e['tipo']] . ' ' . ucfirst($e['tipo']); ?>
                                </span>
                                <span class="incidente-gravedad gravedad-<?php echo $e['gravedad']; ?>">
                                    <?php if($e['gravedad'] == 'alta'): ?>🔴 Alta<?php elseif($e['gravedad'] == 'media'): ?>🟡 Media<?php else: ?>🟢 Baja<?php endif; ?>
                                </span>
                            </div>
                            <div class="incidente-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($e['ubicacion_texto'] ?? '', 0, 50)); ?></div>
                            <div class="incidente-info"><i class="fas fa-user"></i> Reportado por: <?php echo htmlspecialchars($e['reportado_por'] ?? 'Anónimo'); ?></div>
                            <div class="incidente-info"><i class="fas fa-clock"></i> Hace <?php echo round((time() - strtotime($e['fecha_reporte']))/60); ?> minutos</div>
                            <div class="incidente-info"><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars(substr($e['descripcion'] ?? '', 0, 60)); ?>...</div>
                            <form method="POST">
                                <input type="hidden" name="emergencia_id" value="<?php echo $e['id']; ?>">
                                <select name="unidad" required>
                                    <option value="">Seleccionar unidad</option>
                                    <?php foreach($unidades_disponibles as $u): ?>
                                        <option value="<?php echo htmlspecialchars($u['nombre']); ?>">🚒 <?php echo htmlspecialchars($u['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="asignar_emergencia" class="btn-asignar"><i class="fas fa-truck"></i> Asignar Unidad</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-check-circle" style="font-size: 40px; margin-bottom: 10px;"></i>
                            <p>No hay emergencias pendientes</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- En Proceso -->
                <div class="incidente-columna">
                    <h3><i class="fas fa-sync-alt"></i> 🔄 En Proceso</h3>
                    <div class="contador"><?php echo count($en_proceso_list); ?> incidente(s)</div>
                    <?php if(count($en_proceso_list) > 0): ?>
                        <?php foreach($en_proceso_list as $e): ?>
                        <div class="incidente-card en_proceso">
                            <div class="incidente-header">
                                <span class="incidente-tipo tipo-<?php echo $e['tipo']; ?>"><?php $iconos = ['incendio' => '🔥', 'inundacion' => '🌊', 'accidente' => '🚗', 'otros' => '📞']; echo $iconos[$e['tipo']] . ' ' . ucfirst($e['tipo']); ?></span>
                                <span class="incidente-gravedad gravedad-<?php echo $e['gravedad']; ?>"><?php if($e['gravedad'] == 'alta'): ?>🔴 Alta<?php elseif($e['gravedad'] == 'media'): ?>🟡 Media<?php else: ?>🟢 Baja<?php endif; ?></span>
                            </div>
                            <div class="incidente-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($e['ubicacion_texto'] ?? '', 0, 50)); ?></div>
                            <div class="proceso-info">
                                <strong><i class="fas fa-truck"></i> Unidad:</strong> <?php echo htmlspecialchars($e['unidad'] ?? 'No asignada'); ?><br>
                                <strong><i class="fas fa-flag-checkered"></i> Estado:</strong> <?php $estados_unidad = ['asignado' => '📌 Asignado', 'en_camino' => '🚗 En camino', 'en_sitio' => '📍 En el lugar', 'completado' => '✅ Completado']; echo $estados_unidad[$e['estado_asignacion']] ?? '📌 Asignado'; ?>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="asignacion_id" value="<?php echo $e['asignacion_id']; ?>">
                                <select name="estado_asignacion" onchange="this.form.submit()">
                                    <option value="asignado" <?php echo ($e['estado_asignacion'] ?? '') == 'asignado' ? 'selected' : ''; ?>>📌 Asignado</option>
                                    <option value="en_camino" <?php echo ($e['estado_asignacion'] ?? '') == 'en_camino' ? 'selected' : ''; ?>>🚗 En camino</option>
                                    <option value="en_sitio" <?php echo ($e['estado_asignacion'] ?? '') == 'en_sitio' ? 'selected' : ''; ?>>📍 En el lugar</option>
                                    <option value="completado" <?php echo ($e['estado_asignacion'] ?? '') == 'completado' ? 'selected' : ''; ?>>✅ Completado</option>
                                </select>
                                <button type="submit" name="actualizar_estado_asignacion" class="btn-actualizar"><i class="fas fa-sync-alt"></i> Actualizar</button>
                            </form>
                            <form method="POST" onsubmit="return confirm('¿Finalizar esta emergencia?')">
                                <input type="hidden" name="emergencia_id" value="<?php echo $e['id']; ?>">
                                <textarea name="reporte_servicio" placeholder="Reporte de atención (opcional)" rows="2" style="font-size:11px;"></textarea>
                                <button type="submit" name="finalizar_emergencia" class="btn-finalizar"><i class="fas fa-check-circle"></i> Finalizar</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-hourglass-half" style="font-size: 40px; margin-bottom: 10px;"></i>
                            <p>No hay emergencias en proceso</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Finalizados Recientes -->
                <div class="incidente-columna">
                    <h3><i class="fas fa-check-circle"></i> ✅ Últimos Finalizados</h3>
                    <div class="contador"><?php echo min(10, count($historial_completo)); ?> últimos incidentes</div>
                    <?php if(count($historial_completo) > 0): ?>
                        <?php foreach(array_slice($historial_completo, 0, 10) as $e): ?>
                        <div class="incidente-card finalizado">
                            <div class="incidente-header">
                                <span class="incidente-tipo tipo-<?php echo $e['tipo']; ?>"><?php $iconos = ['incendio' => '🔥', 'inundacion' => '🌊', 'accidente' => '🚗', 'otros' => '📞']; echo $iconos[$e['tipo']] . ' ' . ucfirst($e['tipo']); ?></span>
                            </div>
                            <div class="incidente-info"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($e['ubicacion_texto'] ?? '', 0, 40)); ?></div>
                            <div class="incidente-info"><i class="fas fa-check-circle"></i> Finalizado: <?php echo date('d/m/Y H:i', strtotime($e['fecha_finalizacion'])); ?></div>
                            <?php if($e['unidad']): ?>
                            <div class="incidente-info"><i class="fas fa-truck"></i> Unidad: <?php echo htmlspecialchars($e['unidad']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-history" style="font-size: 40px; margin-bottom: 10px;"></i>
                            <p>No hay emergencias finalizadas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- TAB 2: Historial Completo -->
        <div id="tab-historial" class="tab-content">
            <div class="table-container">
                <h3><i class="fas fa-history"></i> Historial de Todas las Emergencias Atendidas</h3>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Fecha</th><th>Tipo</th><th>Ubicación</th><th>Gravedad</th><th>Unidad</th><th>Bombero</th><th>Finalizado</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($historial_completo as $e): ?>
                        <tr>
                            <td>#<?php echo $e['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($e['fecha_reporte'])); ?></td>
                            <td><span class="badge badge-<?php echo $e['tipo']; ?>"><?php echo ucfirst($e['tipo']); ?></span></td>
                            <td><?php echo htmlspecialchars(substr($e['ubicacion_texto'] ?? '', 0, 40)); ?></td>
                            <td><?php if($e['gravedad'] == 'alta'): ?>🔴 Alta<?php elseif($e['gravedad'] == 'media'): ?>🟡 Media<?php else: ?>🟢 Baja<?php endif; ?></td>
                            <td><?php echo htmlspecialchars($e['unidad'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($e['bombero_asignado'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($e['fecha_finalizacion'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($historial_completo) == 0): ?>
                        <tr><td colspan="8" style="text-align:center;">No hay emergencias finalizadas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- TAB 3: Mis Reportes -->
        <div id="tab-reportes" class="tab-content">
            <div class="table-container">
                <h3><i class="fas fa-file-alt"></i> Mis Reportes de Servicio</h3>
                
                <div class="form-reporte">
                    <h4><i class="fas fa-plus-circle"></i> Crear Nuevo Reporte</h4>
                    <form method="POST">
                        <input type="text" name="titulo_reporte" placeholder="Título del reporte" required style="width:100%; padding:12px; margin-bottom:12px; border-radius:10px; border:1px solid #ddd;">
                        <textarea name="reporte_texto" placeholder="Escribe aquí tu reporte sobre las atenciones realizadas, novedades, recomendaciones..." rows="5" required style="width:100%; padding:12px; border-radius:10px; border:1px solid #ddd;"></textarea>
                        <button type="submit" name="guardar_reporte" class="btn-asignar" style="margin-top:15px;"><i class="fas fa-save"></i> Guardar Reporte</button>
                    </form>
                </div>
                
                <h3 style="margin-top: 30px;">📋 Mis Reportes Anteriores</h3>
                <?php if(count($mis_reportes_list) > 0): ?>
                    <?php foreach($mis_reportes_list as $r): ?>
                    <div class="reporte-item">
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                            <strong><i class="fas fa-file"></i> <?php echo htmlspecialchars($r['titulo']); ?></strong>
                            <small style="color:#999;"><?php echo date('d/m/Y H:i', strtotime($r['fecha'])); ?></small>
                        </div>
                        <p style="margin-top: 10px; color: #555;"><?php echo nl2br(htmlspecialchars($r['contenido'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; color:#999; padding:40px;">No has creado ningún reporte aún</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function mostrarTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            event.target.classList.add('active');
        }
        
        var map = L.map('map').setView([5.6948, -76.6612], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        function cargarEmergenciasMapa() {
            fetch('../api/emergencias_todas.php')
                .then(response => response.json())
                .then(data => {
                    map.eachLayer(layer => { if(layer instanceof L.CircleMarker) map.removeLayer(layer); });
                    data.forEach(e => {
                        if(e.latitud && e.longitud) {
                            var color = e.tipo == 'incendio' ? '#e74c3c' : (e.tipo == 'inundacion' ? '#3498db' : (e.tipo == 'accidente' ? '#f39c12' : '#95a5a6'));
                            if(e.estado == 'finalizado') color = '#27ae60';
                            L.circleMarker([parseFloat(e.latitud), parseFloat(e.longitud)], { 
                                color: color, 
                                radius: 10, 
                                fillOpacity: 0.7,
                                weight: 2
                            }).bindPopup(`<b>${e.tipo}</b><br>Estado: ${e.estado}<br>Gravedad: ${e.gravedad}`).addTo(map);
                        }
                    });
                });
        }
        cargarEmergenciasMapa();
        setInterval(cargarEmergenciasMapa, 30000);
    </script>
</body>
</html>