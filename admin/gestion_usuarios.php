<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('administrador');

// Procesar acciones
$mensaje = null;
if(isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    // Verificar que no sea el propio admin
    if($id != $_SESSION['usuario_id']) {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = "✅ Usuario eliminado correctamente";
    } else {
        $mensaje = "❌ No puedes eliminar tu propio usuario";
    }
}

if(isset($_GET['cambiar_rol'])) {
    $id = $_GET['cambiar_rol'];
    $nuevo_rol = $_GET['rol'];
    $stmt = $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?");
    $stmt->execute([$nuevo_rol, $id]);
    $mensaje = "✅ Rol actualizado correctamente";
}

// Obtener todos los usuarios
$usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY fecha_registro DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - <?php echo SITE_NAME; ?></title>
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
        .sidebar-menu {
            list-style: none;
        }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
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
            color: #2c3e50;
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
            font-weight: 600;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-admin { background: #e74c3c; color: white; }
        .badge-bombero { background: #3498db; color: white; }
        .badge-ciudadano { background: #27ae60; color: white; }
        .btn-icon {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            margin: 0 3px;
            font-size: 14px;
        }
        .btn-edit { background: #3498db; color: white; }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-role { background: #f39c12; color: white; }
        .mensaje {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        select {
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 35px;">🚒</div>
            <h1><?php echo SITE_NAME; ?> - Gestión de Usuarios</h1>
        </div>
        <a href="../auth/logout.php" style="color: white; text-decoration: none;"><i class="fas fa-sign-out-alt"></i> Salir</a>
    </div>
    
    <div class="dashboard-wrapper">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="active"><a href="gestion_usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a></li>
                <li><a href="gestion_reportes.php"><i class="fas fa-exclamation-triangle"></i> Gestionar Reportes</a></li>
                <li><a href="estadisticas.php"><i class="fas fa-chart-line"></i> Estadísticas Avanzadas</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="card">
                <h2><i class="fas fa-users"></i> Listado de Usuarios</h2>
                
                <?php if($mensaje): ?>
                    <div class="mensaje"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                 <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Rol</th>
                                <th>Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($usuarios as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['nombre_completo']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['telefono'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $u['rol']; ?>">
                                        <?php echo getRolNombre($u['rol']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?></td>
                                <td>
                                    <a href="editar_usuario.php?id=<?php echo $u['id']; ?>" class="btn-icon btn-edit"><i class="fas fa-edit"></i></a>
                                    <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                                        <a href="?eliminar=<?php echo $u['id']; ?>" class="btn-icon btn-delete" onclick="return confirm('¿Eliminar este usuario?')"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                    <form action="" method="GET" style="display: inline-block;">
                                        <input type="hidden" name="cambiar_rol" value="<?php echo $u['id']; ?>">
                                        <select name="rol" onchange="this.form.submit()" style="padding: 5px;">
                                            <option value="ciudadano" <?php echo $u['rol'] == 'ciudadano' ? 'selected' : ''; ?>>Ciudadano</option>
                                            <option value="bombero" <?php echo $u['rol'] == 'bombero' ? 'selected' : ''; ?>>Bombero</option>
                                            <option value="administrador" <?php echo $u['rol'] == 'administrador' ? 'selected' : ''; ?>>Administrador</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>