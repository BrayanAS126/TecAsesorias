<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_alumno.php';

$pageTitle  = 'Panel principal | ' . APP_NAME;
$activeMenu = 'dashboard';

$alumnoId = $_SESSION['alumno_id'] ?? 0;
$stats = ql_get_dashboard_stats($alumnoId);
$proximasAsesorias = ql_get_proximas_asesorias($alumnoId);
?>

<!-- WELCOME -->
<div class="welcome-card">
    <h2>Bienvenido</h2>
    <p>Aquí están tus próximas asesorías y accesos rápidos.</p>
</div>

<!-- STATS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <div class="stats-number"><?= $stats['proximas'] ?></div>
            <div class="stats-label">Próximas asesorías</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-hourglass-split"></i></div>
            <div class="stats-number"><?= $stats['pendientes'] ?></div>
            <div class="stats-label">Solicitudes pendientes</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>index.php?page=solicitar" class="stats-card"
           style="text-decoration:none; cursor:pointer; display:flex; flex-direction:column; align-items:center; min-height:100%;">
            <div class="stats-icon" style="background:rgba(16,185,129,.12); color:#059669;">
                <i class="bi bi-plus-circle-fill"></i>
            </div>
            <div style="font-size:0.95rem; font-weight:700; margin-top:8px; color:#059669; text-align:center;">
                Solicitar
            </div>
            <div class="stats-label">Nueva asesoría</div>
        </a>
    </div>
    <div class="col-6 col-md-3">
        <a href="<?= BASE_URL ?>index.php?page=historial" class="stats-card"
           style="text-decoration:none; cursor:pointer; display:flex; flex-direction:column; align-items:center; min-height:100%;">
            <div class="stats-icon"><i class="bi bi-journal-check"></i></div>
            <div style="font-size:0.95rem; font-weight:700; margin-top:8px; text-align:center;">
                Ver historial
            </div>
            <div class="stats-number" style="font-size:1.2rem;"><?= $stats['completadas'] ?></div>
        </a>
    </div>
</div>

<!-- PRÓXIMAS ASESORÍAS -->
<h3 class="section-title">Mis próximas asesorías</h3>

<?php if (empty($proximasAsesorias)): ?>
<div class="table-card" style="padding:48px;text-align:center;">
    <div style="font-size:2.5rem;margin-bottom:12px;opacity:.3;">📅</div>
    <div style="font-weight:700;color:var(--primary);margin-bottom:6px;">
        No tienes asesorías próximas
    </div>
    <div style="font-size:.88rem;color:var(--text-gray);margin-bottom:20px;">
        Solicita una asesoría para comenzar.
    </div>
    <a href="<?= BASE_URL ?>index.php?page=solicitar" class="btn-add-user"
       style="display:inline-flex;text-decoration:none;">
        <i class="bi bi-plus-lg"></i> Solicitar asesoría
    </a>
</div>
<?php else: ?>

<div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($proximasAsesorias as $a): ?>
    <div class="table-card" style="padding:20px;">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">

            <!-- Fecha -->
            <div style="
                background:var(--primary);color:#fff;
                border-radius:14px;padding:10px 16px;
                text-align:center;min-width:64px;flex-shrink:0;
            ">
                <div style="font-size:1.3rem;font-weight:700;line-height:1;">
                    <?= date('d', strtotime($a['fecha'])) ?>
                </div>
                <div style="font-size:.65rem;text-transform:uppercase;opacity:.8;margin-top:2px;">
                    <?= obtener_mes_corto($a['fecha']) ?>
                </div>
            </div>

            <!-- Info -->
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.95rem;color:var(--primary);margin-bottom:4px;">
                    <?= htmlspecialchars($a['materia']) ?>
                </div>
                <div style="font-size:.83rem;color:var(--text-gray);display:flex;flex-wrap:wrap;gap:12px;">
                    <span><i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($a['asesor']) ?></span>
                    <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($a['hora']) ?></span>
                    <span><i class="bi bi-building me-1"></i><?= htmlspecialchars($a['salon']) ?> · <?= htmlspecialchars($a['sede']) ?></span>
                </div>
            </div>

            <!-- Tipo de sesión -->
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                <div style="
                    display:inline-flex;align-items:center;gap:6px;
                    background:<?= $a['grupo'] ? 'rgba(59,130,246,.1)' : 'rgba(16,185,129,.1)' ?>;
                    color:<?= $a['grupo'] ? 'var(--primary)' : '#059669' ?>;
                    border-radius:20px;padding:5px 12px;
                    font-size:.78rem;font-weight:700;
                ">
                    <?php if ($a['grupo']): ?>
                        <i class="bi bi-people-fill"></i> En grupo
                    <?php else: ?>
                        <i class="bi bi-person-fill"></i> Individual
                    <?php endif; ?>
                </div>
                <span class="status-badge active">Confirmada</span>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>