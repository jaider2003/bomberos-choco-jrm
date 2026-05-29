<?php
require_once 'includes/config.php';
require_once 'includes/conexion.php';

// Obtener noticias de la base de datos (puedes crear tabla noticias)
$noticias = $pdo->query("SELECT * FROM noticias ORDER BY fecha DESC LIMIT 10")->fetchAll();
if(!$noticias) {
    // Noticias de ejemplo si no hay en BD
    $noticias = [
        ['id'=>1, 'titulo'=>'Nuevo centro de entrenamiento para bomberos', 'fecha'=>'2026-05-15', 'resumen'=>'Inauguramos modernas instalaciones para la capacitación de nuestro personal.', 'imagen'=>'assets/images/noticia1.jpg', 'contenido'=>'...'],
        ['id'=>2, 'titulo'=>'Campaña de prevención de incendios en colegios', 'fecha'=>'2026-05-10', 'resumen'=>'Iniciamos jornadas educativas en las instituciones educativas de Quibdó.', 'imagen'=>'assets/images/noticia2.jpg', 'contenido'=>'...'],
        ['id'=>3, 'titulo'=>'Convenio con la alcaldía para equipos de rescate', 'fecha'=>'2026-05-05', 'resumen'=>'Recibimos nueva flota de ambulancias y equipos de rescate acuático.', 'imagen'=>'assets/images/noticia3.jpg', 'contenido'=>'...'],
        ['id'=>4, 'titulo'=>'Jornada de capacitación en primeros auxilios', 'fecha'=>'2026-04-28', 'resumen'=>'Más de 200 personas participaron en nuestra jornada de formación.', 'imagen'=>'assets/images/noticia4.jpg', 'contenido'=>'...'],
        ['id'=>5, 'titulo'=>'Día Internacional del Bombero', 'fecha'=>'2026-05-04', 'resumen'=>'Celebramos con orgullo el día de nuestros héroes.', 'imagen'=>'assets/images/noticia5.jpg', 'contenido'=>'...'],
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; }
        
        /* Header y Footer - mismos estilos */
        .top-bar { background: #1a1a2e; color: white; padding: 10px 0; font-size: 13px; }
        .top-bar .container-header { max-width: 1400px; margin: 0 auto; padding: 0 30px; display: flex; justify-content: space-between; flex-wrap: wrap; }
        .top-bar-info { display: flex; gap: 20px; flex-wrap: wrap; }
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
        .btn-emergency { background: #e74c3c; color: white; padding: 8px 22px; border-radius: 30px; text-decoration: none; display: flex; align-items: center; gap: 8px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(231, 76, 60, 0); } 100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); } }
        .menu-icon { display: none; font-size: 28px; cursor: pointer; }
        
        .page-header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white; padding: 60px 0; text-align: center; }
        .page-header h1 { font-size: 48px; margin-bottom: 15px; }
        
        .news-section { padding: 60px 0; background: #f8f9fa; }
        .news-container { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .news-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; }
        .news-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .news-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .news-image { height: 220px; background-size: cover; background-position: center; }
        .news-content { padding: 25px; }
        .news-date { color: #e74c3c; font-size: 12px; margin-bottom: 10px; display: flex; align-items: center; gap: 5px; }
        .news-card h3 { font-size: 20px; margin-bottom: 12px; }
        .news-card p { color: #666; line-height: 1.6; margin-bottom: 15px; }
        .btn-read { color: #e74c3c; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 5px; }
        
        .sidebar { margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .sidebar-widget { background: white; border-radius: 20px; padding: 25px; }
        .sidebar-widget h4 { margin-bottom: 20px; color: #1a1a2e; border-left: 4px solid #e74c3c; padding-left: 15px; }
        .categories-list { list-style: none; }
        .categories-list li { margin-bottom: 12px; }
        .categories-list a { color: #666; text-decoration: none; transition: color 0.3s; display: flex; justify-content: space-between; }
        .categories-list a:hover { color: #e74c3c; }
        
        .footer { background: #1a1a2e; color: white; padding: 60px 0 30px; margin-top: 0; }
        .footer .container-footer { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-bottom { text-align: center; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); }
        
        @media (max-width: 900px) {
            .nav-menu { display: none; }
            .menu-icon { display: block; }
            .news-grid { grid-template-columns: 1fr; }
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
                <li><a href="noticias.php" class="active">Noticias</a></li>
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
        <h1>Noticias y Eventos</h1>
        <p>Mantente informado sobre nuestras actividades y novedades</p>
    </section>

    <section class="news-section">
        <div class="news-container">
            <div class="news-grid">
                <?php foreach($noticias as $noticia): ?>
                <div class="news-card">
                    <div class="news-image" style="background-image: url('<?php echo $noticia['imagen']; ?>')"></div>
                    <div class="news-content">
                        <div class="news-date"><i class="far fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($noticia['fecha'])); ?></div>
                        <h3><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($noticia['resumen'], 0, 120)); ?>...</p>
                        <a href="#" class="btn-read">Leer más <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="sidebar">
                <div class="sidebar-widget">
                    <h4><i class="fas fa-tags"></i> Categorías</h4>
                    <ul class="categories-list">
                        <li><a href="#">Eventos <span>(12)</span></a></li>
                        <li><a href="#">Capacitaciones <span>(8)</span></a></li>
                        <li><a href="#">Comunicados <span>(5)</span></a></li>
                        <li><a href="#">Convenios <span>(4)</span></a></li>
                        <li><a href="#">Reconocimientos <span>(6)</span></a></li>
                    </ul>
                </div>
                <div class="sidebar-widget">
                    <h4><i class="fas fa-calendar-alt"></i> Eventos Próximos</h4>
                    <ul class="categories-list">
                        <li><a href="#">Curso de Primeros Auxilios - 20 Jun</a></li>
                        <li><a href="#">Jornada de Prevención - 25 Jun</a></li>
                        <li><a href="#">Día del Bombero - 4 Jul</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container-footer">
            <div class="footer-grid">
                <div><h4><?php echo SITE_NAME; ?></h4><p>Protegiendo a Quibdó y el Chocó desde 1975.</p></div>
                <div><h4>Enlaces</h4><ul><li><a href="index.php" style="color:#bbb;">Inicio</a></li><li><a href="sobre-nosotros.php" style="color:#bbb;">Sobre Nosotros</a></li></ul></div>
                <div><h4>Contacto</h4><p><i class="fas fa-phone"></i> 3202498131<br><i class="fas fa-envelope"></i> info@bomberoschoco.gov.co</p></div>
            </div>
            <div class="footer-bottom"><p>&copy; 2026 <?php echo SITE_NAME; ?> - Todos los derechos reservados.</p></div>
        </div>
    </footer>
</body>
</html>