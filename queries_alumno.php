<?php
/**
 * queries_alumno.php
 * Consultas SQL específicas para el rol de Alumno.
 */

defined('TECASESORA') or exit('Acceso directo no permitido.');

/**
 * Obtiene los datos del perfil del alumno y su ID interno.
 */
function ql_get_datos_alumno(int $id_usuario): ?array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT a.*, u.correo_institucional 
        FROM alumnos a
        JOIN usuarios u ON a.id_usuario = u.id_usuario
        WHERE u.id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    return $stmt->fetch() ?: null;
}

/**
 * Estadísticas para el dashboard del alumno.
 */
function ql_get_dashboard_stats(int $id_alumno): array {
    $pdo = db_connect();
    $stats = [
        'pendientes' => 0,
        'proximas'   => 0,
        'completadas'=> 0
    ];

    // Solicitudes pendientes de aprobación
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_alumno = ? AND estado = 'Pendiente'");
    $stmt->execute([$id_alumno]);
    $stats['pendientes'] = (int) $stmt->fetchColumn();

    // Asesorías ya aceptadas y futuras
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_alumno = ? AND estado = 'Aceptada' AND fecha_asesoria >= CURDATE()");
    $stmt->execute([$id_alumno]);
    $stats['proximas'] = (int) $stmt->fetchColumn();

    // Historial total
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_alumno = ? AND estado = 'Completada'");
    $stmt->execute([$id_alumno]);
    $stats['completadas'] = (int) $stmt->fetchColumn();

    return $stats;
}

/**
 * Obtiene la lista de asesorías del alumno con nombres de asesores y materias.
 */
function ql_get_mis_solicitudes(int $id_alumno): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.id_solicitud AS id,
            s.fecha_asesoria AS fecha,
            s.estado,
            COALESCE(s.razon_cancelacion, '') AS nota,
            m.nombre_materia AS materia,
            al_asesor.nombre_completo AS asesor,
            d.dia_semana AS dia,
            d.hora_inicio AS hora,
            d.modalidad,
            sa.nombre_salon AS salon,
            sa.ubicacion AS sede
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN asesores ase ON s.id_asesor = ase.id_asesor
        JOIN alumnos al_asesor ON ase.id_alumno = al_asesor.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_alumno = ?
        ORDER BY s.fecha_asesoria DESC
    ");
    $stmt->execute([$id_alumno]);
    return $stmt->fetchAll();
}

/**
 * Obtiene la oferta de materias que tienen asesores disponibles.
 */
