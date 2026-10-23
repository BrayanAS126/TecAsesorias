<?php
/**
 * queries_admin.php
 * Todas las consultas SQL del panel de Administrador.
 *
 * NO se incluye directamente — cada página llama solo la función que necesita.
 * Uso en cada página:
 *   require_once __DIR__ . '/../../queries_admin.php';
 *
 * Índice de funciones:
 *   dashboard  → qa_dashboard_stats()
 *   usuarios   → qa_get_usuarios()  |  qa_insert_usuario()  |  qa_toggle_usuario() (cuando haya columna activo)
 *   materias   → qa_get_materias()  |  qa_insert_materia()  |  qa_delete_materia()
 *   salones    → qa_get_salones()   |  qa_insert_salon()    |  qa_delete_salon()
 */

defined('TECASESORA') or exit('Acceso directo no permitido.');


// ══════════════════════════════════════════════════════════════════════════════
//  DASHBOARD — conteos para las tarjetas de estadísticas
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Devuelve un array con los conteos para las 4 tarjetas del dashboard.
 *
 * [
 *   'total_usuarios'   => int,
 *   'total_materias'   => int,
 *   'asesorias_pend'   => int,
 *   'total_salones'    => int,
 * ]
 */
function qa_dashboard_stats(): array {
    $pdo = db_connect();

    $stats = [
        'total_usuarios'  => 0,
        'total_materias'  => 0,
        'asesorias_pend'  => 0,
        'total_salones'   => 0,
    ];

    $stats['total_usuarios'] = (int) $pdo
        ->query("SELECT COUNT(*) FROM usuarios")
        ->fetchColumn();

    $stats['total_materias'] = (int) $pdo
        ->query("SELECT COUNT(*) FROM materias")
        ->fetchColumn();

    $stats['asesorias_pend'] = (int) $pdo
        ->query("SELECT COUNT(*) FROM solicitudes_asesoria WHERE estado = 'Pendiente'")
        ->fetchColumn();

    $stats['total_salones'] = (int) $pdo
        ->query("SELECT COUNT(*) FROM salones")
        ->fetchColumn();

    return $stats;
}


// ══════════════════════════════════════════════════════════════════════════════
//  USUARIOS
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Devuelve todos los usuarios ordenados por fecha de registro descendente.
 * Cada fila incluye iniciales calculadas del correo para el avatar.
 */
function qa_get_usuarios(): array {
    $pdo  = db_connect();
    $stmt = $pdo->query("
        SELECT 
            u.id_usuario,
            u.correo_institucional,
            u.tipo_usuario,
            u.fecha_registro,
            COALESCE(a.nombre_completo, d.nombre_completo, '') as nombre_completo,
            a.no_control,
            a.semestre,
            a.carrera,
            a.genero,
            a.fecha_nacimiento,
            COALESCE(d.id_materia_encargada, am.id_materia) as id_materia,
            ase.tipo_compensacion
        FROM usuarios u
        LEFT JOIN alumnos a ON u.id_usuario = a.id_usuario
        LEFT JOIN docentes d ON u.id_usuario = d.id_usuario
        LEFT JOIN asesores ase ON a.id_alumno = ase.id_alumno
        LEFT JOIN (
            SELECT id_asesor, MIN(id_materia) as id_materia FROM asesores_materias GROUP BY id_asesor
        ) am ON ase.id_asesor = am.id_asesor
        ORDER BY u.fecha_registro DESC
    ");

    $rows = $stmt->fetchAll();
    $usuarios = [];
    foreach ($rows as $row) {
        // Generar iniciales a partir del correo (parte antes del @)
        $partes    = explode('@', $row['correo_institucional']);
        $alias     = $partes[0];                          // ej. "juan.perez"
        $segmentos = explode('.', $alias);
        $iniciales = strtoupper(
            substr($segmentos[0] ?? '', 0, 1) .
            substr($segmentos[1] ?? '', 0, 1)
        );

        $usuarios[] = [
            'id_usuario'          => $row['id_usuario'],
            'iniciales'           => $iniciales ?: '??',
            'correo_institucional'=> $row['correo_institucional'],
            'tipo_usuario'        => $row['tipo_usuario'],
            'nombre_completo'     => $row['nombre_completo'],
            'no_control'          => $row['no_control'] ?? '',
            'semestre'            => $row['semestre'] ?? '',
            'carrera'             => $row['carrera'] ?? '',
            'genero'              => $row['genero'] ?? 'Otro',
            'fecha_nacimiento'    => $row['fecha_nacimiento'] ?? '',
            'id_materia'          => $row['id_materia'] ?? '',
            'tipo_compensacion'   => $row['tipo_compensacion'] ?? 'creditos',
            'fecha_registro'      => $row['fecha_registro']
                                        ? date('d/m/Y', strtotime($row['fecha_registro']))
                                        : '—',
        ];
    }

    return $usuarios;
}

/**
 * Inserta un nuevo usuario con contraseña hasheada.
 *
 * @param string $correo      correo_institucional
 * @param string $password    contraseña en texto plano (se hashea aquí)
 * @param string $tipo        'Alumno' | 'Asesor' | 'Administrador' | 'Docente'
 * @return int  id_usuario del registro insertado
 */
function qa_insert_usuario(string $correo, string $password, string $tipo): int {
    $pdo  = db_connect();
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (correo_institucional, password_hash, tipo_usuario)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$correo, $hash, $tipo]);

    return (int) $pdo->lastInsertId();
}

