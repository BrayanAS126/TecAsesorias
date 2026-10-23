<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
$pageTitle  = 'Panel principal | ' . APP_NAME;
$activeMenu = 'dashboard';

require_once BASE_PATH . 'queries_docente.php';

$docente    = qd_get_docente();
$id_materia = $docente ? (int)$docente['id_materia_encargada'] : 0;

// Obtenemos datos combinando ambas ramas
$stats      = $id_materia ? qd_dashboard_stats($id_materia) : ['total_asesores'=>0,'total_solicitudes'=>0,'pendientes'=>0,'completadas'=>0];
$asesores   = $id_materia ? qd_asesores_activos($id_materia) : [];
$actividad  = $id_materia ? qd_get_actividad_materia($id_materia) : [];
?>

<div class="welcome-card">
    <h2 style="color:var(--text-main);">Bienvenido, <?= htmlspecialchars($docente['nombre_completo'] ?? explode(' ', $_SESSION['nombre'])[0]) ?></h2>
    <p>Materia bajo supervisión: <strong><?= htmlspecialchars($docente['nombre_materia'] ?? 'tu materia') ?></strong></p>
</div>

<!-- STATS -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stats-number"><?= $stats['total_asesores'] ?></div>
            <div class="stats-label">Asesores activos</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-bell-fill"></i></div>
            <div class="stats-number"><?= $stats['total_solicitudes'] ?></div>
            <div class="stats-label">Solicitudes totales</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stats-number"><?= $stats['pendientes'] ?></div>
            <div class="stats-label">Pendientes</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-check-circle-fill"></i></div>
            <div class="stats-number"><?= $stats['completadas'] ?></div>
            <div class="stats-label">Completadas</div>
        </div>
    </div>
</div>

<!-- QUICK ACTIONS -->
<h3 class="section-title">Gestión rápida</h3>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=solicitudes_horario" class="action-card">
            <div class="action-icon"><i class="bi bi-calendar-week-fill"></i></div>
            <div class="action-title">Horarios</div>
            <div class="action-text">Asignar salones a asesores.</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=usuarios" class="action-card">
            <div class="action-icon"><i class="bi bi-person-badge-fill"></i></div>
            <div class="action-title">Asesores</div>
            <div class="action-text">Ver asesores de tu materia.</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=reportes" class="action-card">
            <div class="action-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div class="action-title">Reportes</div>
            <div class="action-text">Estadísticas de asesorías.</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=salones" class="action-card">
            <div class="action-icon"><i class="bi bi-building"></i></div>
            <div class="action-title">Salones</div>
            <div class="action-text">Ver disponibilidad de salones.</div>
        </a>
    </div>
</div>

<!-- ASESORES ACTIVOS -->
<h3 class="section-title">Asesores de <?= htmlspecialchars($docente['nombre_materia'] ?? '') ?></h3>
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr><th>Asesor</th><th>Correo</th><th>Compensación</th><th>Progreso</th><th>Asesorías</th><th>Completadas</th></tr>
            </thead>
            <tbody>
                <?php if (empty($asesores)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Sin asesores registrados.</td></tr>
                <?php else: ?>
                <?php foreach ($asesores as $a):
                    $iniciales = implode('', array_map(fn($p) => strtoupper(substr($p,0,1)), array_slice(explode(' ', $a['nombre_completo']),0,2)));
                ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar"><?= $iniciales ?></div>
                            <div><h6><?= htmlspecialchars($a['nombre_completo']) ?></h6>
                            <span style="font-size:.78rem;color:var(--text-gray);"><?= htmlspecialchars($a['tema'] ?? '—') ?></span></div>
                        </div>
                    </td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($a['correo_institucional']) ?></td>
                    <td><span class="role-badge <?= $a['tipo_compensacion'] === 'creditos' ? 'advisor' : 'student' ?>">
                        <?= $a['tipo_compensacion'] === 'creditos' ? 'Créditos' : 'Serv. Social' ?>
                    </span></td>
                    <td>
                        <div style="background:var(--border-color);border-radius:20px;height:8px;width:120px;">
                            <div style="width:<?= min(100,$a['progreso']) ?>%;height:100%;border-radius:20px;background:linear-gradient(90deg,#1B396A,#3B82F6);"></div>
                        </div>
                        <span style="font-size:.75rem;color:var(--text-gray);"><?= $a['progreso'] ?>%</span>
                    </td>
                    <td><?= (int)$a['total_asesorias'] ?></td>
                    <td><span class="role-badge active"><?= (int)$a['completadas'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ACTIVIDAD RECIENTE (Integrado de docente-implementacion) -->
<div class="activity-card">
    <h3 class="section-title mb-4">Actividad reciente</h3>
    <?php if (empty($actividad)): ?>
        <p class="text-muted small">No hay actividad reciente en tu materia.</p>
    <?php else: ?>
        <?php foreach ($actividad as $act): ?>
        <div class="activity-item">
            <div class="activity-dot" style="background: <?= ($act['estado'] === 'Pendiente') ? 'var(--primary-light)' : '#059669' ?>;"></div>
            <div class="activity-content">
                <h6 style="color:var(--text-main);">
                    <?= htmlspecialchars($act['alumno']) ?> solicitó a <?= htmlspecialchars($act['asesor']) ?>
                </h6>
                <span>
                    Estado: <strong><?= $act['estado'] ?></strong> · 
                    Asesoría: <?= date('d/m/Y', strtotime($act['fecha_asesoria'])) ?>
                    <?php if ($act['tema']): ?>
                        · <i class="bi bi-journal-text ms-1"></i> Tema: <?= htmlspecialchars($act['tema']) ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
