<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('medico');
require_once __DIR__ . '/../../db/db.php';

$medico_id = $_SESSION['id'];

// Número de pacientes por página
$pacientes_por_pagina = 14;

// Obtener el número total de pacientes
$stmt = $conexion->prepare("SELECT COUNT(DISTINCT u.id) AS total_pacientes
    FROM usuarios u
    JOIN citas c ON u.id = c.paciente_id
    WHERE c.medico_id = ?
    UNION
    SELECT COUNT(DISTINCT u.id) AS total_pacientes
    FROM usuarios u
    JOIN tratamientos t ON u.id = t.paciente_id
    WHERE t.medico_id = ?");
$stmt->bind_param("ii", $medico_id, $medico_id);
$stmt->execute();
$total_pacientes = $stmt->get_result()->fetch_assoc()['total_pacientes'];

// Calcular el número total de páginas
$total_paginas = ceil($total_pacientes / $pacientes_por_pagina);

// Obtener el número de página actual
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$pagina_actual = max(1, min($pagina_actual, $total_paginas));

// Calcular el desplazamiento
$desplazamiento = ($pagina_actual - 1) * $pacientes_por_pagina;

// Obtener pacientes del médico con paginación
$stmt = $conexion->prepare("
    SELECT DISTINCT u.id, u.nombre, u.edad, u.sexo, u.correo
    FROM usuarios u
    JOIN citas c ON u.id = c.paciente_id
    WHERE c.medico_id = ?
    UNION
    SELECT DISTINCT u.id, u.nombre, u.edad, u.sexo, u.correo
    FROM usuarios u
    JOIN tratamientos t ON u.id = t.paciente_id
    WHERE t.medico_id = ?
    LIMIT ?, ?
");
$stmt->bind_param("iiii", $medico_id, $medico_id, $desplazamiento, $pacientes_por_pagina);
$stmt->execute();
$pacientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Obtener estadísticas de cada paciente
foreach ($pacientes as &$paciente) {
    // Enfermedades del paciente
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as total_enfermedades
        FROM paciente_enfermedades
        WHERE paciente_id = ?
    ");
    $stmt->bind_param("i", $paciente['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $paciente['total_enfermedades'] = $result['total_enfermedades'];
    
    // Citas pendientes
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as citas_pendientes
        FROM citas
        WHERE paciente_id = ? AND medico_id = ? AND estado = 'pendiente'
    ");
    $stmt->bind_param("ii", $paciente['id'], $medico_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $paciente['citas_pendientes'] = $result['citas_pendientes'];
    
    // Tratamientos activos
    $stmt = $conexion->prepare("
        SELECT COUNT(*) as tratamientos_activos
        FROM tratamientos
        WHERE paciente_id = ? AND medico_id = ? AND estado = 'activo'
    ");
    $stmt->bind_param("ii", $paciente['id'], $medico_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $paciente['tratamientos_activos'] = $result['tratamientos_activos'];
}
unset($paciente); // Romper la referencia
?>

<!DOCTYPE html>
<html lang="es">
<head>
<title>Mis Pacientes | Panel Médico</title>
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
            margin: 0;
            padding: 0;
        }
        
        .main-container {
            width: 100%;
            padding: 20px;
            box-sizing: border-box;
        }
        
        .welcome-header {
            background-color: white;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 2rem;
            width: 100%;
        }
        
        .welcome-header h1 {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0;
        }
        
        .patients-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            width: 100%;
            margin-bottom: 2rem;
        }
        
        .card-header {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            font-weight: 600;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 1rem 1.5rem;
        }
        
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        
        .patients-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .patients-table th {
            background-color: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6e707e;
            border-bottom: 2px solid #e3e6f0;
        }
        
        .patients-table td {
            padding: 1rem;
            border-bottom: 1px solid #e3e6f0;
            vertical-align: middle;
        }
        
        .patients-table tr:last-child td {
            border-bottom: none;
        }
        
        .patients-table tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .badge {
            font-weight: 500;
            padding: 0.4em 0.75em;
            border-radius: 10rem;
            font-size: 0.85rem;
        }
        
        .badge-info {
            background-color: var(--primary-color);
        }
        
        .badge-warning {
            background-color: var(--warning-color);
            color: #1f2d3d;
        }
        
        .badge-success {
            background-color: var(--accent-color);
        }
        
        .btn-view {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e9ecef;
            margin-bottom: 1.5rem;
        }
        
        .empty-state h5 {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
        }
        
        .empty-state p {
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
       <div class="main-content">
            <div class="welcome-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1><i class="fas fa-user-injured me-3"></i>Mis Pacientes</h1>
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                    <a href="nuevo_paciente.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Nuevo Paciente
                    </a>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-injured me-2"></i>Pacientes Asignados
                </div>
                <div class="card-body">
                    <?php if (!empty($pacientes)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Edad</th>
                                        <th>Sexo</th>
                                        <th>Correo</th>
                                        <th>Enfermedades</th>
                                        <th>Citas Pendientes</th>
                                        <th>Tratamientos Activos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pacientes as $paciente): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($paciente['nombre']) ?></td>
                                        <td><?= htmlspecialchars($paciente['edad']) ?></td>
                                        <td><?= htmlspecialchars($paciente['sexo']) ?></td>
                                        <td><?= htmlspecialchars($paciente['correo']) ?></td>
                                        <td><span class="badge bg-info"><?= $paciente['total_enfermedades'] ?></span></td>
                                        <td><span class="badge bg-warning"><?= $paciente['citas_pendientes'] ?></span></td>
                                        <td><span class="badge bg-success"><?= $paciente['tratamientos_activos'] ?></span></td>
                                        <td>
                                            <a href="paciente_detalle.php?id=<?= $paciente['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($pagina_actual == 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?pagina=1">Primera</a>
                                </li>
                                <li class="page-item <?= ($pagina_actual == 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $pagina_actual - 1 ?>">Anterior</a>
                                </li>
                                <li class="page-item <?= ($pagina_actual == $total_paginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $pagina_actual + 1 ?>">Siguiente</a>
                                </li>
                                <li class="page-item <?= ($pagina_actual == $total_paginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="?pagina=<?= $total_paginas ?>">Última</a>
                                </li>
                            </ul>
                        </nav>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            <h5>No tienes pacientes asignados</h5>
                            <p>Actualmente no hay pacientes asociados a tu perfil médico.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
