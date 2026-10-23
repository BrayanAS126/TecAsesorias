<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_asesor.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'asesor') {
    if (!csrf_check($_POST['csrf'] ?? '')) die("Error de seguridad.");

    $id_asesor      = $_SESSION['asesor_id'] ?? 0;
    $dia_semana     = filter_input(INPUT_POST, 'dia_semana', FILTER_DEFAULT);
    $hora_inicio    = filter_input(INPUT_POST, 'hora_inicio', FILTER_DEFAULT);
    $hora_fin       = filter_input(INPUT_POST, 'hora_fin', FILTER_DEFAULT);
    $modalidad      = filter_input(INPUT_POST, 'modalidad', FILTER_DEFAULT);
    $id_salon       = ($modalidad === 'Presencial') ? filter_input(INPUT_POST, 'id_salon', FILTER_VALIDATE_INT) : null;
    $enlace_virtual = ($modalidad === 'En línea') ? filter_input(INPUT_POST, 'enlace_virtual', FILTER_VALIDATE_URL) : null;

    if ($id_asesor && $dia_semana && $hora_inicio && $hora_fin) {
        try {
            qs_insert_disponibilidad($id_asesor, $dia_semana, $hora_inicio, $hora_fin, $modalidad, $id_salon, $enlace_virtual);
            header('Location: ' . BASE_URL . 'index.php?page=horarios_gestion&status=success');
            exit;
        } catch (Exception $e) {
            // Manejar error de duplicado o FK
        }
    }
}
header('Location: ' . BASE_URL . 'index.php?page=horarios_gestion&status=error');
exit;