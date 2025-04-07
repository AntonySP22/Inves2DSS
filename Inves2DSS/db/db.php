<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';      // Servidor de la base de datos
$usuario = 'root';        // Usuario de MySQL
$contrasena = '';         // Contraseña del usuario
$base_datos = 'control_pacientes'; // Nombre de la base de datos

// Crear conexión
$conexion = new mysqli($host, $usuario, $contrasena, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer el conjunto de caracteres
$conexion->set_charset("utf8");
?>