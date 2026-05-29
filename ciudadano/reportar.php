<?php
require_once '../includes/config.php';
require_once '../includes/conexion.php';
verificarRol('ciudadano');

$exito = null;
$error = null;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'];
    $ubicacion = $_POST['ubicacion'];
    $lat = $_POST['latitud'] ?: null;
    $lng = $_POST['longitud'] ?: null;
    $descripcion = $_POST['descripcion'];
    $gravedad = $_POST['gravedad'];
    
    $stmt = $pdo->prepare("INSERT INTO emergencias (usuario_id, tipo, ubicacion_texto, latitud, longitud, descripcion, gravedad) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if($stmt->execute([$_SESSION['usuario_id'], $tipo, $ubicacion, $lat, $lng, $descripcion, $gravedad])) {
        $exito = "✅ Emergencia reportada exitosamente. Los bomberos han sido notificados.";
    } else {
        $error = "❌ Error al reportar la emergencia. Intenta nuevamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Emergencia - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo-area img {
            height: 50px;
        }
        
        .logo-area h1 {
            color: #e74c3c;
            font-size: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-card h2 {
            color: #e74c3c;
            margin-bottom: 20px;
            text-align: center;
        }
        
        select, input, textarea {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
        }
        
        select:focus, input:focus, textarea:focus {
            outline: none;
            border-color: #e74c3c;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .gps-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin: 10px 0;
        }
        
        .gps-btn:hover {
            background: #2980b9;
        }
        
        #map {
            height: 300px;
            margin: 15px 0;
            border-radius: 10px;
        }
        
        button[type="submit"] {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        
        button[type="submit"]:hover {
            background: #c0392b;
        }
        
        .exito {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #e74c3c;
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-area">
            <img src="../assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/50'">
            <h1>Reportar Emergencia</h1>
        </div>
        <a href="dashboard.php" style="color: #e74c3c;">← Volver</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2>🚨 Formulario de Reporte de Emergencia</h2>
            
            <?php if($exito): ?>
                <div class="exito"><?php echo $exito; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="reporteForm">
                <select name="tipo" required>
                    <option value="">Seleccione tipo de emergencia</option>
                    <option value="incendio">🔥 Incendio</option>
                    <option value="inundacion">🌊 Inundación</option>
                    <option value="accidente">🚗 Accidente de tránsito</option>
                    <option value="otros">📞 Otros</option>
                </select>
                
                <input type="text" name="ubicacion" placeholder="Ubicación (dirección o referencia)" required>
                
                <button type="button" class="gps-btn" onclick="obtenerUbicacion()">
                    📡 Usar mi ubicación actual
                </button>
                
                <div id="map"></div>
                <input type="hidden" name="latitud" id="latitud">
                <input type="hidden" name="longitud" id="longitud">
                
                <textarea name="descripcion" placeholder="Describa detalladamente lo que está sucediendo..." rows="4" required></textarea>
                
                <select name="gravedad">
                    <option value="baja">🟢 Baja - Sin riesgo inmediato</option>
                    <option value="media" selected="selected">🟡 Media - Requiere atención</option>
                    <option value="alta">🔴 Alta - Emergencia crítica</option>
                </select>
                
                <button type="submit">🚨 REPORTAR EMERGENCIA</button>
            </form>
            
            <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>
        </div>
    </div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([5.6948, -76.6612], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        var marker;
        
        function obtenerUbicacion() {
            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    
                    document.getElementById('latitud').value = lat;
                    document.getElementById('longitud').value = lng;
                    
                    if(marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);
                    map.setView([lat, lng], 16);
                    
                    // Obtener dirección de la ubicación
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                        .then(response => response.json())
                        .then(data => {
                            if(data.display_name) {
                                document.querySelector('input[name="ubicacion"]').value = data.display_name;
                            }
                        });
                }, function() {
                    alert("No se pudo obtener tu ubicación. Verifica los permisos.");
                });
            } else {
                alert("Tu navegador no soporta GPS");
            }
        }
    </script>
</body>
</html>