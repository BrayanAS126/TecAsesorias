<?php
/**
 * queries_asesor.php
 * Consultas SQL específicas para el rol de Asesor.
 */

defined('TECASESORA') or exit('Acceso directo no permitido.');

/**
 * Estadísticas para el dashboard del asesor.
 */
function qs_get_dashboard_stats(int $id_asesor): array {
    $pdo = db_connect();
    $stats = [
        'pendientes' => 0,
        'proximas'   => 0,
        'completadas'=> 0,
        'canceladas' => 0
    ];

    // Solicitudes esperando respuesta
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_asesor = ? AND estado = 'Pendiente'");
    $stmt->execute([$id_asesor]);
    $stats['pendientes'] = (int) $stmt->fetchColumn();

    // Asesorías aceptadas para el futuro
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_asesor = ? AND estado = 'Aceptada' AND fecha_asesoria >= CURDATE()");
    $stmt->execute([$id_asesor]);
    $stats['proximas'] = (int) $stmt->fetchColumn();

    // Historial de asesorías impartidas (Completadas)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_asesor = ? AND estado = 'Completada'");
    $stmt->execute([$id_asesor]);
    $stats['completadas'] = (int) $stmt->fetchColumn();

    // Solicitudes canceladas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_asesor = ? AND estado = 'Cancelada'");
    $stmt->execute([$id_asesor]);
    $stats['canceladas'] = (int) $stmt->fetchColumn();

    return $stats;
}

/**
 * Obtiene las solicitudes pendientes del asesor con datos del alumno.
 */
function qs_get_solicitudes_pendientes(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.id_solicitud AS id,
            s.fecha_asesoria AS fecha,
            m.nombre_materia AS materia,
            al.nombre_completo AS alumno,
            al.no_control,
            al.carrera,
            d.dia_semana AS dia,
            d.hora_inicio,
            d.hora_fin,
            sa.nombre_salon AS salon
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_asesor = ? AND s.estado = 'Pendiente'
        ORDER BY s.fecha_asesoria ASC
    ");
    $stmt->execute([$id_asesor]);
    $res = $stmt->fetchAll();
    
    foreach ($res as &$r) {
        $r['hora'] = substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fin'], 0, 5);
    }
    return $res;
}

/**
 * Actualiza el estado de una solicitud (Aceptada, Rechazada, Completada).
 */
