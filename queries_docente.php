<?php
/**
 * queries_docente.php
 * Consultas SQL específicas para el rol de Docente.
 */

defined('TECASESORA') or exit('Acceso directo no permitido.');

/**
 * Obtiene los datos del docente logueado y el nombre de su materia.
 */
function qd_get_docente(): ?array {
    $pdo = db_connect();
    $id_usuario = $_SESSION['usuario_id'] ?? 0;
    $stmt = $pdo->prepare("
        SELECT d.*, m.nombre_materia 
        FROM docentes d 
        JOIN materias m ON d.id_materia_encargada = m.id_materia
        WHERE d.id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    return $stmt->fetch() ?: null;
}

/**
 * Estadísticas para el dashboard (versión compatible con pantallas de compañero).
 */
function qd_dashboard_stats(int $id_materia): array {
    $pdo = db_connect();
    $stats = ['total_asesores' => 0, 'total_solicitudes' => 0, 'pendientes' => 0, 'completadas' => 0];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM asesores_materias WHERE id_materia = ?");
    $stmt->execute([$id_materia]);
    $stats['total_asesores'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_materia = ?");
    $stmt->execute([$id_materia]);
    $stats['total_solicitudes'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_materia = ? AND estado = 'Pendiente'");
    $stmt->execute([$id_materia]);
    $stats['pendientes'] = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_materia = ? AND estado = 'Completada'");
    $stmt->execute([$id_materia]);
    $stats['completadas'] = (int)$stmt->fetchColumn();

    return $stats;
}

/**
 * Obtiene asesores activos para el dashboard.
 */
function qd_asesores_activos(int $id_materia): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            al.nombre_completo, 
            u.correo_institucional, 
            ase.tipo_compensacion,
            ase.id_asesor,
            am.tema,
            (SELECT COUNT(*) FROM solicitudes_asesoria s WHERE s.id_asesor = ase.id_asesor AND s.id_materia = ?) as total_asesorias,
            (SELECT COUNT(*) FROM solicitudes_asesoria s WHERE s.id_asesor = ase.id_asesor AND s.id_materia = ? AND s.estado = 'Completada') as completadas,
            (SELECT COUNT(*) FROM solicitudes_asesoria s WHERE s.id_asesor = ase.id_asesor AND s.id_materia = ? AND s.estado = 'Cancelada') as canceladas,
            (SELECT COUNT(*) FROM solicitudes_asesoria s WHERE s.id_asesor = ase.id_asesor AND s.id_materia = ? AND s.estado = 'Pendiente') as pendientes
        FROM asesores ase
        JOIN alumnos al ON ase.id_alumno = al.id_alumno
        JOIN usuarios u ON al.id_usuario = u.id_usuario
        JOIN asesores_materias am ON ase.id_asesor = am.id_asesor
        WHERE am.id_materia = ? AND ase.validado = 1
    ");
    $stmt->execute([$id_materia, $id_materia, $id_materia, $id_materia, $id_materia]);
    $asesores = $stmt->fetchAll();

    foreach ($asesores as &$a) {
        $meta = ($a['tipo_compensacion'] === 'creditos') ? 20 : 480;
        $a['progreso'] = $meta > 0 ? round(($a['completadas'] / $meta) * 100, 1) : 0;
    }
    return $asesores;
}

/**
 * Historial de asesorías para la tabla de reportes.
 */
function qd_reporte_asesorias(int $id_materia): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.fecha_asesoria, 
            al.nombre_completo AS alumno, 
            al_ase.nombre_completo AS asesor, 
            sa.nombre_salon, 
            d.hora_inicio, 
            d.hora_fin, 
            s.estado
        FROM solicitudes_asesoria s
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN asesores ase ON s.id_asesor = ase.id_asesor
        JOIN alumnos al_ase ON ase.id_alumno = al_ase.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_materia = ?
        ORDER BY s.fecha_asesoria DESC
    ");
    $stmt->execute([$id_materia]);
    return $stmt->fetchAll();
}

/**
 * Reporte de progreso de asesores para la página de reportes.
 */
function qd_reporte_asesores(int $id_materia): array {
    return qd_asesores_activos($id_materia);
}

/**
 * Obtiene la lista de asesores que imparten la materia del docente.
 */
function qd_get_asesores_materia(int $id_materia): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            a.id_asesor,
            al.nombre_completo,
            COALESCE(al.carrera, 'Sin carrera') AS carrera,
            al.no_control,
            u.correo_institucional AS correo,
            a.validado,
            (SELECT COUNT(*) FROM solicitudes_asesoria s WHERE s.id_asesor = a.id_asesor AND s.estado = 'Completada') as sesiones_dadas
        FROM asesores a
        JOIN alumnos al ON a.id_alumno = al.id_alumno
        JOIN usuarios u ON al.id_usuario = u.id_usuario
        JOIN asesores_materias am ON a.id_asesor = am.id_asesor
        WHERE am.id_materia = ?
    ");
    $stmt->execute([$id_materia]);
    return $stmt->fetchAll();
}

