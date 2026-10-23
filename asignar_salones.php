<?php
/**
 * asignar_salones.php
 * Procesa la asignación de salones a los bloques de disponibilidad por parte del docente.
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

    $salonesAsignados = $_POST['salon'] ?? []; // Array [id_disponibilidad => id_salon]
    $materiaDocente   = $_SESSION['materia_docente_id'] ?? 0;
    
    if (!empty($salonesAsignados) && $materiaDocente) {
        $pdo = db_connect();
        try {
            $pdo->beginTransaction();

            // Validar que el bloque pertenezca a la materia del docente
            $stmt = $pdo->prepare("
                UPDATE disponibilidad_asesor d
                JOIN asesores_materias am ON d.id_asesor = am.id_asesor
                SET d.id_salon = ? 
                WHERE d.id_disponibilidad = ? AND am.id_materia = ?
            ");
            
            foreach ($salonesAsignados as $idDisp => $idSalon) {
                if (!empty($idSalon)) {
                    $stmt->execute([$idSalon, (int)$idDisp, $materiaDocente]);
                }
            }
            
            $pdo->commit();
            header('Location: ' . BASE_URL . 'index.php?page=solicitudes_horario&status=success');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }
}

header('Location: ' . BASE_URL . 'index.php?page=solicitudes_horario&status=error');
exit;