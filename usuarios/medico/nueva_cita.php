<?php
session_start();
// Primero incluimos las dependencias
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../db/db.php';

// Verificamos el rol y obtenemos el ID del médico
verificarRol('medico');

if (!isset($_SESSION['id'])) {
    die("Error: No se ha identificado al médico. Por favor, inicie sesión nuevamente.");
}

$medico_id = $_SESSION['id'];



$query = "SELECT u.nombre, pm.especialidad 
          FROM usuarios u 
          INNER JOIN perfiles_medicos pm ON u.id = pm.usuario_id 
          WHERE u.id = ? AND u.rol = 'medico'";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    error_log("No se encontró médico con ID: $medico_id");
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

// Agregar después de obtener la información del médico

// Consulta para obtener los pacientes asignados al médico
$query_pacientes = "
    SELECT DISTINCT u.id, u.nombre, u.correo 
    FROM usuarios u
    INNER JOIN citas c ON u.id = c.paciente_id
    WHERE c.medico_id = ? AND u.rol = 'paciente'
    ORDER BY u.nombre ASC
";

$stmt = $conexion->prepare($query_pacientes);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$result_pacientes = $stmt->get_result();

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = $_POST['motivo'];
    $fecha_hora = $fecha . ' ' . $hora;

    // Verificar disponibilidad
    $query_verificar = "SELECT COUNT(*) as total FROM citas WHERE medico_id = ? AND fecha_hora = ?";
    $stmt = $conexion->prepare($query_verificar);
    $stmt->bind_param("is", $medico_id, $fecha_hora);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    if ($resultado['total'] > 0) {
        echo "error";
    } else {
        $query_insertar = "INSERT INTO citas (paciente_id, medico_id, fecha_hora, motivo, estado) VALUES (?, ?, ?, ?, 'pendiente')";
        $stmt = $conexion->prepare($query_insertar);
        $stmt->bind_param("iiss", $paciente_id, $medico_id, $fecha_hora, $motivo);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    }
    exit;
}

// Consulta de citas del médico
$query_citas = "SELECT c.*, 
                       p.nombre as nombre_paciente, 
                       pm.especialidad 
                FROM citas c 
                INNER JOIN usuarios p ON c.paciente_id = p.id 
                INNER JOIN perfiles_medicos pm ON c.medico_id = pm.usuario_id 
                WHERE c.medico_id = ? 
                ORDER BY c.fecha_hora DESC";
$stmt = $conexion->prepare($query_citas);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$result_citas = $stmt->get_result();



?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Salud - Citas</title>
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



        .main-content {
            margin-left: 0;
            width: 100%;
            padding: 20px;
        }

        @media (min-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 100;
                padding: 48px 0 0;
                box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            }

            .main-content {
                margin-left: var(--sidebar-width);
            }
        }
    </style>


    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css" rel="stylesheet">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>
    <div>
        <div class="d-flex">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar p-0">
                <div class="user-profile">
                    <i class="fas fa-user-md fa-3x text-white mb-3"></i>
                    <h5>Dr. <?= htmlspecialchars($medico['nombre']) ?></h5>
                    <small><?= htmlspecialchars($medico['especialidad']) ?></small>
                </div>

                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
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
        </div>

        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <div class="welcome-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1>Nueva Cita</h1>
                </div>

            </div>

            <div class="car mb-4">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Agendar Nueva Cita</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="paciente_id" class="form-label">Paciente</label>
                                            <select class="form-select" id="paciente_id" name="paciente_id" required>
                                                <option value="">Seleccione un paciente</option>
                                                <?php while ($paciente = $result_pacientes->fetch_assoc()): ?>
                                                    <option value="<?= $paciente['id'] ?>">
                                                        <?= htmlspecialchars($paciente['nombre']) ?>
                                                        (<?= htmlspecialchars($paciente['correo']) ?>)
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="fecha" class="form-label">Fecha</label>
                                                <input type="date" class="form-control" id="fecha" name="fecha"
                                                    min="<?= date('Y-m-d') ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="hora" class="form-label">Hora</label>
                                                <input type="time" class="form-control" id="hora" name="hora" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="motivo" class="form-label">Motivo de la consulta</label>
                                            <textarea class="form-control" id="motivo" name="motivo" rows="3"
                                                required></textarea>
                                        </div>


                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <a href="citas.php" class="btn btn-secondary me-md-2">Cancelar</a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-calendar-check me-1"></i>
                                                Solicitar Cita
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form)
                        })
                        .then(response => response.text())
                        .then(data => {
                            if (data.includes('error')) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Ya existe una cita programada para esa fecha y hora.',
                                    confirmButtonColor: '#4e73df'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: 'La cita ha sido agendada correctamente.',
                                    confirmButtonColor: '#4e73df'
                                }).then(() => {
                                    window.location.href = 'citas.php';
                                });
                            }
                        });
                });
            });
        </script>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Font Awesome -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

</body>

</html>