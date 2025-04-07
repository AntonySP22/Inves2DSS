<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('medico');
require_once __DIR__ . '/../../db/db.php';

$medico_id = $_SESSION['id'];

// Obtener las enfermedades disponibles
$stmt = $conexion->prepare("SELECT id, nombre FROM enfermedades");
$stmt->execute();
$enfermedades = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $edad = $_POST['edad'];
    $sexo = $_POST['sexo'];
    $correo = $_POST['correo']; 
    $fecha_diagnostico = $_POST['fecha_diagnostico'] ?? date('Y-m-d');  // Se asigna la fecha del formulario o la actual

    // Generar una contraseña aleatoria por defecto
    $contrasena = bin2hex(random_bytes(8));  // Genera una contraseña aleatoria de 16 caracteres (8 bytes)

    // Encriptar la contraseña
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar nuevo paciente en la tabla de usuarios
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, edad, sexo, correo, contrasena, rol) VALUES (?, ?, ?, ?, ?, 'paciente')");
    $stmt->bind_param("sisss", $nombre, $edad, $sexo, $correo, $contrasena_hash);
    $stmt->execute();

    $nuevo_paciente_id = $stmt->insert_id;

    // Asignar al médico a través de una cita o tratamiento ficticio (para que aparezca en la consulta)
    $stmt = $conexion->prepare("INSERT INTO citas (paciente_id, medico_id, estado) VALUES (?, ?, 'pendiente')");
    $stmt->bind_param("ii", $nuevo_paciente_id, $medico_id);
    $stmt->execute();

    // Asociar enfermedades seleccionadas al paciente
    if (isset($_POST['enfermedades'])) {
        foreach ($_POST['enfermedades'] as $enfermedad_id) {
            // Se usa la fecha de diagnóstico proporcionada en el formulario o la fecha actual
            $stmt = $conexion->prepare("INSERT INTO paciente_enfermedades (paciente_id, enfermedad_id, fecha_diagnostico) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $nuevo_paciente_id, $enfermedad_id, $fecha_diagnostico);
            $stmt->execute();
        }
    }

    // Redirigir a la página de pacientes después de guardar el paciente
    header("Location: pacientes.php");
    exit;
}
?>

<!-- Formulario HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Paciente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Registrar Nuevo Paciente</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="edad">Edad</label>
                <input type="number" id="edad" name="edad" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo" class="form-control" required>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                </select>
            </div> 
            <div class="mb-3">
                <label for="correo">Correo</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>

            <!-- Campo de enfermedades -->
            <div class="mb-3">
                <label for="enfermedades">Enfermedades</label>
                <select multiple id="enfermedades" name="enfermedades[]" class="form-control">
                    <?php foreach ($enfermedades as $enfermedad): ?>
                        <option value="<?= $enfermedad['id'] ?>"><?= htmlspecialchars($enfermedad['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Campo de fecha de diagnóstico -->
            <div class="mb-3">
                <label for="fecha_diagnostico">Fecha de Diagnóstico</label>
                <input type="date" id="fecha_diagnostico" name="fecha_diagnostico" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Guardar Paciente</button>
            <a href="pacientes.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
