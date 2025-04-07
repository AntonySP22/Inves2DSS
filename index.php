<?php
// Incluir la conexión a la base de datos
require_once 'db/db.php';

// Variables para mensajes
$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $correo = trim($_POST['email']);
    $contrasena = trim($_POST['password']);
    
    // Validación básica
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Por favor, ingrese un correo electrónico válido.";
        $tipo_mensaje = "danger";
    } elseif (empty($contrasena)) {
        $mensaje = "Por favor, ingrese su contraseña.";
        $tipo_mensaje = "danger";
    } else {
        // Verificar credenciales en la base de datos
        $sql = "SELECT id, nombre, correo, contrasena, rol FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($sql);
        if ($stmt === false) {
            die('Error en la preparación de la consulta: ' . $conexion->error);
        }
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            
            /// Verificar la contraseña usando password_verify()
if (password_verify($contrasena, $usuario['contrasena'])) {
    // Iniciar sesión
    session_start();
    $_SESSION['id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['rol'] = $usuario['rol'];
    
    // Redirigir según el rol
    switch ($usuario['rol']) {
        case 'admin':
            header("Location: usuarios/admin/index.php");
            break;
        case 'medico':
            header("Location: usuarios/medico/index.php");
            break;
        case 'paciente':
            header("Location: usuarios/paciente/index.php");
            break;
    }
    exit();
} else {
    $mensaje = "Correo electrónico o contraseña incorrectos.";
    $tipo_mensaje = "danger";
}

        } 
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Diseño CSS -->
    <link href="css/login.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Bienvenido de Nuevo</h2>
            
            <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <form id="loginForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Ingrese su correo electrónico" required>
                    <div class="invalid-feedback">Por favor, ingrese un correo electrónico válido.</div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="position-relative">
                        <input type="password" class="form-control password-input" id="password" name="password" placeholder="Ingrese su contraseña" required>
                        <i class="fas fa-eye-slash subtle-icon toggle-password" data-target="password"></i>
                    </div>
                    <div class="invalid-feedback">Por favor, ingrese su contraseña.</div>
                </div>
                
                <button type="submit" class="btn btn-login">Iniciar Sesión</button>
                
                <div class="text-center mt-3">
                    <small>¿No tiene una cuenta? <a href="registro.php" class="text-decoration-none" style="color: var(--primary-color);">Registrarse</a></small>
                </div>
                  <!-- Enlace para ir al formulario de "Olvidé mi contraseña" -->
                <div class="text-center mt-3">
                    <small>¿Olvidaste tu contraseña? <a href="olvide_contrasena.php" class="text-decoration-none">Recupérala aquí</a></small>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Form Validation Script -->
    <script src="js/login.js"></script>
</body>
</html>

