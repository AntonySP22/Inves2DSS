<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('admin');
require_once __DIR__ . '/../../db/db.php';

// Procesar cambios en citas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['actualizar_cita'])) {
        $id = $_POST['id'];
        $estado = $_POST['estado'];
        $notas_medico = $_POST['notas_medico'];

        $stmt = $conexion->prepare("UPDATE citas SET estado = ?, notas_medico = ? WHERE id = ?");
        $stmt->bind_param("ssi", $estado, $notas_medico, $id);
        
        if ($stmt->execute()) {
            $mensaje_exito = "Cita actualizada correctamente";
        } else {
            $mensaje_error = "Error al actualizar cita: " . $conexion->error;
        }
    } elseif (isset($_POST['cancelar_cita'])) {
        $id = $_POST['id'];
        
        $stmt = $conexion->prepare("UPDATE citas SET estado = 'cancelada' WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $mensaje_exito = "Cita cancelada correctamente";
        } else {
            $mensaje_error = "Error al cancelar cita: " . $conexion->error;
        }
    }
}

// Obtener lista de citas con información de pacientes y médicos
$citas = $conexion->query("
    SELECT c.id, 
           c.fecha_hora, 
           c.estado, 
           c.motivo,
           c.notas_medico,
           p.nombre AS paciente_nombre,
           p.id AS paciente_id,
           m.nombre AS medico_nombre,
           pm.especialidad
    FROM citas c
    JOIN usuarios p ON c.paciente_id = p.id
    JOIN usuarios m ON c.medico_id = m.id
    JOIN perfiles_medicos pm ON m.id = pm.usuario_id
    ORDER BY c.fecha_hora DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Citas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --light-color: #fff;
            --border-radius: 10px;
            --error-color: #dc3545;
        }

        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 40px;
        }

        .dashboard-container {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 10px;
        }

        /* Tabla de citas */
        .citas-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .citas-table th, 
        .citas-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .citas-table th {
            font-weight: 500;
            color: #555;
            background-color: #f9f9f9;
        }

        .cita-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pendiente {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completada {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelada {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Modal de edición */
        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: var(--primary-color);
            color: var(--light-color);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .btn-admin {
            background-color: var(--primary-color);
            border: none;
            border-radius: var(--border-radius);
            color: var(--light-color);
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-admin:hover {
            background-color: #5549ff;
            color: var(--light-color);
            transform: translateY(-2px);
        }

        .btn-outline-admin {
            border: 1px solid var(--primary-color);
            border-radius: var(--border-radius);
            color: var(--primary-color);
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-outline-admin:hover {
            background-color: var(--primary-color);
            color: var(--light-color);
        }

        .btn-cancelar {
            border: 1px solid var(--error-color);
            border-radius: var(--border-radius);
            color: var(--error-color);
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-cancelar:hover {
            background-color: var(--error-color);
            color: var(--light-color);
        }

        /* Mensajes de alerta */
        .alert-message {
            border-radius: var(--border-radius);
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--error-color);
        }

        /* Formularios */
        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        textarea.form-control {
            min-height: 100px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .citas-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1 class="dashboard-title">
            <i class="fas fa-calendar-alt me-2"></i>Gestionar Citas
        </h1>
        
        <!-- Mensajes de éxito/error -->
        <?php if (isset($mensaje_exito)): ?>
            <div class="alert-message alert-success">
                <i class="fas fa-check-circle me-2"></i><?= $mensaje_exito ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($mensaje_error)): ?>
            <div class="alert-message alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $mensaje_error ?>
            </div>
        <?php endif; ?>
        
        <!-- Tabla de citas -->
        <table class="citas-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha y Hora</th>
                    <th>Paciente</th>
                    <th>Médico</th>
                    <th>Especialidad</th>
                    <th>Estado</th>
                    <th>Motivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($citas as $cita): ?>
                <tr>
                    <td><?= $cita['id'] ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($cita['fecha_hora'])) ?></td>
                    <td><?= htmlspecialchars($cita['paciente_nombre']) ?></td>
                    <td><?= htmlspecialchars($cita['medico_nombre']) ?></td>
                    <td><?= htmlspecialchars($cita['especialidad']) ?></td>
                    <td>
                        <span class="cita-status status-<?= $cita['estado'] ?>">
                            <?= ucfirst($cita['estado']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($cita['motivo']) ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-admin btn-sm" data-bs-toggle="modal" data-bs-target="#editCitaModal" 
                                    data-id="<?= $cita['id'] ?>"
                                    data-estado="<?= $cita['estado'] ?>"
                                    data-notas_medico="<?= htmlspecialchars($cita['notas_medico']) ?>">
                                <i class="fas fa-edit me-1"></i>Gestionar
                            </button>
                            
                            <?php if ($cita['estado'] == 'pendiente'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $cita['id'] ?>">
                                <button type="submit" name="cancelar_cita" class="btn btn-cancelar btn-sm">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Botón para volver -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-admin">
                <i class="fas fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>
    </div>
    
    <!-- Modal de gestión de cita -->
    <div class="modal fade" id="editCitaModal" tabindex="-1" aria-labelledby="editCitaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCitaModalLabel">Gestionar Cita</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editCitaId">
                        
                        <div class="mb-3">
                            <label for="editEstado" class="form-label">Estado</label>
                            <select class="form-control" id="editEstado" name="estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editNotasMedico" class="form-label">Notas Médicas</label>
                            <textarea class="form-control" id="editNotasMedico" name="notas_medico" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-admin" name="actualizar_cita">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
    <script>
    // Cargar datos en el modal de gestión
    document.addEventListener('DOMContentLoaded', function() {
        var editCitaModal = document.getElementById('editCitaModal');
        if (editCitaModal) {
            editCitaModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                
                document.getElementById('editCitaId').value = button.getAttribute('data-id');
                document.getElementById('editEstado').value = button.getAttribute('data-estado');
                document.getElementById('editNotasMedico').value = button.getAttribute('data-notas_medico');
            });
        }
    });
    </script>
</body>
</html>