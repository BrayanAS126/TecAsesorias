<?php
/**
 * guardar_horario_docente.php
 * Procesa la asignación directa de un horario por parte del docente.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_docente.php';

if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'docente') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Error de seguridad: Token inválido.");
    }

    $idAsesor   = (int)($_POST['id_asesor'] ?? 0);
    $dia        = $_POST['dia_semana'] ?? '';
    $inicio     = $_POST['hora_inicio'] ?? '';
    $fin        = $_POST['hora_fin'] ?? '';
    $idSalon    = (int)($_POST['id_salon'] ?? 0);

    if ($idAsesor > 0 && $idSalon > 0 && !empty($dia) && !empty($inicio) && !empty($fin)) {
        // Validación lógica: la hora de inicio debe ser menor a la de fin
        if ($inicio >= $fin) {
            header('Location: ' . BASE_URL . 'index.php?page=solicitudes_horario&status=time_error');
            exit;
        }

        try {
            if (qd_insert_asignacion_directa($idAsesor, $dia, $inicio, $fin, $idSalon)) {
                header('Location: ' . BASE_URL . 'index.php?page=solicitudes_horario&status=success');
                exit;
            }
        } catch (Exception $e) {
            // Error (posible cruce de horarios o error de base de datos)
        }
    }
}

header('Location: ' . BASE_URL . 'index.php?page=solicitudes_horario&status=error');
exit;