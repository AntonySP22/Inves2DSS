<?php
require_once __DIR__ . '/../../includes/auth.php';
verificarRol('admin');
require_once __DIR__ . '/../../db/db.php';

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 20;
$inicio = ($pagina > 1) ? ($pagina * $por_pagina - $por_pagina) : 0;

// Obtener registros
$registros = $conexion->query("
    SELECT l.*, u.nombre as usuario_nombre 
    FROM logs_acceso l
    JOIN usuarios u ON l.usuario_id = u.id
    ORDER BY l.fecha_hora DESC
    LIMIT $inicio, $por_pagina
")->fetch_all(MYSQLI_ASSOC);

// Calcular total de páginas
$total_registros = $conexion->query("SELECT COUNT(*) as total FROM logs_acceso")->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #f5f5f5;
            --text-color: #333;
        }
        body {
            background-color: var(--secondary-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .badge-primary {
            background-color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-clipboard-list me-2"></i>Registros del Sistema</h1>
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
        </div>

        <div class="table-container p-4">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Fecha/Hora</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?= $registro['id'] ?></td>
                        <td><?= htmlspecialchars($registro['usuario_nombre']) ?></td>
                        <td><?= htmlspecialchars($registro['accion']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($registro['fecha_hora'])) ?></td>
                        <td><?= $registro['ip_address'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($pagina > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina - 1 ?>">Anterior</a>
                    </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?= ($i == $pagina) ? 'active' : '' ?>">
                        <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if ($pagina < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="?pagina=<?= $pagina + 1 ?>">Siguiente</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>