<?php
require_once __DIR__ . '/config.php';
session_start();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!csrf_check($csrf)) {
        $msg = 'Solicitud inválida.';
    } else {
        // Aquí se implementaría el envío de correo; por ahora mostramos mensaje genérico.
        $msg = 'Si el correo existe, recibirás instrucciones para recuperar tu contraseña.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100" style="background:#F4F7FB;">
    <div class="card shadow-sm" style="max-width:420px; width:100%;">
        <div class="card-body p-5">
            <h1 class="h4 mb-3">Recuperar contraseña</h1>
            <?php if (!empty($msg)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>
            <p class="text-muted">Ingresa tu correo institucional y te enviaremos las instrucciones.</p>
            <form action="<?= htmlspecialchars(BASE_URL . 'recuperar.php') ?>" method="POST">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div class="mb-3">
                    <input
                        type="email"
                        name="correo_recuperar"
                        class="form-control"
                        placeholder="usuario@tectijuana.edu.mx"
                        required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-login text-white">
                        Enviar instrucciones
                    </button>
                </div>
            </form>
            <a href="<?= htmlspecialchars(BASE_URL . 'login.php') ?>" class="btn btn-secondary w-100 mt-3">Volver al login</a>
        </div>
    </div>
</body>
</html>
