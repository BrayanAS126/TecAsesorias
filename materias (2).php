<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_alumno.php';

$pageTitle  = 'Materias y asesores | ' . APP_NAME;
$activeMenu = 'materias';

// Obtener datos reales de la BD
$materias = ql_get_materias_con_detalles();
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Materias y asesores</h2>
        <p class="page-section-subtitle">Consulta los asesores disponibles por materia y sus horarios.</p>
    </div>
    <a href="<?= BASE_URL ?>index.php?page=solicitar" class="btn-add-user" style="text-decoration:none;">
        <i class="bi bi-plus-lg"></i> Solicitar asesoría
    </a>
</div>

<!-- BÚSQUEDA -->
<div class="filters-card">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" class="form-control" placeholder="Buscar materia o asesor...">
    </div>
</div>

<!-- LEYENDA -->
<div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--text-gray);">
        <i class="bi bi-person-fill" style="color:var(--primary);"></i> Individual (sin alumnos aún)
    </div>
    <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--text-gray);">
        <i class="bi bi-people-fill" style="color:var(--primary);"></i> En grupo (ya hay alumnos)
    </div>
    <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--text-gray);">
        <i class="bi bi-lock-fill" style="color:var(--text-gray);"></i> Cerrado (no disponible)
    </div>
</div>

<!-- MATERIAS -->
<?php foreach ($materias as $mat): ?>
<div class="table-card mb-3" style="padding:22px;">

    <!-- Nombre de la materia -->
    <div style="
        display:flex;align-items:center;gap:12px;
        margin-bottom:18px;padding-bottom:14px;
        border-bottom:1px solid var(--border-color);
    ">
        <div class="stats-icon" style="margin-bottom:0;width:40px;height:40px;border-radius:12px;font-size:1.1rem;">
            <i class="bi bi-book-fill"></i>
        </div>
        <div style="font-weight:700;font-size:1rem;color:var(--text-main);">
            <?= htmlspecialchars($mat['nombre']) ?>
        </div>
        <span class="role-badge advisor" style="margin-left:auto;">
            <?= count($mat['asesores']) ?> asesor<?= count($mat['asesores']) !== 1 ? 'es' : '' ?>
        </span>
    </div>

    <!-- Asesores de esta materia -->
    <?php foreach ($mat['asesores'] as $a): ?>
    <div style="margin-bottom:18px;">

        <!-- Info del asesor -->
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="
                width:34px;height:34px;border-radius:50%;
                background:var(--primary);color:#fff;
                display:flex;align-items:center;justify-content:center;
                font-weight:700;font-size:.72rem;flex-shrink:0;
            "><?= htmlspecialchars($a['iniciales']) ?></div>
            <span style="font-weight:600;font-size:.9rem;color:var(--primary);">
                <?= htmlspecialchars($a['nombre']) ?>
            </span>
        </div>

        <!-- Horarios del asesor -->
        <div style="display:flex;flex-wrap:wrap;gap:8px;padding-left:44px;">
            <?php foreach ($a['horarios'] as $h): ?>
            <?php if ($h['cerrado']): ?>
                <!-- Grupo cerrado: mostrar pero no clickeable -->
                <div style="
                    display:inline-flex;align-items:center;gap:6px;
                    background:rgba(148,163,184,.12);
                    border:1px solid var(--border-color);
                    border-radius:12px;padding:7px 12px;
                    opacity:.65;
                ">
                    <i class="bi bi-lock-fill" style="font-size:.8rem;color:var(--text-gray);"></i>
                    <div>
                        <div style="font-size:.78rem;font-weight:600;color:var(--text-gray);">
                            <?= htmlspecialchars($h['dia']) ?>
                        </div>
                        <div style="font-size:.72rem;color:var(--text-gray);">
                            <?= htmlspecialchars($h['hora']) ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Grupo abierto -->
                <a href="<?= BASE_URL ?>index.php?page=solicitar"
                   style="
                    display:inline-flex;align-items:center;gap:8px;
                    background:var(--body-bg);
                    border:1.5px solid var(--border-color);
                    border-radius:12px;padding:8px 14px;
                    text-decoration:none;
                    transition:.2s ease;
                   "
                   onmouseover="this.style.borderColor='var(--primary-light)';this.style.background='rgba(59,130,246,.05)'"
                   onmouseout="this.style.borderColor='var(--border-color)';this.style.background='var(--body-bg)'">

                    <!-- Ícono individual o grupo -->
                    <?php if ($h['alumnos'] === 0): ?>
                        <i class="bi bi-person-fill" style="color:var(--primary);font-size:.9rem;"></i>
                    <?php else: ?>
                        <i class="bi bi-people-fill" style="color:var(--primary);font-size:.9rem;"></i>
                    <?php endif; ?>

                    <div>
                        <div style="font-size:.8rem;font-weight:700;color:var(--primary);">
                            <?= htmlspecialchars($h['dia']) ?>
                        </div>
                        <div style="font-size:.72rem;color:var(--text-gray);">
                            <?= htmlspecialchars($h['hora']) ?>
                            · <?= htmlspecialchars($h['salon']) ?>
                        </div>
                    </div>
                </a>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

    </div>
    <?php endforeach; ?>

</div>
<?php endforeach; ?>
