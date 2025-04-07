<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('paciente');
require_once __DIR__ . '/../../db/db.php';

$paciente_id = $_SESSION['id'];

// Obtener información básica del paciente
$stmt = $conexion->prepare("SELECT nombre, edad, sexo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$paciente = $stmt->get_result()->fetch_assoc();

// Obtener registros de salud recientes
$stmt = $conexion->prepare("
    SELECT tipo_registro, valor, fecha_registro, notas
    FROM registros_salud
    WHERE paciente_id = ?
    ORDER BY fecha_registro DESC
    LIMIT 10
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$registros_salud = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener enfermedades crónicas
$stmt = $conexion->prepare("
    SELECT e.nombre, pe.fecha_diagnostico
    FROM paciente_enfermedades pe
    JOIN enfermedades e ON pe.enfermedad_id = e.id
    WHERE pe.paciente_id = ?
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$enfermedades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener último tratamiento activo
$stmt = $conexion->prepare("
    SELECT t.nombre_tratamiento, t.fecha_inicio, u.nombre as medico_nombre
    FROM tratamientos t
    JOIN usuarios u ON t.medico_id = u.id
    WHERE t.paciente_id = ? AND t.estado = 'activo'
    ORDER BY t.fecha_inicio DESC
    LIMIT 1
");
$stmt->bind_param("i", $paciente_id);
$stmt->execute();
$ultimo_tratamiento = $stmt->get_result()->fetch_assoc();

// Tipos de registros disponibles
$tipos_registros = ['presión arterial', 'glucosa', 'peso', 'temperatura', 'frecuencia cardíaca'];
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        
        /* Estilos específicos para salud.php */
        .health-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .health-card {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .health-card h3 {
            font-size: 1.1rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .health-card .value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .health-card .label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 1.5rem;
        }
        
        .registro-item {
            border-left: 3px solid var(--accent-color);
            padding-left: 1rem;
            margin-bottom: 1rem;
        }
        
        .registro-item .valor {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .registro-item .fecha {
            font-size: 0.85rem;
            color: #6c757d;
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
            
            .health-summary {
                grid-template-columns: 1fr;
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
                    <a class="nav-link" href="historial.php">
                        <i class="fas fa-history"></i> Historial Médico
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="salud.php">
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
                <h1><i class="fas fa-heartbeat me-2"></i> Mi Estado de Salud</h1>
                <p class="text-muted">Resumen y seguimiento de tus indicadores de salud</p>
            </div>
            
            <!-- Resumen de salud -->
            <div class="health-summary">
                <div class="health-card">
                    <h3><i class="fas fa-user me-2"></i>Información Básica</h3>
                    <div class="value"><?= htmlspecialchars($paciente['nombre']) ?></div>
                    <div class="label">Nombre</div>
                    <div class="value mt-2"><?= htmlspecialchars($paciente['edad']) ?> años</div>
                    <div class="label">Edad</div>
                    <div class="value mt-2"><?= htmlspecialchars($paciente['sexo']) ?></div>
                    <div class="label">Sexo</div>
                </div>
                
                <div class="health-card">
                    <h3><i class="fas fa-diagnoses me-2"></i>Enfermedades</h3>
                    <?php if (!empty($enfermedades)): ?>
                        <div class="value"><?= count($enfermedades) ?></div>
                        <div class="label">Crónicas diagnosticadas</div>
                        <ul class="list-unstyled mt-2">
                            <?php foreach ($enfermedades as $enfermedad): ?>
                                <li><i class="fas fa-circle-notch fa-xs me-1"></i> <?= htmlspecialchars($enfermedad['nombre']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="value">0</div>
                        <div class="label">Enfermedades crónicas</div>
                        <p class="mt-2 mb-0 text-muted">No registra enfermedades</p>
                    <?php endif; ?>
                </div>
                
                <div class="health-card">
                    <h3><i class="fas fa-prescription-bottle-alt me-2"></i>Tratamiento Actual</h3>
                    <?php if ($ultimo_tratamiento): ?>
                        <div class="value"><?= htmlspecialchars($ultimo_tratamiento['nombre_tratamiento']) ?></div>
                        <div class="label">En curso</div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-user-md me-1"></i>
                                Dr. <?= htmlspecialchars($ultimo_tratamiento['medico_nombre']) ?>
                            </small>
                        </div>
                        <div class="mt-1">
                            <small class="text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Desde <?= date('d/m/Y', strtotime($ultimo_tratamiento['fecha_inicio'])) ?>
                            </small>
                        </div>
                    <?php else: ?>
                        <div class="value">Ninguno</div>
                        <div class="label">Tratamiento activo</div>
                        <p class="mt-2 mb-0 text-muted">No tiene tratamientos en curso</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Gráficos de salud -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-line me-2"></i> Evolución de Salud
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="healthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-bell me-2"></i> Recordatorios
                        </div>
                        <div class="card-body">
                            <?php if ($ultimo_tratamiento): ?>
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-prescription-bottle-alt me-2"></i> Tratamiento Activo</h5>
                                    <p class="mb-1"><?= htmlspecialchars($ultimo_tratamiento['nombre_tratamiento']) ?></p>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Inició el <?= date('d/m/Y', strtotime($ultimo_tratamiento['fecha_inicio'])) ?>
                                    </small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-syringe me-2"></i> Próxima Cita</h5>
                                <p>No tienes citas programadas</p>
                                <a href="citas.php" class="btn btn-sm btn-outline-warning">Agendar cita</a>
                            </div>
                            
                            <div class="alert alert-light">
                                <h5><i class="fas fa-plus-circle me-2"></i> Nuevo Registro</h5>
                                <p>Registra tus indicadores de salud</p>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoRegistroModal">
                                    <i class="fas fa-plus me-1"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Registros recientes -->
            <div class="card mt-4">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i> Registros Recientes
                </div>
                <div class="card-body">
                    <?php if (!empty($registros_salud)): ?>
                        <div class="row">
                            <?php foreach ($registros_salud as $registro): ?>
                            <div class="col-md-6 mb-3">
                                <div class="registro-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="valor"><?= htmlspecialchars($registro['valor']) ?></span>
                                            <span class="ms-2"><?= ucfirst(htmlspecialchars($registro['tipo_registro'])) ?></span>
                                        </div>
                                        <div class="fecha">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($registro['fecha_registro'])) ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($registro['notas'])): ?>
                                        <div class="mt-1 text-muted small">
                                            <i class="fas fa-comment me-1"></i>
                                            <?= htmlspecialchars($registro['notas']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="historial.php" class="btn btn-outline-primary">
                            <i class="fas fa-history me-1"></i> Ver historial completo
                        </a>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <h5>No hay registros de salud</h5>
                            <p>Aún no has registrado ningún indicador de salud.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoRegistroModal">
                                <i class="fas fa-plus me-1"></i> Agregar primer registro
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para nuevo registro -->
    <div class="modal fade" id="nuevoRegistroModal" tabindex="-1" aria-labelledby="nuevoRegistroModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoRegistroModalLabel"><i class="fas fa-plus me-2"></i>Nuevo Registro de Salud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="guardar_registro.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="tipo_registro" class="form-label">Tipo de Registro</label>
                            <select class="form-select" id="tipo_registro" name="tipo_registro" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($tipos_registros as $tipo): ?>
                                    <option value="<?= htmlspecialchars($tipo) ?>"><?= ucfirst(htmlspecialchars($tipo)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="valor" class="form-label">Valor</label>
                            <input type="text" class="form-control" id="valor" name="valor" required>
                        </div>
                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas (opcional)</label>
                            <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <script>
        // Configuración del gráfico
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('healthChart').getContext('2d');
            
            // Datos de ejemplo (deberías reemplazarlos con datos reales de tu base de datos)
            const healthChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [
                        {
                            label: 'Presión Arterial (sistólica)',
                            data: [120, 118, 122, 119, 121, 117],
                            borderColor: '#4e73df',
                            backgroundColor: 'rgba(78, 115, 223, 0.05)',
                            tension: 0.1
                        },
                        {
                            label: 'Glucosa (mg/dL)',
                            data: [95, 97, 102, 98, 99, 96],
                            borderColor: '#1cc88a',
                            backgroundColor: 'rgba(28, 200, 138, 0.05)',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>