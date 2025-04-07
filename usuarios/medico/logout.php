<?php
session_start();

// Guardamos el valor de redirección antes de destruir la sesión
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '../../index.php';

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Mostrar página de despedida antes de redirigir
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierre de sesión</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --accent-color: #1cc88a;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        
        .logout-container {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .logout-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            animation: fadeIn 1s ease;
        }
        
        h2 {
            color: var(--dark-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        p {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .spinner {
            width: 3rem;
            height: 3rem;
            margin: 2rem auto;
            color: var(--primary-color);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h2>Sesión cerrada correctamente</h2>
        <p>Has cerrado tu sesión de manera segura. Serás redirigido automáticamente.</p>
        
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        
        <p class="small text-muted">Si no eres redirigido, <a href="<?= htmlspecialchars($redirect) ?>">haz clic aquí</a></p>
    </div>

    <!-- Redirección automática después de 3 segundos -->
    <script>
        setTimeout(function() {
            window.location.href = "<?= htmlspecialchars($redirect) ?>";
        }, 3000);
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php exit; ?>