/**
 * Elimina un usuario por su id.
 * (En producción considera usar una columna 'activo' en lugar de DELETE)
 *
 * @param int $id_usuario
 */
function qa_delete_usuario(int $id_usuario): void {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
}

/**
 * Inserta el perfil detallado de un alumno.
 */
function qa_insert_alumno(int $id_usuario, string $nombre, int $semestre, string $no_control, string $genero, string $carrera, string $nacimiento, ?string $telefono = null): int {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        INSERT INTO alumnos (id_usuario, nombre_completo, semestre, no_control, genero, carrera, fecha_nacimiento, telefono)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$id_usuario, $nombre, $semestre, $no_control, $genero, $carrera, $nacimiento, $telefono]);
    return (int) $pdo->lastInsertId();
}

/**
 * Inserta el perfil de un asesor (vinculado a un alumno).
 */
function qa_insert_asesor(int $id_usuario, int $id_alumno, string $compensacion, float $horas = 0.00): int {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        INSERT INTO asesores (id_alumno, tipo_compensacion, horas_acumuladas, validado)
        VALUES (?, ?, ?, 1)
    ");
    $stmt->execute([$id_alumno, $compensacion, $horas]);
    return (int) $pdo->lastInsertId();
}

/**
 * Inserta el perfil de un docente.
 */
function qa_insert_docente(int $id_usuario, string $nombre, ?string $telefono, ?string $horario, int $id_materia): int {
    $pdo = db_connect();
    $stmt = $pdo->prepare("
        INSERT INTO docentes (id_usuario, nombre_completo, telefono, horario_atencion, id_materia_encargada)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$id_usuario, $nombre, $telefono, $horario, $id_materia]);
    return (int) $pdo->lastInsertId();
}

/**
 * Actualiza los datos de un usuario y su perfil (alumno/docente).
 */
