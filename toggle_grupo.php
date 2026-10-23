<?php
/**
 * toggle_grupo.php
 * Permite al asesor abrir o cerrar el cupo de un horario específico.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_asesor.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validar sesión y rol
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'asesor') {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2. Validar CSRF
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    // 3. Obtener datos
    $idDisp     = filter_input(INPUT_POST, 'id_disponibilidad', FILTER_VALIDATE_INT);
    $nuevoCupo  = filter_input(INPUT_POST, 'cerrado', FILTER_VALIDATE_INT); // Viene como 0 o 1
    $asesorId   = $_SESSION['asesor_id'] ?? 0;

    if ($idDisp && $asesorId !== 0) {
        // 4. Ejecutar actualización
        if (qs_update_cupo($idDisp, $asesorId, $nuevoCupo)) {
            header('Location: ' . BASE_URL . 'index.php?page=solicitudes&status=updated');
            exit;
        }
    }
}

header('Location: ' . BASE_URL . 'index.php?page=solicitudes&status=error');
exit;