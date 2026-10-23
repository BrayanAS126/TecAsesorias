<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_docente.php';

$pageTitle  = 'Mis Asesores | ' . APP_NAME;
$activeMenu = 'usuarios';

$materiaId = $_SESSION['materia_docente_id'] ?? 0;
$asesores = qd_get_asesores_materia($materiaId);

$estadoLabels = [1 => 'Validado', 0 => 'Pendiente'];
$estadoClases = [1 => 'active',   0 => 'inactive'];
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Asesores asignados</h2>
        <p class="page-section-subtitle">Supervisa el progreso y estado de los asesores de tu materia.</p>
    </div>
</div>

<!-- FILTROS -->
<div class="filters-card">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Buscar usuario...">
            </div>
        </div>
        <div class="col-md-4 col-lg-3">
            <select class="form-select custom-select">
                <option value="">Todas las carreras</option>
                <?php foreach (array_unique(array_column($asesores, 'carrera')) as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <select class="form-select custom-select">
                <option value="">Estado</option>
                <option value="Validado">Validado</option>
                <option value="Pendiente">Pendiente</option>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <a href="<?= BASE_URL ?>exportar_asesores.php" class="btn-export w-100" style="text-decoration:none;">
                <i class="bi bi-download"></i> Exportar
            </a>
        </div>
    </div>
</div>

<!-- TABLA -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Asesor</th>
                    <th>Correo</th>
                    <th>N° Control</th>
                    <th>Estado</th>
                    <th class="text-center">Sesiones dadas</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($asesores)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay asesores registrados para tu materia.</td></tr>
                <?php endif; ?>
                <?php foreach ($asesores as $a): ?>
                <?php 
                    $p = explode(' ', trim($a['nombre_completo']));
                    $ini = strtoupper(substr($p[0] ?? '?', 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
                    $statusLabel = $estadoLabels[$a['validado']];
                    $statusClass = $estadoClases[$a['validado']];
                ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar"><?= $ini ?></div>
                            <div>
                                <h6 style="color:var(--text-main);"><?= htmlspecialchars($a['nombre_completo']) ?></h6>
                                <span><?= htmlspecialchars($a['carrera']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.85rem;"><?= htmlspecialchars($a['correo']) ?></td>
                    <td><code><?= htmlspecialchars($a['no_control']) ?></code></td>
                    <td><span class="status-badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                    <td class="text-center">
                        <span class="role-badge advisor"><?= $a['sesiones_dadas'] ?></span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="<?= BASE_URL ?>index.php?page=reportes&asesor=<?= $a['id_asesor'] ?>" class="action-btn view" title="Ver bitácora detallada">
                                <i class="bi bi-journal-text"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.search-box input');
    const carreraSelect = document.querySelectorAll('.custom-select')[0];
    const estadoSelect = document.querySelectorAll('.custom-select')[1];
    const rows = document.querySelectorAll('tbody tr');

    if (searchInput && carreraSelect && estadoSelect) {
        const filterTable = () => {
            const term = searchInput.value.toLowerCase();
            const carrera = carreraSelect.value.toLowerCase();
            const estado = estadoSelect.value.toLowerCase();

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matchesSearch = text.includes(term);
                const matchesCarrera = carrera === "" || text.includes(carrera);
                const matchesEstado = estado === "" || text.includes(estado);

                row.style.display = (matchesSearch && matchesCarrera && matchesEstado) ? '' : 'none';
            });
        };

        searchInput.addEventListener('keyup', filterTable);
        carreraSelect.addEventListener('change', filterTable);
        estadoSelect.addEventListener('change', filterTable);
    }
});
</script>
