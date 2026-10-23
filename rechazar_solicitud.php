<?php
/**
 * rechazar_solicitud.php
 * Procesa el rechazo de una solicitud de horario por parte del docente.
 */

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validar sesión y rol
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    $idDisponibilidad = filter_input(INPUT_POST, 'id_disponibilidad', FILTER_VALIDATE_INT);
    $motivo      = filter_input(INPUT_POST, 'motivo', FILTER_DEFAULT);

    if ($idDisponibilidad) {
        $pdo = db_connect();
        try {
            // Marcamos las disponibilidades de ese asesor para esa materia como rechazadas
            // En este sistema, 'Rechazada' es un estado de la solicitud_asesoria, 
            // pero aquí el docente rechaza la PROPUESTA de horario (disponibilidad).
            // Por simplicidad del MVP, el docente simplemente no asigna salón o borra el bloque.
            $stmt = $pdo->prepare("DELETE FROM disponibilidad_asesor WHERE id_disponibilidad = ? AND id_salon IS NULL");
            $stmt->execute([$idDisponibilidad]);
            
            header('Location: ' . BASE_URL . 'index.php?page=solicitudes_horario&status=rejected');
            exit;
        } catch (Exception $e) {
            error_log("Error al rechazar horario: " . $e->getMessage());
        }
    }
}

header('Location: ' . BASE_URL . 'index.php?page=solicitudes_horario&status=error');
exit;