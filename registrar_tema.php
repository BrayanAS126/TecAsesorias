<?php
/**
 * registrar_tema.php
 * Guarda el tema visto y marca las solicitudes como 'Completada'.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_asesor.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'asesor') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Error de seguridad.");
    }

    $idsString = $_POST['solicitudes_ids'] ?? '';
    $tema      = filter_input(INPUT_POST, 'tema', FILTER_DEFAULT);
    $asesorId  = $_SESSION['asesor_id'] ?? 0;

    if (!empty($idsString) && $asesorId) {
        $ids = explode(',', $idsString);
        $pdo = db_connect();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                UPDATE solicitudes_asesoria 
                SET estado = 'Completada', razon_cancelacion = ? 
                WHERE id_solicitud = ? AND id_asesor = ?
            ");
            
            foreach ($ids as $id) {
                $stmt->execute([$tema, (int)$id, $asesorId]);
            }
            
            $pdo->commit();
            header('Location: ' . BASE_URL . 'index.php?page=asesorias&status=success');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            // Registrar el error para el administrador del sistema
            error_log("Error al registrar tema de asesoría: " . $e->getMessage());
        }
    }
}

header('Location: ' . BASE_URL . 'index.php?page=asesorias&status=error');
exit;