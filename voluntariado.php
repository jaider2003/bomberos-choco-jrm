<?php
require_once 'includes/config.php';
require_once 'includes/conexion.php';

$mensaje = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $edad = $_POST['edad'];
    $motivo = $_POST['motivo'];
    
    $stmt = $pdo->prepare("INSERT INTO voluntarios (nombre, email, telefono, edad, motivo, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$nombre, $email, $telefono, $edad, $motivo]);
    $mensaje = "✅ ¡Gracias por tu interés! Un asesor se comunicará contigo pronto.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voluntariado - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; }
        .top-bar { background: #1a1a2e; color: white; padding: 10px 0; }
        .main-header { background: white; box-shadow: 0 2px 20px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1400px; margin: 0 auto; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo img { height: 60px; }
        .logo h2 { color: #1a1a2e; font-size: 20px; }
        .logo span { color: #e74c3c; font-size: 12px; display: block; }
        .nav-menu { display: flex; gap: 25px; list-style: none; }
        .nav-menu a { text-decoration: none; color: #2c3e50; font-weight: 500; }
        .nav-menu a:hover, .nav-menu a.active { color: #e74c3c; }
        .nav-buttons { display: flex; gap: 12px; }
        .btn-login { background: transparent; border: 2px solid #e74c3c; color: #e74c3c; padding: 8px 22px; border-radius: 30px; text-decoration: none; }
        .btn-emergency { background: #e74c3c; color: white; padding: 8px 22px; border-radius: 30px; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .menu-icon { display: none; font-size: 28px; cursor: pointer; }
        
        .page-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 60px 0; text-align: center; }
        .volunteer-section { padding: 60px 0; background: #f8f9fa; }
        .volunteer-container { max-width: 1200px; margin: 0 auto; padding: 0 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 50px; }
        .info-box { background: white; border-radius: 20px; padding: 35px; }
        .info-box h3 { margin-bottom: 20px; color: #1a1a2e; }
        .requisitos { list-style: none; margin-top: 20px; }
        .requisitos li { margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
        .form-box { background: white; border-radius: 20px; padding: 35px; }
        .form-group { margin-bottom: 20px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #eee; border-radius: 10px; }
        .btn-submit { background: #e74c3c; color: white; border: none; padding: 14px; border-radius: 40px; width: 100%; cursor: pointer; font-weight: 600; }
        .mensaje-exito { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        
        .footer { background: #1a1a2e; color: white; padding: 60px 0 30px; }
        .footer .container-footer { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; }
        .footer-bottom { text-align: center; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 40px; }
        
        @media (max-width: 900px) { .volunteer-container { grid-template-columns: 1fr; } .nav-menu { display: none; } .menu-icon { display: block; } }
    </style>
</head>
<body>
    <div class="top-bar"><div class="nav-container" style="justify-content: space-between;"><span><i class="fas fa-phone-alt"></i> 3202498131</span><span><i class="fas fa-envelope"></i> voluntarios@bomberoschoco.gov.co</span></div></div>
    <header class="main-header"><div class="nav-container"><div class="logo"><img src="assets/images/logo.png" alt="Logo"><h2><?php echo SITE_NAME; ?><span>Protegiendo a Quibdó</span></h2></div><ul class="nav-menu"><li><a href="index.php">Inicio</a></li><li><a href="sobre-nosotros.php">Sobre Nosotros</a></li><li><a href="noticias.php">Noticias</a></li><li><a href="capacitaciones.php">Capacitaciones</a></li><li><a href="voluntariado.php" class="active">Voluntariado</a></li><li><a href="contacto.php">Contacto</a></li></ul><div class="nav-buttons"><a href="auth/login.php" class="btn-login">Ingresar</a><a href="ciudadano/reportar.php" class="btn-emergency"><i class="fas fa-phone-alt"></i> Reportar Emergencia</a></div><div class="menu-icon"><i class="fas fa-bars"></i></div></div></header>

    <section class="page-header"><h1>Voluntariado</h1><p>Únete a nuestra familia y sé parte del cambio</p></section>

    <section class="volunteer-section">
        <div class="volunteer-container">
            <div class="info-box">
                <h3><i class="fas fa-heart" style="color:#e74c3c;"></i> ¿Por qué ser voluntario?</h3>
                <p>Ser voluntario en el Cuerpo de Bomberos del Chocó es una experiencia transformadora. Contribuyes directamente a la seguridad de tu comunidad mientras desarrollas habilidades valiosas.</p>
                <h3 style="margin-top: 25px;">Requisitos</h3>
                <ul class="requisitos">
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Ser mayor de 18 años</li>
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Buena condición física</li>
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Compromiso de 12 horas semanales</li>
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> No tener antecedentes judiciales</li>
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Vocación de servicio</li>
                </ul>
                <h3 style="margin-top: 25px;">Beneficios</h3>
                <ul class="requisitos">
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Capacitación gratuita en emergencias</li>
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Seguro de vida y accidentes</li>
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Uniforme y equipos de protección</li>
                    <li><i class="fas fa-check-circle" style="color:#27ae60;"></i> Certificaciones oficiales</li>
                </ul>
            </div>
            <div class="form-box">
                <h3>Formulario de inscripción</h3>
                <?php if($mensaje): ?><div class="mensaje-exito"><?php echo $mensaje; ?></div><?php endif; ?>
                <form method="POST">
                    <div class="form-group"><input type="text" name="nombre" placeholder="Nombre completo *" required></div>
                    <div class="form-group"><input type="email" name="email" placeholder="Correo electrónico *" required></div>
                    <div class="form-group"><input type="tel" name="telefono" placeholder="Teléfono *" required></div>
                    <div class="form-group"><input type="number" name="edad" placeholder="Edad *" required></div>
                    <div class="form-group"><textarea name="motivo" rows="4" placeholder="¿Por qué quieres ser voluntario? *" required></textarea></div>
                    <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Enviar solicitud</button>
                </form>
            </div>
        </div>
    </section>

    <footer class="footer"><div class="container-footer"><div class="footer-grid"><div><h4><?php echo SITE_NAME; ?></h4><p>Protegiendo a Quibdó desde 1975.</p></div><div><h4>Contacto</h4><p><i class="fas fa-phone"></i> 3202498131<br><i class="fas fa-envelope"></i> voluntarios@bomberoschoco.gov.co</p></div></div><div class="footer-bottom"><p>&copy; 2026 <?php echo SITE_NAME; ?> - Todos los derechos reservados.</p></div></div></footer>
</body>
</html>