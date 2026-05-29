<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('administrador');

// Procesar eliminación de reporte
$mensaje = null;
if(isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM emergencias WHERE id = ?");
    $stmt->execute([$id]);
    $mensaje = "✅ Reporte eliminado correctamente (Reporte falso eliminado)";
}

// Obtener todos los reportes
$reportes = $pdo->query("SELECT e.*, u.nombre_completo as usuario_nombre FROM emergencias e LEFT JOIN usuarios u ON e.usuario_id = u.id ORDER BY e.fecha_reporte DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Reportes - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .dashboard-wrapper {
            display: flex;
            min-height: calc(100vh - 80px);
        }
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
        .main-content {
            flex: 1;
            padding: 25px;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .card h2 {
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
            padding-left: 15px;
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
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .badge-pendiente { background: #f39c12; color: white; }
        .badge-en_proceso { background: #3498db; color: white; }
        .badge-finalizado { background: #27ae60; color: white; }
        .badge-incendio { background: #e74c3c; color: white; }
        .badge-inundacion { background: #3498db; color: white; }
        .badge-accidente { background: #f39c12; color: white; }
        .btn-view { background: #3498db; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        .btn-delete { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 5px; text-decoration: none; }
        .mensaje {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filter-bar {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .filter-bar input, .filter-bar select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 35px;">🚒</div>
            <h1><?php echo SITE_NAME; ?> - Gestión de Reportes</h1>
        </div>
        <a href="../auth/logout.php" style="color: white; text-decoration: none;"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>
    
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="gestion_usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a></li>
                <li class="active"><a href="gestion_reportes.php"><i class="fas fa-exclamation-triangle"></i> Gestionar Reportes</a></li>
                <li><a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas Avanzadas</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="card">
                <h2><i class="fas fa-list"></i> Todos los Reportes de Emergencia</h2>
                
                <?php if($mensaje): ?>
                    <div class="mensaje"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <div class="filter-bar">
                    <input type="text" id="buscar" placeholder="Buscar por ubicación..." onkeyup="filtrarTabla()">
                    <select id="filtroTipo" onchange="filtrarTabla()">
                        <option value="">Todos los tipos</option>
                        <option value="incendio">Incendio</option>
                        <option value="inundacion">Inundación</option>
                        <option value="accidente">Accidente</option>
                    </select>
                    <select id="filtroEstado" onchange="filtrarTabla()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="finalizado">Finalizado</option>
                    </select>
                </div>
                
                <div style="overflow-x: auto;">
                    <table id="tablaReportes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Ubicación</th>
                                <th>Gravedad</th>
                                <th>Estado</th>
                                <th>Reportado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($reportes as $r): ?>
                            <tr>
                                <td><?php echo $r['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($r['fecha_reporte'])); ?></td>
                                <td><span class="badge badge-<?php echo $r['tipo']; ?>"><?php echo ucfirst($r['tipo']); ?></span></td>
                                <td><?php echo htmlspecialchars(substr($r['ubicacion_texto'] ?? '', 0, 50)); ?></td>
                                <td>
                                    <?php if($r['gravedad'] == 'alta'): ?>
                                        <span style="color:#e74c3c;">🔴 Alta</span>
                                    <?php elseif($r['gravedad'] == 'media'): ?>
                                        <span style="color:#f39c12;">🟡 Media</span>
                                    <?php else: ?>
                                        <span style="color:#27ae60;">🟢 Baja</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-<?php echo $r['estado']; ?>"><?php echo $r['estado']; ?></span></td>
                                <td><?php echo htmlspecialchars($r['usuario_nombre'] ?? 'Anónimo'); ?></td>
                                <td>
                                    <a href="ver_reporte.php?id=<?php echo $r['id']; ?>" class="btn-view"><i class="fas fa-eye"></i> Ver</a>
                                    <a href="?eliminar=<?php echo $r['id']; ?>" class="btn-delete" onclick="return confirm('¿Eliminar este reporte como falso?')"><i class="fas fa-trash"></i> Eliminar</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function filtrarTabla() {
            var input = document.getElementById("buscar");
            var filter = input.value.toLowerCase();
            var tipo = document.getElementById("filtroTipo").value;
            var estado = document.getElementById("filtroEstado").value;
            var table = document.getElementById("tablaReportes");
            var tr = table.getElementsByTagName("tr");
            
            for (var i = 1; i < tr.length; i++) {
                var tdUbicacion = tr[i].getElementsByTagName("td")[3];
                var tdTipo = tr[i].getElementsByTagName("td")[2];
                var tdEstado = tr[i].getElementsByTagName("td")[5];
                
                if (tdUbicacion && tdTipo && tdEstado) {
                    var txtUbicacion = tdUbicacion.textContent || tdUbicacion.innerText;
                    var txtTipo = tdTipo.textContent || tdTipo.innerText;
                    var txtEstado = tdEstado.textContent || tdEstado.innerText;
                    
                    var matchUbicacion = txtUbicacion.toLowerCase().indexOf(filter) > -1;
                    var matchTipo = tipo === "" || txtTipo.toLowerCase() === tipo;
                    var matchEstado = estado === "" || txtEstado.toLowerCase() === estado;
                    
                    if (matchUbicacion && matchTipo && matchEstado) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>