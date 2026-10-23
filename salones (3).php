<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_alumno.php';

$pageTitle  = 'Salones | ' . APP_NAME;
$activeMenu = 'salones';

// Obtener salones reales de la base de datos
$salones = ql_get_salones_agrupados();
?>

<!-- ENCABEZADO -->
<div class="mb-4">
    <h2 class="page-section-title">Salones de asesoría</h2>
    <p class="page-section-subtitle">Ubicación de los salones disponibles en ambas sedes.</p>
</div>

<!-- SEDES -->
<?php foreach ($salones as $sede => $lista): ?>

<h3 class="section-title">
    <i class="bi bi-geo-alt-fill me-2" style="color:var(--primary-light);"></i>
    <?= htmlspecialchars($sede) ?>
</h3>

<div class="row g-3 mb-4">
    <?php foreach ($lista as $s): ?>
    <div class="col-md-6 col-xl-4">
        <div class="table-card" style="padding:22px;">

            <!-- Ícono y nombre -->
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                <div class="stats-icon" style="
                    margin-bottom:0;width:44px;height:44px;
                    border-radius:14px;font-size:1.1rem;
                    background:<?= $sede === 'Tomás Aquino' ? 'rgba(59,130,246,.12)' : 'rgba(16,185,129,.12)' ?>;
                    color:<?= $sede === 'Tomás Aquino' ? 'var(--primary-light)' : '#059669' ?>;
                ">
                    <i class="bi bi-building"></i>
                </div>
                <div>
                    <div style="font-weight:700;font-size:.95rem;color:var(--primary);">
                        <?= htmlspecialchars($s['nombre']) ?>
                    </div>
                    <div style="font-size:.78rem;color:var(--text-gray);">
                        <?= htmlspecialchars($sede) ?>
                    </div>
                </div>
            </div>

            <!-- Detalles -->
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;
                    font-size:.83rem;padding:8px 0;
                    border-bottom:1px solid var(--border-color);">
                    <span style="color:var(--text-gray);">Edificio</span>
                    <span style="font-weight:600;color:var(--primary);">
                        <?= htmlspecialchars($s['edificio']) ?>
                    </span>
                </div>
                <div style="display:flex;justify-content:space-between;
                    font-size:.83rem;padding:8px 0;
                    border-bottom:1px solid var(--border-color);">
                    <span style="color:var(--text-gray);">Piso</span>
                    <span style="font-weight:600;color:var(--primary);">
                        <?= htmlspecialchars($s['piso']) ?>
                    </span>
                </div>
                <div style="display:flex;justify-content:space-between;
                    font-size:.83rem;padding:8px 0;">
                    <span style="color:var(--text-gray);">Capacidad</span>
                    <span style="font-weight:600;color:var(--primary);">
                        <?= $s['capacidad'] ?> personas
                    </span>
                </div>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php endforeach; ?>
