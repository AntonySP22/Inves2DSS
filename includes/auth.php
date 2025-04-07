<?php
// Verificar si el usuario está logueado
function verificarAutenticacion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['id']) || !isset($_SESSION['rol'])) {
        header("Location: /index.php");
        exit();
    }
}

// Verificar rol específico
function verificarRol($rolRequerido) {
    verificarAutenticacion();
    
    if ($_SESSION['rol'] !== $rolRequerido) {
        header("Location: /index.php?error=permisos");
        exit();
    }
}

?>