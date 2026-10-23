<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_asesor.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'asesor') {
    if (!csrf_check($_POST['csrf'] ?? '')) die("Error de seguridad.");

    $id_disponibilidad = filter_input(INPUT_POST, 'id_disponibilidad', FILTER_VALIDATE_INT);
    $id_asesor         = $_SESSION['asesor_id'] ?? 0;

    if ($id_disponibilidad && $id_asesor) {
        try {
            qs_delete_disponibilidad($id_disponibilidad, $id_asesor);
            header('Location: ' . BASE_URL . 'index.php?page=horarios_gestion&status=success');
            exit;
        } catch (Exception $e) {
            // Manejar error de FK si hay solicitudes pendientes para este horario
        }
    }
}
header('Location: ' . BASE_URL . 'index.php?page=horarios_gestion&status=error');
exit;