function qs_procesar_solicitud(int $id_solicitud, int $id_asesor, string $nuevo_estado, ?string $motivo = null): bool {
    $pdo = db_connect();
    
    // Validar que el nuevo estado sea válido
    $estadosValidos = ['Aceptada', 'Rechazada', 'Completada', 'Cancelada'];
    if (!in_array($nuevo_estado, $estadosValidos)) return false;

    try {
        $stmt = $pdo->prepare("
            UPDATE solicitudes_asesoria 
            SET estado = ?, razon_cancelacion = ? 
            WHERE id_solicitud = ? AND id_asesor = ?
        ");
        return $stmt->execute([$nuevo_estado, $motivo, $id_solicitud, $id_asesor]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtiene el horario de disponibilidad del asesor.
 */
function qs_get_mi_disponibilidad(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT d.*, s.nombre_salon, s.ubicacion
        FROM disponibilidad_asesor d
        LEFT JOIN salones s ON d.id_salon = s.id_salon
        WHERE d.id_asesor = ?
        ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado')
    ");
    $stmt->execute([$id_asesor]);
    return $stmt->fetchAll();
}

/**
 * Obtiene todas las solicitudes del asesor (historial completo).
 */
function qs_get_todas_solicitudes(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.id_solicitud AS id,
            s.fecha_asesoria AS fecha,
            s.estado,
            COALESCE(s.razon_cancelacion, '') AS nota,
            m.nombre_materia AS materia,
            al.nombre_completo AS nombre,
            u.correo_institucional AS correo,
            d.id_disponibilidad,
            d.dia_semana AS dia,
            d.hora_inicio,
            d.hora_fin,
            sa.nombre_salon AS salon
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN usuarios u ON al.id_usuario = u.id_usuario
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_asesor = ?
        ORDER BY s.fecha_asesoria DESC, s.id_solicitud DESC
    ");
    $stmt->execute([$id_asesor]);
    $res = $stmt->fetchAll();
    
    foreach ($res as &$r) {
        $r['hora'] = substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fin'], 0, 5);
    }
    return $res;
}

/**
 * Obtiene las sesiones de asesoría agrupadas para la bitácora de temas.
 */
function qs_get_sesiones_bitacora(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.fecha_asesoria AS fecha,
            d.dia_semana AS dia,
            d.hora_inicio,
            d.hora_fin,
            COALESCE(sa.nombre_salon, 'Virtual') AS salon,
            s.estado,
            COALESCE(s.razon_cancelacion, '') AS tema,
            GROUP_CONCAT(al.nombre_completo SEPARATOR '|') AS alumnos_nombres,
            GROUP_CONCAT(s.id_solicitud SEPARATOR ',') AS ids_solicitudes
        FROM solicitudes_asesoria s
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_asesor = ? AND s.estado IN ('Aceptada', 'Completada')
        GROUP BY s.fecha_asesoria, s.id_disponibilidad, s.estado, s.razon_cancelacion
        ORDER BY s.fecha_asesoria DESC
    ");
    $stmt->execute([$id_asesor]);
    $raw = $stmt->fetchAll();

    $sesiones = [];
    foreach ($raw as $r) {
        $nombres = explode('|', $r['alumnos_nombres']);
        $alumnos = array_map(function($nom) {
            $p = explode(' ', trim($nom));
            return [
                'nombre' => $nom,
                'iniciales' => strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''))
            ];
        }, $nombres);

        $r['alumnos'] = $alumnos;
        $r['hora'] = substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fin'], 0, 5);
        $sesiones[] = $r;
    }
    return $sesiones;
}

/**
 * Obtiene la agenda de la semana para el dashboard del asesor.
 */
function qs_get_agenda_semanal(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            d.dia_semana AS dia,
            d.hora_inicio,
            d.hora_fin,
            COALESCE(sa.nombre_salon, 'Virtual') AS salon,
            d.cupo = 0 AS grupo_cerrado,
            al.nombre_completo AS alumno_nombre
        FROM solicitudes_asesoria s
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        WHERE s.id_asesor = ? 
          AND s.estado = 'Aceptada'
          AND s.fecha_asesoria >= CURDATE()
        ORDER BY d.hora_inicio ASC
    ");
    $stmt->execute([$id_asesor]);
    $res = $stmt->fetchAll();

    $agenda = [];
    foreach ($res as $row) {
        $dia = $row['dia'];
        if (!isset($agenda[$dia])) {
            $agenda[$dia] = [
                'hora' => substr($row['hora_inicio'], 0, 5) . ' - ' . substr($row['hora_fin'], 0, 5),
                'salon' => $row['salon'] ?? 'Virtual',
                'grupo_cerrado' => (bool)$row['grupo_cerrado'],
                'alumnos' => []
            ];
        }
        $partes = explode(' ', trim($row['alumno_nombre']));
        $ini = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));
        $agenda[$dia]['alumnos'][] = ['nombre' => $row['alumno_nombre'], 'iniciales' => $ini];
    }
    return $agenda;
}

/**
 * Obtiene la materia asignada al asesor y el docente encargado de la misma.
 */
function qs_get_info_academica(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            m.nombre_materia AS materia,
            COALESCE(doc.nombre_completo, 'Pendiente de asignar') AS docente
        FROM asesores_materias am
        JOIN materias m ON am.id_materia = m.id_materia
        LEFT JOIN docentes doc ON m.id_materia = doc.id_materia_encargada
        WHERE am.id_asesor = ?
        LIMIT 1
    ");
    $stmt->execute([$id_asesor]);
    return $stmt->fetch() ?: ['materia' => 'Sin asignar', 'docente' => 'N/A'];
}

/**
 * Inserta un nuevo bloque de disponibilidad para un asesor.
 */
function qs_insert_disponibilidad(int $id_asesor, string $dia_semana, string $hora_inicio, string $hora_fin, string $modalidad, ?int $id_salon, ?string $enlace_virtual): int {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        INSERT INTO disponibilidad_asesor 
        (id_asesor, dia_semana, hora_inicio, hora_fin, modalidad, id_salon, enlace_virtual)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$id_asesor, $dia_semana, $hora_inicio, $hora_fin, $modalidad, $id_salon, $enlace_virtual]);
    return (int) $pdo->lastInsertId();
}

/**
 * Actualiza un bloque de disponibilidad existente para un asesor.
 */
function qs_update_disponibilidad(int $id_disponibilidad, int $id_asesor, string $dia_semana, string $hora_inicio, string $hora_fin, string $modalidad, ?int $id_salon, ?string $enlace_virtual): bool {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        UPDATE disponibilidad_asesor 
        SET dia_semana = ?, hora_inicio = ?, hora_fin = ?, modalidad = ?, id_salon = ?, enlace_virtual = ?
        WHERE id_disponibilidad = ? AND id_asesor = ?
    ");
    return $stmt->execute([$dia_semana, $hora_inicio, $hora_fin, $modalidad, $id_salon, $enlace_virtual, $id_disponibilidad, $id_asesor]);
}

