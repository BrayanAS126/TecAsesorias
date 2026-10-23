<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_docente.php';

$pageTitle  = 'Materias | ' . APP_NAME;
$activeMenu = 'materias';

$materiaId = $_SESSION['materia_docente_id'] ?? 0;
$materia = qd_get_detalle_materia($materiaId);
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Gestión de materias</h2>
        <p class="page-section-subtitle">Administra las materias disponibles para asesoría.</p>
    </div>
</div>

<!-- TABLA -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Clave</th>
                    <th>Nombre</th>
                    <th>Semestre</th>
                    <th>Asesores activos</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($materia): ?>
                <tr>
                    <td><code><?= htmlspecialchars($materia['clave']) ?></code></td>
                    <td style="color:var(--text-main); font-weight:700;"><?= htmlspecialchars($materia['nombre_materia']) ?></td>
                    <td><?= htmlspecialchars($materia['semestre_materia']) ?>° Semestre</td>
                    <td><span class="role-badge advisor"><?= (int) $materia['total_asesores'] ?> asesores activos</span></td>
                    <td>
                        <div class="actions justify-content-center">
                            <a href="<?= BASE_URL ?>index.php?page=usuarios" class="action-btn view" title="Ver asesores">
                                <i class="bi bi-people-fill"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No tienes una materia asignada actualmente.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
