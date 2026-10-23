<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');

// Ruta absoluta para asegurar que el IDE encuentre las funciones
require_once dirname(__DIR__, 2) . '/queries_asesor.php';

$pageTitle  = 'Estadísticas | ' . APP_NAME;
$activeMenu = 'estadisticas';

$asesorId = $_SESSION['asesor_id'] ?? 0;
$stats = qs_get_dashboard_stats($asesorId);
$alumnosAtendidos = qs_get_alumnos_atendidos($asesorId);

$totalCompletadas = $stats['completadas'];
$totalPendientes  = $stats['pendientes'];
$totalCanceladas  = $stats['canceladas'];
$totalAlumnos     = count($alumnosAtendidos);

$totalAsesorias = $totalCompletadas + $totalCanceladas + $totalPendientes;
$pctCompletadas = $totalAsesorias > 0 ? round(($totalCompletadas / $totalAsesorias) * 100) : 0;
$pctCanceladas  = $totalAsesorias > 0 ? round(($totalCanceladas  / $totalAsesorias) * 100) : 0;
$pctPendientes  = $totalAsesorias > 0 ? round(($totalPendientes  / $totalAsesorias) * 100) : 0;
?>

<!-- STATS -->
<div class="welcome-card">
    <h2>Mis estadísticas</h2>
    <p>Resumen de tu desempeño como asesor en el semestre actual.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stats-number"><?= $totalAlumnos ?></div>
            <div class="stats-label">Alumnos atendidos</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stats-number"><?= $totalCompletadas ?></div>
            <div class="stats-label">Asesorías completadas</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-x-circle-fill"></i></div>
            <div class="stats-number"><?= $totalCanceladas ?></div>
            <div class="stats-label">Canceladas</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stats-number"><?= $totalPendientes ?></div>
            <div class="stats-label">Pendientes</div>
        </div>
    </div>
</div>

<div class="row g-4">

    <!-- Distribución -->
    <div class="col-lg-4">
        <div class="table-card" style="padding:28px;height:100%;">
            <h3 class="section-title mb-4">Distribución</h3>

            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.88rem;font-weight:600;">Completadas</span>
                    <span style="font-size:.88rem;color:var(--text-gray);">
                        <?= $totalCompletadas ?> (<?= $pctCompletadas ?>%)
                    </span>
                </div>
                <div style="background:var(--border-color);border-radius:20px;height:8px;">
                    <div style="width:<?= $pctCompletadas ?>%;height:100%;border-radius:20px;background:linear-gradient(90deg,#059669,#34d399);"></div>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.88rem;font-weight:600;">Canceladas</span>
                    <span style="font-size:.88rem;color:var(--text-gray);">
                        <?= $totalCanceladas ?> (<?= $pctCanceladas ?>%)
                    </span>
                </div>
                <div style="background:var(--border-color);border-radius:20px;height:8px;">
                    <div style="width:<?= $pctCanceladas ?>%;height:100%;border-radius:20px;background:linear-gradient(90deg,#DC2626,#f87171);"></div>
                </div>
            </div>

            <div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-size:.88rem;font-weight:600;">Pendientes</span>
                    <span style="font-size:.88rem;color:var(--text-gray);"><?= $totalPendientes ?></span>
                </div>
                <div style="background:var(--border-color);border-radius:20px;height:8px;">
                    <div style="width:<?= $pctPendientes ?>%;height:100%;border-radius:20px;background:linear-gradient(90deg,#d97706,#fbbf24);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alumnos atendidos -->
    <div class="col-lg-8">
        <div class="table-card" style="padding:0;">
            <div style="padding:24px 24px 0;">
                <h3 class="section-title">Alumnos atendidos</h3>
            </div>
            <div class="table-responsive">
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Alumno</th>
                            <th>Asesorías</th>
                            <th>Última asesoría</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alumnosAtendidos as $i => $a): ?>
                        <?php
                            $p = explode(' ', trim($a['nombre']));
                            $ini = strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
                        ?>
                        <tr>
                            <td style="font-weight:700;color:var(--primary-light);">
                                <?= $i + 1 ?>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?= $ini ?></div>
                                    <div><h6><?= htmlspecialchars($a['nombre']) ?></h6></div>
                                </div>
                            </td>
                            <td>
                                <span class="role-badge advisor"><?= $a['asesorias'] ?></span>
                            </td>
                            <td style="font-size:.85rem;color:var(--text-gray);">
                                <?= date('d/m/Y', strtotime($a['ultima'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
