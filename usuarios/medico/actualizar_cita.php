<?php
session_start();
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../db/db.php';

// Verificar que sea un médico
verificarRol('medico');

// Configurar el header para JSON
header('Content-Type: application/json');

try {
    // Verificar datos recibidos
    if (!isset($_POST['id']) || !isset($_POST['notas'])) {
        throw new Exception('Datos incompletos');
    }

    $cita_id = intval($_POST['id']);
    $notas = trim($_POST['notas']);
    $medico_id = $_SESSION['id'];

    // Actualizar solo las notas y el estado
    $query_actualizar = "UPDATE citas 
                        SET estado = 'completada', 
                            notas_medico = ? 
                        WHERE id = ? AND medico_id = ?";
    
    $stmt = $conexion->prepare($query_actualizar);
    $stmt->bind_param("sii", $notas, $cita_id, $medico_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al actualizar la cita');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conexion)) {
        $conexion->close();
    }
}