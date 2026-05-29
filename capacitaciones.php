<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Capacitaciones - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; }
        
        /* Header */
        .top-bar { background: #1a1a2e; color: white; padding: 10px 0; font-size: 13px; }
        .top-bar .container-header { max-width: 1400px; margin: 0 auto; padding: 0 30px; display: flex; justify-content: space-between; flex-wrap: wrap; }
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
        .page-header h1 { font-size: 48px; margin-bottom: 15px; }
        
        .courses-section { padding: 60px 0; background: #f8f9fa; }
        .courses-container { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .courses-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; }
        .course-card { background: white; border-radius: 20px; overflow: hidden; transition: all 0.3s; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        .course-card:hover { transform: translateY(-5px); }
        .course-icon { background: #e74c3c; padding: 30px; text-align: center; }
        .course-icon i { font-size: 50px; color: white; }
        .course-content { padding: 25px; }
        .course-content h3 { margin-bottom: 10px; }
        .course-info { display: flex; gap: 15px; margin: 15px 0; color: #666; font-size: 14px; }
        .course-info i { color: #e74c3c; }
        .btn-course { background: #1a1a2e; color: white; padding: 10px 20px; border-radius: 30px; text-decoration: none; display: inline-block; margin-top: 15px; }
        
        .footer { background: #1a1a2e; color: white; padding: 60px 0 30px; }
        .footer .container-footer { max-width: 1400px; margin: 0 auto; padding: 0 30px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 40px; }
        .footer-bottom { text-align: center; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); }
        
        @media (max-width: 900px) { .nav-menu { display: none; } .menu-icon { display: block; } }
    </style>
</head>
<body>
    <div class="top-bar"><div class="container-header"><div class="top-bar-info"><span><i class="fas fa-phone-alt"></i> 3202498131</span></div></div></div>
    <header class="main-header"><div class="nav-container"><div class="logo"><img src="assets/images/logo.png" alt="Logo"><h2><?php echo SITE_NAME; ?><span>Protegiendo a Quibdó</span></h2></div><ul class="nav-menu"><li><a href="index.php">Inicio</a></li><li><a href="sobre-nosotros.php">Sobre Nosotros</a></li><li><a href="noticias.php">Noticias</a></li><li><a href="capacitaciones.php" class="active">Capacitaciones</a></li><li><a href="voluntariado.php">Voluntariado</a></li><li><a href="contacto.php">Contacto</a></li></ul><div class="nav-buttons"><a href="auth/login.php" class="btn-login">Ingresar</a><a href="ciudadano/reportar.php" class="btn-emergency"><i class="fas fa-phone-alt"></i> Reportar Emergencia</a></div><div class="menu-icon"><i class="fas fa-bars"></i></div></div></header>

    <section class="page-header"><h1>Capacitaciones y Cursos</h1><p>Formación en prevención y atención de emergencias</p></section>

    <section class="courses-section">
        <div class="courses-container">
            <div class="courses-grid">
                <div class="course-card"><div class="course-icon"><i class="fas fa-heartbeat"></i></div><div class="course-content"><h3>Primeros Auxilios Básicos</h3><p>Aprende técnicas básicas de atención prehospitalaria.</p><div class="course-info"><span><i class="fas fa-clock"></i> 20 horas</span><span><i class="fas fa-certificate"></i> Certificado</span></div><a href="contacto.php" class="btn-course">Inscribirse →</a></div></div>
                <div class="course-card"><div class="course-icon"><i class="fas fa-fire-extinguisher"></i></div><div class="course-content"><h3>Prevención de Incendios</h3><p>Manejo de extintores y planes de evacuación.</p><div class="course-info"><span><i class="fas fa-clock"></i> 16 horas</span><span><i class="fas fa-certificate"></i> Certificado</span></div><a href="contacto.php" class="btn-course">Inscribirse →</a></div></div>
                <div class="course-card"><div class="course-icon"><i class="fas fa-water"></i></div><div class="course-content"><h3>Rescate Acuático</h3><p>Técnicas de rescate en inundaciones y ríos.</p><div class="course-info"><span><i class="fas fa-clock"></i> 24 horas</span><span><i class="fas fa-certificate"></i> Certificado</span></div><a href="contacto.php" class="btn-course">Inscribirse →</a></div></div>
                <div class="course-card"><div class="course-icon"><i class="fas fa-building"></i></div><div class="course-content"><h3>Seguridad Industrial</h3><p>Medidas de seguridad en empresas e industrias.</p><div class="course-info"><span><i class="fas fa-clock"></i> 30 horas</span><span><i class="fas fa-certificate"></i> Certificado</span></div><a href="contacto.php" class="btn-course">Inscribirse →</a></div></div>
            </div>
        </div>
    </section>

    <footer class="footer"><div class="container-footer"><div class="footer-grid"><div><h4><?php echo SITE_NAME; ?></h4><p>Protegiendo a Quibdó desde 1975.</p></div><div><h4>Contacto</h4><p><i class="fas fa-phone"></i> 3202498131<br><i class="fas fa-envelope"></i> info@bomberoschoco.gov.co</p></div></div><div class="footer-bottom"><p>&copy; 2026 <?php echo SITE_NAME; ?> - Todos los derechos reservados.</p></div></div></footer>
</body>
</html>