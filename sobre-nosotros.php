<?php
require_once 'includes/config.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; }
        
        /* Importar estilos del header y footer */
        .top-bar { background: #1a1a2e; color: white; padding: 10px 0; font-size: 13px; }
        .top-bar .container { max-width: 1400px; margin: 0 auto; padding: 0 30px; display: flex; justify-content: space-between; flex-wrap: wrap; }
        .top-bar-info { display: flex; gap: 20px; flex-wrap: wrap; }
        .top-bar-info a { color: #fff; text-decoration: none; }
        .top-bar-social { display: flex; gap: 15px; }
        .top-bar-social a { color: white; transition: all 0.3s; }
        .top-bar-social a:hover { color: #e74c3c; transform: translateY(-2px); }
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
        .btn-login { background: transparent; border: 2px solid #e74c3c; color: #e74c3c; padding: 8px 22px; border-radius: 30px; text-decoration: none; }
        .btn-emergency { background: #e74c3c; color: white; padding: 8px 22px; border-radius: 30px; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .menu-icon { display: none; font-size: 28px; cursor: pointer; }
        
        .page-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 60px 0; text-align: center; }
        .page-header h1 { font-size: 48px; margin-bottom: 15px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 60px 30px; }
        .about-content { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; align-items: center; }
        .about-image img { width: 100%; border-radius: 20px; }
        .mission-vision { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 60px; }
        .mv-card { background: #f8f9fa; padding: 30px; border-radius: 20px; text-align: center; }
        .mv-card i { font-size: 50px; color: #e74c3c; margin-bottom: 20px; }
        .values { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 40px; }
        .value-item { text-align: center; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        
        @media (max-width: 1000px) {
            .nav-menu { display: none; }
            .menu-icon { display: block; }
            .about-content { grid-template-columns: 1fr; }
            .mission-vision { grid-template-columns: 1fr; }
        }
        
        .footer { background: #1a1a2e; color: white; padding: 60px 0 30px; margin-top: 60px; }
        .footer .container-footer { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-col h4 { margin-bottom: 20px; }
        .footer-col ul { list-style: none; }
        .footer-col a { color: #bbb; text-decoration: none; transition: color 0.3s; }
        .footer-col a:hover { color: #e74c3c; }
        .footer-social { display: flex; gap: 15px; margin-top: 20px; }
        .footer-social a { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .footer-social a:hover { background: #e74c3c; transform: translateY(-3px); }
        .footer-bottom { text-align: center; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); color: #888; }
        
        @media (max-width: 1000px) { .nav-menu { display: none; } .menu-icon { display: block; } .about-content { grid-template-columns: 1fr; } .mission-vision { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="container">
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
                <li><a href="sobre-nosotros.php" class="active">Sobre Nosotros</a></li>
                <li><a href="#servicios">Servicios</a></li>
                <li><a href="noticias.php">Noticias</a></li>
                <li><a href="capacitaciones.php">Capacitaciones</a></li>
                <li><a href="voluntariado.php">Voluntariado</a></li>
                <li><a href="contacto.php">Contacto</a></li>
            </ul>
            <div class="nav-buttons">
                <a href="auth/login.php" class="btn-login"><i class="fas fa-user"></i> Ingresar</a>
                <a href="ciudadano/reportar.php" class="btn-emergency"><i class="fas fa-phone-alt"></i> Reportar Emergencia</a>
            </div>
            <div class="menu-icon"><i class="fas fa-bars"></i></div>
        </div>
    </header>

    <section class="page-header">
        <div class="container">
            <h1>Sobre Nosotros</h1>
            <p>Conoce nuestra historia, misión y compromiso con la comunidad</p>
        </div>
    </section>

    <div class="container">
        <div class="about-content">
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1599837565318-67429bde7162?w=600&h=450&fit=crop" alt="Historia">
            </div>
            <div>
                <h2>Nuestra Historia</h2>
                <p>El Benemérito Cuerpo de Bomberos del Chocó JRM fue fundado el 20 de julio de 1975 por un grupo de valientes ciudadanos comprometidos con la seguridad de Quibdó.</p>
                <p style="margin-top: 15px;">Desde entonces, hemos crecido y nos hemos modernizado para enfrentar los desafíos de una ciudad en constante desarrollo, siempre con el objetivo de proteger la vida, el ambiente y los bienes de nuestra comunidad.</p>
            </div>
        </div>

        <div class="mission-vision">
            <div class="mv-card">
                <i class="fas fa-bullseye"></i>
                <h3>Misión</h3>
                <p>Proteger la vida, el ambiente y los bienes de la comunidad mediante la atención oportuna y eficiente de emergencias, la prevención de riesgos y la capacitación continua.</p>
            </div>
            <div class="mv-card">
                <i class="fas fa-eye"></i>
                <h3>Visión</h3>
                <p>Ser reconocidos como la institución de bomberos más moderna y eficiente del país, con tecnología de punta y personal altamente calificado.</p>
            </div>
        </div>

        <h2 style="text-align: center; margin: 50px 0 30px;">Nuestros Valores</h2>
        <div class="values">
            <div class="value-item"><i class="fas fa-shield-alt" style="font-size: 30px; color: #e74c3c;"></i><h4>Honor</h4><p>Actuamos con integridad y nobleza</p></div>
            <div class="value-item"><i class="fas fa-heart" style="font-size: 30px; color: #e74c3c;"></i><h4>Valentía</h4><p>Enfrentamos el peligro sin dudar</p></div>
            <div class="value-item"><i class="fas fa-handshake" style="font-size: 30px; color: #e74c3c;"></i><h4>Compromiso</h4><p>Dedicación total a la comunidad</p></div>
            <div class="value-item"><i class="fas fa-users" style="font-size: 30px; color: #e74c3c;"></i><h4>Trabajo en Equipo</h4><p>Juntos somos más fuertes</p></div>
        </div>
    </div>

    <footer class="footer">
        <div class="container-footer">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p>Protegiendo a Quibdó y el Chocó desde 1975.</p>
                    <div class="footer-social">
                        <!-- REDES SOCIALES EN EL FOOTER -->
                        <a href="https://www.facebook.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.instagram.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.youtube.com/c/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Enlaces</h4>
                    <ul>
                        <li><a href="index.php" style="color:#bbb;">Inicio</a></li>
                        <li><a href="sobre-nosotros.php" style="color:#bbb;">Sobre Nosotros</a></li>
                        <li><a href="noticias.php" style="color:#bbb;">Noticias</a></li>
                        <li><a href="contacto.php" style="color:#bbb;">Contacto</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-phone"></i> 3202498131<br><i class="fas fa-envelope"></i> info@bomberoschoco.gov.co</p>
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