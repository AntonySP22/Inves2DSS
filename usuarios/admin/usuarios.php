<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('admin');
require_once __DIR__ . '/../../db/db.php';

// Procesar actualización de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_usuario'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, correo = ?, rol = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nombre, $correo, $rol, $id);
    
    if ($stmt->execute()) {
        $mensaje_exito = "Usuario actualizado correctamente";
    } else {
        $mensaje_error = "Error al actualizar usuario: " . $conexion->error;
    }
}

// Obtener lista de usuarios (sin el campo activo)
$usuarios = $conexion->query("
    SELECT id, nombre, correo, rol, fecha_registro 
    FROM usuarios 
    ORDER BY fecha_registro DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Usuarios</title>
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

        /* Tabla de usuarios */
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .users-table th, 
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .users-table th {
            font-weight: 500;
            color: #555;
            background-color: #f9f9f9;
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

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .users-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1 class="dashboard-title">
            <i class="fas fa-users me-2"></i>Administrar Usuarios
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
        
        <!-- Tabla de usuarios (versión simplificada) -->
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= $usuario['id'] ?></td>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['correo']) ?></td>
                    <td><?= ucfirst($usuario['rol']) ?></td>
                    <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                    <td>
                        <button class="btn btn-outline-admin btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" 
                                data-id="<?= $usuario['id'] ?>"
                                data-nombre="<?= htmlspecialchars($usuario['nombre']) ?>"
                                data-correo="<?= htmlspecialchars($usuario['correo']) ?>"
                                data-rol="<?= $usuario['rol'] ?>">
                            <i class="fas fa-edit me-1"></i>Editar
                        </button>
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
    
    <!-- Modal de edición (versión simplificada) -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editUserId">
                        
                        <div class="mb-3">
                            <label for="editNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editNombre" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editCorreo" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control" id="editCorreo" name="correo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="editRol" class="form-label">Rol</label>
                            <select class="form-control" id="editRol" name="rol" required>
                                <option value="admin">Administrador</option>
                                <option value="medico">Médico</option>
                                <option value="paciente">Paciente</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-admin" name="actualizar_usuario">Guardar Cambios</button>
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
    // Cargar datos en el modal de edición (versión simplificada)
    document.addEventListener('DOMContentLoaded', function() {
        var editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            editUserModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                
                document.getElementById('editUserId').value = button.getAttribute('data-id');
                document.getElementById('editNombre').value = button.getAttribute('data-nombre');
                document.getElementById('editCorreo').value = button.getAttribute('data-correo');
                document.getElementById('editRol').value = button.getAttribute('data-rol');
            });
        }
    });
    </script>
</body>
</html>