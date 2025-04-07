<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('admin');
require_once __DIR__ . '/../../db/db.php';

// Inicializar mensajes
$mensaje_exito = '';
$mensaje_error = '';

// Obtener configuración actual
$configuracion = [];
$result = $conexion->query("SELECT * FROM configuracion LIMIT 1");
if ($result && $result->num_rows > 0) {
    $configuracion = $result->fetch_assoc();
} else {
    // Configuración por defecto
    $configuracion = [
        'nombre_clinica' => 'Clínica Blanca Maravilla',
        'horario_atencion' => 'Lunes a Viernes, 8:00 AM - 6:00 PM',
        'notificaciones_email' => 1,
        'modo_mantenimiento' => 0,
        'max_citas_dia' => 20,
        'tema_color' => 'principal',
        'logo' => 'logo-default.png'
    ];
}

// Determinar tema actual
$tema_actual = $_COOKIE['tema_color'] ?? ($configuracion['tema_color'] ?? 'principal');

// Procesar cambios de configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Configuración General
        if (isset($_POST['guardar_general'])) {
            $nombre_clinica = $conexion->real_escape_string($_POST['nombre_clinica']);
            $horario_atencion = $conexion->real_escape_string($_POST['horario_atencion']);
            
            $conexion->query("UPDATE configuracion SET 
                nombre_clinica = '$nombre_clinica', 
                horario_atencion = '$horario_atencion'");
                
            $configuracion['nombre_clinica'] = $nombre_clinica;
            $configuracion['horario_atencion'] = $horario_atencion;
            
            $mensaje_exito = "Configuración general actualizada correctamente";
        }
        
        // Preferencias del Sistema
        if (isset($_POST['guardar_preferencias'])) {
            $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0;
            $modo_mantenimiento = isset($_POST['modo_mantenimiento']) ? 1 : 0;
            $max_citas_dia = (int)$_POST['max_citas_dia'];
            
            $conexion->query("UPDATE configuracion SET 
                notificaciones_email = $notificaciones_email, 
                modo_mantenimiento = $modo_mantenimiento,
                max_citas_dia = $max_citas_dia");
                
            $configuracion['notificaciones_email'] = $notificaciones_email;
            $configuracion['modo_mantenimiento'] = $modo_mantenimiento;
            $configuracion['max_citas_dia'] = $max_citas_dia;
            
            $mensaje_exito = "Preferencias del sistema actualizadas correctamente";
        }
        
        // Tema de Color
        if (isset($_POST['guardar_tema'])) {
            $tema_color = $conexion->real_escape_string($_POST['tema_color']);
            
            $conexion->query("UPDATE configuracion SET tema_color = '$tema_color'");
            $configuracion['tema_color'] = $tema_color;
            setcookie('tema_color', $tema_color, time() + (86400 * 30), "/");
            
            $mensaje_exito = "Tema de color aplicado correctamente";
        }
        
        // Subir Logo
        if (isset($_POST['subir_logo']) && isset($_FILES['nuevo_logo'])) {
            $directorio_logos = __DIR__ . '/../../assets/img/logos/';
            
            if ($_FILES['nuevo_logo']['error'] === UPLOAD_ERR_OK) {
                $nombre_archivo = basename($_FILES['nuevo_logo']['name']);
                $extension = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
                $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($extension, $extensiones_permitidas)) {
                    $nombre_unico = 'logo-' . uniqid() . '.' . $extension;
                    $ruta_destino = $directorio_logos . $nombre_unico;
                    
                    if (move_uploaded_file($_FILES['nuevo_logo']['tmp_name'], $ruta_destino)) {
                        if ($configuracion['logo'] !== 'logo-default.png') {
                            @unlink($directorio_logos . $configuracion['logo']);
                        }
                        
                        $conexion->query("UPDATE configuracion SET logo = '$nombre_unico'");
                        $configuracion['logo'] = $nombre_unico;
                        
                        $mensaje_exito = "Logo actualizado correctamente";
                    }
                }
            }
        }
        
        // Respaldar Base de Datos
        if (isset($_POST['respaldar_db'])) {
            $fecha = date('Y-m-d_H-i-s');
            $nombre_archivo = "respaldo_db_$fecha.sql";
            $directorio_respaldos = __DIR__ . '/../../backups/';
            
            if (!is_dir($directorio_respaldos)) {
                mkdir($directorio_respaldos, 0755, true);
            }
            
            // Alternativa PHP para respaldo
            $backup_content = "";
            $tables = $conexion->query("SHOW TABLES");
            
            while ($table = $tables->fetch_array()) {
                $table_name = $table[0];
                $backup_content .= "-- Tabla: $table_name\n";
                
                $create_table = $conexion->query("SHOW CREATE TABLE $table_name");
                $backup_content .= $create_table->fetch_row()[1] . ";\n\n";
                
                $table_data = $conexion->query("SELECT * FROM $table_name");
                while ($row = $table_data->fetch_assoc()) {
                    $columns = implode("`, `", array_keys($row));
                    $values = implode("', '", array_map([$conexion, 'real_escape_string'], array_values($row)));
                    $backup_content .= "INSERT INTO `$table_name` (`$columns`) VALUES ('$values');\n";
                }
                $backup_content .= "\n";
            }
            
            if (file_put_contents($directorio_respaldos . $nombre_archivo, $backup_content)) {
                $mensaje_exito = "Respaldo generado correctamente: $nombre_archivo";
            } else {
                $mensaje_error = "Error al generar el respaldo";
            }
        }
        
    } catch (Exception $e) {
        $mensaje_error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Variables de color según tema */
        :root {
            /* Tema principal (default) */
            --primary-color: #6c63ff;
            --secondary-color: #f5f5f5;
            --text-color: #333;
            --light-color: #fff;
            --border-radius: 10px;
            --error-color: #dc3545;
        }

        /* Tema verde */
        <?php if ($tema_actual === 'verde'): ?>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #f0f9f0;
        }
        <?php endif; ?>

        /* Tema azul */
        <?php if ($tema_actual === 'azul'): ?>
        :root {
            --primary-color: #2196F3;
            --secondary-color: #f0f7fd;
        }
        <?php endif; ?>

        body {
            background-color: var(--secondary-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 40px;
        }

        .dashboard-container {
            background-color: var(--light-color);
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            text-align: center;
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 10px;
        }

        .config-section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .config-section h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .config-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: var(--border-radius);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-admin {
            background-color: var(--primary-color);
            border: none;
            border-radius: var(--border-radius);
            color: var(--light-color);
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-admin:hover {
            background-color: #5549ff;
            color: var(--light-color);
            transform: translateY(-2px);
        }

        /* Checkbox personalizado */
        .checkbox-custom {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .checkbox-custom input[type="checkbox"],
        .checkbox-custom input[type="radio"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border: 2px solid #aaa;
            border-radius: 4px;
            margin-right: 10px;
            position: relative;
            cursor: pointer;
        }

        .checkbox-custom input[type="radio"] {
            border-radius: 50%;
        }

        .checkbox-custom input[type="checkbox"]:checked,
        .checkbox-custom input[type="radio"]:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkbox-custom input[type="checkbox"]:checked::after {
            content: "✓";
            position: absolute;
            top: -1px;
            left: 2px;
            color: white;
            font-size: 14px;
        }

        .checkbox-custom input[type="radio"]:checked::after {
            content: "";
            position: absolute;
            top: 3px;
            left: 3px;
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }

        .theme-option {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .theme-color {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid #eee;
        }

        /* Mensajes de alerta */
        .alert-message {
            border-radius: var(--border-radius);
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--error-color);
        }

        @media (max-width: 768px) {
            .config-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1 class="dashboard-title">
            <i class="fas fa-cog me-2"></i>Configuración del Sistema
        </h1>
        
        <?php if ($mensaje_exito): ?>
            <div class="alert-message alert-success">
                <i class="fas fa-check-circle me-2"></i><?= $mensaje_exito ?>
            </div>
        <?php endif; ?>
        
        <?php if ($mensaje_error): ?>
            <div class="alert-message alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?= $mensaje_error ?>
            </div>
        <?php endif; ?>
        
        <!-- Configuración General -->
        <div class="config-section">
            <h3><i class="fas fa-sliders-h me-2"></i>Configuración General</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="config-card">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nombreClinica" class="form-label">Nombre de la Clínica</label>
                                <input type="text" class="form-control" id="nombreClinica" name="nombre_clinica" 
                                    value="<?= htmlspecialchars($configuracion['nombre_clinica']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="horarioAtencion" class="form-label">Horario de Atención</label>
                                <input type="text" class="form-control" id="horarioAtencion" name="horario_atencion" 
                                    value="<?= htmlspecialchars($configuracion['horario_atencion']) ?>">
                            </div>
                            <button type="submit" class="btn btn-admin" name="guardar_general">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-card">
                        <h5 class="mb-3">Preferencias del Sistema</h5>
                        <form method="POST">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="notificacionesEmail" name="notificaciones_email" 
                                    <?= $configuracion['notificaciones_email'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="notificacionesEmail">Notificaciones por Email</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="mantenimientoMode" name="modo_mantenimiento"
                                    <?= $configuracion['modo_mantenimiento'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="mantenimientoMode">Modo Mantenimiento</label>
                            </div>
                            <div class="mb-3">
                                <label for="maxCitasDia" class="form-label">Máximo de citas por día</label>
                                <input type="number" class="form-control" id="maxCitasDia" name="max_citas_dia" 
                                    value="<?= $configuracion['max_citas_dia'] ?>" min="1">
                            </div>
                            <button type="submit" class="btn btn-admin" name="guardar_preferencias">
                                <i class="fas fa-save me-1"></i> Guardar Preferencias
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Apariencia -->
        <div class="config-section">
            <h3><i class="fas fa-palette me-2"></i>Apariencia</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="config-card">
                        <h5 class="mb-3">Tema de Color</h5>
                        <form method="POST">
                            <div class="theme-option">
                                <div class="theme-color" style="background-color: #6c63ff;"></div>
                                <div class="checkbox-custom">
                                    <input type="radio" id="temaPrincipal" name="tema_color" value="principal" 
                                        <?= $tema_actual === 'principal' ? 'checked' : '' ?>>
                                    <label for="temaPrincipal">Tema Principal</label>
                                </div>
                            </div>
                            <div class="theme-option">
                                <div class="theme-color" style="background-color: #4CAF50;"></div>
                                <div class="checkbox-custom">
                                    <input type="radio" id="temaVerde" name="tema_color" value="verde"
                                        <?= $tema_actual === 'verde' ? 'checked' : '' ?>>
                                    <label for="temaVerde">Tema Verde</label>
                                </div>
                            </div>
                            <div class="theme-option">
                                <div class="theme-color" style="background-color: #2196F3;"></div>
                                <div class="checkbox-custom">
                                    <input type="radio" id="temaAzul" name="tema_color" value="azul"
                                        <?= $tema_actual === 'azul' ? 'checked' : '' ?>>
                                    <label for="temaAzul">Tema Azul</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-admin mt-3" name="guardar_tema">
                                <i class="fas fa-save me-1"></i> Aplicar Tema
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-card">
                        <h5 class="mb-3">Logo del Sistema</h5>
                        <div class="mb-3">
                            <img src="/assets/img/logos/<?= $configuracion['logo'] ?>" alt="Logo actual" 
                                style="max-width: 200px; max-height: 100px; margin-bottom: 15px;">
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nuevoLogo" class="form-label">Subir nuevo logo</label>
                                <input type="file" class="form-control" id="nuevoLogo" name="nuevo_logo" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-admin" name="subir_logo">
                                <i class="fas fa-upload me-1"></i> Subir Logo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Herramientas de Administración -->
        <div class="config-section">
            <h3><i class="fas fa-tools me-2"></i>Herramientas de Administración</h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="config-card">
                        <h5 class="mb-3">Respaldar Base de Datos</h5>
                        <p class="text-muted mb-3">Crea una copia de seguridad de toda la información del sistema.</p>
                        <form method="POST">
                            <button type="submit" class="btn btn-admin" name="respaldar_db">
                                <i class="fas fa-database me-1"></i> Generar Respaldo
                            </button>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="config-card">
                        <h5 class="mb-3">Registros del Sistema</h5>
                        <p class="text-muted mb-3">Visualiza los registros de actividad del sistema.</p>
                        <a href="registros.php" class="btn btn-admin">
                            <i class="fas fa-clipboard-list me-1"></i> Ver Registros
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-admin">
                <i class="fas fa-arrow-left me-1"></i> Volver al Panel
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script>
    // Aplicar tema inmediatamente al cambiar selección
    document.querySelectorAll('input[name="tema_color"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const root = document.documentElement;
            
            if (this.value === 'principal') {
                root.style.setProperty('--primary-color', '#6c63ff');
                root.style.setProperty('--secondary-color', '#f5f5f5');
            } else if (this.value === 'verde') {
                root.style.setProperty('--primary-color', '#4CAF50');
                root.style.setProperty('--secondary-color', '#f0f9f0');
            } else if (this.value === 'azul') {
                root.style.setProperty('--primary-color', '#2196F3');
                root.style.setProperty('--secondary-color', '#f0f7fd');
            }
        });
    });
    </script>
</body>
</html>