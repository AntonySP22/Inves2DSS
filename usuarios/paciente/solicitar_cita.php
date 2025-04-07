<?php
session_start();
require_once '../../db/db.php';
$paciente_id = $_SESSION['id'];



// Verificar autorización del calendario
$query_auth = "SELECT is_authorized FROM google_calendar_auth WHERE usuario_id = ?";
$stmt_auth = $conexion->prepare($query_auth);
$stmt_auth->bind_param("i", $paciente_id);
$stmt_auth->execute();
$result_auth = $stmt_auth->get_result();
$is_authorized = $result_auth->num_rows > 0 ? $result_auth->fetch_assoc()['is_authorized'] : false;

// Consulta del paciente
$query = "SELECT id, nombre, correo FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$result = $stmt->get_result();
$paciente = $result->fetch_assoc();

if (!$paciente) {
    die("No se encontró información del paciente");
}


// Modificar la consulta de médicos para usar la tabla perfiles_medicos
$query_medicos = "SELECT u.id, u.nombre, pm.especialidad 
                 FROM usuarios u 
                 INNER JOIN perfiles_medicos pm ON u.id = pm.usuario_id 
                 WHERE u.rol = 'medico'";
$result_medicos = $conexion->query($query_medicos);



// Modificar el procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['authorize_calendar'])) {
        require_once __DIR__ . '/calendario/authorize.php';
        $auth_url = getAuthUrl();
        header('Location: ' . $auth_url);
        exit;
    }

    $medico_id = $_POST['id_medico'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $motivo = $_POST['motivo'] ?? '';

    if (isset($_POST['authorize_calendar']) && !$is_authorized) {
        require_once __DIR__ . '/calendario/authorize.php';
        $auth_url = getAuthUrl();
        header('Location: ' . $auth_url);
        exit;
    }

    // Si el usuario ya está autorizado o no quiere autorizar, continuar con la creación del evento
    if ($is_authorized) {
    } else {
        $event_details['sendUpdates'] = 'none';
    }

    // Validación de campos
    if (empty($medico_id) || empty($fecha) || empty($hora) || empty($motivo)) {
        $error = "Todos los campos son obligatorios";
    } else {
        $fecha_hora = $fecha . ' ' . $hora;

        // Obtener información del médico
        $query_medico = "SELECT u.*, pm.especialidad 
                        FROM usuarios u 
                        INNER JOIN perfiles_medicos pm ON u.id = pm.usuario_id 
                        WHERE u.id = ?";
        $stmt_medico = $conexion->prepare($query_medico);
        $stmt_medico->bind_param("i", $medico_id);
        $stmt_medico->execute();
        $result_medico = $stmt_medico->get_result();
        $medico = $result_medico->fetch_assoc();

        // Insertar la cita
        $query = "INSERT INTO citas (paciente_id, medico_id, fecha_hora, motivo, estado) 
                 VALUES (?, ?, ?, ?, 'pendiente')";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("iiss", $paciente_id, $medico_id, $fecha_hora, $motivo);

        if ($stmt->execute()) {
            require_once '../paciente/calendario/create_event.php';

            try {

                $fecha_hora = $fecha . ' ' . $hora;
                $fecha_hora_timestamp = strtotime($fecha_hora);

                $event_details = [
                    'summary' => 'Cita Médica - ' . $paciente['nombre'],
                    'location' => 'Clínica Médica',
                    'description' => "Cita médica con Dr. {$medico['nombre']}\n" .
                        "Paciente: {$paciente['nombre']}\n" .
                        "Motivo: {$motivo}",
                    'start' => [
                        'dateTime' => date('c', $fecha_hora_timestamp),
                        'timeZone' => 'America/El_Salvador',
                    ],
                    'end' => [
                        'dateTime' => date('c', strtotime('+1 hour', $fecha_hora_timestamp)),
                        'timeZone' => 'America/El_Salvador',
                    ],
                    'paciente_email' => $paciente['correo'],
                    'medico_nombre' => $medico['nombre'] 
                ];

                // Pasamos también el ID del paciente
                $result = createGoogleCalendarEvent($event_details, $paciente_id);

                if ($result) {
                    $success = true;
                }
            } catch (Exception $e) {
                $error = "La cita se registró pero hubo un error al crear el evento: " . $e->getMessage();
            }
        } else {
            $error = "Error al registrar la cita: " . $conexion->error;
        }
    }
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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

    <?php
    // Agregar después de la consulta del paciente
    $query_auth = "SELECT is_authorized FROM google_calendar_auth WHERE usuario_id = ?";
    $stmt_auth = $conexion->prepare($query_auth);
    $stmt_auth->bind_param("i", $paciente_id);
    $stmt_auth->execute();
    $result_auth = $stmt_auth->get_result();
    $is_authorized = $result_auth->num_rows > 0 ? $result_auth->fetch_assoc()['is_authorized'] : false;


    ?>

    <?php if (!$is_authorized && !isset($_GET['auth_success'])) { ?>
        <div class="main-content">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-calendar me-2"></i>Autorización Requerida</h4>
                            </div>
                            <div class="card-body text-center py-5">
                                <i class="fas fa-calendar-plus fa-4x text-primary mb-4"></i>
                                <h5 class="mb-3">Autorización de Calendario de Google</h5>
                                <p class="mb-4">Para poder agendar citas y recibir notificaciones, necesitamos acceso a tu calendario de Google.</p>
                                <form method="POST" action="">
                                    <input type="hidden" name="authorize_calendar" value="1">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fab fa-google me-2"></i>
                                        Autorizar Calendario
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
        exit;
    }
    ?>

    <div class="main-content">
        <div class="welcome-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Nueva Cita</h1>
                <h5><?= htmlspecialchars($paciente['correo']) ?></h5>
                
            </div>
        </div>


        <div class="car mb-4">
            <div class="container py-5">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white">
                                <h4 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Solicitar Nueva Cita</h4>
                            </div>
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>

                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label for="id_medico" class="form-label">Médico</label>
                                        <select class="form-select" id="id_medico" name="id_medico" required>
                                            <option value="">Seleccione un médico</option>
                                            <?php while ($medico = $result_medicos->fetch_assoc()): ?>
                                                <option value="<?= $medico['id'] ?>">
                                                    Dr. <?= htmlspecialchars($medico['nombre']) ?> -
                                                    <?= htmlspecialchars($medico['especialidad']) ?>
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


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Font Awesome -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

</body>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($success)): ?>
    <script>
        Swal.fire({
            title: '¡Éxito!',
            text: 'La cita se ha registrado correctamente',
            icon: 'success',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#4e73df'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = './citas.php';
            }
        });
    </script>
<?php endif; ?>

<?php if (isset($error)): ?>
    <script>
        Swal.fire({
            title: '¡Error!',
            text: '<?php echo $error; ?>',
            icon: 'error',
            confirmButtonText: 'Aceptar',
            confirmButtonColor: '#4e73df'
        });
    </script>
<?php endif; ?>
</body>

</html>