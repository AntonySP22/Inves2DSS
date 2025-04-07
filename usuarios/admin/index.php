<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('admin');
require_once __DIR__ . '/../../db/db.php';

// Obtener estadísticas
$stats = [
    'total_usuarios' => $conexion->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0],
    'total_pacientes' => $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'paciente'")->fetch_row()[0],
    'total_medicos' => $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'medico'")->fetch_row()[0],
    'citas_hoy' => $conexion->query("SELECT COUNT(*) FROM citas WHERE DATE(fecha_hora) = CURDATE()")->fetch_row()[0],
];
?>
<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('admin');
require_once __DIR__ . '/../../db/db.php';

// Obtener estadísticas
$stats = [
    'total_usuarios' => $conexion->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0],
    'total_pacientes' => $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'paciente'")->fetch_row()[0],
    'total_medicos' => $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'medico'")->fetch_row()[0],
    'citas_hoy' => $conexion->query("SELECT COUNT(*) FROM citas WHERE DATE(fecha_hora) = CURDATE()")->fetch_row()[0],
   
];

// Obtener actividad reciente
$actividades = $conexion->query("
    SELECT l.*, u.nombre 
    FROM logs_acceso l
    JOIN usuarios u ON l.usuario_id = u.id
    ORDER BY l.fecha_hora DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Obtener alertas recientes
$alertas = $conexion->query("
    SELECT n.*, u.nombre 
    FROM notificaciones n
    JOIN usuarios u ON n.usuario_id = u.id
    WHERE n.tipo = 'alerta'
    ORDER BY n.fecha_envio DESC
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --light-color: #fff;
            --border-radius: 10px;
            --error-color: #dc3545;
        }

        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .dashboard-container {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 40px;
        }

        .dashboard-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
        }

        .stat-card {
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card-primary {
            background-color: var(--primary-color);
            color: var(--light-color);
        }

        .stat-card-success {
            background-color: #28a745;
            color: var(--light-color);
        }

        .stat-card-info {
            background-color: #17a2b8;
            color: var(--light-color);
        }

        .stat-card-warning {
            background-color: #ffc107;
            color: var(--text-color);
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-color);
        }

        .nav-pills .nav-link {
            color: var(--primary-color);
        }

        .btn-admin {
            background-color: var(--primary-color);
            border: none;
            border-radius: var(--border-radius);
            color: var(--light-color);
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-admin:hover {
            background-color: #5549ff;
            color: var(--light-color);
            transform: translateY(-2px);
        }
         
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-container">
            <h2 class="dashboard-title">
                <i class="fas fa-user-shield me-2"></i>Panel de Administración
            </h2>
            <div class="btn-group">

    </div>
            
            <!-- Tarjetas de estadísticas -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card stat-card-primary">
                        <h5><i class="fas fa-users me-2"></i>Usuarios</h5>
                        <h2><?= $stats['total_usuarios'] ?></h2>
                        <small>Total registrados</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card stat-card-success">
                        <h5><i class="fas fa-user-injured me-2"></i>Pacientes</h5>
                        <h2><?= $stats['total_pacientes'] ?></h2>
                        <small>En el sistema</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card stat-card-info">
                        <h5><i class="fas fa-user-md me-2"></i>Médicos</h5>
                        <h2><?= $stats['total_medicos'] ?></h2>
                        <small>Activos</small>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="stat-card stat-card-warning">
                        <h5><i class="fas fa-calendar-check me-2"></i>Citas Hoy</h5>
                        <h2><?= $stats['citas_hoy'] ?></h2>
                        <small>Programadas</small>
                    </div>
                </div>
            </div>
            
            <!-- Navegación -->
            <ul class="nav nav-pills mb-4 justify-content-center">
                <li class="nav-item mx-2">
                    <a class="nav-link active" href="resumen.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Resumen
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="usuarios.php">
                        <i class="fas fa-users me-1"></i> Usuarios
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="citas.php">
                        <i class="fas fa-calendar me-1"></i> Citas
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="configuracion.php">
                        <i class="fas fa-cog me-1"></i> Configuración
                    </a>
                </li>
            </ul>
            
            <!-- Acciones rápidas -->
            <div class="text-center mt-4">
                <h5 class="mb-3">Acciones Rápidas</h5>
                <div class="d-flex justify-content-center flex-wrap">
                    <a href="../../registro.php" class="btn btn-admin mx-2 mb-2">
                        <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
                    </a>
                    <a href="reportes.php" class="btn btn-admin mx-2 mb-2">
                        <i class="fas fa-file-alt me-1"></i> Generar Reporte
                    </a>
                    <a href="backup.php" class="btn btn-admin mx-2 mb-2">
                        <i class="fas fa-database me-1"></i> Respaldar Datos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>