function qa_update_usuario_perfil(int $id_usuario, array $data): bool {
    $pdo = db_connect();
    try {
        $pdo->beginTransaction();

        // 1. Actualizar cuenta básica
        $stmt = $pdo->prepare("UPDATE usuarios SET correo_institucional = ?, tipo_usuario = ? WHERE id_usuario = ?");
        $stmt->execute([$data['correo'], $data['tipo'], $id_usuario]);

        // 2. Actualizar/Insertar perfil específico
        if ($data['tipo'] === 'Alumno' || $data['tipo'] === 'Asesor') {
            $stmt = $pdo->prepare("
                INSERT INTO alumnos (id_usuario, nombre_completo, semestre, no_control, genero, carrera, fecha_nacimiento)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    nombre_completo = VALUES(nombre_completo),
                    semestre = VALUES(semestre),
                    no_control = VALUES(no_control),
                    genero = VALUES(genero),
                    carrera = VALUES(carrera),
                    fecha_nacimiento = VALUES(fecha_nacimiento)
            ");
            $stmt->execute([$id_usuario, $data['nombre'], $data['semestre'], $data['no_control'], $data['genero'], $data['carrera'], $data['fecha_nacimiento']]);

            // 3. Si el rol es Asesor, asegurar que tenga registro en la tabla 'asesores'
            if ($data['tipo'] === 'Asesor') {
                $stmtAs = $pdo->prepare("INSERT INTO asesores (id_alumno, validado, tipo_compensacion) 
                                        SELECT id_alumno, 1, ? FROM alumnos WHERE id_usuario = ?
                                        ON DUPLICATE KEY UPDATE validado = 1, tipo_compensacion = VALUES(tipo_compensacion)");
                $stmtAs->execute([$data['tipo_compensacion'] ?? 'creditos', $id_usuario]);

                // 4. Vincular con la materia seleccionada
                // Primero obtenemos el id_asesor
                $stmtGetAs = $pdo->prepare("SELECT id_asesor FROM asesores ase JOIN alumnos al ON ase.id_alumno = al.id_alumno WHERE al.id_usuario = ?");
                $stmtGetAs->execute([$id_usuario]);
                $id_asesor = $stmtGetAs->fetchColumn();

                if ($id_asesor) {
                    // Borramos asignaciones previas para asegurar que el cambio de ID de materia sea efectivo
                    $stmtDel = $pdo->prepare("DELETE FROM asesores_materias WHERE id_asesor = ?");
                    $stmtDel->execute([$id_asesor]);

                    if (!empty($data['id_materia'])) {
                        // Insertamos la nueva materia vinculada
                        $stmtAm = $pdo->prepare("INSERT INTO asesores_materias (id_asesor, id_materia, tema) VALUES (?, ?, 'Temas generales')");
                        $stmtAm->execute([$id_asesor, $data['id_materia']]);
                    }
                }
            }
        } elseif ($data['tipo'] === 'Docente') {
            if (empty($data['id_materia'])) {
                throw new Exception("El docente debe tener una materia encargada asignada.");
            }
            $stmt = $pdo->prepare("
                INSERT INTO docentes (id_usuario, nombre_completo, id_materia_encargada)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    nombre_completo = VALUES(nombre_completo),
                    id_materia_encargada = VALUES(id_materia_encargada)
            ");
            $stmt->execute([$id_usuario, $data['nombre'], $data['id_materia']]);
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}


// ══════════════════════════════════════════════════════════════════════════════
//  MATERIAS
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Devuelve todas las materias con el conteo de asesores que las imparten.
 */
function qa_get_materias(): array {
    $pdo  = db_connect();
    $stmt = $pdo->query(
        "SELECT m.id_materia,
                m.clave,
                m.nombre_materia,
                m.semestre_materia,
                COUNT(am.id_asesor) AS total_asesores
         FROM   materias m
         LEFT JOIN asesores_materias am ON am.id_materia = m.id_materia
         GROUP  BY m.id_materia
         ORDER  BY m.semestre_materia, m.nombre_materia"
    );

    return $stmt->fetchAll();
}

/**
 * Inserta una nueva materia.
 *
 * @param string $clave           ej. 'ISC-101'
 * @param string $nombre_materia  ej. 'Cálculo Diferencial'
 * @param int    $semestre        1–9
 * @return int  id_materia insertado
 */
function qa_insert_materia(string $clave, string $nombre_materia, int $semestre): int {
    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "INSERT INTO materias (clave, nombre_materia, semestre_materia)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$clave, $nombre_materia, $semestre]);

    return (int) $pdo->lastInsertId();
}

/**
 * Actualiza una materia existente.
 */
function qa_update_materia(int $id_materia, string $clave, string $nombre, int $semestre): bool {
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE materias SET clave = ?, nombre_materia = ?, semestre_materia = ? WHERE id_materia = ?");
    return $stmt->execute([$clave, $nombre, $semestre, $id_materia]);
}

/**
 * Elimina una materia por su id.
 *
 * @param int $id_materia
 */
function qa_delete_materia(int $id_materia): void {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("DELETE FROM materias WHERE id_materia = ?");
    $stmt->execute([$id_materia]);
}


// ══════════════════════════════════════════════════════════════════════════════
//  SALONES
// ══════════════════════════════════════════════════════════════════════════════

/**
 * Devuelve todos los salones.
 * El campo 'ocupado' se calcula revisando si hay disponibilidad asignada hoy.
 */
function qa_get_salones(): array {
    $pdo  = db_connect();
    $stmt = $pdo->query(
        "SELECT s.id_salon,
                s.nombre_salon,
                s.ubicacion,
                s.capacidad,
                CASE
                    WHEN COUNT(d.id_disponibilidad) > 0 THEN 'Ocupado'
                    ELSE 'Disponible'
                END AS estado
         FROM   salones s
         LEFT JOIN disponibilidad_asesor d
                ON d.id_salon = s.id_salon
               AND d.dia_semana = DAYNAME(CURDATE())
         GROUP  BY s.id_salon
         ORDER  BY s.nombre_salon"
    );

    return $stmt->fetchAll();
}

/**
 * Inserta un nuevo salón.
 *
 * @param string $nombre_salon  ej. 'Salón A-101'
 * @param string $ubicacion     ej. 'Tomás Aquino'
 * @param int    $capacidad     número de personas
 * @return int  id_salon insertado
 */
function qa_insert_salon(string $nombre_salon, string $ubicacion, int $capacidad): int {
    $pdo  = db_connect();
    $stmt = $pdo->prepare(
        "INSERT INTO salones (nombre_salon, ubicacion, capacidad)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$nombre_salon, $ubicacion, $capacidad]);

    return (int) $pdo->lastInsertId();
}

/**
 * Actualiza un salón existente.
 */
function qa_update_salon(int $id_salon, string $nombre, string $ubicacion, int $capacidad): bool {
    $pdo = db_connect();
    $stmt = $pdo->prepare("UPDATE salones SET nombre_salon = ?, ubicacion = ?, capacidad = ? WHERE id_salon = ?");
    return $stmt->execute([$nombre, $ubicacion, $capacidad, $id_salon]);
}

/**
 * Elimina un salón por su id.
 *
 * @param int $id_salon
 */
function qa_delete_salon(int $id_salon): void {
    $pdo  = db_connect();
    $stmt = $pdo->prepare("DELETE FROM salones WHERE id_salon = ?");
    $stmt->execute([$id_salon]);
}

/**
 * Obtiene la actividad más reciente de todo el sistema (nuevos usuarios y solicitudes).
 */
function qa_get_actividad_reciente(): array {
    $pdo = db_connect();
    // Combinamos registros de usuarios y solicitudes para mostrar qué está pasando
    $sql = "
        (SELECT 'usuario' as tipo, correo_institucional as titulo, fecha_registro as fecha FROM usuarios)
        UNION
        (SELECT 'asesoria' as tipo, m.nombre_materia as titulo, s.fecha_creacion as fecha 
         FROM solicitudes_asesoria s 
         JOIN materias m ON s.id_materia = m.id_materia)
        ORDER BY fecha DESC 
        LIMIT 5
    ";
    return $pdo->query($sql)->fetchAll();
}

/**
 * Obtiene el historial global de todas las solicitudes de asesoría en el sistema.
 */
function qa_get_global_solicitudes_reporte(): array {
    $pdo = db_connect();
    $stmt = $pdo->query("
        SELECT 
            s.fecha_asesoria,
            al.nombre_completo AS alumno,
            al_ase.nombre_completo AS asesor,
            m.nombre_materia,
            COALESCE(sa.nombre_salon, 'Virtual') AS lugar,
            s.estado,
            s.fecha_creacion
        FROM solicitudes_asesoria s
        JOIN alumnos al ON s.id_alumno = al.id_alumno
        JOIN asesores ase ON s.id_asesor = ase.id_asesor
        JOIN alumnos al_ase ON ase.id_alumno = al_ase.id_alumno
        JOIN materias m ON s.id_materia = m.id_materia
        LEFT JOIN disponibilidad_asesor d ON s.id_disponibilidad = d.id_disponibilidad
        LEFT JOIN salones sa ON d.id_salon = sa.id_salon
        ORDER BY s.fecha_creacion DESC
    ");
    return $stmt->fetchAll();
}
