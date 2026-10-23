<?php
/**
 * enviar_solicitud.php
 * Procesa el formulario de solicitud de asesoría enviado por el alumno.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_alumno.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar sesión y rol
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['alumno', 'asesor'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Intentar recuperar el alumno_id si no está en sesión (por si el registro es reciente)
    if (empty($_SESSION['alumno_id'])) {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT id_alumno FROM alumnos WHERE id_usuario = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $al = $stmt->fetch();
        if ($al) {
            $_SESSION['alumno_id'] = $al['id_alumno'];
        }
    }

    // 1. Validar CSRF
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Error de seguridad: Token CSRF no válido.");
    }

    // 2. Obtener y limpiar datos
    $materiaId = filter_input(INPUT_POST, 'materia_id', FILTER_VALIDATE_INT);
    $asesorId  = filter_input(INPUT_POST, 'asesor_id', FILTER_VALIDATE_INT);
    $bloqueId  = filter_input(INPUT_POST, 'bloque_id', FILTER_VALIDATE_INT);
    $alumnoId  = $_SESSION['alumno_id'] ?? null;

    if ($materiaId && $asesorId && $bloqueId && $alumnoId) {
        
        // 3. Determinar la fecha de la asesoría basada en el día de la semana del bloque
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT dia_semana FROM disponibilidad_asesor WHERE id_disponibilidad = ? AND cupo > 0");
        $stmt->execute([$bloqueId]);
        $diaNombre = $stmt->fetchColumn();

        if ($diaNombre) {
            $diasMapa = [
                'Lunes'     => 'Monday',
                'Martes'    => 'Tuesday',
                'Miércoles' => 'Wednesday',
                'Jueves'    => 'Thursday',
                'Viernes'   => 'Friday',
                'Sábado'    => 'Saturday'
            ];
            
            $targetDay = $diasMapa[$diaNombre];
            $todayName = date('l');
            
            // Si hoy es el día de la asesoría, se agenda para hoy; si no, para la siguiente fecha disponible.
            $fechaAsesoria = ($todayName === $targetDay) ? date('Y-m-d') : date('Y-m-d', strtotime("next $targetDay"));
            
            if (!$fechaAsesoria) {
                header('Location: ' . BASE_URL . 'index.php?page=solicitar&status=invalid_date');
                exit;
            }

            // 4. Intentar insertar en la base de datos
            if (ql_insert_solicitud($alumnoId, $asesorId, $materiaId, $bloqueId, $fechaAsesoria)) {
                header('Location: ' . BASE_URL . 'index.php?page=solicitudes&status=success');
                exit;
            } else {
                // Probablemente error de duplicado (Unique Key)
                header('Location: ' . BASE_URL . 'index.php?page=solicitar&status=duplicate');
                exit;
            }
        }
    }
}

// Si llegamos aquí es porque algo falló o no es un método POST válido
header('Location: ' . BASE_URL . 'index.php?page=solicitar&status=error');
exit;