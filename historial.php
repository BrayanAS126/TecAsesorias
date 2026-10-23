<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_alumno.php';

$pageTitle  = 'Historial | ' . APP_NAME;
$activeMenu = 'historial';

// Obtener historial real de la base de datos
$alumnoId = $_SESSION['alumno_id'] ?? 0;
$historial = ql_get_historial_asesorias($alumnoId);
?>

<!-- ENCABEZADO -->
<div class="mb-4">
    <h2 class="page-section-title">Historial de asesorías</h2>
    <p class="page-section-subtitle">Tus asesorías completadas con materia, asesor, fecha y tema.</p>
</div>

<!-- FILTROS -->
<div class="filters-card">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Buscar materia, asesor o tema...">
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <select class="form-select custom-select">
                <option value="">Todas las materias</option>
                <?php foreach (array_unique(array_column($historial, 'materia')) as $m): ?>
                <option><?= htmlspecialchars($m) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- HISTORIAL -->
<?php if (empty($historial)): ?>
<div class="table-card" style="padding:48px;text-align:center;">
    <div style="font-size:2.5rem;margin-bottom:12px;opacity:.3;">📖</div>
    <div style="font-weight:700;color:var(--primary);margin-bottom:6px;">Sin asesorías completadas</div>
    <div style="font-size:.88rem;color:var(--text-gray);">
        Aquí aparecerán tus asesorías una vez que las completes.
    </div>
</div>
<?php else: ?>

<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Materia</th>
                    <th>Asesor</th>
                    <th>Horario</th>
                    <th>Salón</th>
                    <th>Tipo</th>
                    <th>Tema visto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $h): ?>
                <tr>
                    <td style="font-size:.85rem;color:var(--text-gray);white-space:nowrap;">
                        <?= htmlspecialchars($h['fecha']) ?>
                    </td>
                    <td style="font-weight:600;">
                        <?= htmlspecialchars($h['materia']) ?>
                    </td>
                    <td>
                        <span style="font-size:.875rem;">
                            <?= htmlspecialchars($h['asesor']) ?>
                        </span>
                    </td>
                    <td style="font-size:.85rem;white-space:nowrap;">
                        <?= htmlspecialchars($h['dia']) ?>
                        <span style="color:var(--text-gray);"> · <?= htmlspecialchars($h['hora']) ?></span>
                    </td>
                    <td>
                        <span class="role-badge advisor">
                            <i class="bi bi-building me-1"></i>
                            <?= htmlspecialchars($h['salon']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($h['grupo']): ?>
                            <span style="
                                display:inline-flex;align-items:center;gap:4px;
                                font-size:.78rem;color:var(--primary);font-weight:600;
                            ">
                                <i class="bi bi-people-fill"></i> Grupo
                            </span>
                        <?php else: ?>
                            <span style="
                                display:inline-flex;align-items:center;gap:4px;
                                font-size:.78rem;color:#059669;font-weight:600;
                            ">
                                <i class="bi bi-person-fill"></i> Individual
                            </span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.85rem;">
                        <?php if ($h['tema']): ?>
                            <?= htmlspecialchars($h['tema']) ?>
                        <?php else: ?>
                            <span style="color:var(--text-gray);font-style:italic;">Sin registrar</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>
