<?php
session_start();
require_once '../../../db/db.php';
require_once 'authorize.php';

if (isset($_GET['code'])) {
    try {
        $token = handleCallback($_GET['code']);
        
        // Crear variables para los valores que vamos a vincular
        $usuario_id = $_SESSION['id'];
        $access_token = $token['access_token'];
        $refresh_token = $token['refresh_token'] ?? '';
        
        // Guardar el token y el estado de autorización
        $query = "INSERT INTO google_calendar_auth 
                 (usuario_id, is_authorized, auth_date, access_token, refresh_token) 
                 VALUES (?, TRUE, NOW(), ?, ?) 
                 ON DUPLICATE KEY UPDATE 
                 is_authorized = TRUE, 
                 auth_date = NOW(), 
                 access_token = ?, 
                 refresh_token = ?";
        
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("issss", 
            $usuario_id,
            $access_token,
            $refresh_token,
            $access_token,
            $refresh_token
        );
        
        $stmt->execute();
        
        header('Location: ../solicitar_cita.php?auth_success=1');
        exit;
    } catch (Exception $e) {
        header('Location: ../solicitar_cita.php?auth_error=' . urlencode($e->getMessage()));
        exit;
    }

    if ($stmt->execute()) {
        // Redireccionar de vuelta con un parámetro de éxito
        header('Location: ../solicitar_cita.php?auth_success=1');
        exit;
    }
}