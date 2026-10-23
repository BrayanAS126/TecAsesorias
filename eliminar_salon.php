<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_admin.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'administrador') {
    if (!csrf_check($_POST['csrf'] ?? '')) die("Error de seguridad.");

    $id = filter_input(INPUT_POST, 'id_salon', FILTER_VALIDATE_INT);

    if ($id) {
        try {
            qa_delete_salon($id);
            header('Location: ' . BASE_URL . 'index.php?page=salones&status=success');
            exit;
        } catch (Exception $e) {}
    }
}
header('Location: ' . BASE_URL . 'index.php?page=salones&status=error');
exit;