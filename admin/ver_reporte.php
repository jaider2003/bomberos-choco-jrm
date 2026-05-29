<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('administrador');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($id == 0) {
    header("Location: gestion_reportes.php");
    exit();
}

// Obtener datos del reporte
$stmt = $pdo->prepare("
    SELECT e.*, u.nombre_completo as usuario_nombre, u.email as usuario_email, u.telefono as usuario_telefono 
    FROM emergencias e 
    LEFT JOIN usuarios u ON e.usuario_id = u.id 
    WHERE e.id = ?
");
$stmt->execute([$id]);
$reporte = $stmt->fetch();

if(!$reporte) {
    header("Location: gestion_reportes.php");
    exit();
}

// Obtener asignaciones del reporte
$stmt = $pdo->prepare("
    SELECT a.*, u.nombre_completo as bombero_nombre 
    FROM asignaciones a 
    LEFT JOIN usuarios u ON a.bombero_id = u.id 
    WHERE a.emergencia_id = ?
    ORDER BY a.fecha_asignacion DESC
");
$stmt->execute([$id]);
$asignaciones = $stmt->fetchAll();

// Procesar acciones (cambiar estado, asignar, etc)
$mensaje = null;
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['cambiar_estado'])) {
        $nuevo_estado = $_POST['estado'];
        $stmt = $pdo->prepare("UPDATE emergencias SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
        $mensaje = "✅ Estado actualizado correctamente";
        
        // Recargar datos
        $stmt = $pdo->prepare("SELECT * FROM emergencias WHERE id = ?");
        $stmt->execute([$id]);
        $reporte = $stmt->fetch();
    }
    
    if(isset($_POST['asignar_unidad'])) {
        $unidad = $_POST['unidad'];
        $bombero_id = $_POST['bombero_id'] ?? null;
        
        $stmt = $pdo->prepare("INSERT INTO asignaciones (emergencia_id, bombero_id, unidad) VALUES (?, ?, ?)");
        $stmt->execute([$id, $bombero_id, $unidad]);
        
        // Actualizar estado de la emergencia a "en_proceso" si estaba pendiente
        if($reporte['estado'] == 'pendiente') {
            $stmt = $pdo->prepare("UPDATE emergencias SET estado = 'en_proceso', fecha_asignacion = NOW() WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        $mensaje = "✅ Unidad asignada correctamente";
        
        // Recargar datos
        $stmt = $pdo->prepare("SELECT * FROM emergencias WHERE id = ?");
        $stmt->execute([$id]);
        $reporte = $stmt->fetch();
        
        $stmt = $pdo->prepare("
            SELECT a.*, u.nombre_completo as bombero_nombre 
            FROM asignaciones a 
            LEFT JOIN usuarios u ON a.bombero_id = u.id 
            WHERE a.emergencia_id = ?
            ORDER BY a.fecha_asignacion DESC
        ");
        $stmt->execute([$id]);
        $asignaciones = $stmt->fetchAll();
    }
}

// Obtener lista de bomberos para asignar
$bomberos = $pdo->query("SELECT id, nombre_completo FROM usuarios WHERE rol = 'bombero' ORDER BY nombre_completo")->fetchAll();

// Colores según tipo de emergencia
$colores = [
    'incendio' => ['bg' => '#fee', 'border' => '#e74c3c', 'icon' => '🔥'],
    'inundacion' => ['bg' => '#e8f4f8', 'border' => '#3498db', 'icon' => '🌊'],
    'accidente' => ['bg' => '#fff3e0', 'border' => '#f39c12', 'icon' => '🚗'],
    'otros' => ['bg' => '#f0f0f0', 'border' => '#95a5a6', 'icon' => '📞']
];
$color = $colores[$reporte['tipo']] ?? $colores['otros'];

$estados = [
    'pendiente' => ['label' => 'Pendiente', 'color' => '#f39c12', 'icon' => '⏳'],
    'en_proceso' => ['label' => 'En Proceso', 'color' => '#3498db', 'icon' => '🔄'],
    'finalizado' => ['label' => 'Finalizado', 'color' => '#27ae60', 'icon' => '✅']
];
$estado_actual = $estados[$reporte['estado']];

$gravedades = [
    'baja' => ['label' => 'Baja', 'color' => '#27ae60', 'icon' => '🟢'],
    'media' => ['label' => 'Media', 'color' => '#f39c12', 'icon' => '🟡'],
    'alta' => ['label' => 'Alta', 'color' => '#e74c3c', 'icon' => '🔴']
];
$gravedad_actual = $gravedades[$reporte['gravedad']];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Reporte #<?php echo $reporte['id']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #7f8c8d;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-bottom: 20px;
        }
        
        .btn-back:hover {
            background: #6c7a7d;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .card-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .info-item label {
            font-size: 12px;
            color: #7f8c8d;
            display: block;
            margin-bottom: 5px;
        }
        
        .info-item .value {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .info-item .value.large {
            font-size: 20px;
            font-weight: bold;
        }
        
        .descripcion-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
        }
        
        .descripcion-box p {
            margin-top: 10px;
            line-height: 1.6;
            color: #555;
        }
        
        #map {
            height: 300px;
            border-radius: 12px;
            margin-top: 15px;
        }
        
        .acciones-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
        }
        
        .acciones-form select, 
        .acciones-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-right: 10px;
        }
        
        .btn-primary {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .mensaje {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .asignacion-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .historico-item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr; }
            .acciones-form select, .acciones-form input { width: 100%; margin-bottom: 10px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 35px;">🚒</div>
            <h1><?php echo SITE_NAME; ?> - Detalle del Reporte</h1>
        </div>
        <a href="../auth/logout.php" style="color: white;"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>
    
    <div class="container">
        <a href="gestion_reportes.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Reportes
        </a>
        
        <?php if($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <!-- Información principal del reporte -->
        <div class="card" style="border-left: 5px solid <?php echo $color['border']; ?>;">
            <div class="card-header">
                <h2>
                    <span><?php echo $color['icon']; ?></span>
                    Reporte #<?php echo $reporte['id']; ?> - Emergencia por <?php echo ucfirst($reporte['tipo']); ?>
                </h2>
                <div>
                    <span class="badge" style="background: <?php echo $estado_actual['color']; ?>; color: white;">
                        <?php echo $estado_actual['icon']; ?> <?php echo $estado_actual['label']; ?>
                    </span>
                    <span class="badge" style="background: <?php echo $gravedad_actual['color']; ?>; color: white; margin-left: 8px;">
                        <?php echo $gravedad_actual['icon']; ?> Gravedad <?php echo $gravedad_actual['label']; ?>
                    </span>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <label><i class="fas fa-calendar"></i> Fecha del Reporte</label>
                    <div class="value large"><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_reporte'])); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-clock"></i> Tiempo transcurrido</label>
                    <div class="value large">
                        <?php 
                        $fecha_reporte = new DateTime($reporte['fecha_reporte']);
                        $ahora = new DateTime();
                        $diferencia = $fecha_reporte->diff($ahora);
                        echo $diferencia->h . ' horas, ' . $diferencia->i . ' minutos';
                        ?>
                    </div>
                </div>
                <?php if($reporte['fecha_asignacion']): ?>
                <div class="info-item">
                    <label><i class="fas fa-truck"></i> Fecha de Asignación</label>
                    <div class="value"><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_asignacion'])); ?></div>
                </div>
                <?php endif; ?>
                <?php if($reporte['fecha_finalizacion']): ?>
                <div class="info-item">
                    <label><i class="fas fa-check-circle"></i> Fecha de Finalización</label>
                    <div class="value"><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_finalizacion'])); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="info-grid" style="margin-top: 15px;">
                <div class="info-item">
                    <label><i class="fas fa-map-marker-alt"></i> Ubicación</label>
                    <div class="value"><?php echo htmlspecialchars($reporte['ubicacion_texto'] ?? 'No especificada'); ?></div>
                </div>
                <?php if($reporte['latitud'] && $reporte['longitud']): ?>
                <div class="info-item">
                    <label><i class="fas fa-location-dot"></i> Coordenadas GPS</label>
                    <div class="value">Lat: <?php echo $reporte['latitud']; ?> | Lng: <?php echo $reporte['longitud']; ?></div>
                </div>
                <?php endif; ?>
                <div class="info-item">
                    <label><i class="fas fa-user"></i> Reportado por</label>
                    <div class="value">
                        <?php if($reporte['usuario_nombre']): ?>
                            <strong><?php echo htmlspecialchars($reporte['usuario_nombre']); ?></strong><br>
                            <small><?php echo htmlspecialchars($reporte['usuario_email'] ?? ''); ?></small><br>
                            <small>📞 <?php echo htmlspecialchars($reporte['usuario_telefono'] ?? 'No registrado'); ?></small>
                        <?php else: ?>
                            <span style="color: #999;">Anónimo / No registrado</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="descripcion-box">
                <label><i class="fas fa-file-alt"></i> Descripción detallada</label>
                <p><?php echo nl2br(htmlspecialchars($reporte['descripcion'] ?? 'Sin descripción')); ?></p>
            </div>
            
            <!-- Mapa -->
            <?php if($reporte['latitud'] && $reporte['longitud']): ?>
            <div style="margin-top: 20px;">
                <label><i class="fas fa-map"></i> Ubicación en el mapa</label>
                <div id="map"></div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Panel de Acciones del Administrador -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-cogs"></i> Acciones del Administrador</h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <!-- Cambiar Estado -->
                <div class="acciones-form">
                    <h3><i class="fas fa-exchange-alt"></i> Cambiar Estado</h3>
                    <form method="POST" style="margin-top: 15px;">
                        <select name="estado" style="width: 100%; margin-bottom: 10px;">
                            <option value="pendiente" <?php echo $reporte['estado'] == 'pendiente' ? 'selected' : ''; ?>>⏳ Pendiente</option>
                            <option value="en_proceso" <?php echo $reporte['estado'] == 'en_proceso' ? 'selected' : ''; ?>>🔄 En Proceso</option>
                            <option value="finalizado" <?php echo $reporte['estado'] == 'finalizado' ? 'selected' : ''; ?>>✅ Finalizado</option>
                        </select>
                        <button type="submit" name="cambiar_estado" class="btn-primary">Actualizar Estado</button>
                    </form>
                </div>
                
                <!-- Asignar Unidad -->
                <div class="acciones-form">
                    <h3><i class="fas fa-truck-fast"></i> Asignar Unidad</h3>
                    <form method="POST" style="margin-top: 15px;">
                        <input type="text" name="unidad" placeholder="Número de unidad (ej: U-01)" style="width: 100%; margin-bottom: 10px;">
                        <select name="bombero_id" style="width: 100%; margin-bottom: 10px;">
                            <option value="">Seleccionar bombero (opcional)</option>
                            <?php foreach($bomberos as $b): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="asignar_unidad" class="btn-success">Asignar Unidad</button>
                    </form>
                </div>
                
                <!-- Eliminar Reporte -->
                <div class="acciones-form">
                    <h3><i class="fas fa-trash-alt"></i> Eliminar Reporte</h3>
                    <p style="font-size: 13px; color: #e74c3c; margin: 10px 0;">⚠️ Esta acción no se puede deshacer.</p>
                    <a href="gestion_reportes.php?eliminar=<?php echo $reporte['id']; ?>" 
                       class="btn-warning" 
                       style="display: inline-block; text-decoration: none;"
                       onclick="return confirm('¿Estás seguro de eliminar este reporte como falso?')">
                        <i class="fas fa-trash"></i> Eliminar Reporte
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Asignaciones realizadas -->
        <?php if(count($asignaciones) > 0): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-clipboard-list"></i> Historial de Asignaciones</h2>
            </div>
            <?php foreach($asignaciones as $a): ?>
            <div class="asignacion-item">
                <div style="display: flex; justify-content: space-between; flex-wrap: wrap;">
                    <div>
                        <strong><i class="fas fa-truck"></i> Unidad:</strong> <?php echo htmlspecialchars($a['unidad']); ?>
                    </div>
                    <div>
                        <strong><i class="fas fa-user"></i> Bombero:</strong> <?php echo htmlspecialchars($a['bombero_nombre'] ?? 'No asignado'); ?>
                    </div>
                    <div>
                        <strong><i class="fas fa-calendar"></i> Asignado:</strong> <?php echo date('d/m/Y H:i', strtotime($a['fecha_asignacion'])); ?>
                    </div>
                    <div>
                        <strong><i class="fas fa-flag-checkered"></i> Estado:</strong> 
                        <?php 
                        $estados_asig = [
                            'asignado' => '📌 Asignado',
                            'en_camino' => '🚗 En camino',
                            'en_sitio' => '📍 En sitio',
                            'completado' => '✅ Completado'
                        ];
                        echo $estados_asig[$a['estado_asignacion']] ?? $a['estado_asignacion'];
                        ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Línea de tiempo del reporte -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-chart-line"></i> Línea de Tiempo</h2>
            </div>
            <div class="historico-item">
                <span><i class="fas fa-plus-circle" style="color: #27ae60;"></i> Reporte creado</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_reporte'])); ?></span>
            </div>
            <?php if($reporte['fecha_asignacion']): ?>
            <div class="historico-item">
                <span><i class="fas fa-truck" style="color: #3498db;"></i> Emergencia asignada</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_asignacion'])); ?></span>
            </div>
            <?php endif; ?>
            <?php if($reporte['fecha_finalizacion']): ?>
            <div class="historico-item">
                <span><i class="fas fa-check-circle" style="color: #27ae60;"></i> Emergencia finalizada</span>
                <span><?php echo date('d/m/Y H:i:s', strtotime($reporte['fecha_finalizacion'])); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if($reporte['latitud'] && $reporte['longitud']): ?>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([<?php echo $reporte['latitud']; ?>, <?php echo $reporte['longitud']; ?>], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        var markerColor = '';
        <?php if($reporte['tipo'] == 'incendio'): ?>
            markerColor = 'red';
        <?php elseif($reporte['tipo'] == 'inundacion'): ?>
            markerColor = 'blue';
        <?php elseif($reporte['tipo'] == 'accidente'): ?>
            markerColor = 'orange';
        <?php else: ?>
            markerColor = 'gray';
        <?php endif; ?>
        
        var marker = L.circleMarker([<?php echo $reporte['latitud']; ?>, <?php echo $reporte['longitud']; ?>], {
            color: markerColor,
            radius: 15,
            fillOpacity: 0.7,
            weight: 3
        }).addTo(map);
        
        marker.bindPopup(`
            <b><?php echo ucfirst($reporte['tipo']); ?></b><br>
            <b>Estado:</b> <?php echo $reporte['estado']; ?><br>
            <b>Gravedad:</b> <?php echo $reporte['gravedad']; ?><br>
            <?php echo addslashes(htmlspecialchars(substr($reporte['descripcion'] ?? '', 0, 100))); ?>
        `).openPopup();
    </script>
    <?php endif; ?>
</body>
</html>