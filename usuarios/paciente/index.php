<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('paciente');
require_once __DIR__ . '/../../db/db.php';

$paciente_id = $_SESSION['id'];

// Consultas preparadas para seguridad
$stmt = $conexion->prepare("SELECT nombre, edad, sexo, correo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();

// Obtener enfermedades del paciente
$stmt = $conexion->prepare("
    SELECT e.nombre, e.descripcion, pe.fecha_diagnostico 
    FROM paciente_enfermedades pe
    JOIN enfermedades e ON pe.enfermedad_id = e.id
    WHERE pe.paciente_id = ?
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$enfermedades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener próximas citas
$stmt = $conexion->prepare("
    SELECT c.id, c.fecha_hora, c.motivo, c.estado, 
           u.nombre as medico_nombre, p.especialidad
    FROM citas c
    JOIN usuarios u ON c.medico_id = u.id
    JOIN perfiles_medicos p ON u.id = p.usuario_id
    WHERE c.paciente_id = ? AND c.fecha_hora >= NOW()
    ORDER BY c.fecha_hora ASC
    LIMIT 3
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$proximas_citas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener tratamientos activos
$stmt = $conexion->prepare("
    SELECT t.id, t.nombre_tratamiento, t.descripcion, 
           t.fecha_inicio, t.fecha_fin, t.estado,
           u.nombre as medico_nombre
    FROM tratamientos t
    JOIN usuarios u ON t.medico_id = u.id
    WHERE t.paciente_id = ? AND t.estado = 'activo'
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$tratamientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener medicamentos para cada tratamiento
$medicamentos_por_tratamiento = [];
foreach ($tratamientos as $tratamiento) {
    $stmt = $conexion->prepare("
        SELECT nombre_medicamento, dosis, frecuencia, via_administracion
        FROM medicamentos
        WHERE tratamiento_id = ?
    ");
    $stmt->bind_param("i", $tratamiento['id']);
    $stmt->execute();
    $medicamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $medicamentos_por_tratamiento[$tratamiento['id']] = $medicamentos;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Salud - <?= htmlspecialchars($paciente['nombre']) ?></title>
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
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            min-height: 100vh;
            width: var(--sidebar-width);
            position: fixed;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
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
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
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
        
        .list-group-item {
            border-left: 0;
            border-right: 0;
            padding: 1.25rem 1.5rem;
            transition: background-color 0.2s ease;
        }
        
        .list-group-item:first-child {
            border-top: 0;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
        
        .badge-success {
            background-color: var(--accent-color);
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.25rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .welcome-header {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .welcome-header h1 {
            font-weight: 600;
            color: var(--dark-color);
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
                    <a class="nav-link active" href="index.php">
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
                    <a class="nav-link" href="historial.php">
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
                <div class="d-flex justify-content-between align-items-center">
                    <h1>Bienvenido, <?= htmlspecialchars($paciente['nombre']) ?></h1>
                    <a href="solicitar_cita.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-1"></i> Nueva Cita
                    </a>
                </div>
            </div>
            
            <!-- Enfermedades crónicas -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-diagnoses me-2"></i>Mis Enfermedades Crónicas
                </div>
                <div class="card-body">
                    <?php if (!empty($enfermedades)): ?>
                        <div class="list-group">
                            <?php foreach ($enfermedades as $enfermedad): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-2"><?= htmlspecialchars($enfermedad['nombre']) ?></h5>
                                        <p class="mb-2"><?= htmlspecialchars($enfermedad['descripcion']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-day me-1"></i>
                                            Diagnosticado el <?= date('d/m/Y', strtotime($enfermedad['fecha_diagnostico'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary">Crónica</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-heartbeat"></i>
                            <h5>No tienes enfermedades registradas</h5>
                            <p>Actualmente no hay enfermedades crónicas asociadas a tu historial médico.</p>
                            <a href="salud.php" class="btn btn-outline-primary">
                                <i class="fas fa-plus-circle me-1"></i> Actualizar mi historial
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row">
                <!-- Próximas citas -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-calendar-day me-2"></i>Próximas Citas
                        </div>
                        <div class="card-body">
                            <?php if (!empty($proximas_citas)): ?>
                                <div class="list-group">
                                    <?php foreach ($proximas_citas as $cita): ?>
                                    <a href="cita.php?id=<?= $cita['id'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="mb-2">Dr. <?= htmlspecialchars($cita['medico_nombre']) ?></h5>
                                                <p class="mb-2"><i class="fas fa-comment-medical me-1"></i> <?= htmlspecialchars($cita['motivo']) ?></p>
                                                <small class="text-muted">
                                                    <i class="fas fa-star me-1"></i> <?= $cita['especialidad'] ?>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge badge-success"><?= ucfirst($cita['estado']) ?></span>
                                                <div class="mt-2">
                                                    <small class="text-primary">
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <h5>No tienes citas programadas</h5>
                                    <p>Actualmente no hay citas médicas agendadas en tu calendario.</p>
                                    <a href="solicitar_cita.php" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus me-1"></i> Agendar una cita
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Tratamientos activos -->
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-prescription-bottle-alt me-2"></i>Mis Tratamientos Activos
                        </div>
                        <div class="card-body">
                            <?php if (!empty($tratamientos)): ?>
                                <div class="accordion" id="tratamientosAccordion">
                                    <?php foreach ($tratamientos as $tratamiento): ?>
                                    <div class="card border-0 mb-2">
                                        <div class="card-header bg-light" id="heading<?= $tratamiento['id'] ?>">
                                            <h2 class="mb-0">
                                                <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $tratamiento['id'] ?>" aria-expanded="true" aria-controls="collapse<?= $tratamiento['id'] ?>">
                                                    <span>
                                                        <i class="fas fa-prescription me-2"></i>
                                                        <?= htmlspecialchars($tratamiento['nombre_tratamiento']) ?>
                                                    </span>
                                                    <i class="fas fa-chevron-down"></i>
                                                </button>
                                            </h2>
                                        </div>
                                        
                                        <div id="collapse<?= $tratamiento['id'] ?>" class="collapse" aria-labelledby="heading<?= $tratamiento['id'] ?>" data-parent="#tratamientosAccordion">
                                            <div class="card-body">
                                                <p><?= htmlspecialchars($tratamiento['descripcion']) ?></p>
                                                
                                                <?php if (!empty($medicamentos_por_tratamiento[$tratamiento['id']])): ?>
                                                    <h6 class="mt-3"><i class="fas fa-pills me-1"></i> Medicamentos:</h6>
                                                    <ul class="list-group">
                                                        <?php foreach ($medicamentos_por_tratamiento[$tratamiento['id']] as $medicamento): ?>
                                                        <li class="list-group-item">
                                                            <div class="d-flex justify-content-between">
                                                                <strong><?= htmlspecialchars($medicamento['nombre_medicamento']) ?></strong>
                                                                <span class="badge bg-primary"><?= htmlspecialchars($medicamento['dosis']) ?></span>
                                                            </div>
                                                            <div class="text-muted small mt-1">
                                                                <i class="fas fa-redo me-1"></i> <?= htmlspecialchars($medicamento['frecuencia']) ?> | 
                                                                <i class="fas fa-syringe me-1"></i> <?= htmlspecialchars($medicamento['via_administracion']) ?>
                                                            </div>
                                                        </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                                
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-md me-1"></i>
                                                            Dr. <?= htmlspecialchars($tratamiento['medico_nombre']) ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-md-6 text-end">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar-check me-1"></i>
                                                            Desde <?= date('d/m/Y', strtotime($tratamiento['fecha_inicio'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-prescription-bottle"></i>
                                    <h5>No tienes tratamientos activos</h5>
                                    <p>Actualmente no hay tratamientos médicos en curso.</p>
                                    <a href="historial.php" class="btn btn-outline-primary">
                                        <i class="fas fa-history me-1"></i> Ver mi historial
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>