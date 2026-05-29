<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('ciudadano');

$usuario_id = $_SESSION['usuario_id'];

// ============================================
// ESTADÍSTICAS DEL USUARIO
// ============================================

// Total de reportes del usuario
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emergencias WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_reportes = $stmt->fetch();

// Reportes por estado
$stmt = $pdo->prepare("
    SELECT estado, COUNT(*) as cantidad 
    FROM emergencias 
    WHERE usuario_id = ? 
    GROUP BY estado
");
$stmt->execute([$usuario_id]);
$reportes_por_estado = $stmt->fetchAll();
$pendientes = 0; $en_proceso = 0; $finalizados = 0;
foreach($reportes_por_estado as $rpe) {
    if($rpe['estado'] == 'pendiente') $pendientes = $rpe['cantidad'];
    if($rpe['estado'] == 'en_proceso') $en_proceso = $rpe['cantidad'];
    if($rpe['estado'] == 'finalizado') $finalizados = $rpe['cantidad'];
}

// Tiempo promedio de respuesta
$stmt = $pdo->prepare("
    SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_reporte, fecha_asignacion)) as promedio 
    FROM emergencias 
    WHERE usuario_id = ? AND fecha_asignacion IS NOT NULL
");
$stmt->execute([$usuario_id]);
$tiempo_promedio = $stmt->fetch();

