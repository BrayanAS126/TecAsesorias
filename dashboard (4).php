<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_asesor.php';

$pageTitle  = 'Panel principal | ' . APP_NAME;
$activeMenu = 'dashboard';

$asesorId = $_SESSION['asesor_id'] ?? 0;
$stats = qs_get_dashboard_stats($asesorId);
$solicitudesRecientes = qs_get_solicitudes_pendientes($asesorId);

$agenda = qs_get_agenda_semanal($asesorId);

$diaActual  = date('N'); // 1=Lunes ... 5=Viernes
$diasSemana = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
?>

<!-- WELCOME -->
<div class="welcome-card">
    <h2>Bienvenido, Asesor</h2>
    <p>Aquí está tu agenda y el resumen de solicitudes recibidas.</p>
</div>

<!-- STATS -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-bell-fill"></i></div>
            <div class="stats-number"><?= $stats['pendientes'] ?></div>
            <div class="stats-label">Solicitudes nuevas</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stats-number"><?= $stats['proximas'] ?></div>
            <div class="stats-label">Próximas asesorías</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stats-number"><?= $stats['completadas'] ?></div>
            <div class="stats-label">Asesorías impartidas</div>
        </div>
    </div>
</div>

<!-- AGENDA SEMANAL -->
<h3 class="section-title">Agenda de esta semana</h3>

<div class="table-card mb-4" style="padding:0;overflow:hidden;">
    <div style="
        display:grid;
        grid-template-columns:repeat(5,1fr);
        border-bottom:1px solid var(--border-color);
    ">
        <?php foreach ($diasSemana as $i => $dia): ?>
        <?php
            $esHoy   = ($i + 1) === (int)$diaActual;
            $bloque  = $agenda[$dia] ?? null;
            $bgHoy   = $esHoy ? 'rgba(59,130,246,.07)' : 'transparent';
            $borderR = $i < 4 ? '1px solid var(--border-color)' : 'none';
        ?>
        <div style="border-right:<?= $borderR ?>;background:<?= $bgHoy ?>;">

            <!-- Cabecera del día -->
            <div style="
                padding:14px 16px 10px;
                border-bottom:1px solid var(--border-color);
                text-align:center;
            ">
                <div style="
                    font-size:.7rem;font-weight:700;
                    text-transform:uppercase;letter-spacing:1px;
                    color:<?= $esHoy ? 'var(--primary-light)' : 'var(--text-gray)' ?>;
                "><?= $dia ?></div>
                <?php if ($esHoy): ?>
                <div style="
                    display:inline-block;width:8px;height:8px;
                    border-radius:50%;background:var(--primary-light);margin-top:4px;
                "></div>
                <?php endif; ?>
            </div>

            <!-- Contenido del día -->
            <div style="padding:12px 10px;min-height:160px;">
                <?php if ($bloque): ?>

                    <!-- Hora y salón -->
                    <div style="
                        font-size:.72rem;font-weight:700;
                        color:var(--primary);margin-bottom:8px;
                    ">
                        <i class="bi bi-clock me-1"></i><?= htmlspecialchars($bloque['hora']) ?>
                        <span style="color:var(--text-gray);font-weight:400;margin-left:4px;">
                            · <?= htmlspecialchars($bloque['salon']) ?>
                        </span>
                    </div>

                    <!-- Badge grupo cerrado -->
                    <?php if ($bloque['grupo_cerrado']): ?>
                    <div style="
                        display:inline-flex;align-items:center;gap:4px;
                        background:rgba(220,38,38,.1);color:#DC2626;
                        border-radius:20px;padding:2px 8px;
                        font-size:.68rem;font-weight:700;
                        margin-bottom:8px;
                    ">
                        <i class="bi bi-lock-fill"></i> Grupo cerrado
                    </div>
                    <?php else: ?>
                    <div style="
                        display:inline-flex;align-items:center;gap:4px;
                        background:rgba(16,185,129,.1);color:#059669;
                        border-radius:20px;padding:2px 8px;
                        font-size:.68rem;font-weight:700;
                        margin-bottom:8px;
                    ">
                        <i class="bi bi-unlock-fill"></i> Abierto
                    </div>
                    <?php endif; ?>

                    <!-- Lista de alumnos -->
                    <div style="display:flex;flex-direction:column;gap:5px;">
                        <?php foreach ($bloque['alumnos'] as $alumno): ?>
                        <div style="
                            display:flex;align-items:center;gap:6px;
                            background:rgba(59,130,246,.07);
                            border-radius:10px;padding:5px 8px;
                        ">
                            <div style="
                                width:24px;height:24px;border-radius:50%;
                                background:var(--primary);color:#fff;
                                display:flex;align-items:center;justify-content:center;
                                font-size:.6rem;font-weight:700;flex-shrink:0;
                            "><?= htmlspecialchars($alumno['iniciales']) ?></div>
                            <span style="font-size:.75rem;font-weight:600;color:var(--primary);">
                                <?= htmlspecialchars($alumno['nombre']) ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Contador -->
                    <div style="
                        font-size:.72rem;color:var(--text-gray);
                        margin-top:8px;text-align:right;
                    ">
                        <?= count($bloque['alumnos']) ?> alumno(s)
                    </div>

                <?php else: ?>
                <div style="
                    text-align:center;color:var(--text-gray);
                    font-size:.8rem;margin-top:28px;opacity:.5;
                ">
                    <i class="bi bi-dash-circle" style="font-size:1.2rem;display:block;margin-bottom:4px;"></i>
                    Sin asesoría
                </div>
                <?php endif; ?>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- SOLICITUDES RECIENTES -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="section-title mb-0">Solicitudes recientes</h3>
    <a href="<?= BASE_URL ?>index.php?page=solicitudes"
       style="font-size:.85rem;color:var(--primary-light);text-decoration:none;font-weight:600;">
        Ver todas <i class="bi bi-arrow-right"></i>
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Día solicitado</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($solicitudesRecientes)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay solicitudes pendientes en este momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_slice($solicitudesRecientes, 0, 5) as $s): ?>
                    <?php
                        $p = explode(' ', trim($s['alumno']));
                        $ini = strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
                    ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar"><?= $ini ?></div>
                                <div><h6><?= htmlspecialchars($s['alumno']) ?></h6></div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($s['dia']) ?></td>
                        <td><?= htmlspecialchars($s['hora']) ?></td>
                        <td><span class="status-badge inactive">Pendiente</span></td>
                        <td class="text-center">
                            <div class="actions justify-content-center">
                                <a href="<?= BASE_URL ?>index.php?page=solicitudes" class="action-btn view" title="Ver todas">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>