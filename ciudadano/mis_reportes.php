<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('ciudadano');

$usuario_id = $_SESSION['usuario_id'];

// ============================================
// FILTROS Y BÚSQUEDA
// ============================================
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// Construir consulta con filtros
$sql = "SELECT * FROM emergencias WHERE usuario_id = ?";
$params = [$usuario_id];

if($filtro_tipo && $filtro_tipo != '') {
    $sql .= " AND tipo = ?";
    $params[] = $filtro_tipo;
}

if($filtro_estado && $filtro_estado != '') {
    $sql .= " AND estado = ?";
    $params[] = $filtro_estado;
}

if($busqueda != '') {
    $sql .= " AND (ubicacion_texto LIKE ? OR descripcion LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

// Obtener total de registros para paginación
$sql_count = str_replace("SELECT *", "SELECT COUNT(*) as total", $sql);
$stmt = $pdo->prepare($sql_count);
$stmt->execute($params);
$total_registros = $stmt->fetch()['total'];
$total_paginas = ceil($total_registros / $por_pagina);

// Obtener registros de la página actual
$sql .= " ORDER BY fecha_reporte DESC LIMIT $offset, $por_pagina";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$mis_reportes = $stmt->fetchAll();

// ============================================
// ESTADÍSTICAS PARA EL RESUMEN
// ============================================
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emergencias WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$total_reportes = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emergencias WHERE usuario_id = ? AND estado = 'pendiente'");
$stmt->execute([$usuario_id]);
$pendientes = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emergencias WHERE usuario_id = ? AND estado = 'en_proceso'");
$stmt->execute([$usuario_id]);
$en_proceso = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM emergencias WHERE usuario_id = ? AND estado = 'finalizado'");
$stmt->execute([$usuario_id]);
$finalizados = $stmt->fetch();

// Obtener tipos de emergencia para el filtro
$tipos = $pdo->query("SELECT DISTINCT tipo FROM emergencias")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reportes - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        /* Header */
        .header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            padding: 15px 25px;
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
            height: 50px;
            width: auto;
            border-radius: 12px;
        }

        .logo-area h1 {
            font-size: 22px;
            color: #2c3e50;
        }

        .logo-area h1 span {
            color: #e74c3c;
            font-size: 14px;
            display: block;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f0f0f0;
            padding: 8px 18px;
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
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .logout-btn {
            color: #e74c3c;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 25px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #e74c3c;
            color: white;
        }

        .btn-back {
            background: #7f8c8d;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .btn-back:hover {
            background: #6c7a7d;
        }

        /* Contenedor */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
        }

        /* Tarjetas de estadísticas */
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
            transition: transform 0.3s;
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card.active {
            border: 2px solid #e74c3c;
            background: #fff5f5;
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            margin-top: 5px;
        }

        /* Filtros */
        .filters-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .filters-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 150px;
        }

        .filter-group label {
            display: block;
            font-size: 12px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #e74c3c;
        }

        .btn-filter {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-filter:hover {
            background: #c0392b;
        }

        .btn-clear {
            background: #95a5a6;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            cursor: pointer;
        }

        /* Tabla de reportes */
        .table-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-header h2 {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
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
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-incendio { background: #e74c3c; color: white; }
        .badge-inundacion { background: #3498db; color: white; }
        .badge-accidente { background: #f39c12; color: white; }
        .badge-otros { background: #95a5a6; color: white; }

        .badge-pendiente { background: #f39c12; color: white; }
        .badge-en_proceso { background: #3498db; color: white; }
        .badge-finalizado { background: #27ae60; color: white; }

        .btn-view {
            background: #3498db;
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-view:hover {
            background: #2980b9;
        }

        /* Paginación */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .pagination a, .pagination span {
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            background: #f0f0f0;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #e74c3c;
            color: white;
        }

        .pagination .active {
            background: #e74c3c;
            color: white;
        }

        /* Sin resultados */
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #999;
        }

        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filters-row {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            th, td {
                padding: 10px;
                font-size: 13px;
            }
            
            .logo-img {
                height: 40px;
            }
            
            .logo-area h1 {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-area">
            <img src="../assets/images/logo.png" alt="Logo" class="logo-img" onerror="this.style.display='none'">
            <h1><?php echo SITE_NAME; ?><span>Mis Reportes</span></h1>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['nombre'], 0, 1)); ?>
                </div>
                <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            </div>
            <a href="dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>

    <div class="container">
        <!-- Tarjetas de resumen -->
        <div class="stats-grid">
            <div class="stat-card <?php echo !$filtro_estado ? 'active' : ''; ?>" onclick="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['estado' => '', 'pagina' => 1])); ?>'">
                <div class="stat-number"><?php echo $total_reportes['total']; ?></div>
                <div class="stat-label">📋 Total Reportes</div>
            </div>
            <div class="stat-card <?php echo $filtro_estado == 'pendiente' ? 'active' : ''; ?>" onclick="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['estado' => 'pendiente', 'pagina' => 1])); ?>'">
                <div class="stat-number"><?php echo $pendientes['total']; ?></div>
                <div class="stat-label">⏳ Pendientes</div>
            </div>
            <div class="stat-card <?php echo $filtro_estado == 'en_proceso' ? 'active' : ''; ?>" onclick="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['estado' => 'en_proceso', 'pagina' => 1])); ?>'">
                <div class="stat-number"><?php echo $en_proceso['total']; ?></div>
                <div class="stat-label">🔄 En Proceso</div>
            </div>
            <div class="stat-card <?php echo $filtro_estado == 'finalizado' ? 'active' : ''; ?>" onclick="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['estado' => 'finalizado', 'pagina' => 1])); ?>'">
                <div class="stat-number"><?php echo $finalizados['total']; ?></div>
                <div class="stat-label">✅ Finalizados</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-card">
            <form method="GET" action="">
                <div class="filters-row">
                    <div class="filter-group">
                        <label><i class="fas fa-filter"></i> Tipo de Emergencia</label>
                        <select name="tipo">
                            <option value="">Todos</option>
                            <option value="incendio" <?php echo $filtro_tipo == 'incendio' ? 'selected' : ''; ?>>🔥 Incendio</option>
                            <option value="inundacion" <?php echo $filtro_tipo == 'inundacion' ? 'selected' : ''; ?>>🌊 Inundación</option>
                            <option value="accidente" <?php echo $filtro_tipo == 'accidente' ? 'selected' : ''; ?>>🚗 Accidente</option>
                            <option value="otros" <?php echo $filtro_tipo == 'otros' ? 'selected' : ''; ?>>📞 Otros</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-tag"></i> Estado</label>
                        <select name="estado">
                            <option value="">Todos</option>
                            <option value="pendiente" <?php echo $filtro_estado == 'pendiente' ? 'selected' : ''; ?>>⏳ Pendiente</option>
                            <option value="en_proceso" <?php echo $filtro_estado == 'en_proceso' ? 'selected' : ''; ?>>🔄 En Proceso</option>
                            <option value="finalizado" <?php echo $filtro_estado == 'finalizado' ? 'selected' : ''; ?>>✅ Finalizado</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-search"></i> Buscar</label>
                        <input type="text" name="buscar" placeholder="Ubicación o descripción..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="mis_reportes.php" class="btn-clear"><i class="fas fa-eraser"></i> Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de reportes -->
        <div class="table-card">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Mis Reportes de Emergencia</h2>
                <a href="reportar.php" class="btn-view" style="background: #e74c3c;"><i class="fas fa-plus-circle"></i> Nuevo Reporte</a>
            </div>

            <?php if(count($mis_reportes) > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Ubicación</th>
                                <th>Gravedad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($mis_reportes as $reporte): ?>
                            <tr>
                                <td>#<?php echo $reporte['id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($reporte['fecha_reporte'])); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $reporte['tipo']; ?>">
                                        <?php 
                                        $iconos = ['incendio' => '🔥', 'inundacion' => '🌊', 'accidente' => '🚗', 'otros' => '📞'];
                                        echo $iconos[$reporte['tipo']] . ' ' . ucfirst($reporte['tipo']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $ubicacion = htmlspecialchars(substr($reporte['ubicacion_texto'] ?? '', 0, 50));
                                    echo $ubicacion . (strlen($reporte['ubicacion_texto'] ?? '') > 50 ? '...' : '');
                                    ?>
                                </td>
                                <table>
                                    <?php if($reporte['gravedad'] == 'alta'): ?>
                                        <span style="color:#e74c3c;">🔴 Alta</span>
                                    <?php elseif($reporte['gravedad'] == 'media'): ?>
                                        <span style="color:#f39c12;">🟡 Media</span>
                                    <?php else: ?>
                                        <span style="color:#27ae60;">🟢 Baja</span>
                                    <?php endif; ?>
                                </td>
                                <tr>
                                    <span class="badge badge-<?php echo $reporte['estado']; ?>">
                                        <?php 
                                        $estados_iconos = ['pendiente' => '⏳', 'en_proceso' => '🔄', 'finalizado' => '✅'];
                                        echo $estados_iconos[$reporte['estado']] . ' ' . ucfirst($reporte['estado']); 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="seguimiento.php?id=<?php echo $reporte['id']; ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if($total_paginas > 1): ?>
                <div class="pagination">
                    <?php if($pagina > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>"><i class="fas fa-chevron-left"></i> Anterior</a>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if($i == $pagina): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if($pagina < $total_paginas): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>">Siguiente <i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>No tienes reportes registrados</h3>
                    <p>No se encontraron reportes con los filtros seleccionados.</p>
                    <a href="reportar.php" style="display: inline-block; margin-top: 20px; background: #e74c3c; color: white; padding: 12px 25px; border-radius: 25px; text-decoration: none;">
                        <i class="fas fa-plus-circle"></i> Reportar Emergencia
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>