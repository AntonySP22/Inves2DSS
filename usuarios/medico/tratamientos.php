<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('medico');
require_once __DIR__ . '/../../db/db.php';

if (!isset($_SESSION['id'])) {
    die("Error: No se ha identificado al médico. Por favor, inicie sesión nuevamente.");
}

$medico_id = $_SESSION['id'];

// Obtener información del médico
$query_medico = "
    SELECT u.nombre, u.correo, p.especialidad, p.licencia_medica 
    FROM usuarios u
    INNER JOIN perfiles_medicos p ON u.id = p.usuario_id
    WHERE u.id = ? AND u.rol = 'medico'
";
$stmt = $conexion->prepare($query_medico);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$medico = $stmt->get_result()->fetch_assoc();

// Manejar cambio de estado si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    $tratamiento_id = $_POST['tratamiento_id'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    $query = "UPDATE tratamientos SET estado = ? WHERE id = ? AND medico_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sii", $nuevo_estado, $tratamiento_id, $medico_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje_exito'] = "Estado del tratamiento actualizado correctamente";
        header("Location: tratamientos.php");
        exit();
    } else {
        $error = "Error al actualizar el estado: " . $conexion->error;
    }
}

// Obtener parámetros de filtrado
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_paciente = isset($_GET['paciente']) ? $_GET['paciente'] : '';
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir consulta base
$query_base = "
    SELECT t.*, u.nombre as paciente_nombre, u.id as paciente_id,
           (SELECT COUNT(*) FROM medicamentos m WHERE m.tratamiento_id = t.id) as total_medicamentos
    FROM tratamientos t
    JOIN usuarios u ON t.paciente_id = u.id
    WHERE t.medico_id = ?
";

// Añadir condiciones de filtrado
$conditions = [];
$params = [$medico_id];
$types = "i";

if ($filtro_estado) {
    $conditions[] = "t.estado = ?";
    $params[] = $filtro_estado;
    $types .= "s";
}

if ($filtro_paciente) {
    $conditions[] = "u.nombre LIKE ?";
    $params[] = "%$filtro_paciente%";
    $types .= "s";
}

if ($filtro_fecha_inicio) {
    $conditions[] = "t.fecha_inicio >= ?";
    $params[] = $filtro_fecha_inicio;
    $types .= "s";
}

if ($filtro_fecha_fin) {
    $conditions[] = "t.fecha_fin <= ?";
    $params[] = $filtro_fecha_fin;
    $types .= "s";
}

if (!empty($conditions)) {
    $query_base .= " AND " . implode(" AND ", $conditions);
}

// Ordenación
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_inicio_desc';
switch ($orden) {
    case 'nombre_asc':
        $query_base .= " ORDER BY t.nombre_tratamiento ASC";
        break;
    case 'nombre_desc':
        $query_base .= " ORDER BY t.nombre_tratamiento DESC";
        break;
    case 'fecha_inicio_asc':
        $query_base .= " ORDER BY t.fecha_inicio ASC";
        break;
    case 'fecha_fin_asc':
        $query_base .= " ORDER BY t.fecha_fin ASC";
        break;
    case 'fecha_fin_desc':
        $query_base .= " ORDER BY t.fecha_fin DESC";
        break;
    default:
        $query_base .= " ORDER BY t.fecha_inicio DESC";
}

// Obtener tratamientos
$stmt = $conexion->prepare($query_base);

if ($types !== "i") {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, $medico_id);
}

$stmt->execute();
$tratamientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener pacientes para filtro
$query_pacientes = "
    SELECT DISTINCT u.id, u.nombre 
    FROM tratamientos t
    JOIN usuarios u ON t.paciente_id = u.id
    WHERE t.medico_id = ?
    ORDER BY u.nombre ASC
