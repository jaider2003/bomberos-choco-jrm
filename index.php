<?php
require_once 'includes/config.php';
// No requiere verificar rol - es página pública
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Protegiendo a Quibdó y el Chocó</title>
    <meta name="description" content="Cuerpo de Bomberos del Chocó JRM - Atención de emergencias, capacitaciones, equipos de seguridad y voluntariado en Quibdó">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============================================
           TUS ESTILOS EXISTENTES (SE MANTIENEN IGUAL)
        ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            background: #fff;
        }

        /* ============================================
           HEADER Y NAVEGACIÓN
        ============================================ */
        .top-bar {
            background: #1a1a2e;
            color: white;
            padding: 10px 0;
            font-size: 13px;
        }

        .top-bar .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .top-bar-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .top-bar-info a {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s;
        }

        .top-bar-info a:hover {
            color: #e74c3c;
        }

        .top-bar-social {
            display: flex;
            gap: 15px;
        }

        .top-bar-social a {
            color: white;
            transition: all 0.3s;
        }

        .top-bar-social a:hover {
            color: #e74c3c;
            transform: translateY(-2px);
        }

        /* Header Principal */
        .main-header {
            background: white;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo img {
            height: 60px;
        }

        .logo h2 {
            color: #1a1a2e;
            font-size: 20px;
        }

        .logo span {
            color: #e74c3c;
            font-size: 12px;
            display: block;
        }

        .nav-menu {
            display: flex;
            gap: 25px;
            list-style: none;
        }

        .nav-menu a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            transition: all 0.3s;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            color: #e74c3c;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
        }

        .btn-login {
            background: transparent;
            border: 2px solid #e74c3c;
            color: #e74c3c;
            padding: 8px 22px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: #e74c3c;
            color: white;
        }

        .btn-emergency {
            background: #e74c3c;
            color: white;
            padding: 8px 22px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(231, 76, 60, 0); }
            100% { box-shadow: 0 0 0 0 rgba(231, 76, 60, 0); }
        }

        .menu-icon {
            display: none;
            font-size: 28px;
            cursor: pointer;
        }

        /* ============================================
           HERO SECTION
        ============================================ */
        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: "🚒";
            font-size: 300px;
            position: absolute;
            right: -50px;
            bottom: -80px;
            opacity: 0.05;
        }

        .hero .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 48px;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content h1 span {
            color: #e74c3c;
        }

        .hero-content p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: #e74c3c;
            color: white;
            padding: 14px 35px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background: #c0392b;
            transform: translateY(-3px);
        }

        .btn-secondary {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 12px 35px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: white;
            color: #1a1a2e;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 40px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #e74c3c;
        }

        .hero-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        /* ============================================
           SERVICIOS
        ============================================ */
        .services {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 36px;
            color: #1a1a2e;
            margin-bottom: 15px;
        }

        .section-title p {
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .services-grid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: white;
            border-radius: 20px;
            padding: 35px 25px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .service-icon {
            font-size: 60px;
            margin-bottom: 20px;
        }

        .service-card h3 {
            font-size: 22px;
            margin-bottom: 15px;
        }

        .service-card p {
            color: #666;
            margin-bottom: 20px;
        }

        .service-link {
            color: #e74c3c;
            text-decoration: none;
            font-weight: 500;
        }

        /* ============================================
           SOBRE NOSOTROS
        ============================================ */
        .about {
            padding: 80px 0;
            background: white;
        }

        .about .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
        }

        .about-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .about-content h2 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #1a1a2e;
        }

        .about-content h2 span {
            color: #e74c3c;
        }

        .about-content p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .about-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 25px;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .feature i {
            color: #e74c3c;
            font-size: 20px;
        }

        /* ============================================
           NOTICIAS
        ============================================ */
        .news {
            padding: 80px 0;
            background: #f8f9fa;
        }

        .news-grid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .news-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .news-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }

        .news-content {
            padding: 20px;
        }

        .news-date {
            color: #e74c3c;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .news-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .news-card p {
            color: #666;
            font-size: 14px;
        }

        /* ============================================
           VIDEO DESTACADO
        ============================================ */
        .video-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            text-align: center;
        }

        .video-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .video-placeholder {
            background: #2c3e50;
            border-radius: 20px;
            padding: 60px;
            margin-top: 30px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .video-placeholder:hover {
            background: #34495e;
        }

        .video-placeholder i {
            font-size: 80px;
            color: #e74c3c;
        }

        /* ============================================
           ESTACIONES
        ============================================ */
        .stations {
            padding: 80px 0;
            background: white;
        }

        .stations-grid {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .station-card {
            background: #f8f9fa;
            border-radius: 20px;
            padding: 25px;
            text-align: center;
        }

        .station-card i {
            font-size: 50px;
            color: #e74c3c;
            margin-bottom: 15px;
        }

        /* ============================================
           FOOTER
        ============================================ */
        .footer {
            background: #1a1a2e;
            color: white;
            padding: 60px 0 30px;
        }

        .footer .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-col h4 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 10px;
        }

        .footer-col a {
            color: #bbb;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-col a:hover {
            color: #e74c3c;
        }

        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .footer-social a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .footer-social a:hover {
            background: #e74c3c;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: #888;
            font-size: 14px;
        }

        /* ============================================
           RESPONSIVE
        ============================================ */
        @media (max-width: 1000px) {
            .hero .container,
            .about .container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-content h1 {
                font-size: 36px;
            }
            
            .nav-menu {
                display: none;
            }
            
            .menu-icon {
                display: block;
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 28px;
            }
            
            .section-title h2 {
                font-size: 28px;
            }
            
            .hero-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-info">
                <span><i class="fas fa-phone-alt"></i> 3202498131</span>
                <span><i class="fas fa-envelope"></i> info@bomberoschoco.gov.co</span>
                <span><i class="fas fa-map-marker-alt"></i> Quibdó, Chocó - Colombia</span>
            </div>
            <div class="top-bar-social">
                <!-- Enlaces a redes sociales actualizados con URLs reales -->
                <a href="https://www.facebook.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-twitter"></i></a>
                <a href="https://www.instagram.com/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/c/bomberoschoco" target="_blank" rel="noopener noreferrer"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="nav-container">
            <div class="logo">
                <img src="assets/images/logo.png" alt="Logo Bomberos Chocó" onerror="this.src='https://via.placeholder.com/60x60?text=🚒'">
                <h2><?php echo SITE_NAME; ?><span>Protegiendo a Quibdó</span></h2>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="active">Inicio</a></li>
                <li><a href="sobre-nosotros.php">Sobre Nosotros</a></li>
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
            <div class="menu-icon">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Protegiendo a <span>Quibdó</span> y todo el <span>Chocó</span></h1>
                <p>Más de 50 años de servicio comprometido con la seguridad y bienestar de nuestra comunidad. Atención de emergencias las 24 horas del día.</p>
                <div class="hero-buttons">
                    <a href="ciudadano/reportar.php" class="btn-primary"><i class="fas fa-phone-alt"></i> Reportar Emergencia</a>
                    <a href="sobre-nosotros.php" class="btn-secondary">Conócenos</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div>Años de servicio</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100+</div>
                        <div>Bomberos activos</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">10k+</div>
                        <div>Emergencias atendidas</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="assets/images/logan-weaver-lgnwvr-oZXjSB2LtuU-unsplash.jpg" alt="Bomberos en acción - Logan Weaver">
            </div>
        </div>
    </section>

    <!-- Servicios -->
    <section class="services" id="servicios">
        <div class="section-title">
            <h2>Nuestros Servicios</h2>
            <p>Ofrecemos atención integral en gestión de riesgos y emergencias</p>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">🔥</div>
                <h3>Atención de Incendios</h3>
                <p>Respuesta inmediata a incendios estructurales, forestales e industriales con equipos de última generación.</p>
                <a href="#" class="service-link">Más información →</a>
            </div>
            <div class="service-card">
                <div class="service-icon">🚑</div>
                <h3>Atención Prehospitalaria</h3>
                <p>Servicio de ambulancias con personal altamente capacitado para emergencias médicas.</p>
                <a href="#" class="service-link">Más información →</a>
            </div>
            <div class="service-card">
                <div class="service-icon">🌊</div>
                <h3>Rescate en Inundaciones</h3>
                <p>Unidades especializadas en rescate acuático para inundaciones y desastres naturales.</p>
                <a href="#" class="service-link">Más información →</a>
            </div>
            <div class="service-card">
                <div class="service-icon">📚</div>
                <h3>Capacitaciones</h3>
                <p>Cursos y talleres en prevención de incendios, primeros auxilios y gestión de riesgos.</p>
                <a href="#" class="service-link">Más información →</a>
            </div>
            <div class="service-card">
                <div class="service-icon">🛡️</div>
                <h3>Equipos de Seguridad</h3>
                <p>Venta y asesoría en equipos de protección personal y sistemas contra incendios.</p>
                <a href="#" class="service-link">Más información →</a>
            </div>
            <div class="service-card">
                <div class="service-icon">🏢</div>
                <h3>Inspecciones Técnicas</h3>
                <p>Inspecciones de seguridad en edificaciones, industrias y establecimientos comerciales.</p>
                <a href="#" class="service-link">Más información →</a>
            </div>
        </div>
    </section>

    <!-- Sobre Nosotros -->
    <section class="about">
        <div class="container">
            <div class="about-image">
                <img src="https://images.unsplash.com/photo-1599837565318-67429bde7162?w=600&h=450&fit=crop" alt="Estación de Bomberos">
            </div>
            <div class="about-content">
                <h2>Sobre <span>Nosotros</span></h2>
                <p>El Benemérito Cuerpo de Bomberos del Chocó JRM fue fundado el 20 de julio de 1975, con la misión de proteger la vida, el ambiente y los bienes de la comunidad quibdoseña ante situaciones de riesgo y emergencias.</p>
                <p>Contamos con personal altamente calificado, equipos de última tecnología y una flota de unidades especializadas para atender todo tipo de emergencias las 24 horas del día, los 365 días del año.</p>
                <div class="about-features">
                    <div class="feature"><i class="fas fa-check-circle"></i> 50+ años de experiencia</div>
                    <div class="feature"><i class="fas fa-check-circle"></i> Atención 24/7</div>
                    <div class="feature"><i class="fas fa-check-circle"></i> Personal certificado</div>
                    <div class="feature"><i class="fas fa-check-circle"></i> Equipos modernos</div>
                </div>
                <a href="sobre-nosotros.php" class="btn-primary" style="margin-top: 25px; display: inline-block;">Leer más →</a>
            </div>
        </div>
    </section>

    <!-- Video Destacado -->
    <section class="video-section">
        <div class="video-container">
            <h2>Video Institucional</h2>
            <p>Conoce nuestro trabajo y compromiso con la comunidad</p>
            <div class="video-placeholder" onclick="window.open('https://www.youtube.com/watch?v=x8d7NnVCFMY&t=4s', '_blank')">
                <i class="fab fa-youtube"></i>
                <p style="margin-top: 10px;">Haz clic para ver el video</p>
            </div>
        </div>
    </section>

    <!-- Estaciones -->
    <section class="stations">
        <div class="section-title">
            <h2>Nuestras Estaciones</h2>
            <p>Estamos presentes en diferentes puntos de Quibdó para una respuesta más rápida</p>
        </div>
        <div class="stations-grid">
            <div class="station-card">
                <i class="fas fa-building"></i>
                <h3>Estación Central</h3>
                <p>Avenida de las Américas #20-54</p>
                <p>Barrio Versalles</p>
                <p>Tel: 3202498131</p>
            </div>
            <div class="station-card">
                <i class="fas fa-building"></i>
                <h3>Estación Norte</h3>
                <p>Barrio El Porvenir</p>
                <p>Calle 30 # 15-20</p>
                <p>Tel: 3202498132</p>
            </div>
            <div class="station-card">
                <i class="fas fa-building"></i>
                <h3>Estación Sur</h3>
                <p>Barrio La Playita</p>
                <p>Carrera 10 # 25-08</p>
                <p>Tel: 3202498133</p>
            </div>
        </div>
    </section>

    <!-- Noticias -->
    <section class="news">
        <div class="section-title">
            <h2>Últimas Noticias</h2>
            <p>Mantente informado sobre nuestras actividades y novedades</p>
        </div>
        <div class="news-grid">
            <div class="news-card">
                <div class="news-image" style="background-image: url('assets/images/noticia1.jpg')"></div>
                <div class="news-content">
                    <div class="news-date"><i class="far fa-calendar-alt"></i> 15 de Mayo, 2026</div>
                    <h3>Nuevo centro de entrenamiento para bomberos</h3>
                    <p>Inauguramos modernas instalaciones para la capacitación de nuestro personal en técnicas de rescate.</p>
                    <a href="#" class="service-link">Leer más →</a>
                </div>
            </div>
            <div class="news-card">
                <div class="news-image" style="background-image: url('assets/images/noticia2.jpg')"></div>
                <div class="news-content">
                    <div class="news-date"><i class="far fa-calendar-alt"></i> 10 de Mayo, 2026</div>
                    <h3>Campaña de prevención de incendios en colegios</h3>
                    <p>Iniciamos jornadas educativas en las instituciones educativas de Quibdó.</p>
                    <a href="#" class="service-link">Leer más →</a>
                </div>
            </div>
            <div class="news-card">
                <div class="news-image" style="background-image: url('assets/images/noticia3.jpg')"></div>
                <div class="news-content">
                    <div class="news-date"><i class="far fa-calendar-alt"></i> 5 de Mayo, 2026</div>
                    <h3>Convenio con la alcaldía para equipos de rescate</h3>
                    <p>Recibimos nueva flota de ambulancias y equipos de rescate acuático.</p>
                    <a href="#" class="service-link">Leer más →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4><?php echo SITE_NAME; ?></h4>
                    <p>Protegiendo a Quibdó y el Chocó con compromiso, honor y valentía desde 1975.</p>
                    <div class="footer-social">
                        <!-- Enlaces a redes sociales actualizados con URLs reales (mismos enlaces) -->
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
        // Menú responsive
        document.querySelector('.menu-icon').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('show');
        });
    </script>
</body>
</html>