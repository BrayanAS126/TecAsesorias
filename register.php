<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" style="background:#F4F7FB;">
    <div class="card shadow-sm text-center" style="max-width:420px; width:100%;">
        <div class="card-body p-5">
            <i class="bi bi-shield-lock-fill text-primary mb-3" style="font-size:3rem;"></i>
            <h1 class="h4 mb-2">Acceso restringido</h1>
            <p class="text-muted mb-4">
                El registro de nuevos usuarios es administrado por el equipo del Tecnológico de Tijuana.
                Si necesitas acceso, contacta a tu administrador.
            </p>
            <a href="<?= htmlspecialchars(BASE_URL . 'login.php') ?>" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Volver al login
            </a>
        </div>
    </div>
</body>
</html>