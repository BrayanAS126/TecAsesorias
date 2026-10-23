<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_asesor.php';

$pageTitle  = 'Gestionar Horarios | ' . APP_NAME;
$activeMenu = 'horarios_gestion';

$asesorId = $_SESSION['asesor_id'] ?? 0;
$horarios = qs_get_mi_disponibilidad($asesorId);
$salones  = qs_get_todos_salones();

$diasSemana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Gestionar mis horarios</h2>
        <p class="page-section-subtitle">Define tu disponibilidad para ofrecer asesorías.</p>
    </div>
    <button class="btn-add-user" data-bs-toggle="modal" data-bs-target="#modalHorario">
        <i class="bi bi-plus-lg"></i> Nuevo horario
    </button>
</div>

<!-- ALERTAS -->
<?php if (isset($_GET['status'])): ?>
    <div class="alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-<?= $_GET['status'] === 'success' ? 'check-circle-fill' : 'exclamation-octagon-fill' ?> me-2"></i>
        <strong><?= $_GET['status'] === 'success' ? '¡Éxito!' : 'Error:' ?></strong> 
        <?= $_GET['status'] === 'success' ? 'La operación se realizó correctamente.' : 'No se pudo procesar la solicitud.' ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- TABLA DE HORARIOS -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Hora</th>
                    <th>Modalidad</th>
                    <th>Salón / Enlace</th>
                    <th>Cupo</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($horarios)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aún no tienes horarios registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($horarios as $h): ?>
                <tr>
                    <td style="font-weight:700;"><?= htmlspecialchars($h['dia_semana']) ?></td>
                    <td><?= substr($h['hora_inicio'], 0, 5) ?> - <?= substr($h['hora_fin'], 0, 5) ?></td>
                    <td>
                        <span class="role-badge <?= $h['modalidad'] === 'Presencial' ? 'advisor' : 'student' ?>">
                            <?= htmlspecialchars($h['modalidad']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($h['modalidad'] === 'Presencial'): ?>
                            <i class="bi bi-building me-1"></i><?= htmlspecialchars($h['nombre_salon'] ?? 'Sin asignar') ?>
                        <?php else: ?>
                            <i class="bi bi-link-45deg me-1"></i><?= htmlspecialchars($h['enlace_virtual'] ?? 'N/A') ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="role-badge <?= $h['cupo'] > 0 ? 'active' : 'inactive' ?>">
                            <?= $h['cupo'] > 0 ? 'Abierto' : 'Cerrado' ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions justify-content-center">
                            <button class="action-btn edit" 
                                    title="Editar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalHorario"
                                    data-id="<?= $h['id_disponibilidad'] ?>"
                                    data-dia="<?= htmlspecialchars($h['dia_semana']) ?>"
                                    data-inicio="<?= substr($h['hora_inicio'], 0, 5) ?>"
                                    data-fin="<?= substr($h['hora_fin'], 0, 5) ?>"
                                    data-modalidad="<?= htmlspecialchars($h['modalidad']) ?>"
                                    data-salon="<?= $h['id_salon'] ?>"
                                    data-enlace="<?= htmlspecialchars($h['enlace_virtual'] ?? '') ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="action-btn delete" 
                                    title="Eliminar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $h['id_disponibilidad'] ?>"
                                    data-dia="<?= htmlspecialchars($h['dia_semana']) ?>"
                                    data-hora="<?= substr($h['hora_inicio'], 0, 5) ?> - <?= substr($h['hora_fin'], 0, 5) ?>">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL GUARDAR / EDITAR HORARIO -->
<div class="modal fade" id="modalHorario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:none; padding:10px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Nuevo Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formHorario" action="<?= BASE_URL ?>guardar_disponibilidad.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id_disponibilidad" id="disp_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Día de la semana</label>
                        <select name="dia_semana" id="disp_dia" class="form-select custom-select" required>
                            <?php foreach ($diasSemana as $dia): ?>
                                <option value="<?= $dia ?>"><?= $dia ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hora inicio</label>
                            <input type="time" name="hora_inicio" id="disp_inicio" class="form-control" required style="border-radius:12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hora fin</label>
                            <input type="time" name="hora_fin" id="disp_fin" class="form-control" required style="border-radius:12px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Modalidad</label>
                        <select name="modalidad" id="disp_modalidad" class="form-select custom-select" required onchange="toggleModalidadFields(this.value)">
                            <option value="Presencial">Presencial</option>
                            <option value="En línea">En línea</option>
                        </select>
                    </div>

                    <div id="presencialFields" class="mb-3">
                        <label class="form-label small fw-bold">Salón</label>
                        <select name="id_salon" id="disp_salon" class="form-select custom-select">
                            <option value="">Seleccionar salón...</option>
                            <?php foreach ($salones as $s): ?>
                                <option value="<?= $s['id_salon'] ?>"><?= htmlspecialchars($s['nombre_salon']) ?> (<?= htmlspecialchars($s['ubicacion']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="onlineFields" class="mb-3" style="display:none;">
                        <label class="form-label small fw-bold">Enlace Virtual</label>
                        <input type="url" name="enlace_virtual" id="disp_enlace" class="form-control" placeholder="Ej. https://meet.google.com/abc-xyz" style="border-radius:12px;">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-add-user" id="btnSubmit">Crear Horario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL ELIMINAR -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content" style="border-radius:24px; border:none; padding:10px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Eliminar horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>eliminar_disponibilidad.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id_disponibilidad" id="delete_id">
                    <p class="text-muted small">
                        ¿Estás seguro que deseas eliminar el horario del
                        <strong><span id="delete_dia" class="text-dark"></span></strong>
                        de <strong id="delete_hora" class="text-dark"></strong>?
                        <br><br>
                        <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Esto cancelará cualquier solicitud pendiente para este horario.</span>
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-add-user" style="background:#DC2626;">Confirmar eliminación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalHorario = document.getElementById('modalHorario');
    const modalEliminar = document.getElementById('modalEliminar');

    window.toggleModalidadFields = function(modalidad) {
        document.getElementById('presencialFields').style.display = modalidad === 'Presencial' ? 'block' : 'none';
        document.getElementById('onlineFields').style.display = modalidad === 'En línea' ? 'block' : 'none';
        
        const salonSelect = document.getElementById('disp_salon');
        const enlaceInput = document.getElementById('disp_enlace');
        
        if (modalidad === 'En línea') salonSelect.value = '';
        if (modalidad === 'Presencial') enlaceInput.value = '';
    };

    if (modalHorario) {
        modalHorario.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            const id = btn.getAttribute('data-id');
            const form = document.getElementById('formHorario');
            
            if (id) {
                document.getElementById('modalTitle').textContent = 'Editar Horario';
                document.getElementById('btnSubmit').textContent = 'Guardar Cambios';
                form.action = '<?= BASE_URL ?>actualizar_disponibilidad.php';
                document.getElementById('disp_id').value = id;
                document.getElementById('disp_dia').value = btn.getAttribute('data-dia');
                document.getElementById('disp_inicio').value = btn.getAttribute('data-inicio');
                document.getElementById('disp_fin').value = btn.getAttribute('data-fin');
                document.getElementById('disp_modalidad').value = btn.getAttribute('data-modalidad');
                document.getElementById('disp_salon').value = btn.getAttribute('data-salon');
                document.getElementById('disp_enlace').value = btn.getAttribute('data-enlace');
                toggleModalidadFields(btn.getAttribute('data-modalidad'));
            } else {
                document.getElementById('modalTitle').textContent = 'Nuevo Horario';
                document.getElementById('btnSubmit').textContent = 'Crear Horario';
                form.action = '<?= BASE_URL ?>guardar_disponibilidad.php';
                form.reset();
                document.getElementById('disp_id').value = '';
                toggleModalidadFields('Presencial'); // Default para nuevo horario
            }
        });
    }

    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            document.getElementById('delete_id').value = btn.getAttribute('data-id');
            document.getElementById('delete_dia').textContent = btn.getAttribute('data-dia');
            document.getElementById('delete_hora').textContent = btn.getAttribute('data-hora');
        });
    }
});
</script>