<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_admin.php';

$pageTitle  = 'Reportes Globales | ' . APP_NAME;
$activeMenu = 'reportes';

$stats = qa_dashboard_stats();
$log   = array_slice(qa_get_global_solicitudes_reporte(), 0, 15); // Mostrar últimos 15

$estadoClases = [
    'Completada' => 'active',
    'Pendiente'  => 'inactive',
    'Aceptada'   => 'active',
    'Cancelada'  => 'admin',
    'Rechazada'  => 'teacher'
];
?>

<!-- ENCABEZADO -->
<div class="welcome-card">
    <h2 style="color:var(--text-main);">Reportes y Auditoría</h2>
    <p>Visualiza el estado global de las asesorías y exporta datos para trámites oficiales.</p>
</div>

<!-- TARJETAS DE EXPORTACIÓN -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <a href="<?= BASE_URL ?>exportar_admin_usuarios.php" class="stats-card text-decoration-none" style="display:block;">
            <div class="stats-icon"><i class="bi bi-people-fill"></i></div>
            <div class="action-title mt-2">Exportar Usuarios</div>
            <div class="stats-label">Todos los roles (CSV)</div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <a href="<?= BASE_URL ?>exportar_admin_asesorias.php" class="stats-card text-decoration-none" style="display:block;">
            <div class="stats-icon" style="background:rgba(16,185,129,.12); color:#059669;">
                <i class="bi bi-journal-check"></i>
            </div>
            <div class="action-title mt-2">Bitácora Global</div>
            <div class="stats-label">Todas las materias (CSV)</div>
        </a>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-number" style="font-size:1.5rem;"><?= $stats['total_materias'] ?></div>
            <div class="stats-label">Materias en oferta</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-number" style="font-size:1.5rem;"><?= $stats['asesorias_pend'] ?></div>
            <div class="stats-label">Pendientes de validar</div>
        </div>
    </div>
</div>

<!-- VISTA PREVIA DEL LOG GLOBAL -->
<h3 class="section-title">Actividad reciente en el sistema</h3>
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Solicitud</th>
                    <th>Materia</th>
                    <th>Asesor</th>
                    <th>Alumno</th>
                    <th>Estado</th>
                    <th>Lugar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($log)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay actividad registrada aún.</td></tr>
                <?php endif; ?>
                <?php foreach ($log as $r): ?>
                <tr>
                    <td style="font-size:.8rem; color:var(--text-gray);">
                        <?= date('d/m/Y H:i', strtotime($r['fecha_creacion'])) ?>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($r['nombre_materia']) ?></td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($r['asesor']) ?></td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($r['alumno']) ?></td>
                    <td>
                        <span class="status-badge <?= $estadoClases[$r['estado']] ?? 'inactive' ?>" style="font-size:.7rem;">
                            <?= $r['estado'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="role-badge advisor" style="font-size:.7rem;">
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($r['lugar']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 text-center">
    <p class="small text-muted">
        <i class="bi bi-info-circle me-1"></i>
        La vista previa solo muestra las últimas 15 transacciones. Para auditoría completa, use los botones de exportación.
    </p>
</div>