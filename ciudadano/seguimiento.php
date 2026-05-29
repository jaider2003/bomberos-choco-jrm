<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('ciudadano');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM emergencias WHERE id = ? AND usuario_id = ?");
$stmt->execute([$id, $_SESSION['usuario_id']]);
$reporte = $stmt->fetch();

if(!$reporte) {
    header("Location: mis_reportes.php");
    exit();
}

$estados = [
    'pendiente' => ['icon' => '⏳', 'color' => '#f39c12', 'texto' => 'Tu reporte está pendiente. Los bomberos lo revisarán pronto.'],
    'en_proceso' => ['icon' => '🔄', 'color' => '#3498db', 'texto' => 'Tu reporte está siendo atendido. Una unidad ha sido asignada.'],
    'finalizado' => ['icon' => '✅', 'color' => '#27ae60', 'texto' => 'Tu emergencia ha sido atendida exitosamente.']
];

// Obtener asignaciones
$stmt = $pdo->prepare("SELECT a.*, u.nombre_completo as bombero_nombre FROM asignaciones a LEFT JOIN usuarios u ON a.bombero_id = u.id WHERE a.emergencia_id = ? ORDER BY a.fecha_asignacion DESC");
$stmt->execute([$id]);
$asignaciones = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Reporte #<?php echo $id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { max-width: 900px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #7f8c8d;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            margin-bottom: 20px;
        }
        .status-badge { text-align: center; font-size: 80px; margin-bottom: 20px; }
        .status-text {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: <?php echo $estados[$reporte['estado']]['color']; ?>;
            margin-bottom: 15px;
        }
        .status-message { text-align: center; color: #666; margin-bottom: 30px; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 25px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        .info-item label { font-size: 12px; color: #7f8c8d; display: block; }
        .info-item .value { font-size: 16px; font-weight: 500; margin-top: 5px; }
        .timeline { margin: 30px 0; }
        .timeline-step {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        .timeline-step.completed { opacity: 1; }
        .timeline-icon {
            width: 50px;
            height: 50px;
            background: <?php echo $estados[$reporte['estado']]['color']; ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 15px;
        }
        .btn-volver {
            display: block;
            background: #e74c3c;
            color: white;
            text-align: center;
            padding: 15px;
            border-radius: 12px;
            text-decoration: none;
            margin-top: 20px;
        }
        .asignacion-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .card { padding: 20px; }
            .status-text { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="mis_reportes.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Mis Reportes
        </a>
        
        <div class="card">
            <div class="status-badge"><?php echo $estados[$reporte['estado']]['icon']; ?></div>
            <div class="status-text">Reporte #<?php echo $id; ?> - <?php echo ucfirst($reporte['estado']); ?></div>
            <div class="status-message"><?php echo $estados[$reporte['estado']]['texto']; ?></div>
            
            <div class="info-grid">
                <div class="info-item">
                    <label><i class="fas fa-calendar"></i> Fecha del Reporte</label>
                    <div class="value"><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_reporte'])); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-fire"></i> Tipo de Emergencia</label>
                    <div class="value">
                        <?php 
                        $iconos = ['incendio' => '🔥', 'inundacion' => '🌊', 'accidente' => '🚗', 'otros' => '📞'];
                        echo $iconos[$reporte['tipo']] . ' ' . ucfirst($reporte['tipo']); 
                        ?>
                    </div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-chart-line"></i> Gravedad</label>
                    <div class="value">
                        <?php if($reporte['gravedad'] == 'alta'): ?>
                            🔴 Alta
                        <?php elseif($reporte['gravedad'] == 'media'): ?>
                            🟡 Media
                        <?php else: ?>
                            🟢 Baja
                        <?php endif; ?>
                    </div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-map-marker-alt"></i> Ubicación</label>
                    <div class="value"><?php echo htmlspecialchars($reporte['ubicacion_texto'] ?? 'No especificada'); ?></div>
                </div>
            </div>
            
            <div class="info-item" style="margin-top: 15px;">
                <label><i class="fas fa-file-alt"></i> Descripción</label>
                <div class="value" style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 5px;">
                    <?php echo nl2br(htmlspecialchars($reporte['descripcion'] ?? 'Sin descripción')); ?>
                </div>
            </div>
            
            <div class="timeline">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-line"></i> Línea de Tiempo</h3>
                <div class="timeline-step <?php echo $reporte['fecha_reporte'] ? 'completed' : ''; ?>">
                    <div class="timeline-icon"><i class="fas fa-check"></i></div>
                    <div>
                        <strong>Reporte creado</strong><br>
                        <small><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_reporte'])); ?></small>
                    </div>
                </div>
                <div class="timeline-step <?php echo $reporte['fecha_asignacion'] ? 'completed' : ''; ?>">
                    <div class="timeline-icon"><i class="fas fa-truck"></i></div>
                    <div>
                        <strong>Unidad asignada</strong><br>
                        <small><?php echo $reporte['fecha_asignacion'] ? date('d/m/Y H:i:s', strtotime($reporte['fecha_asignacion'])) : 'Pendiente'; ?></small>
                    </div>
                </div>
                <div class="timeline-step <?php echo $reporte['fecha_finalizacion'] ? 'completed' : ''; ?>">
                    <div class="timeline-icon"><i class="fas fa-flag-checkered"></i></div>
                    <div>
                        <strong>Emergencia finalizada</strong><br>
                        <small><?php echo $reporte['fecha_finalizacion'] ? date('d/m/Y H:i:s', strtotime($reporte['fecha_finalizacion'])) : 'Pendiente'; ?></small>
                    </div>
                </div>
            </div>
            
            <?php if(count($asignaciones) > 0): ?>
            <div style="margin-top: 20px;">
                <h3><i class="fas fa-clipboard-list"></i> Historial de Asignaciones</h3>
                <?php foreach($asignaciones as $a): ?>
                <div class="asignacion-item">
                    <strong><i class="fas fa-truck"></i> Unidad:</strong> <?php echo htmlspecialchars($a['unidad']); ?><br>
                    <strong><i class="fas fa-user"></i> Bombero:</strong> <?php echo htmlspecialchars($a['bombero_nombre'] ?? 'No asignado'); ?><br>
                    <strong><i class="fas fa-calendar"></i> Asignado:</strong> <?php echo date('d/m/Y H:i', strtotime($a['fecha_asignacion'])); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <a href="mis_reportes.php" class="btn-volver">← Volver a Mis Reportes</a>
        </div>
    </div>
</body>
</html>