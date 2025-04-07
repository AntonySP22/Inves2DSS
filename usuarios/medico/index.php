<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('medico');
require_once __DIR__ . '/../../db/db.php';

// Verificar que el ID del médico está en sesión
if (!isset($_SESSION['id'])) {
    die("Error: No se ha identificado al médico. Por favor, inicie sesión nuevamente.");
}

$medico_id = $_SESSION['id'];

// Consulta mejorada con manejo de errores
$query_medico = "
    SELECT u.nombre, u.correo, p.especialidad, p.licencia_medica 
    FROM usuarios u
    INNER JOIN perfiles_medicos p ON u.id = p.usuario_id
    WHERE u.id = ? AND u.rol = 'medico'
";

$stmt = $conexion->prepare($query_medico);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Depuración: Verificar qué ID está buscando
    error_log("No se encontró médico con ID: $medico_id");

    // Consulta alternativa para verificar si existe como usuario
    $stmt = $conexion->prepare("SELECT nombre, rol FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $medico_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();

    if ($user_data) {
        error_log("Usuario existe pero no es médico. Rol: " . $user_data['rol']);
        die("Error: Su cuenta no tiene privilegios de médico. Contacte al administrador.");
    } else {
        die("Error: Su cuenta no existe en el sistema. Por favor, inicie sesión nuevamente.");
    }
}

$medico = $result->fetch_assoc();

// Obtener cantidad de pacientes (consulta preparada)
$stmt = $conexion->prepare("
    SELECT COUNT(DISTINCT paciente_id) as total_pacientes
    FROM citas
    WHERE medico_id = ?
");
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$total_pacientes = $stmt->get_result()->fetch_assoc()['total_pacientes'];

// Obtener próximas citas (consulta preparada)
$stmt = $conexion->prepare("
    SELECT c.*, u.nombre as paciente_nombre
    FROM citas c
    JOIN usuarios u ON c.paciente_id = u.id
    WHERE c.medico_id = ? AND c.fecha_hora >= NOW()
    ORDER BY c.fecha_hora ASC
    LIMIT 5
");
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$proximas_citas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Contar citas pendientes
$stmt = $conexion->prepare("
    SELECT COUNT(*) as total 
    FROM citas 
    WHERE medico_id = ? AND estado = 'pendiente' AND fecha_hora >= NOW()
");
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$total_citas_pendientes = $stmt->get_result()->fetch_assoc()['total'];

// Contar tratamientos activos
$stmt = $conexion->prepare("
    SELECT COUNT(*) as total 
    FROM tratamientos 
    WHERE medico_id = ? AND estado = 'activo'
");
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$total_tratamientos_activos = $stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Médico - Dr. <?= htmlspecialchars($medico['nombre']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #1cc88a;
            --dark-color: #5a5c69;
            --light-color: #ffffff;
        }

        body {
            background-color: var(--secondary-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #224abe 100%);
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            border-left: 3px solid transparent;
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
            margin-right: 0.5rem;
        }

        .user-profile {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-profile h5 {
            color: white;
            margin-bottom: 0.25rem;
        }

        .user-profile small {
            color: rgba(255, 255, 255, 0.6);
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 0.5rem 0.5rem 0 0 !important;
        }

        .stat-card {
            color: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            text-align: center;
        }

        .stat-card-primary {
            background-color: var(--primary-color);
        }

        .stat-card-success {
            background-color: var(--accent-color);
        }

        .stat-card-info {
            background-color: #36b9cc;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #e9ecef;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar p-0">
                <div class="user-profile">
                    <i class="fas fa-user-md fa-3x text-white mb-3"></i>
                    <h5>Dr. <?= htmlspecialchars($medico['nombre']) ?></h5>
                    <small><?= htmlspecialchars($medico['especialidad']) ?></small>
                </div>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pacientes.php">
                            <i class="fas fa-user-injured"></i> Mis Pacientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="citas.php">
                            <i class="fas fa-calendar-check"></i> Citas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tratamientos.php">
                            <i class="fas fa-prescription-bottle"></i> Tratamientos
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
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <h2 class="mb-4">Bienvenido, Dr. <?= htmlspecialchars($medico['nombre']) ?></h2>

                <!-- Tarjetas de resumen -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-card stat-card-primary">
                            <h5><i class="fas fa-user-injured me-2"></i> Pacientes</h5>
                            <h3><?= $total_pacientes ?></h3>
                            <small>Total asignados</small>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="stat-card stat-card-success">
                            <h5><i class="fas fa-calendar-day me-2"></i> Citas Pendientes</h5>
                            <h3><?= $total_citas_pendientes ?></h3>
                            <small>Por atender</small>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="stat-card stat-card-info">
                            <h5><i class="fas fa-prescription-bottle-alt me-2"></i> Tratamientos Activos</h5>
                            <h3><?= $total_tratamientos_activos ?></h3>
                            <small>En curso</small>
                        </div>
                    </div>
                </div>

                <!-- Próximas citas -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-day me-2"></i> Próximas Citas
                    </div>
                    <div class="card-body">
                        <?php if (!empty($proximas_citas)): ?>
                            <div class="list-group">
                                <?php foreach ($proximas_citas as $cita): ?>
                                    <a href="#" class="list-group-item list-group-item-action" data-bs-toggle="modal"
                                    data-bs-target="#citaModal<?= $cita['id'] ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?= htmlspecialchars($cita['paciente_nombre']) ?></h5>
                                            <small><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></small>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars($cita['motivo']) ?></p>
                                        <small class="text-muted"><?= ucfirst($cita['estado']) ?></small>
                                    </a>

                                                                        <!-- Reemplazar el contenido del modal actual con este -->
                                    <div class="modal fade" id="citaModal<?= $cita['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detalles de la Cita</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <h6 class="fw-bold">Paciente</h6>
                                                        <p><?= htmlspecialchars($cita['paciente_nombre']) ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6 class="fw-bold">Fecha y Hora</h6>
                                                        <p><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6 class="fw-bold">Estado</h6>
                                                        <span class="badge bg-<?= $cita['estado'] === 'pendiente' ? 'warning' : ($cita['estado'] === 'completada' ? 'success' : 'danger') ?>">
                                                            <?= ucfirst($cita['estado']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6 class="fw-bold">Motivo de la Consulta</h6>
                                                        <p><?= nl2br(htmlspecialchars($cita['motivo'])) ?></p>
                                                    </div>
                                                    <?php if (!empty($cita['notas_medico'])): ?>
                                                        <div class="mb-3">
                                                            <h6 class="fw-bold">Notas del Médico</h6>
                                                            <p><?= nl2br(htmlspecialchars($cita['notas_medico'])) ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h5>No tienes citas programadas</h5>
                                <p>Actualmente no hay citas médicas agendadas.</p>
                                <a href="nueva_cita.php" class="btn btn-primary mt-2">
                                    <i class="fas fa-calendar-plus me-1"></i> Agendar cita
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Acciones rápidas -->
                <div class="text-center mt-4">
                    <div class="d-flex justify-content-center flex-wrap">
                        <a href="nueva_cita.php" class="btn btn-primary mx-2 mb-2">
                            <i class="fas fa-calendar-plus me-1"></i> Nueva Cita
                        </a>
                        <a href="nuevo_tratamiento.php" class="btn btn-primary mx-2 mb-2">
                            <i class="fas fa-file-medical me-1"></i> Nuevo Tratamiento
                        </a>
                        <a href="reportes.php" class="btn btn-primary mx-2 mb-2">
                            <i class="fas fa-file-alt me-1"></i> Generar Reporte
                        </a>
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