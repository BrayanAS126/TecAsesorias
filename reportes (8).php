<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_docente.php';

$pageTitle  = 'Reportes | ' . APP_NAME;
$activeMenu = 'reportes';

$idAsesor = filter_input(INPUT_GET, 'asesor', FILTER_VALIDATE_INT);
$bitacora = [];
$nombreAsesor = '';

if ($idAsesor) {
    $nombreAsesor = qd_get_nombre_asesor($idAsesor);
    $bitacora = qd_get_bitacora_asesor($idAsesor);
}
?>

<!-- WELCOME -->
<div class="welcome-card">
    <?php if ($idAsesor): ?>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 style="color:var(--text-main);">Bitácora: <?= htmlspecialchars($nombreAsesor) ?></h2>
                <p>Historial detallado de temas abordados en las asesorías.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php?page=usuarios" class="btn-export" style="text-decoration:none; padding:10px 20px;">
                <i class="bi bi-arrow-left"></i> Volver a asesores
            </a>
        </div>
    <?php else: ?>
        <h2 style="color:var(--text-main);">Reportes y estadísticas</h2>
        <p>Descarga y visualiza los reportes del sistema.</p>
    <?php endif; ?>
</div>

<?php if ($idAsesor): ?>
    <!-- VISTA BITÁCORA DETALLADA -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Alumno</th>
                        <th>Lugar</th>
                        <th>Tema registrado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bitacora)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Este asesor no tiene sesiones completadas registradas.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($bitacora as $b): ?>
                    <tr>
                        <td style="font-size:.85rem;"><?= date('d/m/Y', strtotime($b['fecha'])) ?></td>
                        <td style="font-size:.82rem; white-space:nowrap;">
                            <?= substr($b['hora_inicio'], 0, 5) ?> - <?= substr($b['hora_fin'], 0, 5) ?>
                        </td>
                        <td><?= htmlspecialchars($b['alumno']) ?></td>
                        <td>
                            <span class="role-badge advisor" style="font-size:.7rem;">
                                <i class="bi bi-building me-1"></i><?= htmlspecialchars($b['lugar']) ?>
                            </span>
                        </td>
                        <td style="font-size:.88rem; color:var(--text-gray); font-style:italic;">
                            <i class="bi bi-journal-text me-1"></i>
                            <?= htmlspecialchars($b['tema']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>

<!-- CARDS DE DESCARGA -->
<div class="row g-4">

    <div class="col-md-6 col-xl-3">
        <a href="<?= BASE_URL ?>exportar_asesores.php" class="stats-card text-decoration-none" style="display:block;">
            <div class="stats-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="action-title mt-2">Exportar usuarios</div>
            <div class="stats-label">CSV / Excel</div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="<?= BASE_URL ?>exportar_asesorias.php" class="stats-card text-decoration-none" style="display:block;">
            <div class="stats-icon">
                <i class="bi bi-calendar-check-fill"></i>
            </div>
            <div class="action-title mt-2">Exportar asesorías</div>
            <div class="stats-label">CSV / Excel</div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="<?= BASE_URL ?>exportar_materias.php" class="stats-card text-decoration-none" style="display:block;">
            <div class="stats-icon">
                <i class="bi bi-book-fill"></i>
            </div>
            <div class="action-title mt-2">Exportar materias</div>
            <div class="stats-label">CSV / Excel</div>
        </a>
    </div>

    <div class="col-md-6 col-xl-3">
        <a href="<?= BASE_URL ?>exportar_salones.php" class="stats-card text-decoration-none" style="display:block;">
            <div class="stats-icon">
                <i class="bi bi-building"></i>
            </div>
            <div class="action-title mt-2">Exportar salones</div>
            <div class="stats-label">CSV / Excel</div>
        </a>
    </div>

</div>
<?php endif; ?>
