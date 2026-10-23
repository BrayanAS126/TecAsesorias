<?php
/**
 * cambiar_estatus.php
 * Procesa la aceptación o rechazo de una solicitud por parte del asesor.
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
    $solicitudId = filter_input(INPUT_POST, 'solicitud_id', FILTER_VALIDATE_INT);
    $nuevoEstado = filter_input(INPUT_POST, 'estado', FILTER_DEFAULT);
    $motivo      = filter_input(INPUT_POST, 'nota', FILTER_DEFAULT);
    $asesorId    = $_SESSION['asesor_id'] ?? 0;

    if ($solicitudId && $asesorId && $nuevoEstado) {
        // 4. Ejecutar actualización
        if (qs_procesar_solicitud($solicitudId, $asesorId, $nuevoEstado, $motivo)) {
            header('Location: ' . BASE_URL . 'index.php?page=solicitudes&status=updated');
            exit;
        }
    }
}

header('Location: ' . BASE_URL . 'index.php?page=solicitudes&status=error');
exit;