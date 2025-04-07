<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('medico');
require_once __DIR__ . '/../../db/db.php';

$medico_id = $_SESSION['id'];
$paciente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Verificar que el paciente es realmente paciente del médico
$stmt = $conexion->prepare("
    SELECT u.id, u.nombre, u.edad, u.sexo, u.correo
    FROM usuarios u
    WHERE u.id = ? AND u.rol = 'paciente' AND (
        EXISTS (SELECT 1 FROM citas WHERE paciente_id = u.id AND medico_id = ?) OR
        EXISTS (SELECT 1 FROM tratamientos WHERE paciente_id = u.id AND medico_id = ?)
    )
");
$stmt->bind_param("iii", $paciente_id, $medico_id, $medico_id);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();

if (!$paciente) {
    header("Location: pacientes.php");
    exit;
}

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

// Obtener citas con este médico
$stmt = $conexion->prepare("
    SELECT c.id, c.fecha_hora, c.motivo, c.estado, c.notas_medico
    FROM citas c
    WHERE c.paciente_id = ? AND c.medico_id = ?
    ORDER BY c.fecha_hora DESC
    LIMIT 5
");
$stmt->bind_param("ii", $paciente_id, $medico_id);
$stmt->execute();
$citas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener tratamientos con este médico
$stmt = $conexion->prepare("
    SELECT t.id, t.nombre_tratamiento, t.descripcion, 
           t.fecha_inicio, t.fecha_fin, t.estado
    FROM tratamientos t
    WHERE t.paciente_id = ? AND t.medico_id = ?
    ORDER BY t.fecha_inicio DESC
");
$stmt->bind_param("ii", $paciente_id, $medico_id);
$stmt->execute();
$tratamientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Mismo head que en los archivos anteriores -->
    <title>Detalle del Paciente</title>
    <title>Detalle del Paciente | Panel Médico</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #2e59d9;
            --secondary-color: #f8f9fc;
            --accent-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: var(--secondary-color);
            font-family: 'Poppins', sans-serif;
        }
        
        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .patient-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .patient-header h1 {
            font-weight: 600;
            color: var(--dark-color);
            margin: 0;
        }
        
        .btn-back {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            height: 100%;
        }
        
        .card-header {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            font-weight: 600;
            border-radius: 0.75rem 0.75rem 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
        
        .card-header i {
            margin-right: 10px;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .info-item {
            margin-bottom: 1.5rem;
        }
        
        .info-item h5 {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .info-item p {
            color: #4a4a4a;
            margin-bottom: 0;
            font-size: 1.05rem;
        }
        
        .list-group-item {
            border-left: 0;
            border-right: 0;
            padding: 1.25rem 1.5rem;
            transition: all 0.2s ease;
        }
        
        .list-group-item:first-child {
            border-top: 0;
        }
        
        .list-group-item:hover {
            background-color: rgba(78, 115, 223, 0.03);
        }
        
        .disease-name {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .disease-desc {
            color: #6c757d;
            margin-bottom: 0.75rem;
        }
        
        .date-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.75em;
            border-radius: 10rem;
            font-size: 0.85rem;
        }
        
        .badge-primary {
            background-color: var(--primary-color);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
            color: #1f2d3d;
        }
        
        .badge-success {
            background-color: var(--accent-color);
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
        }
        
        .treatment-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .treatment-desc {
            color: #6c757d;
            margin-bottom: 0.75rem;
        }
        
        .appointment-time {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .appointment-reason {
            color: #6c757d;
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .patient-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-back {
                margin-top: 1rem;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (igual que antes) -->
        
        <!-- Contenido principal -->
        <div class="main-content">
            <div class="welcome-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1>Detalle del Paciente: <?= htmlspecialchars($paciente['nombre']) ?></h1>
                    <a href="pacientes.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
            
            <!-- Información básica -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-info-circle me-2"></i>Información Básica
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5>Nombre:</h5>
                                <p><?= htmlspecialchars($paciente['nombre']) ?></p>
                            </div>
                            <div class="mb-3">
                                <h5>Edad:</h5>
                                <p><?= htmlspecialchars($paciente['edad']) ?> años</p>
                            </div>
                            <div class="mb-3">
                                <h5>Sexo:</h5>
                                <p><?= htmlspecialchars($paciente['sexo']) ?></p>
                            </div>
                            <div>
                                <h5>Correo:</h5>
                                <p><?= htmlspecialchars($paciente['correo']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enfermedades -->
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-diagnoses me-2"></i>Enfermedades Crónicas
                        </div>
                        <div class="card-body">
                            <?php if (!empty($enfermedades)): ?>
                                <div class="list-group">
                                    <?php foreach ($enfermedades as $enfermedad): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                            <h5 class="mb-2"><?= !empty($enfermedad['nombre']) ? htmlspecialchars($enfermedad['nombre']) : 'Nombre no disponible' ?></h5>
                                            <p class="mb-2"><?= !empty($enfermedad['descripcion']) ? htmlspecialchars($enfermedad['descripcion']) : 'Descripción no disponible' ?></p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar-day me-1"></i>
                                                Diagnosticado el <?= !empty($enfermedad['fecha_diagnostico']) ? date('d/m/Y', strtotime($enfermedad['fecha_diagnostico'])) : 'Fecha no disponible' ?>
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
                                    <h5>No tiene enfermedades registradas</h5>
                                    <p>No se han registrado enfermedades crónicas para este paciente.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Citas y tratamientos -->
            <div class="row">
              <!-- Citas recientes -->
<div class="col-md-6 mb-4">
    <div class="card h-100">
        <div class="card-header">
            <i class="fas fa-calendar-day me-2"></i>Citas Recientes
        </div>
        <div class="card-body">
            <?php if (!empty($citas)): ?>
                <div class="list-group">
                    <?php foreach ($citas as $cita): ?>
                    <a href="cita.php?id=<?= $cita['id'] ?>" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-2"><?= !empty($cita['fecha_hora']) ? date('d/m/Y H:i', strtotime($cita['fecha_hora'])) : 'Fecha no disponible' ?></h5>
                                <p class="mb-2"><i class="fas fa-comment-medical me-1"></i> <?= !empty($cita['motivo']) ? htmlspecialchars($cita['motivo']) : 'Motivo no disponible' ?></p>
                            </div>
                            <span class="badge <?= $cita['estado'] === 'pendiente' ? 'bg-warning' : 'bg-success' ?>">
                                <?= ucfirst($cita['estado']) ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h5>No hay citas registradas</h5>
                    <p>No se han realizado citas con este paciente.</p>
                    <a href="solicitar_cita.php?paciente_id=<?= $paciente_id ?>" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-1"></i> Agendar cita
                    </a>
                </div>
            <?php endif; ?>
            
            <!-- Botón para crear nueva cita, con mismo estilo que "Crear tratamiento" -->
            <div class="empty-state mt-3">
                <i class="fas fa-calendar-plus"></i>
                <h5>Crear nueva cita</h5>
                <p>Agrega una nueva cita para este paciente.</p>
                <a href="citas.php?paciente_id=<?= $paciente_id ?>" class="btn btn-primary">
                    <i class="fas fa-calendar-plus me-1"></i> Nueva cita
                </a>
            </div>
        </div>
    </div>
</div>
                
                <!-- Tratamientos -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-prescription-bottle-alt me-2"></i>Tratamientos
                        </div>
                        <div class="card-body">
                            <?php if (!empty($tratamientos)): ?>
                                <div class="list-group">
                                    <?php foreach ($tratamientos as $tratamiento): ?>
                                    <a href="tratamiento.php?id=<?= $tratamiento['id'] ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="mb-2"><?= htmlspecialchars($tratamiento['nombre_tratamiento']) ?></h5>
                                                <p class="mb-2"><?= htmlspecialchars(substr($tratamiento['descripcion'], 0, 100)) ?>...</p>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-check me-1"></i>
                                                    Desde <?= !empty($tratamiento['fecha_inicio']) ? date('d/m/Y', strtotime($tratamiento['fecha_inicio'])) : 'Fecha no disponible' ?>
                                                </small>

                                            </div>
                                            <span class="badge <?= $tratamiento['estado'] === 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                                <?= ucfirst($tratamiento['estado']) ?>
                                            </span>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-prescription-bottle"></i>
                                    <h5>No hay tratamientos</h5>
                                    <p>No se han registrado tratamientos para este paciente.</p>
                                    <a href="nuevo_tratamiento.php?paciente_id=<?= $paciente_id ?>" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i> Crear tratamiento
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