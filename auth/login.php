<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';

// Si ya está logueado, redirigir según su rol
if(usuarioLogueado()) {
    if($_SESSION['rol'] == 'administrador') {
        header("Location: ../admin/admin_dashboard.php");
    } elseif($_SESSION['rol'] == 'bombero') {
        header("Location: ../bombero/centro_control.php");
    } else {
        header("Location: ../ciudadano/dashboard.php");
    }
    exit();
}

$error = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['rol'] = $usuario['rol'];
        
        $update = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
        $update->execute([$usuario['id']]);
        
        if($usuario['rol'] == 'administrador') {
            header("Location: ../admin/admin_dashboard.php");
        } elseif($usuario['rol'] == 'bombero') {
            header("Location: ../bombero/centro_control.php");
        } else {
            header("Location: ../ciudadano/dashboard.php");
        }
        exit();
    } else {
        $error = "Correo electrónico o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?php echo SITE_NAME; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('../assets/images/imagen6.jpg') no-repeat center center;
            background-size: cover;
            opacity: 0.08;
            z-index: 0;
        }
        
        .login-container {
            background: white;
            border-radius: 30px;
            padding: 45px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            text-align: center;
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-area {
            margin-bottom: 25px;
        }
        
        .logo-img {
            width: 100px;
            height: 100px;
            border-radius: 25px;
            object-fit: cover;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        
        h2 {
            color: #1a1a2e;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: #e74c3c;
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }
        
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(231, 76, 60, 0.4);
        }
        
        .error {
            background: #fee;
            color: #c0392b;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #e74c3c;
            text-align: left;
        }
        
        .register-link {
            margin-top: 25px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .register-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .emergency-badge {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .emergency-badge i {
            color: #e74c3c;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-area">
            <img src="../assets/images/logo.png" alt="Logo Bomberos Chocó" class="logo-img" onerror="this.src='https://via.placeholder.com/100x100?text=🚒'">
            <h2><?php echo SITE_NAME; ?></h2>
            <p class="subtitle">Sistema de Atención de Emergencias</p>
        </div>
        
        <?php if($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                <input type="email" name="email" placeholder="usuario@correo.com" required>
            </div>
            <div class="input-group">
                <label><i class="fas fa-lock"></i> Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit">Iniciar sesión</button>
        </form>
        
        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
        </div>
        
        <div class="emergency-badge">
            <i class="fas fa-phone-alt"></i> Emergencias: 119 | Línea de atención: 3202498131
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>