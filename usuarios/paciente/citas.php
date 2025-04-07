<?php
session_start();

$paciente_id = $_SESSION['id'];


require_once '../../db/db.php'; 

// Consulta para obtener los datos del paciente
$query = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$result = $stmt->get_result();
$paciente = $result->fetch_assoc();

// Verificar si se encontró el paciente
if (!$paciente) {
    die("No se encontró información del paciente");
}



// Consulta para obtener las citas del paciente
$query_citas = "SELECT c.*, u.nombre as nombre_medico, pm.especialidad 
                FROM citas c 
                INNER JOIN usuarios u ON c.medico_id = u.id 
                INNER JOIN perfiles_medicos pm ON u.id = pm.usuario_id 
                WHERE c.paciente_id = ? 
                ORDER BY c.fecha_hora DESC";
$stmt = $conexion->prepare($query_citas);
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$result_citas = $stmt->get_result();


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

        .card .badge {
            font-size: 0.85rem;
            padding: 0.5em 0.8em;
        }

        .card-body .text-truncate {
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .shadow-sm {
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
            transition: all 0.3s ease;
        }

        .shadow-sm:hover {
            box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
            transform: translateY(-2px);
        }

        .bg-info {
            background-color: #36b9cc !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }


        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 1rem 1.5rem;
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


        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #f8f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease-out;
        }

        .loader {
            text-align: center;
        }

        .loader .spinner-border {
            width: 3rem;
            height: 3rem;
        }

        .loader p {
            color: var(--primary-color);
            font-weight: 500;
            margin-top: 0.5rem;
        }

        .fadeOut {
            opacity: 0;
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');
            const mainContent = document.getElementById('mainContent');


            function hidePreloader() {
                preloader.classList.add('fadeOut');
                setTimeout(() => {
                    preloader.style.display = 'none';
                    mainContent.style.display = 'flex';
                    mainContent.style.opacity = 0;
                    setTimeout(() => {
                        mainContent.style.opacity = 1;
                    }, 50);
                }, 500);
            }

            
            setTimeout(hidePreloader, 300);
        });
    </script>
</head>

<body>
    <div id="preloader">
        <div class="loader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando...</p>
        </div>
    </div>

    <div style="display: none;" id="mainContent">
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
                        <a class="nav-link active" href="citas.php">
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
                        <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión </a>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="welcome-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1>Mis Citas</h1>
                    <a href="solicitar_cita.php" class="btn btn-primary">
                        <i class="fas fa-calendar-plus me-1"></i> Nueva Cita
                    </a>
                </div>
            </div>


            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Mis Citas</h5>
                </div>

                <div class="card-body"  style="background-color: #f8f9fc;">
                    <?php if ($result_citas->num_rows > 0): ?>
                        <div class="row">
                            <?php while ($cita = $result_citas->fetch_assoc()): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="mb-1">
                                                        <i class="fas fa-user-md text-primary me-2"></i>
                                                        Dr. <?= htmlspecialchars($cita['nombre_medico']) ?>
                                                    </h5>
                                                    <span class="badge bg-info text-white">
                                                        <i class="fas fa-stethoscope me-1"></i>
                                                        <?= htmlspecialchars($cita['especialidad']) ?>
                                                    </span>
                                                </div>
                                                <span class="badge bg-<?= $cita['estado'] === 'pendiente' ? 'warning' : ($cita['estado'] === 'completada' ? 'success' : 'danger') ?>">
                                                    <?= ucfirst($cita['estado']) ?>
                                                </span>
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex align-items-center text-muted mb-2">
                                                    <i class="fas fa-calendar-alt me-2"></i>
                                                    <span><?= date('d/m/Y', strtotime($cita['fecha_hora'])) ?></span>
                                                </div>
                                                <div class="d-flex align-items-center text-muted">
                                                    <i class="fas fa-clock me-2"></i>
                                                    <span><?= date('h:i A', strtotime($cita['fecha_hora'])) ?></span>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <small class="text-muted d-block mb-1">Motivo de la consulta:</small>
                                                <p class="mb-0 text-truncate">
                                                    <?= htmlspecialchars($cita['motivo']) ?>
                                                </p>
                                            </div>

                                            <button class="btn btn-outline-primary btn-sm w-100"
                                                data-bs-toggle="modal"
                                                data-bs-target="#citaModal<?= $cita['id'] ?>">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Ver detalles
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal se mantiene igual -->
                                <div class="modal fade" id="citaModal<?= $cita['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detalles de la Cita</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <h6 class="fw-bold">Médico</h6>
                                                    <p>Dr. <?= htmlspecialchars($cita['nombre_medico']) ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <h6 class="fw-bold">Especialidad</h6>
                                                    <p><?= htmlspecialchars($cita['especialidad']) ?></p>
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
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <h5>No hay citas registradas</h5>
                            <p>Actualmente no tienes citas programadas.</p>
                            <a href="solicitar_cita.php" class="btn btn-primary">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Solicitar Nueva Cita
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

</body>

</html>