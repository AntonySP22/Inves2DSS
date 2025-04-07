<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('paciente');
require_once __DIR__ . '/../../db/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $paciente_id = $_SESSION['id'];
    $tipo_registro = $_POST['tipo_registro'];
    $valor = $_POST['valor'];
    $notas = isset($_POST['notas']) ? $_POST['notas'] : null;

    // Validar que los campos obligatorios no estén vacíos
    if (empty($tipo_registro) || empty($valor)) {
        // Manejar el error de validación si es necesario
        echo "Por favor, complete todos los campos requeridos.";
        exit;
    }

    // Preparar la consulta para insertar el registro de salud
    $stmt = $conexion->prepare("
        INSERT INTO registros_salud (paciente_id, tipo_registro, valor, notas)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $paciente_id, $tipo_registro, $valor, $notas);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Si la inserción fue exitosa, redirigir o mostrar un mensaje
        header('Location: salud.php'); // Redirigir a la página de salud o donde sea necesario
        exit;
    } else {
        // Si ocurrió un error, mostrar el mensaje de error
        echo "Error al guardar el registro: " . $stmt->error;
    }

    $stmt->close();
}

?>
