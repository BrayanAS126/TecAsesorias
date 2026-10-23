<?php
/**
 * eliminar_usuario.php
 * Procesa la baja de un usuario.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'administrador') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Error de seguridad: Token inválido.");
    }

    $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

    // Evitar que el administrador se elimine a sí mismo por error
    if ($idUsuario && $idUsuario !== (int)$_SESSION['usuario_id']) {
        try {
            qa_delete_usuario($idUsuario);
            header('Location: ' . BASE_URL . 'index.php?page=usuarios&status=success');
            exit;
        } catch (Exception $e) {
            // Error de FK si el usuario tiene registros vinculados
        }
    }
}

header('Location: ' . BASE_URL . 'index.php?page=usuarios&status=error');
exit;