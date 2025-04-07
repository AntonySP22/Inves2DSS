<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('paciente');
require_once __DIR__ . '/../../db/db.php';

$paciente_id = $_SESSION['id'];

// Obtener información básica del paciente
$stmt = $conexion->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();

// Obtener historial médico completo
$historial = [];

// 1. Enfermedades crónicas
$stmt = $conexion->prepare("
    SELECT e.nombre, e.descripcion, pe.fecha_diagnostico 
    FROM paciente_enfermedades pe
    JOIN enfermedades e ON pe.enfermedad_id = e.id
    WHERE pe.paciente_id = ?
    ORDER BY pe.fecha_diagnostico DESC
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$historial['enfermedades'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 2. Historial de citas
$stmt = $conexion->prepare("
    SELECT c.id, c.fecha_hora, c.motivo, c.estado, c.notas_medico,
           u.nombre as medico_nombre, p.especialidad
    FROM citas c
    JOIN usuarios u ON c.medico_id = u.id
    JOIN perfiles_medicos p ON u.id = p.usuario_id
    WHERE c.paciente_id = ?
    ORDER BY c.fecha_hora DESC
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$historial['citas'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Historial de tratamientos
$stmt = $conexion->prepare("
    SELECT t.*, u.nombre as medico_nombre
    FROM tratamientos t
    JOIN usuarios u ON t.medico_id = u.id
    WHERE t.paciente_id = ?
    ORDER BY t.fecha_inicio DESC
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$historial['tratamientos'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. Registros de salud (presión, glucosa, etc.)
$stmt = $conexion->prepare("
    SELECT tipo_registro, valor, fecha_registro, notas
    FROM registros_salud
    WHERE paciente_id = ?
    ORDER BY fecha_registro DESC
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$historial['registros'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - <?= htmlspecialchars($paciente['nombre']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #ffffff;
            --sidebar-width: 250px;
        }
        
        body {
            background-color: var(--secondary-color);
            font-family: 'Poppins', sans-serif;
            color: var(--dark-color);
            overflow-x: hidden;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            min-height: 100vh;
            width: var(--sidebar-width);
            position: fixed;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            top: 0;
            left: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            margin: 0.25rem 0;
        }
        
        .sidebar .nav-link:hover {
            color: var(--light-color);
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            color: var(--light-color);
            border-left: 3px solid var(--accent-color);
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }
        
        .user-profile {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 1rem;
        }
        
        .user-profile h5 {
            color: white;
            margin-bottom: 0.25rem;
        }
        
        .user-profile small {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            width: calc(100% - var(--sidebar-width));
            position: relative;
            min-height: 100vh;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1.5rem rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 1rem 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: #e9ecef;
        }
        
        .empty-state h5 {
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
        }
        
        .empty-state p {
            margin-bottom: 1.5rem;
            color: #6c757d;
        }
        
        /* Estilos específicos para historial */
        .historial-section {
            margin-bottom: 2.5rem;
        }
        
        .historial-section h2 {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
        }
        
        .registro-item {
            border-left: 3px solid var(--accent-color);
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        
        .registro-fecha {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .timeline {
            position: relative;
            padding-left: 1.5rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.5rem;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--primary-color);
            border: 2px solid white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-profile">
                <i class="fas fa-user-circle fa-4x text-white"></i>
                <h5><?= htmlspecialchars($paciente['nombre']) ?></h5>
                <small>Paciente</small>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="citas.php">
                        <i class="fas fa-calendar"></i> Mis Citas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tratamientos.php">
                        <i class="fas fa-prescription-bottle"></i> Mis Tratamientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="historial.php">
                        <i class="fas fa-history"></i> Historial Médico
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="salud.php">
                        <i class="fas fa-heartbeat"></i> Mi Salud
                    </a>
                </li>
            </ul>
            
            <div class="mt-auto p-3">
                <a href="logout.php?redirect=../../index.php" class="btn btn-outline-light btn-sm w-100 mt-3">
                    <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="main-content">
            <div class="welcome-header">
                <h1><i class="fas fa-history me-2"></i> Mi Historial Médico Completo</h1>
                <p class="text-muted">Registro completo de tu actividad médica</p>
            </div>
            
            <!-- Sección de Enfermedades Crónicas -->
            <div class="historial-section">
                <h2><i class="fas fa-diagnoses me-2"></i>Enfermedades Crónicas</h2>
                
                <?php if (!empty($historial['enfermedades'])): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="timeline">
                                <?php foreach ($historial['enfermedades'] as $enfermedad): ?>
                                <div class="timeline-item mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($enfermedad['nombre']) ?></h5>
                                            <p class="card-text"><?= htmlspecialchars($enfermedad['descripcion']) ?></p>
                                            <div class="registro-fecha">
                                                <i class="fas fa-calendar-day me-1"></i>
                                                Diagnosticado el <?= date('d/m/Y', strtotime($enfermedad['fecha_diagnostico'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="fas fa-heartbeat"></i>
                                <h5>No hay enfermedades registradas</h5>
                                <p>No se han diagnosticado enfermedades crónicas en tu historial.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sección de Citas Médicas -->
            <div class="historial-section">
                <h2><i class="fas fa-calendar-check me-2"></i>Citas Médicas</h2>
                
                <?php if (!empty($historial['citas'])): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="timeline">
                                <?php foreach ($historial['citas'] as $cita): ?>
                                <div class="timeline-item mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="card-title">Dr. <?= htmlspecialchars($cita['medico_nombre']) ?></h5>
                                                <span class="badge bg-<?= $cita['estado'] == 'completada' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($cita['estado']) ?>
                                                </span>
                                            </div>
                                            <h6 class="card-subtitle mb-2 text-muted"><?= $cita['especialidad'] ?></h6>
                                            <p class="card-text"><strong>Motivo:</strong> <?= htmlspecialchars($cita['motivo']) ?></p>
                                            <?php if (!empty($cita['notas_medico'])): ?>
                                                <div class="alert alert-light mt-2">
                                                    <strong>Notas del médico:</strong>
                                                    <p class="mb-0"><?= htmlspecialchars($cita['notas_medico']) ?></p>
                                                </div>
                                            <?php endif; ?>
                                            <div class="registro-fecha">
                                                <i class="fas fa-clock me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h5>No hay citas registradas</h5>
                                <p>No se encontraron citas médicas en tu historial.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sección de Tratamientos -->
            <div class="historial-section">
                <h2><i class="fas fa-prescription-bottle-alt me-2"></i>Tratamientos Médicos</h2>
                
                <?php if (!empty($historial['tratamientos'])): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="timeline">
                                <?php foreach ($historial['tratamientos'] as $tratamiento): ?>
                                <div class="timeline-item mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <h5 class="card-title"><?= htmlspecialchars($tratamiento['nombre_tratamiento']) ?></h5>
                                                <span class="badge bg-<?= $tratamiento['estado'] == 'activo' ? 'success' : 'secondary' ?>">
                                                    <?= ucfirst($tratamiento['estado']) ?>
                                                </span>
                                            </div>
                                            <p class="card-text"><?= htmlspecialchars($tratamiento['descripcion']) ?></p>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-muted">
                                                    <i class="fas fa-user-md me-1"></i>
                                                    Dr. <?= htmlspecialchars($tratamiento['medico_nombre']) ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    <?= date('d/m/Y', strtotime($tratamiento['fecha_inicio'])) ?>
                                                    <?php if ($tratamiento['fecha_fin']): ?>
                                                        - <?= date('d/m/Y', strtotime($tratamiento['fecha_fin'])) ?>
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="fas fa-prescription-bottle"></i>
                                <h5>No hay tratamientos registrados</h5>
                                <p>No se encontraron tratamientos médicos en tu historial.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sección de Registros de Salud -->
            <div class="historial-section">
                <h2><i class="fas fa-chart-line me-2"></i>Registros de Salud</h2>
                
                <?php if (!empty($historial['registros'])): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Fecha</th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historial['registros'] as $registro): ?>
                                        <tr>
                                            <td><?= ucfirst(htmlspecialchars($registro['tipo_registro'])) ?></td>
                                            <td><?= htmlspecialchars($registro['valor']) ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($registro['fecha_registro'])) ?></td>
                                            <td><?= !empty($registro['notas']) ? htmlspecialchars($registro['notas']) : '--' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="empty-state">
                                <i class="fas fa-chart-line"></i>
                                <h5>No hay registros de salud</h5>
                                <p>No se encontraron mediciones o controles en tu historial.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>