";
$stmt = $conexion->prepare($query_pacientes);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$pacientes_filtro = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Contar tratamientos por estado
$query_estados = "SELECT estado, COUNT(*) as total FROM tratamientos WHERE medico_id = ? GROUP BY estado";
$stmt = $conexion->prepare($query_estados);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$estados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Contar total de tratamientos
$query_total = "SELECT COUNT(*) as total FROM tratamientos WHERE medico_id = ?";
$stmt = $conexion->prepare($query_total);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$total_tratamientos = $stmt->get_result()->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tratamientos - Dr. <?= htmlspecialchars($medico['nombre']) ?></title>
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
        
        .badge-activo {
            background-color: var(--accent-color);
        }
        .badge-completado {
            background-color: #36b9cc;
        }
        .badge-suspendido {
            background-color: #f6c23e;
        }
        .badge-cancelado {
            background-color: #e74a3b;
        }
        .tratamiento-card {
            transition: transform 0.2s;
        }
        .tratamiento-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .filter-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .pagination .page-link {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
                        <a class="nav-link active" href="tratamientos.php">
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
                <!-- Mensajes de éxito/error -->
                <?php if (isset($_SESSION['mensaje_exito'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['mensaje_exito'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['mensaje_exito']); ?>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-prescription-bottle me-2"></i> Tratamientos</h2>
                    <div>
                        <a href="nuevo_tratamiento.php" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Nuevo Tratamiento
                        </a>
                        <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosCollapse">
                            <i class="fas fa-filter me-1"></i> Filtros
                        </button>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="collapse mb-4" id="filtrosCollapse">
                    <div class="filter-card">
                        <form method="get" class="row g-3">
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos</option>
                                    <option value="activo" <?= $filtro_estado == 'activo' ? 'selected' : '' ?>>Activo</option>
                                    <option value="suspendido" <?= $filtro_estado == 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                                    <option value="completado" <?= $filtro_estado == 'completado' ? 'selected' : '' ?>>Completado</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="paciente" class="form-label">Paciente</label>
                                <input type="text" class="form-control" id="paciente" name="paciente" 
                                       value="<?= htmlspecialchars($filtro_paciente) ?>" placeholder="Nombre del paciente">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label">Fecha inicio desde</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                       value="<?= htmlspecialchars($filtro_fecha_inicio) ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label">Fecha fin hasta</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                       value="<?= htmlspecialchars($filtro_fecha_fin) ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="orden" class="form-label">Ordenar por</label>
                                <select class="form-select" id="orden" name="orden">
                                    <option value="fecha_inicio_desc" <?= $orden == 'fecha_inicio_desc' ? 'selected' : '' ?>>Fecha inicio (reciente)</option>
                                    <option value="fecha_inicio_asc" <?= $orden == 'fecha_inicio_asc' ? 'selected' : '' ?>>Fecha inicio (antiguo)</option>
                                    <option value="fecha_fin_desc" <?= $orden == 'fecha_fin_desc' ? 'selected' : '' ?>>Fecha fin (reciente)</option>
                                    <option value="fecha_fin_asc" <?= $orden == 'fecha_fin_asc' ? 'selected' : '' ?>>Fecha fin (antiguo)</option>
                                    <option value="nombre_asc" <?= $orden == 'nombre_asc' ? 'selected' : '' ?>>Nombre (A-Z)</option>
                                    <option value="nombre_desc" <?= $orden == 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z-A)</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i> Aplicar Filtros
                                </button>
                                <a href="tratamientos.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-1"></i> Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Resumen de tratamientos -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-card stat-card-primary">
                            <h5><i class="fas fa-prescription-bottle-alt me-2"></i> Total Tratamientos</h5>
                            <h3><?= $total_tratamientos ?></h3>
                            <small>Registrados</small>
                        </div>
                    </div>
                    
                    <?php foreach ($estados as $estado): ?>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card <?= 
                            $estado['estado'] == 'activo' ? 'stat-card-success' : 
                            ($estado['estado'] == 'completado' ? 'stat-card-info' : 'stat-card-primary')
                        ?>">
                            <h5><i class="fas fa-<?= 
                                $estado['estado'] == 'activo' ? 'check-circle' : 
                                ($estado['estado'] == 'completado' ? 'clipboard-check' : 'pause-circle')
                            ?> me-2"></i> <?= ucfirst($estado['estado']) ?></h5>
                            <h3><?= $estado['total'] ?></h3>
                            <small>Tratamientos</small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Listado de tratamientos -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i> Listado de Tratamientos
                        </div>
                        <div class="text-muted">
                            Mostrando <?= count($tratamientos) ?> de <?= $total_tratamientos ?> registros
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tratamientos)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Paciente</th>
                                            <th>Tratamiento</th>
                                            <th>Medicamentos</th>
                                            <th>Fecha Inicio</th>
                                            <th>Fecha Fin</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tratamientos as $tratamiento): ?>
                                            <tr class="tratamiento-card">
                                                <td>
                                                    <a href="paciente_detalle.php?id=<?= $tratamiento['paciente_id'] ?>">
                                                        <?= htmlspecialchars($tratamiento['paciente_nombre']) ?>
                                                    </a>
                                                </td>
                                                <td><?= htmlspecialchars($tratamiento['nombre_tratamiento']) ?></td>
                                                <td>
                                                    <?= $tratamiento['total_medicamentos'] ?>
                                                    <small class="text-muted d-block"><?= $tratamiento['descripcion'] ? substr($tratamiento['descripcion'], 0, 30) . '...' : '' ?></small>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($tratamiento['fecha_inicio'])) ?></td>
                                                <td>
                                                    <?= $tratamiento['fecha_fin'] ? date('d/m/Y', strtotime($tratamiento['fecha_fin'])) : 'Indefinido' ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= 
                                                        $tratamiento['estado'] == 'activo' ? 'badge-activo' : 
                                                        ($tratamiento['estado'] == 'completado' ? 'badge-completado' : 
                                                        ($tratamiento['estado'] == 'suspendido' ? 'badge-suspendido' : 'badge-cancelado'))
                                                    ?>">
                                                        <?= ucfirst($tratamiento['estado']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="tratamiento_detalle.php?id=<?= $tratamiento['id'] ?>" class="btn btn-sm btn-info me-1" title="Ver detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="editar_tratamiento.php?id=<?= $tratamiento['id'] ?>" class="btn btn-sm btn-warning me-1" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-secondary" 
                                                                data-bs-toggle="modal" data-bs-target="#cambiarEstadoModal"
                                                                data-id="<?= $tratamiento['id'] ?>" 
                                                                data-estado="<?= $tratamiento['estado'] ?>"
                                                                title="Cambiar estado">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginación -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mt-4">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php else: ?>
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-prescription-bottle-alt fa-4x text-muted mb-3"></i>
                                <h4>No se encontraron tratamientos</h4>
                                <p class="text-muted">No hay tratamientos que coincidan con los criterios de búsqueda.</p>
                                <a href="nuevo_tratamiento.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Crear nuevo tratamiento
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar estado -->
    <div class="modal fade" id="cambiarEstadoModal" tabindex="-1" aria-labelledby="cambiarEstadoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="tratamiento_id" id="modalTratamientoId">
                    <input type="hidden" name="cambiar_estado" value="1">
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="cambiarEstadoModalLabel">Cambiar estado del tratamiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nuevo_estado" class="form-label">Seleccione el nuevo estado:</label>
                            <select class="form-select" id="nuevo_estado" name="nuevo_estado" required>
                                <option value="activo">Activo</option>
                                <option value="suspendido">Suspendido</option>
                                <option value="completado">Completado</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Configurar modal para cambiar estado
        var cambiarEstadoModal = document.getElementById('cambiarEstadoModal');
        cambiarEstadoModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var tratamientoId = button.getAttribute('data-id');
            var estadoActual = button.getAttribute('data-estado');
            
            var modal = this;
            modal.querySelector('#modalTratamientoId').value = tratamientoId;
            modal.querySelector('#nuevo_estado').value = estadoActual;
        });
        
        // Mostrar mensajes de alerta con timeout
        var alertas = document.querySelectorAll('.alert');
        alertas.forEach(function(alerta) {
            setTimeout(function() {
                var bsAlert = new bootstrap.Alert(alerta);
                bsAlert.close();
            }, 5000);
        });
    </script>
</body>
</html>