function ql_get_materias_disponibles(): array {
    $pdo = db_connect();
    $stmt = $pdo->query("
        SELECT DISTINCT m.*, 
               (SELECT COUNT(*) FROM asesores_materias am WHERE am.id_materia = m.id_materia) as num_asesores
        FROM materias m
        JOIN asesores_materias am ON m.id_materia = am.id_materia
        ORDER BY m.nombre_materia ASC
    ");
    return $stmt->fetchAll();
}

/**
 * Registra una nueva solicitud de asesoría.
 */
function ql_insert_solicitud(int $id_alumno, int $id_asesor, int $id_materia, int $id_disp, string $fecha): bool {
    $pdo = db_connect();

    // 1. Verificar si ya existe un registro previo (incluso si fue cancelado)
    $stmt = $pdo->prepare("
        SELECT id_solicitud, estado 
        FROM solicitudes_asesoria 
        WHERE id_alumno = ? AND id_disponibilidad = ? AND fecha_asesoria = ?
    ");
    $stmt->execute([$id_alumno, $id_disp, $fecha]);
    $existente = $stmt->fetch();

    if ($existente) {
        // Si existe y ya fue procesada (cancelada o rechazada), la reactivamos
        if (in_array($existente['estado'], ['Cancelada', 'Rechazada'])) {
            $update = $pdo->prepare("
                UPDATE solicitudes_asesoria 
                SET estado = 'Pendiente', razon_cancelacion = NULL, id_materia = ?, id_asesor = ?
                WHERE id_solicitud = ?
            ");
            return $update->execute([$id_materia, $id_asesor, $existente['id_solicitud']]);
        }
        // Si está Pendiente, Aceptada o Completada, es un duplicado prohibido
        return false;
    }

    // 2. Si no existe, procedemos con la inserción normal
    try {
        $stmt = $pdo->prepare("
            INSERT INTO solicitudes_asesoria 
            (id_alumno, id_asesor, id_materia, id_disponibilidad, fecha_asesoria, estado)
            VALUES (?, ?, ?, ?, ?, 'Pendiente')
        ");
        return $stmt->execute([$id_alumno, $id_asesor, $id_materia, $id_disp, $fecha]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtiene los asesores que imparten una materia específica.
 */
function ql_get_asesores_por_materia(int $id_materia): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            a.id_asesor, 
            al.nombre_completo,
            m.nombre_materia
        FROM asesores a
        JOIN alumnos al ON a.id_alumno = al.id_alumno
        JOIN asesores_materias am ON a.id_asesor = am.id_asesor
        JOIN materias m ON am.id_materia = m.id_materia
        WHERE am.id_materia = ? AND a.validado = 1
    ");
    $stmt->execute([$id_materia]);
    
    $resultados = [];
    while ($row = $stmt->fetch()) {
        // Generar iniciales simples
        $partes = explode(' ', trim($row['nombre_completo']));
        $primero = $partes[0] ?? '';
        $ultimo  = (count($partes) > 1) ? end($partes) : $primero;
        $iniciales = strtoupper(substr($primero, 0, 1) . substr($ultimo, 0, 1));
        
        $resultados[] = [
            'id' => (int)$row['id_asesor'],
            'nombre' => $row['nombre_completo'],
            'materia' => $row['nombre_materia'],
            'iniciales' => $iniciales
        ];
    }
    return $resultados;
}

/**
 * Obtiene los horarios disponibles de un asesor específico.
 */
function ql_get_disponibilidad_asesor(int $id_asesor, bool $incluirCerrados = false): array {
    $pdo = db_connect();
    $where = "WHERE d.id_asesor = ?";
    if (!$incluirCerrados) {
        $where .= " AND d.cupo > 0";
    }

    $stmt = $pdo->prepare("
        SELECT d.*, s.nombre_salon, s.ubicacion
        FROM disponibilidad_asesor d
        LEFT JOIN salones s ON d.id_salon = s.id_salon
        $where
    ");
    $stmt->execute([$id_asesor]);
    return $stmt->fetchAll();
}

/**
 * Obtiene las próximas asesorías confirmadas de un alumno.
 */
function ql_get_proximas_asesorias(int $id_alumno): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.fecha_asesoria AS fecha,
            d.dia_semana AS dia,
            d.hora_inicio,
            d.hora_fin,
            m.nombre_materia AS materia,
            al_asesor.nombre_completo AS asesor,
            sa.nombre_salon AS salon,
            sa.ubicacion AS sede,
            d.cupo > 1 AS grupo
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN asesores ase ON s.id_asesor = ase.id_asesor
        JOIN alumnos al_asesor ON ase.id_alumno = al_asesor.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_alumno = ? AND s.estado = 'Aceptada' AND s.fecha_asesoria >= CURDATE()
        ORDER BY s.fecha_asesoria ASC
    ");
    $stmt->execute([$id_alumno]);
    $res = $stmt->fetchAll();
    foreach($res as &$r) {
        $r['hora'] = substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fin'], 0, 5);
    }
    return $res;
}

/**
 * Obtiene el historial de asesorías completadas de un alumno.
 */
function ql_get_historial_asesorias(int $id_alumno): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            m.nombre_materia AS materia,
            al_asesor.nombre_completo AS asesor,
            d.dia_semana AS dia,
            s.fecha_asesoria AS fecha,
            d.hora_inicio,
            d.hora_fin,
            sa.nombre_salon AS salon,
            sa.ubicacion AS sede,
            s.razon_cancelacion AS tema,
            d.cupo > 1 AS grupo
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN asesores ase ON s.id_asesor = ase.id_asesor
        JOIN alumnos al_asesor ON ase.id_alumno = al_asesor.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_alumno = ? AND s.estado = 'Completada'
        ORDER BY s.fecha_asesoria DESC
    ");
    $stmt->execute([$id_alumno]);
    $res = $stmt->fetchAll();
    foreach($res as &$r) {
        $r['hora'] = substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fin'], 0, 5);
        $r['fecha'] = date('d/m/Y', strtotime($r['fecha']));
    }
    return $res;
}

/**
 * Obtiene todos los salones registrados agrupados por sede.
 */
function ql_get_salones_agrupados(): array {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT * FROM salones ORDER BY ubicacion, nombre_salon");
    $raw = $stmt->fetchAll();
    $agrupados = [];
    foreach($raw as $s) {
        $agrupados[$s['ubicacion']][] = [
            'nombre'    => $s['nombre_salon'],
            'edificio'  => 'General',
            'piso'      => 'P.B.',
            'capacidad' => $s['capacidad']
        ];
    }
    return $agrupados;
}

/**
 * Obtiene materias con sus respectivos asesores y horarios para la vista general.
 */
function ql_get_materias_con_detalles(): array {
    $materias = ql_get_materias_disponibles();
    foreach ($materias as &$m) {
        $m['id'] = $m['id_materia'];
        $m['nombre'] = $m['nombre_materia'];
        $asesores = ql_get_asesores_por_materia($m['id_materia']);
        foreach ($asesores as &$ase) {
            $disponibilidad = ql_get_disponibilidad_asesor($ase['id'], true);
            $ase['horarios'] = array_map(function($d) {
                return [
                    'dia'     => $d['dia_semana'],
                    'hora'    => substr($d['hora_inicio'], 0, 5) . ' - ' . substr($d['hora_fin'], 0, 5),
                    'salon'   => $d['nombre_salon'] ?? 'Virtual',
                    'alumnos' => 0,
                    'cerrado' => ((int)$d['cupo'] === 0)
                ];
            }, $disponibilidad);
        }
        $m['asesores'] = $asesores;
    }
    return $materias;
}

/**
 * Cancela una solicitud de asesoría pendiente o aceptada.
 */
function ql_cancelar_solicitud(int $id_solicitud, int $id_alumno, string $motivo): bool {
    $pdo = db_connect();
    try {
        $stmt = $pdo->prepare("
            UPDATE solicitudes_asesoria 
            SET estado = 'Cancelada', razon_cancelacion = ? 
            WHERE id_solicitud = ? AND id_alumno = ? AND estado IN ('Pendiente', 'Aceptada')
        ");
        return $stmt->execute([$motivo, $id_solicitud, $id_alumno]);
    } catch (PDOException $e) {
        return false;
    }
}