/**
 * Obtiene la actividad reciente (solicitudes) de la materia.
 */
function qd_get_actividad_materia(int $id_materia): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.id_solicitud,
            s.fecha_asesoria,
            s.estado,
            al.nombre_completo AS alumno,
            al_ase.nombre_completo AS asesor,
            COALESCE(s.razon_cancelacion, '') AS tema
        FROM solicitudes_asesoria s
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN asesores ase ON s.id_asesor = ase.id_asesor
        JOIN alumnos al_ase ON ase.id_alumno = al_ase.id_alumno
        WHERE s.id_materia = ?
        ORDER BY s.fecha_creacion DESC
        LIMIT 10
    ");
    $stmt->execute([$id_materia]);
    return $stmt->fetchAll();
}

/**
 * Obtiene el detalle de una materia específica.
 */
function qd_get_detalle_materia(int $id_materia): ?array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT m.*, 
               (SELECT COUNT(*) FROM asesores_materias am WHERE am.id_materia = m.id_materia) as total_asesores
        FROM materias m 
        WHERE m.id_materia = ?
    ");
    $stmt->execute([$id_materia]);
    return $stmt->fetch() ?: null;
}

/**
 * Obtiene el catálogo completo de salones.
 */
function qd_get_todos_salones(): array {
    $pdo = db_connect();
    $stmt = $pdo->query("
        SELECT *, 
        (SELECT COUNT(*) FROM disponibilidad_asesor d WHERE d.id_salon = s.id_salon) as usos
        FROM salones s 
        ORDER BY ubicacion, nombre_salon
    ");
    return $stmt->fetchAll();
}

/**
 * Obtiene las solicitudes de horario (bloques de disponibilidad) de los asesores de la materia.
 */
function qd_get_solicitudes_horario(int $id_materia): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            a.id_asesor,
            al.nombre_completo AS nombre,
            u.correo_institucional AS correo,
            m.nombre_materia AS materia,
            d.id_disponibilidad,
            d.dia_semana AS dia,
            d.hora_inicio,
            d.hora_fin,
            d.id_salon,
            s.nombre_salon,
            s.ubicacion
        FROM disponibilidad_asesor d
        JOIN asesores a ON d.id_asesor = a.id_asesor
        JOIN alumnos al ON a.id_alumno = al.id_alumno
        JOIN usuarios u ON al.id_usuario = u.id_usuario
        JOIN asesores_materias am ON a.id_asesor = am.id_asesor
        JOIN materias m ON am.id_materia = m.id_materia
        LEFT JOIN salones s ON d.id_salon = s.id_salon
        WHERE am.id_materia = ?
        ORDER BY al.nombre_completo, FIELD(d.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado')
    ");
    $stmt->execute([$id_materia]);
    $raw = $stmt->fetchAll();

    $grouped = [];
    foreach ($raw as $row) {
        $idA = $row['id_asesor'];
        if (!isset($grouped[$idA])) {
            $p = explode(' ', trim($row['nombre']));
            $ini = strtoupper(substr($p[0] ?? '?', 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
            
            $grouped[$idA] = [
                'id_asesor' => $idA,
                'iniciales' => $ini,
                'nombre'    => $row['nombre'],
                'correo'    => $row['correo'],
                'materia'   => $row['materia'],
                'bloques'   => []
            ];
        }
        $grouped[$idA]['bloques'][] = [
            'id'    => $row['id_disponibilidad'],
            'dia'   => $row['dia'],
            'hora'  => substr($row['hora_inicio'], 0, 5) . ' - ' . substr($row['hora_fin'], 0, 5),
            'id_salon' => $row['id_salon'],
            'nombre_salon' => $row['nombre_salon'],
            'ubicacion' => $row['ubicacion']
        ];
    }
    
    foreach ($grouped as &$g) {
        $pendientes = array_filter($g['bloques'], fn($b) => is_null($b['id_salon']));
        $g['estado'] = count($pendientes) > 0 ? 'pendiente' : 'aceptada';
    }

    return array_values($grouped);
}

/**
 * Obtiene la bitácora completa de un asesor específico.
 */
function qd_get_bitacora_asesor(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.fecha_asesoria AS fecha,
            m.nombre_materia AS materia,
            al.nombre_completo AS alumno,
            COALESCE(s.razon_cancelacion, 'Sin registrar') AS tema,
            s.estado,
            d.hora_inicio,
            d.hora_fin,
            COALESCE(sa.nombre_salon, 'Virtual') AS lugar
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_asesor = ? AND s.estado = 'Completada'
        ORDER BY s.fecha_asesoria DESC
    ");
    $stmt->execute([$id_asesor]);
    return $stmt->fetchAll();
}

/**
 * Obtiene el nombre completo de un asesor a partir de su ID.
 */
function qd_get_nombre_asesor(int $id_asesor): string {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT al.nombre_completo 
        FROM alumnos al
        JOIN asesores a ON al.id_alumno = a.id_alumno
        WHERE a.id_asesor = ?
    ");
    $stmt->execute([$id_asesor]);
    return $stmt->fetchColumn() ?: 'Asesor desconocido';
}

/**
 * Obtiene todas las asesorías completadas de la materia para exportación.
 */
function qd_get_todas_asesorias_materia(int $id_materia): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.fecha_asesoria,
            al_ase.nombre_completo AS asesor,
            al.nombre_completo AS alumno,
            m.nombre_materia,
            COALESCE(s.razon_cancelacion, 'Sin registro') AS tema,
            sa.nombre_salon
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN asesores ase ON s.id_asesor = ase.id_asesor
        JOIN alumnos al_ase ON ase.id_alumno = al_ase.id_alumno
        LEFT JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_materia = ? AND s.estado = 'Completada'
        ORDER BY s.fecha_asesoria DESC
    ");
    $stmt->execute([$id_materia]);
    return $stmt->fetchAll();
}

/**
 * Permite al docente crear una asignación de horario directa para un asesor.
 */
function qd_insert_asignacion_directa(int $id_asesor, string $dia, string $inicio, string $fin, int $id_salon): bool {
    $pdo = db_connect();
    try {
        $stmt = $pdo->prepare("
            INSERT INTO disponibilidad_asesor 
            (id_asesor, dia_semana, hora_inicio, hora_fin, modalidad, id_salon, cupo)
            VALUES (?, ?, ?, ?, 'Presencial', ?, 5)
        ");
        return $stmt->execute([$id_asesor, $dia, $inicio, $fin, $id_salon]);
    } catch (PDOException $e) {
        return false;
    }
}