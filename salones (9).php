<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_docente.php';

$pageTitle  = 'Salones | ' . APP_NAME;
$activeMenu = 'salones';

$salones = qd_get_todos_salones();
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Gestión de salones</h2>
        <p class="page-section-subtitle">Administra los salones disponibles para asesorías.</p>
    </div>
</div>

<!-- TABLA -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Capacidad</th>
                    <th class="text-center">Uso actual</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salones as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['nombre_salon']) ?></td>
                    <td><?= htmlspecialchars($s['ubicacion']) ?></td>
                    <td><?= (int) $s['capacidad'] ?> personas</td>
                    <td class="text-center">
                        <span class="role-badge <?= $s['usos'] > 0 ? 'advisor' : 'student' ?>">
                            <?= $s['usos'] ?> horarios asignados
                        </span>
                    </td>
                    <td>
                        <div class="actions justify-content-center">
                            <i class="bi bi-info-circle text-muted"></i>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
