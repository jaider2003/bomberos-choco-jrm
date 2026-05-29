<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('administrador');

// ============================================
// ESTADÍSTICAS GENERALES
// ============================================

// Totales principales
$total_emergencias = $pdo->query("SELECT COUNT(*) as total FROM emergencias")->fetch();
$total_usuarios = $pdo->query("SELECT COUNT(*) as total FROM usuarios")->fetch();
$total_bomberos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'bombero'")->fetch();
$total_ciudadanos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'ciudadano'")->fetch();

// Emergencias por estado
$pendientes = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE estado = 'pendiente'")->fetch();
$en_proceso = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE estado = 'en_proceso'")->fetch();
$finalizados = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE estado = 'finalizado'")->fetch();

// Emergencias hoy
$hoy = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE DATE(fecha_reporte) = CURDATE()")->fetch();
$semana = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE WEEK(fecha_reporte) = WEEK(CURDATE())")->fetch();
$mes = $pdo->query("SELECT COUNT(*) as total FROM emergencias WHERE MONTH(fecha_reporte) = MONTH(CURDATE())")->fetch();

// Emergencias por tipo
$por_tipo = $pdo->query("SELECT tipo, COUNT(*) as cantidad FROM emergencias GROUP BY tipo")->fetchAll();

// Tiempo promedio de respuesta
$tiempo_promedio = $pdo->query("SELECT AVG(TIMESTAMPDIFF(MINUTE, fecha_reporte, fecha_asignacion)) as promedio FROM emergencias WHERE fecha_asignacion IS NOT NULL")->fetch();

