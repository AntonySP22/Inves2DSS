<?php
require_once __DIR__ . '/../../includes/auth.php'; // Autenticación del usuario
verificarRol('medico'); // Verificar que el rol del usuario sea 'medico'
require_once __DIR__ . '/../../db/db.php';

session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Obtener los reportes disponibles
$reportes = [
    'pacientes' => 'Pacientes con Enfermedades',
    'citas' => 'Citas Pendientes',
    'tratamientos' => 'Tratamientos Activos',
];

$reporte = $_GET['reporte'] ?? 'pacientes'; // Por defecto mostramos los pacientes

// Dependiendo del reporte solicitado, la consulta SQL varía
switch ($reporte) {
    case 'citas':
        $sql = "SELECT c.id AS cita_id, u.nombre AS paciente, m.nombre AS medico, c.fecha_hora, c.motivo
                FROM citas c
                JOIN usuarios u ON c.paciente_id = u.id
                JOIN perfiles_medicos pm ON c.medico_id = pm.usuario_id
                JOIN usuarios m ON pm.usuario_id = m.id
                WHERE c.estado = 'pendiente'";
        break;

    case 'tratamientos':
        $sql = "SELECT t.id AS tratamiento_id, u.nombre AS paciente, m.nombre AS medico, t.nombre_tratamiento, t.fecha_inicio, t.estado
                FROM tratamientos t
                JOIN usuarios u ON t.paciente_id = u.id
                JOIN perfiles_medicos pm ON t.medico_id = pm.usuario_id
                JOIN usuarios m ON pm.usuario_id = m.id
                WHERE t.estado = 'activo'";
        break;

    case 'pacientes':
    default:
        $sql = "SELECT u.id AS paciente_id, u.nombre, e.nombre AS enfermedad, pe.fecha_diagnostico
                FROM usuarios u
                LEFT JOIN paciente_enfermedades pe ON u.id = pe.paciente_id
                LEFT JOIN enfermedades e ON pe.enfermedad_id = e.id
                WHERE u.rol = 'paciente' AND pe.fecha_diagnostico IS NOT NULL";
        break;
}

// Ejecutar la consulta
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Médicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .container {
            max-width: 900px;
            margin-top: 50px;
        }

        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem rgba(58, 59, 69, 0.1);
        }

        .card-header {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            font-weight: 600;
            padding: 1rem;
        }

        .list-group-item {
            background-color: white;
            border: none;
        }

        .list-group-item a {
            text-decoration: none;
            color: var(--primary-dark);
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .list-group-item a:hover {
            color: var(--primary-color);
        }

        .table th {
            background-color: #f8f9fa;
            text-transform: uppercase;
            font-size: 0.85rem;
            color: var(--dark-color);
            padding: 12px;
        }

        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .btn-back {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                <h2><i class="fas fa-file-medical me-2"></i> Reportes Médicos</h2>
            </div>
            <div class="card-body">
                <nav>
                    <ul class="list-group">
                        <?php foreach ($reportes as $clave => $nombre): ?>
                            <li class="list-group-item">
                                <a href="?reporte=<?= $clave ?>"><i class="fas fa-chevron-right me-2"></i> <?= $nombre ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <h3 class="mt-4 text-center"><?= $reportes[$reporte] ?></h3>

                <div class="table-responsive">
                    <table class="table table-striped mt-3">
                        <thead>
                            <tr>
                                <?php
                                // Mostrar encabezados de tabla dinámicamente
                                $columnas = $resultado->fetch_fields();
                                foreach ($columnas as $columna) {
                                    echo "<th>" . ucfirst(str_replace("_", " ", $columna->name)) . "</th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mostrar datos en la tabla
                            while ($row = $resultado->fetch_assoc()) {
                                echo "<tr>";
                                foreach ($row as $valor) {
                                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                                }
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Botón de Volver -->
                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-back">
                        <i class="fas fa-arrow-left me-2"></i> Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>