<?php
// Conexión a la base de datos
require_once 'db/db.php';

// Variables para mostrar mensajes al usuario
$mensaje = ''; // Mensaje que se mostrará al usuario (éxito o error)
$tipo_mensaje = ''; // Tipo de mensaje (success o danger)

// Procesar el formulario cuando el usuario lo envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos enviados por el formulario
    $nombre = trim($_POST['nombre']); // Nombre del usuario
    $correo = trim($_POST['correo']); // Correo electrónico del usuario
    $contrasena = trim($_POST['contrasena']); // Contraseña del usuario
    $confirmar_contrasena = trim($_POST['confirmar_contrasena']); // Confirmación de la contraseña
    $edad = isset($_POST['edad']) ? intval($_POST['edad']) : 0; // Edad del usuario (opcional)
    $sexo = $_POST['sexo']; // Sexo del usuario
    
    // Determinar el rol del usuario (paciente, doctor o admin)
    $rol_map = [
        'paciente' => 'paciente',
        'doctor' => 'medico',
        'admin' => 'admin'
    ];
    $rol = $rol_map[$_POST['rol']]; // Rol seleccionado por el usuario
    
    // Validar los datos ingresados por el usuario
    $errores = [];
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio"; // Validar que el nombre no esté vacío
    }
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido"; // Validar que el correo sea válido
    }
    if (strlen($contrasena) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres"; // Validar que la contraseña sea suficientemente larga
    }
    if ($contrasena !== $confirmar_contrasena) {
        $errores[] = "Las contraseñas no coinciden"; // Validar que ambas contraseñas sean iguales
    }
    
    // Si no hay errores, guardar los datos en la base de datos
    if (empty($errores)) {
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT); // Encriptar la contraseña
        $sql = "INSERT INTO usuarios (nombre, correo, contrasena, edad, sexo, rol) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conexion->prepare($sql); // Preparar la consulta SQL
        $stmt->bind_param("sssiss", $nombre, $correo, $contrasena_hash, $edad, $sexo, $rol); // Vincular los datos
        
        if ($stmt->execute()) {
            $mensaje = "Usuario registrado correctamente"; // Mensaje de éxito
            $tipo_mensaje = "success";
        } else {
            if ($conexion->errno == 1062) {
                $mensaje = "El correo electrónico ya está registrado"; // Error si el correo ya existe
            } else {
                $mensaje = "Error al registrar usuario: " . $conexion->error; // Otro error
            }
            $tipo_mensaje = "danger";
        }
        $stmt->close(); // Cerrar la consulta
    } else {
        $mensaje = $errores[0]; // Mostrar el primer error encontrado
        $tipo_mensaje = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro</title>
    <!-- Estilos y librerías necesarias -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="css/registro.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Formulario de registro -->
        <div class="form-container">
            <h2 class="form-title">Crear una Cuenta</h2>
            <form id="registrationForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
                <!-- Campo para el nombre -->
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ingrese su nombre completo" required>
                    <div class="invalid-feedback">Por favor ingrese su nombre completo.</div>
                </div>
                
                <!-- Campo para el correo -->
                <div class="mb-3">
                    <label for="correo" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="correo" name="correo" placeholder="Ingrese su correo electrónico" required>
                    <div class="invalid-feedback">Por favor ingrese un correo electrónico válido.</div>
                </div>
                
                <!-- Campo para la contraseña -->
                <div class="mb-3">
                    <label for="contrasena" class="form-label">Contraseña</label>
                    <div class="position-relative">
                        <input type="password" class="form-control password-input" id="contrasena" name="contrasena" placeholder="Cree una contraseña" required>
                        <i class="fas fa-eye-slash subtle-icon toggle-password" data-target="contrasena"></i>
                    </div>
                    <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
                </div>
                
                <!-- Campo para confirmar la contraseña -->
                <div class="mb-3">
                    <label for="confirmar_contrasena" class="form-label">Confirmar Contraseña</label>
                    <div class="position-relative">
                        <input type="password" class="form-control password-input" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="Confirme su contraseña" required>
                        <i class="fas fa-eye-slash subtle-icon toggle-password" data-target="confirmar_contrasena"></i>
                    </div>
                    <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                </div>
                
                <!-- Campo para la edad -->
                <div class="mb-3">
                    <label for="edad" class="form-label">Edad</label>
                    <input type="number" class="form-control" id="edad" name="edad" placeholder="Ingrese su edad" min="1" max="120">
                </div>
                
                <!-- Campo para el sexo -->
                <div class="mb-3">
                    <label class="form-label">Sexo</label>
                    <select class="form-select" name="sexo">
                        <option value="Masculino">Masculino</option>
                        <option value="Femenino">Femenino</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <!-- Campo para el tipo de usuario -->
                <div class="mb-4">
                    <label class="form-label">Tipo de Usuario</label>
                    <div class="role-options">
                        <div class="role-option">
                            <input type="radio" class="form-check-input" id="rolPaciente" name="rol" value="paciente" checked>
                            <label class="form-check-label" for="rolPaciente">Paciente</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" class="form-check-input" id="rolDoctor" name="rol" value="doctor">
                            <label class="form-check-label" for="rolDoctor">Doctor</label>
                        </div>
                        <div class="role-option">
                            <input type="radio" class="form-check-input" id="rolAdmin" name="rol" value="admin">
                            <label class="form-check-label" for="rolAdmin">Admin</label>
                        </div>
                    </div>
                </div>
                
                <!-- Botón para enviar el formulario -->
                <button type="submit" class="btn btn-register">Registrarse</button>
                
                <!-- Enlace para iniciar sesión -->
                <div class="text-center mt-3">
                    <small>¿Ya tiene una cuenta? <a href="index.php" class="text-decoration-none">Iniciar sesión</a></small>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/registro.js"></script>
</body>
</html>