/**
 * Elimina un bloque de disponibilidad por su ID.
 */
function qs_delete_disponibilidad(int $id_disponibilidad, int $id_asesor): bool {
    $pdo = db_connect();
    $stmt = $pdo->prepare("DELETE FROM disponibilidad_asesor WHERE id_disponibilidad = ? AND id_asesor = ?");
    return $stmt->execute([$id_disponibilidad, $id_asesor]);
}

/**
 * Obtiene todos los salones disponibles para que el asesor los elija.
 */
function qs_get_todos_salones(): array {
    $pdo = db_connect();
    $stmt = $pdo->query("SELECT id_salon, nombre_salon, ubicacion, capacidad FROM salones ORDER BY ubicacion, nombre_salon");
    return $stmt->fetchAll();
}

/**
 * Obtiene un bloque de disponibilidad específico por su ID.
 */
function qs_get_disponibilidad_by_id(int $id_disponibilidad, int $id_asesor): ?array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT d.*, s.nombre_salon, s.ubicacion
        FROM disponibilidad_asesor d
        LEFT JOIN salones s ON d.id_salon = s.id_salon
        WHERE d.id_disponibilidad = ? AND d.id_asesor = ?
    ");
    $stmt->execute([$id_disponibilidad, $id_asesor]);
    return $stmt->fetch();
}
/**
 * Actualiza el cupo de un bloque de disponibilidad (0 = Cerrado, 1 = Abierto).
 */
function qs_update_cupo(int $id_disp, int $id_asesor, int $nuevo_cupo): bool {
    $pdo = db_connect();
    try {
        $stmt = $pdo->prepare("
            UPDATE disponibilidad_asesor 
            SET cupo = ? 
            WHERE id_disponibilidad = ? AND id_asesor = ?
        ");
        return $stmt->execute([$nuevo_cupo, $id_disp, $id_asesor]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Obtiene el resumen de progreso y tipo de compensación del asesor.
 */
function qs_get_progreso_asesor(int $id_asesor): array {
    $pdo = db_connect();
    
    // 1. Obtener datos base del asesor
    $stmt = $pdo->prepare("SELECT tipo_compensacion FROM asesores WHERE id_asesor = ?");
    $stmt->execute([$id_asesor]);
    $asesor = $stmt->fetch();
    
    if (!$asesor) return [];

    // 2. Contar sesiones completadas (1 sesión = 1 hora)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_asesoria WHERE id_asesor = ? AND estado = 'Completada'");
    $stmt->execute([$id_asesor]);
    $horas_completadas = (int) $stmt->fetchColumn();

    // 3. Definir metas (Ejemplo: 20 horas para un crédito, 480 para servicio social)
    $meta = ($asesor['tipo_compensacion'] === 'creditos') ? META_CREDITOS : META_SERVICIO_SOCIAL;
    
    // 4. Calcular porcentaje
    $porcentaje = ($horas_completadas / $meta) * 100;
    if ($porcentaje > 100) $porcentaje = 100;

    return [
        'tipo'       => $asesor['tipo_compensacion'],
        'completadas'=> $horas_completadas,
        'meta'       => $meta,
        'porcentaje' => round($porcentaje, 1)
    ];
}

/**
 * Obtiene el historial detallado de asesorías completadas para el conteo de horas.
 */
function qs_get_historial_creditos(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            s.fecha_asesoria AS fecha,
            m.nombre_materia AS materia,
            al.nombre_completo AS alumno,
            '1' AS horas
        FROM solicitudes_asesoria s
        JOIN materias m ON s.id_materia = m.id_materia
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        WHERE s.id_asesor = ? AND s.estado = 'Completada'
        ORDER BY s.fecha_asesoria DESC
        LIMIT 10
    ");
    $stmt->execute([$id_asesor]);
    return $stmt->fetchAll();
}

/**
 * Obtiene la lista de alumnos atendidos y el conteo de asesorías por cada uno.
 */
function qs_get_alumnos_atendidos(int $id_asesor): array {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        SELECT 
            al.nombre_completo AS nombre,
            COUNT(s.id_solicitud) AS asesorias,
            MAX(s.fecha_asesoria) AS ultima
        FROM solicitudes_asesoria s
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        WHERE s.id_asesor = ? AND s.estado = 'Completada'
        GROUP BY al.id_alumno
        ORDER BY asesorias DESC
    ");
    $stmt->execute([$id_asesor]);
    return $stmt->fetchAll();
}