// Emergencias por mes (últimos 6 meses)
$por_mes = $pdo->query("
    SELECT DATE_FORMAT(fecha_reporte, '%b') as mes, COUNT(*) as cantidad 
    FROM emergencias 
    WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    GROUP BY MONTH(fecha_reporte) 
    ORDER BY MONTH(fecha_reporte) ASC
")->fetchAll();

// Top 5 ciudadanos que más reportan
$top_reportantes = $pdo->query("
    SELECT u.nombre_completo, COUNT(e.id) as total 
    FROM usuarios u 
    JOIN emergencias e ON u.id = e.usuario_id 
    WHERE u.rol = 'ciudadano'
    GROUP BY u.id 
    ORDER BY total DESC 
    LIMIT 5
")->fetchAll();

// Últimas 10 emergencias
$ultimas = $pdo->query("
    SELECT e.*, u.nombre_completo as usuario_nombre 
    FROM emergencias e 
    LEFT JOIN usuarios u ON e.usuario_id = u.id 
    ORDER BY e.fecha_reporte DESC 
    LIMIT 10
")->fetchAll();

// Tasa de resolución (finalizados / total * 100)
$tasa_resolucion = 0;
if($total_emergencias['total'] > 0) {
    $tasa_resolucion = round(($finalizados['total'] / $total_emergencias['total']) * 100, 1);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        /* Header Moderno */
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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
            font-size: 13px;
            color: #e74c3c;
            display: block;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-badge {
            background: rgba(255,255,255,0.15);
            padding: 8px 18px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        /* Contenedor */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
        }
        
        /* Navegación */
        .nav-menu {
            background: white;
            padding: 12px 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-menu a {
            padding: 10px 22px;
            background: #f0f2f5;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .nav-menu a:hover, .nav-menu a.active {
            background: #e74c3c;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .stat-card.primary::before { background: #e74c3c; }
        .stat-card.success::before { background: #27ae60; }
        .stat-card.warning::before { background: #f39c12; }
        .stat-card.info::before { background: #3498db; }
        
        .stat-icon {
            font-size: 35px;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .stat-trend {
            font-size: 12px;
            margin-top: 8px;
        }
        
        .trend-up { color: #27ae60; }
        .trend-down { color: #e74c3c; }
        
        /* Grid de dos columnas */
        .grid-2cols {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .grid-3cols {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 25px;
        }
        
        /* Cards */
        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .chart-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .chart-card h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            border-left: 4px solid #e74c3c;
            padding-left: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Tabla */
        .table-container {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .table-container h3 {
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            padding-left: 15px;
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
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-incendio { background: #e74c3c; color: white; }
        .badge-inundacion { background: #3498db; color: white; }
        .badge-accidente { background: #f39c12; color: white; }
        .badge-otros { background: #95a5a6; color: white; }
        .badge-pendiente { background: #f39c12; color: white; }
        .badge-en_proceso { background: #3498db; color: white; }
        .badge-finalizado { background: #27ae60; color: white; }
        
        /* Ranking */
        .ranking-list {
            list-style: none;
        }
        
        .ranking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .ranking-position {
            width: 30px;
            height: 30px;
            background: #f0f2f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .position-1 { background: #f1c40f; color: #2c3e50; }
        .position-2 { background: #bdc3c7; color: #2c3e50; }
        .position-3 { background: #cd6133; color: white; }
        
        /* Responsive */
        @media (max-width: 1000px) {
            .grid-2cols, .grid-3cols {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .logo-area {
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .nav-menu {
                justify-content: center;
            }
            
            .nav-menu a {
                padding: 8px 15px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-area">
            <img src="../assets/images/logo.png" alt="Logo Bomberos Chocó JRM" class="logo-img" onerror="this.style.display='none'; this.nextSibling.style.display='flex';">
            <h1><?php echo SITE_NAME; ?><span>Panel de Administración</span></h1>
        </div>
        <div class="user-info">
            <div class="user-badge">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            </div>
            <a href="../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>
    
    <div class="container">
        <!-- Menú de navegación -->
        <div class="nav-menu">
            <a href="admin_dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="gestion_usuarios.php">
                <i class="fas fa-users"></i> Gestionar Usuarios
            </a>
            <a href="gestion_reportes.php">
                <i class="fas fa-exclamation-triangle"></i> Gestionar Reportes
            </a>
            <a href="estadisticas.php">
                <i class="fas fa-chart-line"></i> Estadísticas Avanzadas
            </a>
        </div>
        
        <!-- Tarjetas de estadísticas -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">📋</div>
                <div class="stat-number"><?php echo $total_emergencias['total']; ?></div>
                <div class="stat-label">Total Emergencias</div>
                <div class="stat-trend">
                    <i class="fas fa-calendar"></i> <?php echo $mes['total']; ?> este mes
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">⏳</div>
                <div class="stat-number"><?php echo $pendientes['total']; ?></div>
                <div class="stat-label">Pendientes</div>
                <div class="stat-trend">
                    <i class="fas fa-clock"></i> Por atender
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">🔄</div>
                <div class="stat-number"><?php echo $en_proceso['total']; ?></div>
                <div class="stat-label">En Proceso</div>
                <div class="stat-trend">
                    <i class="fas fa-truck"></i> En atención
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $finalizados['total']; ?></div>
                <div class="stat-label">Finalizados</div>
                <div class="stat-trend">
                    <i class="fas fa-chart-line"></i> <?php echo $tasa_resolucion; ?>% resueltos
                </div>
            </div>
            <div class="stat-card primary">
                <div class="stat-icon">👥</div>
                <div class="stat-number"><?php echo $total_usuarios['total']; ?></div>
                <div class="stat-label">Usuarios</div>
                <div class="stat-trend">
                    <i class="fas fa-user-tie"></i> <?php echo $total_bomberos['total']; ?> bomberos
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">⏱️</div>
                <div class="stat-number"><?php echo round($tiempo_promedio['promedio'] ?? 0); ?> min</div>
                <div class="stat-label">Tiempo Promedio</div>
                <div class="stat-trend">
                    <i class="fas fa-gauge-high"></i> Desde reporte a asignación
                </div>
            </div>
        </div>
        
        <!-- Gráficas principales -->
        <div class="grid-2cols">
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Emergencias por Tipo</h3>
                <canvas id="graficoTipo" height="250"></canvas>
                <div style="margin-top: 15px; text-align: center;">
                    <?php foreach($por_tipo as $t): ?>
                        <span style="display: inline-block; margin: 0 10px;">
                            <span style="background: <?php echo $t['tipo'] == 'incendio' ? '#e74c3c' : ($t['tipo'] == 'inundacion' ? '#3498db' : ($t['tipo'] == 'accidente' ? '#f39c12' : '#95a5a6')); ?>; width: 12px; height: 12px; display: inline-block; border-radius: 2px;"></span>
                            <?php echo ucfirst($t['tipo']); ?> (<?php echo $t['cantidad']; ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> Emergencias por Estado</h3>
                <canvas id="graficoEstado" height="250"></canvas>
                <div style="margin-top: 15px; text-align: center;">
                    <span style="display: inline-block; margin: 0 10px;"><span style="background: #f39c12; width: 12px; height: 12px; display: inline-block; border-radius: 2px;"></span> Pendiente (<?php echo $pendientes['total']; ?>)</span>
                    <span style="display: inline-block; margin: 0 10px;"><span style="background: #3498db; width: 12px; height: 12px; display: inline-block; border-radius: 2px;"></span> En Proceso (<?php echo $en_proceso['total']; ?>)</span>
                    <span style="display: inline-block; margin: 0 10px;"><span style="background: #27ae60; width: 12px; height: 12px; display: inline-block; border-radius: 2px;"></span> Finalizado (<?php echo $finalizados['total']; ?>)</span>
                </div>
            </div>
        </div>
        
        <!-- Segunda fila de gráficas -->
        <div class="grid-3cols">
            <div class="chart-card">
                <h3><i class="fas fa-chart-column"></i> Emergencias por Mes</h3>
                <canvas id="graficoMes" height="200"></canvas>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-trophy"></i> Top 5 Reportantes</h3>
                <?php if(count($top_reportantes) > 0): ?>
                    <ul class="ranking-list">
                        <?php $pos = 1; foreach($top_reportantes as $r): ?>
                        <li class="ranking-item">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div class="ranking-position <?php echo 'position-' . $pos; ?>"><?php echo $pos; ?></div>
                                <span><?php echo htmlspecialchars($r['nombre_completo']); ?></span>
                            </div>
                            <span class="badge" style="background:#e74c3c; color:white;"><?php echo $r['total']; ?> reportes</span>
                        </li>
                        <?php $pos++; endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 30px;">No hay datos suficientes</p>
                <?php endif; ?>
            </div>
            
            <div class="chart-card">
                <h3><i class="fas fa-calendar-alt"></i> Actividad Reciente</h3>
                <div style="text-align: center; padding: 20px;">
                    <div style="font-size: 48px; font-weight: bold; color: #e74c3c;"><?php echo $hoy['total']; ?></div>
                    <div>Reportes hoy</div>
                    <hr style="margin: 15px 0;">
                    <div style="display: flex; justify-content: space-around;">
                        <div>
                            <div style="font-size: 28px; font-weight: bold; color: #3498db;"><?php echo $semana['total']; ?></div>
                            <div style="font-size: 12px; color: #666;">Esta semana</div>
                        </div>
                        <div>
                            <div style="font-size: 28px; font-weight: bold; color: #27ae60;"><?php echo $mes['total']; ?></div>
                            <div style="font-size: 12px; color: #666;">Este mes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Últimas emergencias -->
        <div class="table-container">
            <h3><i class="fas fa-history"></i> Últimas Emergencias Reportadas</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Ubicación</th>
                        <th>Gravedad</th>
                        <th>Estado</th>
                        <th>Reportado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ultimas as $e): ?>
                    <tr>
                        <td>#<?php echo $e['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($e['fecha_reporte'])); ?></td>
                        <td><span class="badge badge-<?php echo $e['tipo']; ?>"><?php echo ucfirst($e['tipo']); ?></span></td>
                        <td><?php echo htmlspecialchars(substr($e['ubicacion_texto'] ?? '', 0, 35)); ?></td>
                        <td>
                            <?php if($e['gravedad'] == 'alta'): ?>🔴 Alta
                            <?php elseif($e['gravedad'] == 'media'): ?>🟡 Media
                            <?php else: ?>🟢 Baja<?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?php echo $e['estado']; ?>"><?php echo ucfirst($e['estado']); ?></span></td>
                        <td><?php echo htmlspecialchars($e['usuario_nombre'] ?? 'Anónimo'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($ultimas) == 0): ?>
                    <tr><td colspan="7" style="text-align: center;">No hay emergencias registradas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico por tipo
        const ctxTipo = document.getElementById('graficoTipo').getContext('2d');
        const datosTipo = <?php echo json_encode($por_tipo); ?>;
        new Chart(ctxTipo, {
            type: 'doughnut',
            data: {
                labels: datosTipo.map(d => d.tipo),
                datasets: [{
                    data: datosTipo.map(d => d.cantidad),
                    backgroundColor: datosTipo.map(d => 
                        d.tipo == 'incendio' ? '#e74c3c' : 
                        (d.tipo == 'inundacion' ? '#3498db' : 
                        (d.tipo == 'accidente' ? '#f39c12' : '#95a5a6'))
                    ),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
        
        // Gráfico por estado
        const ctxEstado = document.getElementById('graficoEstado').getContext('2d');
        new Chart(ctxEstado, {
            type: 'bar',
            data: {
                labels: ['Pendiente', 'En Proceso', 'Finalizado'],
                datasets: [{
                    label: 'Cantidad',
                    data: [<?php echo $pendientes['total']; ?>, <?php echo $en_proceso['total']; ?>, <?php echo $finalizados['total']; ?>],
                    backgroundColor: ['#f39c12', '#3498db', '#27ae60'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } }
            }
        });
        
        // Gráfico por mes
        const ctxMes = document.getElementById('graficoMes').getContext('2d');
        const datosMes = <?php echo json_encode($por_mes); ?>;
        new Chart(ctxMes, {
            type: 'line',
            data: {
                labels: datosMes.map(d => d.mes),
                datasets: [{
                    label: 'Emergencias',
                    data: datosMes.map(d => d.cantidad),
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#e74c3c',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true
            }
        });
    </script>
</body>
</html>