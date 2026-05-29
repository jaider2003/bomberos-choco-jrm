<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';

$error = null;
$exito = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif(strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()) {
            $error = "Este correo ya está registrado";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, email, telefono, direccion, password, rol) VALUES (?, ?, ?, ?, ?, 'ciudadano')");
            
            if($stmt->execute([$nombre, $email, $telefono, $direccion, $hashed_password])) {
                $exito = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
            } else {
                $error = "Error al registrar. Intenta nuevamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - <?php echo SITE_NAME; ?></title>
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
        
        .register-container {
            background: white;
            border-radius: 30px;
            padding: 45px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
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
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo-img {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            object-fit: cover;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        
        h2 {
            text-align: center;
            color: #1a1a2e;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .input-group {
            margin-bottom: 18px;
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
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
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
        }
        
        .exito {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            border-left: 4px solid #27ae60;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .login-link a {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .emergency-badge {
            margin-top: 25px;
            text-align: center;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .emergency-badge i {
            color: #e74c3c;
            margin-right: 5px;
        }
        
        @media (max-width: 550px) {
            .register-container {
                padding: 30px;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo-area">
            <img src="../assets/images/logo.png" alt="Logo Bomberos Chocó" class="logo-img" onerror="this.src='https://via.placeholder.com/80x80?text=🚒'">
            <h2>Crear Cuenta</h2>
            <p class="subtitle">Regístrate y ayuda a proteger tu comunidad</p>
        </div>
        
        <?php if($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($exito): ?>
            <div class="exito">
                <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="input-group">
                <label><i class="fas fa-user"></i> Nombre completo</label>
                <input type="text" name="nombre_completo" placeholder="Ej: Juan Pérez" required>
            </div>
            
            <div class="input-group">
                <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                <input type="email" name="email" placeholder="usuario@ejemplo.com" required>
            </div>
            
            <div class="form-row">
                <div class="input-group">
                    <label><i class="fas fa-phone"></i> Teléfono</label>
                    <input type="tel" name="telefono" placeholder="Ej: 3123456789">
                </div>
                <div class="input-group">
                    <label><i class="fas fa-map-marker-alt"></i> Dirección</label>
                    <input type="text" name="direccion" placeholder="Ej: Barrio Versalles">
                </div>
            </div>
            
            <div class="form-row">
                <div class="input-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <input type="password" name="password" placeholder="Mínimo 6 caracteres" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-check-circle"></i> Confirmar contraseña</label>
                    <input type="password" name="confirm_password" placeholder="Repite tu contraseña" required>
                </div>
            </div>
            
            <button type="submit">Registrarse</button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
        
        <div class="emergency-badge">
            <i class="fas fa-phone-alt"></i> Emergencias: 119 | Protegiendo a Quibdó
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>