// Últimos 5 reportes
$stmt = $pdo->prepare("
    SELECT * FROM emergencias 
    WHERE usuario_id = ? 
    ORDER BY fecha_reporte DESC 
    LIMIT 5
");
$stmt->execute([$usuario_id]);
$ultimos_reportes = $stmt->fetchAll();

// Reportes por tipo
$stmt = $pdo->prepare("
    SELECT tipo, COUNT(*) as cantidad 
    FROM emergencias 
    WHERE usuario_id = ? 
    GROUP BY tipo
");
$stmt->execute([$usuario_id]);
$tipos_reportes = $stmt->fetchAll();

// Notificaciones no leídas
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = ? AND leido = 0");
$stmt->execute([$usuario_id]);
$notificaciones_no_leidas = $stmt->fetch();

// Últimas notificaciones
$stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 20");
$stmt->execute([$usuario_id]);
$todas_notificaciones = $stmt->fetchAll();

// Consejos de seguridad
$consejos = [
    ["icon" => "🔥", "titulo" => "Prevención de Incendios", "texto" => "No sobrecargues enchufes y revisa el estado de las instalaciones eléctricas regularmente."],
    ["icon" => "🌊", "titulo" => "Inundaciones", "texto" => "Mantén despejadas las alcantarillas y no arrojes basura a las calles."],
    ["icon" => "🚗", "titulo" => "Accidentes de Tránsito", "texto" => "Respeta las señales de tránsito y no uses el celular mientras conduces."],
    ["icon" => "📞", "titulo" => "Números de Emergencia", "texto" => "Guarda el número de bomberos: 119 y el de la policía: 123."],
    ["icon" => "🏠", "titulo" => "Plan Familiar", "texto" => "Prepara un plan de evacuación en caso de emergencia con tu familia."],
    ["icon" => "🧯", "titulo" => "Extintores", "texto" => "Verifica que tu extintor esté cargado y vigente. Aprende a usarlo."],
];
$consejo_aleatorio = $consejos[array_rand($consejos)];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Mi Dashboard - <?php echo SITE_NAME; ?></title>
    
    <!-- ========================================== -->
    <!-- BOOTSTRAP 5 (Framework requerido)          -->
    <!-- ========================================== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Tus estilos existentes (DESPUÉS de Bootstrap para que prevalezcan) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ========================================== */
        /* TUS ESTILOS ORIGINALES (COMPLETOS)         */
        /* ========================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
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
            color: white;
            font-size: 22px;
        }

        .logo-area h1 span {
            color: #e74c3c;
            font-size: 13px;
            display: block;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notif-btn {
            position: relative;
            background: rgba(255,255,255,0.15);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            color: white;
        }

        .notif-btn:hover {
            background: #e74c3c;
        }

        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74c3c;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 50%;
            display: <?php echo ($notificaciones_no_leidas['total'] > 0) ? 'flex' : 'none'; ?>;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.15);
            padding: 8px 18px;
            border-radius: 50px;
            color: white;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
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

        .notif-panel {
            position: fixed;
            top: 80px;
            right: 20px;
            width: 380px;
            max-height: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            z-index: 1001;
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notif-panel-header {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notif-panel-header h3 {
            margin: 0;
            font-size: 16px;
        }

        .close-panel {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        .notif-list {
            overflow-y: auto;
            flex: 1;
            max-height: 420px;
        }

        .notif-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .notif-item:hover {
            background: #f8f9fa;
        }

        .notif-item.no-leida {
            background: #fff3e0;
        }

        .notif-item.no-leida:hover {
            background: #ffe8cc;
        }

        .notif-icon {
            font-size: 24px;
        }

        .notif-content {
            flex: 1;
        }

        .notif-titulo {
            font-weight: 600;
            font-size: 14px;
        }

        .notif-mensaje {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .notif-fecha {
            font-size: 10px;
            color: #999;
            margin-top: 6px;
        }

        .notif-badge-red {
            width: 10px;
            height: 10px;
            background: #e74c3c;
            border-radius: 50%;
        }

        .notif-footer {
            padding: 12px 20px;
            border-top: 1px solid #eee;
            text-align: center;
        }

        .marcar-todas-btn {
            background: none;
            border: none;
            color: #e74c3c;
            cursor: pointer;
            font-size: 13px;
        }

        .toast-notification {
            position: fixed;
            bottom: 100px;
            right: 20px;
            background: white;
            border-radius: 16px;
            padding: 15px 20px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            z-index: 2000;
            min-width: 320px;
            max-width: 380px;
            animation: slideInRight 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid #e74c3c;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .toast-notification .toast-icon {
            font-size: 28px;
        }

        .toast-notification .toast-content {
            flex: 1;
        }

        .toast-notification .toast-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .toast-notification .toast-message {
            font-size: 12px;
            color: #666;
        }

        .toast-notification .toast-close {
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: #999;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
        }

        .hero-banner {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 25px;
            padding: 40px;
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
            width: 50%;
            height: 100%;
            background: url('../assets/images/logan-weaver-lgnwvr-oZXjSB2LtuU-unsplash.jpg') no-repeat center center;
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
            font-size: 32px;
            margin-bottom: 15px;
        }

        .hero-content h2 span {
            color: #e74c3c;
        }

        .hero-content p {
            opacity: 0.9;
            margin-bottom: 20px;
            max-width: 500px;
        }

        .hero-stats {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .hero-stat {
            background: rgba(255,255,255,0.15);
            padding: 10px 20px;
            border-radius: 15px;
            backdrop-filter: blur(5px);
        }

        .hero-stat .number {
            font-size: 28px;
            font-weight: bold;
            color: #e74c3c;
        }

        .hero-image {
            position: relative;
            z-index: 1;
        }

        .hero-image img {
            width: 280px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 3px solid rgba(255,255,255,0.2);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 22px;
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
            background: #e74c3c;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }

        .stat-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 13px;
            margin-top: 5px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .card-header h3 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2c3e50;
            font-size: 18px;
        }

        .btn-view-all {
            color: #e74c3c;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }

        .reporte-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
            transition: all 0.3s;
            cursor: pointer;
            border-left: 4px solid;
        }

        .reporte-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .reporte-item.pendiente { border-left-color: #f39c12; }
        .reporte-item.en_proceso { border-left-color: #3498db; }
        .reporte-item.finalizado { border-left-color: #27ae60; }

        .reporte-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .reporte-tipo {
            font-weight: 600;
            font-size: 14px;
        }

        .reporte-fecha {
            font-size: 11px;
            color: #999;
        }

        .reporte-ubicacion {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }

        .reporte-estado {
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        .estado-pendiente { background: #f39c12; color: white; }
        .estado-proceso { background: #3498db; color: white; }
        .estado-finalizado { background: #27ae60; color: white; }

        .acciones-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .accion-btn {
            padding: 20px;
            border-radius: 15px;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .accion-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .accion-btn i {
            font-size: 30px;
            display: block;
            margin-bottom: 10px;
        }

        .accion-btn strong {
            display: block;
            font-size: 14px;
        }

        .accion-btn small {
            font-size: 11px;
        }

        .btn-reportar { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
        .btn-reportar small { color: rgba(255,255,255,0.8); }
        .btn-ver { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
        .btn-ver small { color: rgba(255,255,255,0.8); }
        .btn-llamar { background: linear-gradient(135deg, #27ae60, #229954); color: white; }
        .btn-llamar small { color: rgba(255,255,255,0.8); }
        .btn-compartir { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
        .btn-compartir small { color: rgba(255,255,255,0.8); }

        .tip-card {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            border-radius: 20px;
            padding: 20px;
            color: white;
            margin-top: 30px;
        }

        .tip-card h4 {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .emergency-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            box-shadow: 0 5px 25px rgba(231, 76, 60, 0.4);
            animation: pulse 2s infinite;
            transition: all 0.3s;
            z-index: 1000;
        }

        .emergency-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(231, 76, 60, 0.6);
        }

        .emergency-btn i {
            font-size: 30px;
        }

        .emergency-btn span {
            font-size: 11px;
            margin-top: 5px;
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 5px 25px rgba(231, 76, 60, 0.4); }
            50% { transform: scale(1.05); box-shadow: 0 8px 35px rgba(231, 76, 60, 0.6); }
            100% { transform: scale(1); box-shadow: 0 5px 25px rgba(231, 76, 60, 0.4); }
        }

        @media (max-width: 900px) {
            .main-grid { grid-template-columns: 1fr; }
            .hero-banner { flex-direction: column; text-align: center; }
            .hero-content p { max-width: 100%; }
            .hero-stats { justify-content: center; }
            .hero-image { margin-top: 20px; }
            .emergency-btn { width: 60px; height: 60px; bottom: 20px; right: 20px; }
            .emergency-btn i { font-size: 24px; }
            .emergency-btn span { display: none; }
            .notif-panel { width: 320px; right: 10px; }
            .toast-notification { min-width: 280px; right: 10px; bottom: 80px; }
        }
        
        @media (max-width: 600px) {
            .header { flex-direction: column; gap: 15px; text-align: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .acciones-grid { grid-template-columns: 1fr; }
        }

        /* ========================================== */
        /* ESTILOS DE COMPATIBILIDAD CON BOOTSTRAP   */
        /* ========================================== */
        /* Evitar que Bootstrap modifique nuestros estilos */
        .card {
            background: white !important;
            border: none !important;
        }
        
        .btn {
            font-family: 'Poppins', sans-serif !important;
        }
        
        /* Estilo para el badge de Bootstrap que agregaremos */
        .bootstrap-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Botón de ayuda flotante de Bootstrap */
        .bootstrap-help-btn {
            position: fixed;
            bottom: 130px;
            right: 30px;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
            z-index: 999;
            border: none;
        }
        
        .bootstrap-help-btn:hover {
            transform: scale(1.1);
            background: #2980b9;
        }
        
        @media (max-width: 900px) {
            .bootstrap-help-btn {
                bottom: 100px;
                right: 20px;
                width: 40px;
                height: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-area">
            <img src="../assets/images/logo.png" alt="Logo Bomberos Chocó JRM" class="logo-img" onerror="this.style.display='none'">
            <h1><?php echo SITE_NAME; ?><span>Protegiendo a Quibdó</span></h1>
        </div>
        <div class="user-menu">
            <div class="notif-btn" id="notifBtn">
                <i class="fas fa-bell"></i>
                <span class="notif-badge" id="notifBadge"><?php echo $notificaciones_no_leidas['total']; ?></span>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            </div>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </div>

    <div id="notifPanel" class="notif-panel">
        <div class="notif-panel-header">
            <h3><i class="fas fa-bell"></i> Notificaciones</h3>
            <button class="close-panel" id="closePanelBtn">&times;</button>
        </div>
        <div id="notifList" class="notif-list">
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-spinner fa-spin" style="font-size: 30px;"></i>
                <p>Cargando notificaciones...</p>
            </div>
        </div>
        <div class="notif-footer">
            <button id="marcarTodasBtn" class="marcar-todas-btn">
                <i class="fas fa-check-double"></i> Marcar todas como leídas
            </button>
        </div>
    </div>

    <div class="container">
        <div class="hero-banner">
            <div class="hero-content">
                <h2>¡Bienvenido, <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>! 👋</h2>
                <p>Tu seguridad es nuestra prioridad. Reporta cualquier emergencia y te ayudaremos inmediatamente. Estamos contigo 24/7.</p>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="number"><?php echo $total_reportes['total']; ?></div>
                        <div>Reportes realizados</div>
                    </div>
                    <div class="hero-stat">
                        <div class="number"><?php echo round($tiempo_promedio['promedio'] ?? 0); ?></div>
                        <div>Min tiempo promedio</div>
                    </div>
                    <div class="hero-stat">
                        <div class="number">119</div>
                        <div>Línea de emergencia</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="../assets/images/logan-weaver-lgnwvr-oZXjSB2LtuU-unsplash.jpg" alt="Bombero en acción" onerror="this.src='https://via.placeholder.com/280x200?text=Bomberos'">
            </div>
        </div>

        <!-- BADGE DE BOOTSTRAP (EVIDENCIA DEL USO DEL FRAMEWORK) -->
        <div class="text-center mb-4">
            <span class="bootstrap-badge">
                <i class="bi bi-bootstrap-fill"></i> 
                
                <i class="bi bi-check-circle-fill"></i>
            </span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?php echo $total_reportes['total']; ?></div>
                <div class="stat-label">Total Reportes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?php echo $pendientes; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🔄</div>
                <div class="stat-number"><?php echo $en_proceso; ?></div>
                <div class="stat-label">En Proceso</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $finalizados; ?></div>
                <div class="stat-label">Finalizados</div>
            </div>
        </div>

        <div class="main-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Mis Últimos Reportes</h3>
                    <a href="mis_reportes.php" class="btn-view-all">Ver todos →</a>
                </div>
                <div>
                    <?php if(count($ultimos_reportes) > 0): ?>
                        <?php foreach($ultimos_reportes as $reporte): ?>
                            <div class="reporte-item <?php echo $reporte['estado']; ?>" onclick="window.location.href='seguimiento.php?id=<?php echo $reporte['id']; ?>'">
                                <div class="reporte-header">
                                    <span class="reporte-tipo">
                                        <?php 
                                        $iconos = ['incendio' => '🔥', 'inundacion' => '🌊', 'accidente' => '🚗', 'otros' => '📞'];
                                        echo $iconos[$reporte['tipo']] ?? '📞'; 
                                        ?> <?php echo ucfirst($reporte['tipo']); ?>
                                    </span>
                                    <span class="reporte-fecha"><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])); ?></span>
                                </div>
                                <div class="reporte-ubicacion">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($reporte['ubicacion_texto'] ?? '', 0, 55)); ?>
                                </div>
                                <div>
                                    <span class="reporte-estado estado-<?php echo $reporte['estado']; ?>">
                                        <?php 
                                        $estados_iconos = ['pendiente' => '⏳', 'en_proceso' => '🔄', 'finalizado' => '✅'];
                                        echo $estados_iconos[$reporte['estado']] . ' ' . ucfirst($reporte['estado']); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-clipboard-list" style="font-size: 50px; margin-bottom: 15px;"></i>
                            <p>No has realizado ningún reporte aún</p>
                            <a href="reportar.php" style="display: inline-block; margin-top: 15px; background: #e74c3c; color: white; padding: 10px 20px; border-radius: 25px; text-decoration: none;">
                                Reportar ahora
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Mis Reportes por Tipo</h3>
                </div>
                <?php if(count($tipos_reportes) > 0): ?>
                    <canvas id="graficoTipos" height="200"></canvas>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #999;">
                        <i class="fas fa-chart-simple" style="font-size: 40px; margin-bottom: 15px;"></i>
                        <p>No hay datos para mostrar</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="main-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
                </div>
                <div class="acciones-grid">
                    <a href="reportar.php" class="accion-btn btn-reportar">
                        <i class="fas fa-plus-circle"></i>
                        <strong>Reportar</strong>
                        <small>Nueva emergencia</small>
                    </a>
                    <a href="mis_reportes.php" class="accion-btn btn-ver">
                        <i class="fas fa-list"></i>
                        <strong>Ver todos</strong>
                        <small>Mis reportes</small>
                    </a>
                    <a href="#" onclick="contactarEmergencia()" class="accion-btn btn-llamar">
                        <i class="fas fa-phone-alt"></i>
                        <strong>Llamar</strong>
                        <small>119 - Emergencias</small>
                    </a>
                    <a href="#" onclick="compartirUbicacion()" class="accion-btn btn-compartir">
                        <i class="fas fa-share-alt"></i>
                        <strong>Compartir</strong>
                        <small>Mi ubicación</small>
                    </a>
                </div>
            </div>

            <div class="tip-card">
                <h4><i class="fas fa-lightbulb"></i> Consejo de Seguridad del Día</h4>
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div style="font-size: 40px;"><?php echo $consejo_aleatorio['icon']; ?></div>
                    <div>
                        <strong><?php echo $consejo_aleatorio['titulo']; ?></strong>
                        <p style="opacity: 0.9; margin-top: 5px; font-size: 13px;"><?php echo $consejo_aleatorio['texto']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="reportar.php" class="emergency-btn">
        <i class="fas fa-phone-alt"></i>
        <span>EMERGENCIA</span>
    </a>

    <!-- BOTÓN DE AYUDA CON BOOTSTRAP (EVIDENCIA DEL USO DEL FRAMEWORK) -->
    <button type="button" class="bootstrap-help-btn" data-bs-toggle="modal" data-bs-target="#ayudaModal">
        <i class="bi bi-question-lg"></i>
    </button>

    <!-- MODAL DE BOOTSTRAP (EVIDENCIA DEL USO DEL FRAMEWORK) -->
    <div class="modal fade" id="ayudaModal" tabindex="-1" aria-labelledby="ayudaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white;">
                    <h5 class="modal-title" id="ayudaModalLabel">
                        <i class="bi bi-headset"></i> Centro de Ayuda
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="bi bi-megaphone-fill" style="font-size: 50px; color: #e74c3c;"></i>
                    </div>
                    <h6><i class="bi bi-telephone-fill text-danger"></i> Líneas de Emergencia</h6>
                    <p class="mb-3"><strong>119</strong> - Bomberos<br><strong>123</strong> - Policía<br><strong>132</strong> - Ambulancias</p>
                    
                    <h6><i class="bi bi-file-text-fill text-primary"></i> ¿Cómo reportar?</h6>
                    <p class="mb-3">Usa el botón <strong>"Reportar Emergencia"</strong> en Acciones Rápidas o el botón flotante rojo.</p>
                    
                    <h6><i class="bi bi-geo-alt-fill text-success"></i> Compartir ubicación</h6>
                    <p class="mb-0">Usa el botón <strong>"Compartir"</strong> para enviar tu ubicación exacta a los bomberos.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cerrar
                    </button>
                    <a href="reportar.php" class="btn btn-danger">
                        <i class="bi bi-telephone-fill"></i> Reportar Ahora
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- BOOTSTRAP JS (Necesario para el modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Gráfico de tipos de reportes
        <?php if(count($tipos_reportes) > 0): ?>
        const ctx = document.getElementById('graficoTipos').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($tipos_reportes, 'tipo')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($tipos_reportes, 'cantidad')); ?>,
                    backgroundColor: ['#e74c3c', '#3498db', '#f39c12', '#95a5a6'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 } } }
                }
            }
        });
        <?php endif; ?>

        // ============================================
        // SISTEMA DE NOTIFICACIONES CON POLLING
        // ============================================
        
        let panelAbierto = false;
        let pollingInterval = null;
        let ultimoIdNotificacion = 0;
        
        const notifBtn = document.getElementById('notifBtn');
        const notifPanel = document.getElementById('notifPanel');
        const closePanelBtn = document.getElementById('closePanelBtn');
        const marcarTodasBtn = document.getElementById('marcarTodasBtn');
        const notifList = document.getElementById('notifList');
        const notifBadge = document.getElementById('notifBadge');
        
        function reproducirSonido() {
            try {
                const audio = new Audio('https://www.soundjay.com/misc/sounds/bell-ringing-05.mp3');
                audio.volume = 0.2;
                audio.play().catch(e => console.log('Sonido no disponible'));
            } catch(e) {
                console.log('Sonido no soportado');
            }
        }
        
        function mostrarToast(titulo, mensaje, tipo) {
            const toast = document.createElement('div');
            toast.className = 'toast-notification';
            
            let icono = '🔔';
            let colorBorder = '#e74c3c';
            if(tipo === 'emergencia') {
                icono = '🚨';
                colorBorder = '#e74c3c';
            } else if(tipo === 'estado') {
                icono = '📢';
                colorBorder = '#3498db';
            } else {
                icono = 'ℹ️';
                colorBorder = '#27ae60';
            }
            
            toast.style.borderLeftColor = colorBorder;
            
            toast.innerHTML = `
                <div class="toast-icon">${icono}</div>
                <div class="toast-content">
                    <div class="toast-title">${escapeHtml(titulo)}</div>
                    <div class="toast-message">${escapeHtml(mensaje)}</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if(toast && toast.remove) toast.remove();
            }, 5000);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function formatFecha(fecha) {
            const date = new Date(fecha);
            return date.toLocaleString('es-CO', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        function verificarNotificacionesNuevas() {
            fetch('../api/obtener_notificaciones.php')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        if(notifBadge) {
                            if(data.total_no_leidas > 0) {
                                notifBadge.textContent = data.total_no_leidas;
                                notifBadge.style.display = 'flex';
                            } else {
                                notifBadge.style.display = 'none';
                            }
                        }
                        
                        if(data.notificaciones.length > 0) {
                            const ultimaNotif = data.notificaciones[0];
                            if(ultimaNotif.id > ultimoIdNotificacion) {
                                ultimoIdNotificacion = ultimaNotif.id;
                                if(!panelAbierto) {
                                    mostrarToast(ultimaNotif.titulo, ultimaNotif.mensaje, ultimaNotif.tipo);
                                    reproducirSonido();
                                }
                                if(panelAbierto) {
                                    cargarNotificaciones();
                                }
                            }
                        }
                    }
                })
                .catch(error => console.error('Error en polling:', error));
        }
        
        function cargarNotificaciones() {
            fetch('../api/obtener_notificaciones.php')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        if(notifBadge) {
                            if(data.total_no_leidas > 0) {
                                notifBadge.textContent = data.total_no_leidas;
                                notifBadge.style.display = 'flex';
                            } else {
                                notifBadge.style.display = 'none';
                            }
                        }
                        
                        if(data.notificaciones.length > 0) {
                            ultimoIdNotificacion = data.notificaciones[0].id;
                        }
                        
                        if(notifList) {
                            if(data.notificaciones.length > 0) {
                                notifList.innerHTML = data.notificaciones.map(notif => `
                                    <div class="notif-item ${notif.leido ? '' : 'no-leida'}" onclick="marcarLeida(${notif.id})">
                                        <div class="notif-icon">${notif.tipo === 'emergencia' ? '🚨' : (notif.tipo === 'estado' ? '📢' : 'ℹ️')}</div>
                                        <div class="notif-content">
                                            <div class="notif-titulo">${escapeHtml(notif.titulo)}</div>
                                            <div class="notif-mensaje">${escapeHtml(notif.mensaje)}</div>
                                            <div class="notif-fecha"><i class="far fa-clock"></i> ${formatFecha(notif.fecha)}</div>
                                        </div>
                                        ${!notif.leido ? '<div class="notif-badge-red"></div>' : ''}
                                    </div>
                                `).join('');
                            } else {
                                notifList.innerHTML = `
                                    <div style="text-align: center; padding: 50px 20px; color: #999;">
                                        <i class="fas fa-bell-slash" style="font-size: 40px; margin-bottom: 15px;"></i>
                                        <p>No tienes notificaciones</p>
                                    </div>
                                `;
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error cargando notificaciones:', error);
                    if(notifList) {
                        notifList.innerHTML = `
                            <div style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 40px; margin-bottom: 15px;"></i>
                                <p>Error al cargar notificaciones</p>
                            </div>
                        `;
                    }
                });
        }
        
        function marcarLeida(id) {
            fetch(`../api/marcar_notificacion.php?id=${id}`)
                .then(() => cargarNotificaciones());
        }
        
        function marcarTodasLeidas() {
            fetch('../api/marcar_todas_notificaciones.php')
                .then(() => cargarNotificaciones());
        }
        
        function toggleNotificaciones() {
            if(panelAbierto) {
                notifPanel.style.display = 'none';
                panelAbierto = false;
            } else {
                notifPanel.style.display = 'flex';
                panelAbierto = true;
                cargarNotificaciones();
            }
        }
        
        function contactarEmergencia() {
            if(confirm("¿Deseas llamar al número de emergencias 119?")) {
                window.location.href = "tel:119";
            }
        }
        
        function compartirUbicacion() {
            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const url = `https://www.google.com/maps?q=${pos.coords.latitude},${pos.coords.longitude}`;
                    if(navigator.share) {
                        navigator.share({
                            title: 'Mi ubicación actual',
                            text: 'Estoy aquí, necesito ayuda',
                            url: url
                        });
                    } else {
                        prompt("Comparte esta ubicación:", url);
                    }
                });
            } else {
                alert("Tu navegador no soporta geolocalización");
            }
        }
        
        if(notifBtn) notifBtn.addEventListener('click', toggleNotificaciones);
        if(closePanelBtn) closePanelBtn.addEventListener('click', () => {
            notifPanel.style.display = 'none';
            panelAbierto = false;
        });
        if(marcarTodasBtn) marcarTodasBtn.addEventListener('click', marcarTodasLeidas);
        
        document.addEventListener('click', function(event) {
            if(panelAbierto && notifPanel && !notifPanel.contains(event.target) && notifBtn && !notifBtn.contains(event.target)) {
                notifPanel.style.display = 'none';
                panelAbierto = false;
            }
        });
        
        cargarNotificaciones();
        pollingInterval = setInterval(verificarNotificacionesNuevas, 10000);
        
        window.marcarLeida = marcarLeida;
        window.contactarEmergencia = contactarEmergencia;
        window.compartirUbicacion = compartirUbicacion;
    </script>
</body>
</html>