<?php
require_once 'db/db.php';
session_start(); // Iniciar sesión para almacenar el correo temporalmente

$mensaje = '';
$mostrar_campos_cambio = false;

// Verificar si se hace la solicitud para cambiar la contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['correo'])) {
    $correo = trim($_POST['correo']);

    if (empty($correo)) {
        $mensaje = "Por favor, ingrese su correo electrónico.";
    } else {
        // Verificar si el correo existe en la base de datos
        $sql = "SELECT id, correo FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            $mensaje = "Correo no encontrado.";
        } else {
            // Guardar el correo en la sesión para la siguiente solicitud
            $_SESSION['correo'] = $correo;
            $mostrar_campos_cambio = true;
        }
        $stmt->close();
    }
}

// Cambio de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['correo'])) {
    $correo = $_SESSION['correo']; // Recuperar el correo almacenado en la sesión
    $nueva_contrasena = isset($_POST['nueva_contrasena']) && !empty($_POST['nueva_contrasena']) ? trim($_POST['nueva_contrasena']) : null;
    $confirmar_contrasena = isset($_POST['confirmar_contrasena']) && !empty($_POST['confirmar_contrasena']) ? trim($_POST['confirmar_contrasena']) : null;

    if (!$nueva_contrasena || !$confirmar_contrasena) {
        $mensaje = "Debe ingresar una nueva contraseña y confirmarla.";
    } elseif ($nueva_contrasena !== $confirmar_contrasena) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (empty($nueva_contrasena) || strlen((string)$nueva_contrasena) < 8) { // Corrección aquí
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        // Hash de la nueva contraseña
        $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $sql = "UPDATE usuarios SET contrasena = ? WHERE correo = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $nueva_contrasena_hash, $correo);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $mensaje = "Contraseña actualizada correctamente.";
                unset($_SESSION['correo']); // Eliminar la sesión después del cambio
                header("Location: index.php");
                exit();
            } else {
                $mensaje = "Error: la contraseña no se actualizó.";
            }
        } else {
            $mensaje = "Error al actualizar la contraseña: " . $conexion->error;
        }
        $stmt->close();
    }
}
?>

<!-- Formulario HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Recuperar Contraseña</h2>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info" role="alert">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para ingresar el correo -->
        <?php if (!$mostrar_campos_cambio): ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingrese su correo electrónico" required>
            </div>
            <button type="submit" class="btn btn-primary">Recuperar Contraseña</button>
        </form>
        <?php else: ?>
        <!-- Formulario para cambiar la contraseña -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" placeholder="Ingrese su nueva contraseña" required>
            </div>
            <div class="mb-3">
                <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="Confirme su nueva contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
 