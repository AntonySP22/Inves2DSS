<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('paciente');
require_once __DIR__ . '/../../db/db.php';

$paciente_id = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $medico_id = $_POST['medico_id'];  // Se podría seleccionar el médico para el tratamiento
    $nombre_tratamiento = $_POST['nombre_tratamiento'];
    $descripcion = $_POST['descripcion'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'] ?? null;  // Si no se proporciona, se dejará como NULL
    $estado = 'activo';  // El tratamiento se creará como activo inicialmente

    // Insertar tratamiento en la base de datos
    $stmt = $conexion->prepare("INSERT INTO tratamientos (paciente_id, medico_id, nombre_tratamiento, descripcion, fecha_inicio, fecha_fin, estado) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssss", $paciente_id, $medico_id, $nombre_tratamiento, $descripcion, $fecha_inicio, $fecha_fin, $estado);

    if ($stmt->execute()) {
        header('Location: tratamientos.php');  // Redirigir a la página de tratamientos después de guardar
    } else {
        echo "Error al registrar el tratamiento: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Tratamiento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Registrar Nuevo Tratamiento</h1>
        <form action="nuevo_tratamiento.php" method="POST">
            <div class="mb-3">
                <label for="medico_id" class="form-label">Médico</label>
                <input type="number" class="form-control" id="medico_id" name="medico_id" required>
            </div>

            <div class="mb-3">
                <label for="nombre_tratamiento" class="form-label">Nombre del Tratamiento</label>
                <input type="text" class="form-control" id="nombre_tratamiento" name="nombre_tratamiento" required>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
            </div>

            <div class="mb-3">
                <label for="fecha_fin" class="form-label">Fecha de Fin (opcional)</label>
                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
            </div>

            <button type="submit" class="btn btn-primary">Registrar Tratamiento</button>
        </form>
    </div>
</body>
</html>
