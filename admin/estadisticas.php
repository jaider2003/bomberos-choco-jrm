<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('administrador');

// Estadísticas generales
$total_emergencias = $pdo->query("SELECT COUNT(*) as total FROM emergencias")->fetch();
$total_usuarios = $pdo->query("SELECT COUNT(*) as total FROM usuarios")->fetch();
$total_bomberos = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'bombero'")->fetch();

// Emergencias por tipo
$por_tipo = $pdo->query("SELECT tipo, COUNT(*) as cantidad FROM emergencias GROUP BY tipo")->fetchAll();

// Emergencias por mes (últimos 12 meses)
$por_mes = $pdo->query("SELECT DATE_FORMAT(fecha_reporte, '%Y-%m') as mes, COUNT(*) as cantidad FROM emergencias WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL 12 MONTH) GROUP BY mes ORDER BY mes ASC")->fetchAll();

// Emergencias por gravedad
$por_gravedad = $pdo->query("SELECT gravedad, COUNT(*) as cantidad, ROUND(COUNT(*) * 100 / (SELECT COUNT(*) FROM emergencias), 2) as porcentaje FROM emergencias GROUP BY gravedad")->fetchAll();

// Tiempo de respuesta promedio por tipo
$tiempo_por_tipo = $pdo->query("SELECT tipo, AVG(TIMESTAMPDIFF(MINUTE, fecha_reporte, fecha_asignacion)) as promedio FROM emergencias WHERE fecha_asignacion IS NOT NULL GROUP BY tipo")->fetchAll();

// Reportes por día (últimos 30 días)
$por_dia = $pdo->query("SELECT DATE(fecha_reporte) as dia, COUNT(*) as cantidad FROM emergencias WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY dia ORDER BY dia ASC")->fetchAll();

// Top usuarios que más reportan
$top_reportantes = $pdo->query("SELECT u.nombre_completo, COUNT(e.id) as total FROM usuarios u JOIN emergencias e ON u.id = e.usuario_id GROUP BY u.id ORDER BY total DESC LIMIT 5")->fetchAll();

// Tasa de atención (emergencias finalizadas / total)
$tasa_atencion = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM emergencias WHERE estado = 'finalizado') * 100.0 / COUNT(*) as tasa 
    FROM emergencias")->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas Avanzadas - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dashboard-wrapper { display: flex; min-height: calc(100vh - 80px); }
        .sidebar {
            width: 260px;
            background: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }
        .sidebar-menu li {
            list-style: none;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: #333;
            text-decoration: none;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            background: #e74c3c;
            color: white;
        }
        .main-content { flex: 1; padding: 25px; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #e74c3c; }
        .stat-card .label { color: #7f8c8d; }
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .chart-card h3 {
            margin-bottom: 15px;
            border-left: 4px solid #e74c3c;
            padding-left: 15px;
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        @media (max-width: 768px) {
            .charts-row { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 35px;">🚒</div>
            <h1><?php echo SITE_NAME; ?> - Estadísticas Avanzadas</h1>
        </div>
        <a href="../auth/logout.php" style="color: white;"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>
    
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="gestion_usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a></li>
                <li><a href="gestion_reportes.php"><i class="fas fa-exclamation-triangle"></i> Gestionar Reportes</a></li>
                <li class="active"><a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas Avanzadas</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?php echo $total_emergencias['total']; ?></div>
                    <div class="label">Total Emergencias</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $total_usuarios['total']; ?></div>
                    <div class="label">Usuarios</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $total_bomberos['total']; ?></div>
                    <div class="label">Bomberos</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo round($tasa_atencion['tasa'] ?? 0, 1); ?>%</div>
                    <div class="label">Tasa de Atención</div>
                </div>
            </div>
            
            <div class="charts-row">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Emergencias por Tipo</h3>
                    <canvas id="graficoTipo" height="250"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-chart-line"></i> Emergencias por Mes</h3>
                    <canvas id="graficoMes" height="250"></canvas>
                </div>
            </div>
            
            <div class="charts-row">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Emergencias por Gravedad</h3>
                    <canvas id="graficoGravedad" height="250"></canvas>
                </div>
                <div class="chart-card">
                    <h3><i class="fas fa-clock"></i> Tiempo Respuesta por Tipo</h3>
                    <canvas id="graficoTiempo" height="250"></canvas>
                </div>
            </div>
            
            <div class="charts-row">
                <div class="chart-card">
                    <h3><i class="fas fa-calendar-day"></i> Reportes Últimos 30 Días</h3>
                    <canvas id="graficoDia" height="200"></canvas>
                </div>
                <div class="table-container">
                    <h3><i class="fas fa-trophy"></i> Top 5 Usuarios que más Reportan</h3>
                    <table>
                        <thead><tr><th>Usuario</th><th>Reportes</th></tr></thead>
                        <tbody>
                            <?php foreach($top_reportantes as $tr): ?>
                            <tr><td><?php echo htmlspecialchars($tr['nombre_completo']); ?></td><td><?php echo $tr['total']; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Gráfico por tipo
        new Chart(document.getElementById('graficoTipo'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($por_tipo, 'tipo')); ?>,
                datasets: [{
                    label: 'Cantidad',
                    data: <?php echo json_encode(array_column($por_tipo, 'cantidad')); ?>,
                    backgroundColor: ['#e74c3c', '#3498db', '#f39c12']
                }]
            }
        });
        
        // Gráfico por mes
        new Chart(document.getElementById('graficoMes'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($por_mes, 'mes')); ?>,
                datasets: [{
                    label: 'Emergencias',
                    data: <?php echo json_encode(array_column($por_mes, 'cantidad')); ?>,
                    borderColor: '#e74c3c',
                    fill: true
                }]
            }
        });
        
        // Gráfico por gravedad
        new Chart(document.getElementById('graficoGravedad'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($por_gravedad, 'gravedad')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($por_gravedad, 'cantidad')); ?>,
                    backgroundColor: ['#e74c3c', '#f39c12', '#27ae60']
                }]
            }
        });
        
        // Gráfico tiempo respuesta
        new Chart(document.getElementById('graficoTiempo'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($tiempo_por_tipo, 'tipo')); ?>,
                datasets: [{
                    label: 'Minutos promedio',
                    data: <?php echo json_encode(array_column($tiempo_por_tipo, 'promedio')); ?>,
                    backgroundColor: '#3498db'
                }]
            }
        });
        
        // Gráfico por día
        new Chart(document.getElementById('graficoDia'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($por_dia, 'dia')); ?>,
                datasets: [{
                    label: 'Reportes por día',
                    data: <?php echo json_encode(array_column($por_dia, 'cantidad')); ?>,
                    backgroundColor: '#27ae60'
                }]
            }
        });
    </script>
</body>
</html>