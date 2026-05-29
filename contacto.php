<?php
require_once 'includes/config.php';
require_once 'includes/conexion.php';

$mensaje_enviado = null;
$error = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $asunto = trim($_POST['asunto']);
    $mensaje = trim($_POST['mensaje']);
    
    if(empty($nombre) || empty($email) || empty($mensaje)) {
        $error = "Por favor complete todos los campos obligatorios.";
    } else {
        // Guardar en base de datos (opcional)
        $stmt = $pdo->prepare("INSERT INTO contactos (nombre, email, telefono, asunto, mensaje, fecha) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$nombre, $email, $telefono, $asunto, $mensaje]);
        
        // Enviar correo (opcional - configurar SMTP)
        $mensaje_enviado = "✅ Mensaje enviado correctamente. Nos comunicaremos con usted pronto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; }
        
        /* Header y Footer */
        .top-bar { background: #1a1a2e; color: white; padding: 10px 0; font-size: 13px; }
        .top-bar .container-header { max-width: 1400px; margin: 0 auto; padding: 0 30px; display: flex; justify-content: space-between; flex-wrap: wrap; }
        .top-bar-info { display: flex; gap: 20px; flex-wrap: wrap; }
        .top-bar-info a { color: #fff; text-decoration: none; }
        .main-header { background: white; box-shadow: 0 2px 20px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000; }
        .nav-container { max-width: 1400px; margin: 0 auto; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo img { height: 60px; }
        .logo h2 { color: #1a1a2e; font-size: 20px; }
        .logo span { color: #e74c3c; font-size: 12px; display: block; }
        .nav-menu { display: flex; gap: 25px; list-style: none; }
        .nav-menu a { text-decoration: none; color: #2c3e50; font-weight: 500; transition: all 0.3s; }
        .nav-menu a:hover, .nav-menu a.active { color: #e74c3c; }
        .nav-buttons { display: flex; gap: 12px; }
        .btn-login { background: transparent; border: 2px solid #e74c3c; color: #e74c3c; padding: 8px 22px; border-radius: 30px; text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .btn-login:hover { background: #e74c3c; color: white; }
        .btn-emergency { background: #e74c3c; color: white; padding: 8px 22px; border-radius: 30px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-weight: 500; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(231, 76, 60, 0); } 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); } }
        .menu-icon { display: none; font-size: 28px; cursor: pointer; }
        
        .page-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 60px 0; text-align: center; }
        .page-header h1 { font-size: 48px; margin-bottom: 15px; }
        
        .contact-section { padding: 60px 0; background: #f8f9fa; }
        .contact-container { max-width: 1400px; margin: 0 auto; padding: 0 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 50px; }
        
        .contact-info { background: white; border-radius: 20px; padding: 35px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .contact-info h3 { font-size: 24px; margin-bottom: 25px; color: #1a1a2e; }
        .info-item { display: flex; gap: 15px; margin-bottom: 25px; align-items: flex-start; }
        .info-icon { width: 50px; height: 50px; background: #f8f9fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #e74c3c; }
        .info-text h4 { margin-bottom: 5px; color: #1a1a2e; }
        .info-text p { color: #666; }
        
        .contact-form { background: white; border-radius: 20px; padding: 35px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .contact-form h3 { font-size: 24px; margin-bottom: 25px; color: #1a1a2e; }
        .form-group { margin-bottom: 20px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 15px; border: 2px solid #eee; border-radius: 10px; font-family: inherit; transition: all 0.3s; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #e74c3c; }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .btn-submit { background: #e74c3c; color: white; border: none; padding: 14px 30px; border-radius: 40px; font-weight: 600; cursor: pointer; transition: all 0.3s; width: 100%; }
        .btn-submit:hover { background: #c0392b; transform: translateY(-2px); }
        
        .map-section { padding: 60px 0; background: white; }
        .map-container { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .map-container h3 { text-align: center; margin-bottom: 30px; font-size: 28px; }
        .map { height: 400px; background: #f0f2f5; border-radius: 20px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-align: center; border-radius: 20px; }
        
        .mensaje-exito { background: #d4edda; color: #155724; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .mensaje-error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        
        .footer { background: #1a1a2e; color: white; padding: 60px 0 30px; margin-top: 0; }
        .footer .container-footer { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-col h4 { margin-bottom: 20px; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 10px; }
        .footer-col a { color: #bbb; text-decoration: none; transition: color 0.3s; }
        .footer-col a:hover { color: #e74c3c; }
        .footer-social { display: flex; gap: 15px; margin-top: 20px; }
        .footer-social a { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .footer-social a:hover { background: #e74c3c; transform: translateY(-3px); }
        .footer-bottom { text-align: center; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); color: #888; }
        
        @media (max-width: 900px) {
            .contact-container { grid-template-columns: 1fr; }
            .nav-menu { display: none; }
            .menu-icon { display: block; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container-header">
            <div class="top-bar-info">
                <span><i class="fas fa-phone-alt"></i> 3202498131</span>
                <span><i class="fas fa-envelope"></i> info@bomberoschoco.gov.co</span>
                <span><i class="fas fa-map-marker-alt"></i> Quibdó, Chocó - Colombia</span>
            </div>
            <div class="top-bar-social">
                <!-- REDES SOCIALES CON ENLACES REALES -->
                <a href="https://www.facebook.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                <a href="https://www.instagram.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/c/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <header class="main-header">
        <div class="nav-container">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/60x60?text=🚒'">
                <h2><?php echo SITE_NAME; ?><span>Protegiendo a Quibdó</span></h2>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php">Inicio</a></li>
                <li><a href="sobre-nosotros.php">Sobre Nosotros</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="noticias.php">Noticias</a></li>
                <li><a href="capacitaciones.php">Capacitaciones</a></li>
                <li><a href="voluntariado.php">Voluntariado</a></li>
                <li><a href="contacto.php" class="active">Contacto</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="auth/login.php" class="btn-login"><i class="fas fa-user"></i> Ingresar</a>
                <a href="ciudadano/reportar.php" class="btn-emergency"><i class="fas fa-phone-alt"></i> Reportar Emergencia</a>
            </div>
            <div class="menu-icon"><i class="fas fa-bars"></i></div>
        </div>
    </header>

    <section class="page-header">
        <h1>Contacto</h1>
        <p>Estamos aquí para servirte. Contáctanos en cualquier momento.</p>
    </section>

    <section class="contact-section">
        <div class="contact-container">
            <div class="contact-info">
                <h3><i class="fas fa-map-marker-alt" style="color:#e74c3c;"></i> Información de Contacto</h3>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-text">
                        <h4>Dirección</h4>
                        <p>Avenida de las Américas #20-54<br>Barrio Versalles, Quibdó - Chocó</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="info-text">
                        <h4>Teléfonos</h4>
                        <p><strong>Emergencias:</strong> 119<br><strong>Oficina:</strong> 3202498131<br><strong>Fax:</strong> 3202498131</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="info-text">
                        <h4>Correos Electrónicos</h4>
                        <p><strong>General:</strong> info@bomberoschoco.gov.co<br><strong>Capacitaciones:</strong> academia@bomberoschoco.gov.co<br><strong>Inspecciones:</strong> inspecciones@bomberoschoco.gov.co<br><strong>Notificaciones Judiciales:</strong> notificaciones@bomberoschoco.gov.co</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-text">
                        <h4>Horarios de Atención</h4>
                        <p><strong>Emergencias:</strong> 24 horas / 7 días<br><strong>Oficinas:</strong> Lunes a viernes, 7:30 AM - 5:30 PM<br><strong>Sábados:</strong> 8:00 AM - 12:00 PM</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <h3><i class="fas fa-paper-plane" style="color:#e74c3c;"></i> Envíanos un Mensaje</h3>
                <?php if($mensaje_enviado): ?>
                    <div class="mensaje-exito"><?php echo $mensaje_enviado; ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="mensaje-error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="form-group">
                        <input type="text" name="nombre" placeholder="Nombre completo *" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Correo electrónico *" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="telefono" placeholder="Teléfono">
                    </div>
                    <div class="form-group">
                        <select name="asunto">
                            <option value="">Seleccione un asunto</option>
                            <option value="Información">Solicitud de información</option>
                            <option value="Capacitación">Solicitud de capacitación</option>
                            <option value="Inspección">Solicitud de inspección</option>
                            <option value="Voluntariado">Voluntariado</option>
                            <option value="Queja">Queja o sugerencia</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea name="mensaje" placeholder="Escriba su mensaje aquí *" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Enviar Mensaje</button>
                </form>
            </div>
        </div>
    </section>

    <section class="map-section">
        <div class="map-container">
            <h3>Ubicación de Nuestra Estación Central</h3>
            <div class="map">
                <div>
                    <i class="fas fa-map-marked-alt" style="font-size: 50px; margin-bottom: 15px;"></i>
                    <p><strong>📍 Estación Central</strong><br>Avenida de las Américas #20-54<br>Barrio Versalles, Quibdó - Chocó</p>
                    <p style="margin-top: 15px;">
                        <a href="https://maps.google.com/?q=Quibdó+Chocó" target="_blank" style="color: white; background: #e74c3c; padding: 8px 20px; border-radius: 30px; text-decoration: none; display: inline-block; margin-top: 10px;">
                            <i class="fas fa-directions"></i> Cómo llegar
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container-footer">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p>Protegiendo a Quibdó y el Chocó con compromiso, honor y valentía desde 1975.</p>
                    <div class="footer-social">
                        <!-- REDES SOCIALES EN EL FOOTER -->
                        <a href="https://www.facebook.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.youtube.com/c/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Enlaces Rápidos</h4>
                    <ul>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="sobre-nosotros.php">Sobre Nosotros</a></li>
                        <li><a href="noticias.php">Noticias</a></li>
                        <li><a href="contacto.php">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Servicios</h4>
                    <ul>
                        <li><a href="#">Atención de Emergencias</a></li>
                        <li><a href="#">Capacitaciones</a></li>
                        <li><a href="#">Equipos de Seguridad</a></li>
                        <li><a href="#">Inspecciones Técnicas</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contacto</h4>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> Av. de las Américas #20-54</li>
                        <li><i class="fas fa-phone"></i> 3202498131</li>
                        <li><i class="fas fa-envelope"></i> info@bomberoschoco.gov.co</li>
                        <li><i class="fas fa-clock"></i> 24 horas / 7 días</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 <?php echo SITE_NAME; ?> - Benemérito Cuerpo de Bomberos del Chocó. Todos los derechos reservados.</p>
                <p>Sitio desarrollado para la protección y seguridad de la comunidad quibdoseña.</p>
            </div>
        </div>
    </footer>

    <script>
        document.querySelector('.menu-icon')?.addEventListener('click', function() {
            document.querySelector('.nav-menu')?.classList.toggle('show');
        });
    </script>
</body>
</html>