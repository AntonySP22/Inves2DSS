<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('admin');
require_once __DIR__ . '/../../db/db.php';

// Obtener datos para el resumen
$stats = [
    'tratamientos_activos' => $conexion->query("SELECT COUNT(*) FROM tratamientos WHERE estado = 'activo'")->fetch_row()[0],
    'total_usuarios' => $conexion->query("SELECT COUNT(*) FROM usuarios")->fetch_row()[0],
    'total_pacientes' => $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'paciente'")->fetch_row()[0],
    'total_medicos' => $conexion->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'medico'")->fetch_row()[0],
    'citas_hoy' => $conexion->query("SELECT COUNT(*) FROM citas WHERE DATE(fecha_hora) = CURDATE()")->fetch_row()[0]
];

$actividades = $conexion->query("
    SELECT l.*, u.nombre 
    FROM logs_acceso l
    JOIN usuarios u ON l.usuario_id = u.id
    ORDER BY l.fecha_hora DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

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
    <title>Resumen del Sistema</title>
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
            padding-top: 40px;
        }

        .dashboard-container {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 10px;
        }

        .section-title {
            color: var(--primary-color);
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }

        /* Estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 400;
            margin: 5px 0;
            color: var(--text-color);
        }

        .stat-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin: 0;
        }

        /* Tabla de actividad */
        .activity-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .activity-table th, 
        .activity-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .activity-table th {
            font-weight: 500;
            color: #555;
            background-color:rgb(174, 172, 179);
        }

        /* Alertas */
        .alerts-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .alert-item {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .alert-item:last-child {
            border-bottom: none;
        }

        .alert-user {
            font-weight: 500;
            display: block;
        }

        .alert-message {
            font-size: 0.9rem;
        }

        .alert-time {
            color: #777;
            font-size: 0.85rem;
            display: block;
            margin-top: 3px;
        }

        /* Botones */
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

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1 class="dashboard-title">
            <i class="fas fa-tachometer-alt me-2"></i>Resumen del Sistema
        </h1>
        
        <!-- Tratamientos Activos -->
        <h2 class="section-title">Tratamientos Activos</h2>
        <div class="stat-card">
            <div class="stat-value"><?= $stats['tratamientos_activos'] ?></div>
        </div>
        
        <!-- Actividad Reciente -->
        <h2 class="section-title">Actividad Reciente</h2>
        <table class="activity-table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($actividades as $actividad): ?>
                <tr>
                    <td><?= htmlspecialchars($actividad['nombre']) ?></td>
                    <td><?= htmlspecialchars($actividad['accion']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($actividad['fecha_hora'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Alertas Recientes -->
        <h2 class="section-title">Alertas Recientes</h2>
        <ul class="alerts-list">
            <?php foreach($alertas as $alerta): ?>
            <li class="alert-item">
                <span class="alert-user"><?= htmlspecialchars($alerta['nombre']) ?></span>
                <span class="alert-message"><?= htmlspecialchars($alerta['mensaje']) ?></span>
                <span class="alert-time"><?= date('d/m H:i', strtotime($alerta['fecha_envio'])) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <!-- Botón para volver -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-admin">
                <i class="fas fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>