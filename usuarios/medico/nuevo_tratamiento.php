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

// Obtener pacientes del médico
$query_pacientes = "
    SELECT DISTINCT u.id, u.nombre 
    FROM citas c
    JOIN usuarios u ON c.paciente_id = u.id
    WHERE c.medico_id = ?
    ORDER BY u.nombre ASC
";
$stmt = $conexion->prepare($query_pacientes);
$stmt->bind_param("i", $medico_id);
$stmt->execute();
$pacientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Procesar el formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente_id = $_POST['paciente_id'];
    $nombre_tratamiento = $_POST['nombre_tratamiento'];
    $descripcion = $_POST['descripcion'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'] ?: null;
    $estado = $_POST['estado'];
    
    // Insertar el tratamiento
    $query = "
        INSERT INTO tratamientos (
            paciente_id, 
            medico_id, 
            nombre_tratamiento, 
            descripcion, 
            fecha_inicio, 
            fecha_fin, 
            estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param(
        "iisssss", 
        $paciente_id, 
        $medico_id, 
        $nombre_tratamiento, 
        $descripcion, 
        $fecha_inicio, 
        $fecha_fin, 
        $estado
    );
    
    if ($stmt->execute()) {
        $tratamiento_id = $conexion->insert_id;
        
        // Insertar medicamentos
        if (isset($_POST['medicamento_nombre'])) {
            foreach ($_POST['medicamento_nombre'] as $index => $nombre) {
                $query_med = "
                    INSERT INTO medicamentos (
                        tratamiento_id,
                        nombre_medicamento,
                        dosis,
                        frecuencia,
                        via_administracion
                    ) VALUES (?, ?, ?, ?, ?)
                ";
                
                $stmt_med = $conexion->prepare($query_med);
                $stmt_med->bind_param(
                    "issss",
                    $tratamiento_id,
                    $nombre,
                    $_POST['medicamento_dosis'][$index],
                    $_POST['medicamento_frecuencia'][$index],
                    $_POST['medicamento_via'][$index]
                );
                $stmt_med->execute();
            }
        }
        
        $_SESSION['mensaje_exito'] = "Tratamiento creado exitosamente";
        header("Location: tratamientos.php");
        exit();
    } else {
        $error = "Error al crear el tratamiento: " . $conexion->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Tratamiento - Dr. <?= htmlspecialchars($medico['nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        
        .medicamento-item {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
            position: relative;
        }
        
        .btn-remove-medicamento {
            position: absolute;
            right: 15px;
            top: 15px;
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
                    <h2><i class="fas fa-file-medical me-2"></i> Nuevo Tratamiento</h2>
                    <a href="tratamientos.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
                
                <form method="POST" id="formTratamiento">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-info-circle me-2"></i> Información Básica
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paciente_id" class="form-label">Paciente *</label>
                                    <select class="form-select" id="paciente_id" name="paciente_id" required>
                                        <option value="">Seleccionar paciente</option>
                                        <?php foreach ($pacientes as $paciente): ?>
                                            <option value="<?= $paciente['id'] ?>" <?= isset($_POST['paciente_id']) && $_POST['paciente_id'] == $paciente['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($paciente['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="estado" class="form-label">Estado *</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="activo" <?= isset($_POST['estado']) && $_POST['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                                        <option value="suspendido" <?= isset($_POST['estado']) && $_POST['estado'] == 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                                        <option value="completado" <?= isset($_POST['estado']) && $_POST['estado'] == 'completado' ? 'selected' : '' ?>>Completado</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
                                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                                           value="<?= isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-d') ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fecha_fin" class="form-label">Fecha de Finalización (opcional)</label>
                                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                                           value="<?= isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : '' ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="nombre_tratamiento" class="form-label">Nombre del Tratamiento *</label>
                                <input type="text" class="form-control" id="nombre_tratamiento" name="nombre_tratamiento" 
                                       value="<?= isset($_POST['nombre_tratamiento']) ? htmlspecialchars($_POST['nombre_tratamiento']) : '' ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción del Tratamiento</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sección de Medicamentos -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-pills me-2"></i> Medicamentos
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="btnAddMedicamento">
                                <i class="fas fa-plus me-1"></i> Añadir Medicamento
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="medicamentos-container">
                                <!-- Los medicamentos se añadirán aquí dinámicamente -->
                                <?php if (isset($_POST['medicamento_nombre'])): ?>
                                    <?php foreach ($_POST['medicamento_nombre'] as $index => $nombre): ?>
                                        <div class="medicamento-item position-relative mb-3">
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-medicamento">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nombre del Medicamento *</label>
                                                    <input type="text" class="form-control" name="medicamento_nombre[]" 
                                                           value="<?= htmlspecialchars($nombre) ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Dosis *</label>
                                                    <input type="text" class="form-control" name="medicamento_dosis[]" 
                                                           value="<?= htmlspecialchars($_POST['medicamento_dosis'][$index]) ?>" required>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Frecuencia *</label>
                                                    <input type="text" class="form-control" name="medicamento_frecuencia[]" 
                                                           value="<?= htmlspecialchars($_POST['medicamento_frecuencia'][$index]) ?>" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Vía de Administración *</label>
                                                    <select class="form-select" name="medicamento_via[]" required>
                                                        <option value="Oral" <?= $_POST['medicamento_via'][$index] == 'Oral' ? 'selected' : '' ?>>Oral</option>
                                                        <option value="Inyección" <?= $_POST['medicamento_via'][$index] == 'Inyección' ? 'selected' : '' ?>>Inyección</option>
                                                        <option value="Tópica" <?= $_POST['medicamento_via'][$index] == 'Tópica' ? 'selected' : '' ?>>Tópica</option>
                                                        <option value="Inhalación" <?= $_POST['medicamento_via'][$index] == 'Inhalación' ? 'selected' : '' ?>>Inhalación</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Plantilla para nuevos medicamentos (oculta) -->
                            <div id="medicamento-template" class="d-none">
                                <div class="medicamento-item position-relative mb-3">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-medicamento">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre del Medicamento *</label>
                                            <input type="text" class="form-control" name="medicamento_nombre[]" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Dosis *</label>
                                            <input type="text" class="form-control" name="medicamento_dosis[]" required>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Frecuencia *</label>
                                            <input type="text" class="form-control" name="medicamento_frecuencia[]" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Vía de Administración *</label>
                                            <select class="form-select" name="medicamento_via[]" required>
                                                <option value="Oral">Oral</option>
                                                <option value="Inyección">Inyección</option>
                                                <option value="Tópica">Tópica</option>
                                                <option value="Inhalación">Inhalación</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-1"></i> Guardar Tratamiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Añadir nuevo medicamento
            document.getElementById('btnAddMedicamento').addEventListener('click', function() {
                const template = document.getElementById('medicamento-template');
                const clone = template.cloneNode(true);
                clone.classList.remove('d-none');
                clone.removeAttribute('id');
                document.getElementById('medicamentos-container').appendChild(clone);
            });
            
            // Eliminar medicamento
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('btn-remove-medicamento') || 
                    e.target.closest('.btn-remove-medicamento')) {
                    const btn = e.target.classList.contains('btn-remove-medicamento') ? 
                               e.target : e.target.closest('.btn-remove-medicamento');
                    btn.closest('.medicamento-item').remove();
                }
            });
            
            // Validación del formulario
            document.getElementById('formTratamiento').addEventListener('submit', function(e) {
                const pacienteId = document.getElementById('paciente_id').value;
                if (!pacienteId) {
                    alert('Por favor seleccione un paciente');
                    e.preventDefault();
                    return;
                }
                
                const medicamentos = document.querySelectorAll('input[name="medicamento_nombre[]"]');
                if (medicamentos.length === 0) {
                    alert('Debe añadir al menos un medicamento');
                    e.preventDefault();
                    return;
                }
                
                // Validar que todos los campos requeridos de medicamentos estén completos
                let isValid = true;
                medicamentos.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.focus();
                        return;
                    }
                });
                
                if (!isValid) {
                    alert('Por favor complete todos los campos requeridos de los medicamentos');
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>