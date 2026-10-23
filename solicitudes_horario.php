<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_docente.php';

$pageTitle  = 'Solicitudes de horario | ' . APP_NAME;
$activeMenu = 'solicitudes_horario';

$materiaId   = $_SESSION['materia_docente_id'] ?? 0;
$solicitudes = qd_get_solicitudes_horario($materiaId);
$misAsesores = qd_get_asesores_materia($materiaId);
$salones     = qd_get_todos_salones();

$estadoClases = [
    'pendiente' => 'inactive',
    'aceptada'  => 'active',
    'rechazada' => 'teacher',
];

$estadoLabels = [
    'pendiente' => 'Pendiente',
    'aceptada'  => 'Aceptada',
    'rechazada' => 'Rechazada',
];
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Solicitudes de horario</h2>
        <p class="page-section-subtitle">
            Revisa los horarios solicitados por asesores y asigna salones por bloque.
        </p>
    </div>
    <button class="btn-add-user" data-bs-toggle="modal" data-bs-target="#modalAsignacionDirecta">
        <i class="bi bi-calendar-plus-fill"></i> Asignar nuevo horario
    </button>
</div>

<!-- ALERTAS DE STATUS -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> Los salones han sido asignados correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'rejected'): ?>
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Solicitud rechazada.</strong> Se ha notificado al asesor con el motivo indicado.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        <strong>Error al guardar:</strong> No se pudo procesar la asignación. Verifica que el asesor y el salón sean válidos.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'time_error'): ?>
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-clock-fill me-2"></i>
        <strong>Horario inválido:</strong> La hora de inicio debe ser anterior a la hora de fin (Ej. 11:00 AM a 12:00 PM).
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- FILTROS -->
<div class="filters-card">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Buscar asesor o materia...">
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <select class="form-select custom-select">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="aceptada">Aceptada</option>
                <option value="rechazada">Rechazada</option>
            </select>
        </div>
    </div>
</div>

<!-- LISTA DE SOLICITUDES -->
<?php foreach ($solicitudes as $s): ?>
<?php $estadoClass = $estadoClases[$s['estado']] ?? 'inactive'; ?>

<div class="table-card mb-3" style="padding: 24px;">

    <!-- Cabecera de la solicitud -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">

        <div class="d-flex align-items-center gap-3">
            <div class="user-avatar"><?= htmlspecialchars($s['iniciales']) ?></div>
            <div>
                <div style="font-weight:700;font-size:.95rem;">
                    <?= htmlspecialchars($s['nombre']) ?>
                </div>
                <div style="font-size:.82rem;color:var(--text-gray);">
                    <?= htmlspecialchars($s['correo']) ?>
                </div>
                <div style="font-size:.82rem;color:var(--text-gray);">
                    <i class="bi bi-book-fill me-1"></i>
                    <?= htmlspecialchars($s['materia']) ?>
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="role-badge <?= $estadoClass ?>">
                <?= $estadoLabels[$s['estado']] ?>
            </span>

            <?php if ($s['estado'] === 'pendiente'): ?>
                <!-- Botón rechazar solicitud completa -->
                <button
                    class="action-btn delete"
                    title="Rechazar solicitud"
                    data-bs-toggle="modal"
                    data-bs-target="#modalRechazar"
                    data-id="<?= $s['id_asesor'] ?>">
                    <i class="bi bi-x-lg"></i>
                </button>
            <?php endif; ?>
        </div>

    </div>

    <!-- Bloques de horario -->
    <form action="<?= BASE_URL ?>asignar_salones.php" method="POST">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="asesor_id" value="<?= $s['id_asesor'] ?>">

        <div class="table-responsive">
            <table class="table custom-table align-middle mb-3">
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Hora</th>
                        <th>Salón asignado</th>
                        <?php if ($s['estado'] === 'pendiente'): ?>
                            <th class="text-center">Disponibilidad</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($s['bloques'] as $i => $bloque): ?>
                    <tr>
                        <td style="font-weight:600;">
                            <i class="bi bi-calendar3 me-2" style="color:var(--primary-light);"></i>
                            <?= htmlspecialchars($bloque['dia']) ?>
                        </td>
                        <td>
                            <i class="bi bi-clock me-2" style="color:var(--primary-light);"></i>
                            <?= htmlspecialchars($bloque['hora']) ?>
                        </td>
                        <td>
                            <?php if ($s['estado'] === 'pendiente' || $s['estado'] === 'aceptada'): ?>
                                <!-- Docente elige salón por cada bloque -->
                                <select
                                    name="salon[<?= $bloque['id'] ?>]"
                                    class="form-select custom-select"
                                    style="max-width:260px;"
                                    required>
                                    <option value="">Seleccionar salón...</option>
                                    <?php foreach ($salones as $salon): ?>
                                        <option value="<?= $salon['id_salon'] ?>" <?= $bloque['id_salon'] == $salon['id_salon'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($salon['nombre_salon']) ?>
                                            — <?= htmlspecialchars($salon['ubicacion']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <span style="color:var(--text-gray);font-size:.85rem;">—</span>
                            <?php endif; ?>
                        </td>

                        <?php if ($s['estado'] === 'pendiente'): ?>
                        <td class="text-center">
                            <!-- Indicador visual de disponibilidad del salón -->
                            <!-- Brandon: verificar en BD si el salón está libre -->
                            <span class="status-badge active" style="font-size:.72rem;">
                                <i class="bi bi-check-circle-fill me-1"></i>Disponible
                            </span>
                        </td>
                        <?php endif; ?>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botón de acción para el docente -->
        <?php if ($s['estado'] === 'pendiente' || $s['estado'] === 'aceptada'): ?>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn-add-user" style="padding:10px 24px;">
                <i class="bi bi-check-lg"></i> <?= $s['estado'] === 'aceptada' ? 'Actualizar' : 'Confirmar' ?> asignación
            </button>
        </div>
        <?php endif; ?>

    </form>

</div>

<?php endforeach; ?>

<!-- ── MODAL ASIGNACIÓN DIRECTA (Martínez crea el horario) ────────────── -->
<div class="modal fade" id="modalAsignacionDirecta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:none; padding:10px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Nueva Asignación de Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>guardar_horario_docente.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Seleccionar Asesor</label>
                        <select name="id_asesor" class="form-select custom-select" required>
                            <option value="">-- Selecciona un asesor --</option>
                            <?php foreach ($misAsesores as $ase): ?>
                                <option value="<?= $ase['id_asesor'] ?>"><?= htmlspecialchars($ase['nombre_completo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Día de la semana</label>
                        <select name="dia_semana" class="form-select custom-select" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                        </select>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hora inicio</label>
                            <input type="time" name="hora_inicio" class="form-control" required style="border-radius:12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hora fin</label>
                            <input type="time" name="hora_fin" class="form-control" required style="border-radius:12px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Asignar Salón</label>
                        <select name="id_salon" class="form-select custom-select" required>
                            <option value="">-- Seleccionar salón --</option>
                            <?php foreach ($salones as $salon): ?>
                                <option value="<?= $salon['id_salon'] ?>"><?= htmlspecialchars($salon['nombre_salon']) ?> (<?= htmlspecialchars($salon['ubicacion']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-add-user">Finalizar Asignación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── MODAL RECHAZAR SOLICITUD ────────────────────────────────────────── -->
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:20px;border:none;padding:8px;">

            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Rechazar solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form action="<?= BASE_URL ?>rechazar_solicitud.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="solicitud_id" id="inputRechazarId" value="">

                    <p style="font-size:.9rem;color:var(--text-gray);">
                        El asesor será notificado del rechazo. Indica el motivo:
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">
                            Motivo del rechazo
                        </label>
                        <textarea
                            name="motivo"
                            class="form-control"
                            rows="3"
                            placeholder="Ej. No hay salones disponibles en ese horario..."
                            style="border-radius:12px;"
                            required></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn-export" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="btn-add-user"
                            style="padding:10px 22px;background:#DC2626;">
                            <i class="bi bi-x-lg"></i> Rechazar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
// Pasar el ID de la solicitud al modal de rechazo
// Brandon: este script ya está listo, no necesita cambios
document.addEventListener('DOMContentLoaded', () => {
    const modalRechazar = document.getElementById('modalRechazar');
    if (modalRechazar) {
        modalRechazar.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            const id  = btn.getAttribute('data-id');
            document.getElementById('inputRechazarId').value = id;
        });
    }

    // Buscador y Filtro en tiempo real
    const searchInput = document.querySelector('.search-box input');
    const statusSelect = document.querySelector('.custom-select');
    const cards = document.querySelectorAll('.table-card:not(.filters-card)');

    const filterCards = () => {
        const term = searchInput.value.toLowerCase();
        const status = statusSelect.value.toLowerCase();

        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            const matchesSearch = text.includes(term);
            const matchesStatus = status === "" || text.includes(status);
            card.style.display = (matchesSearch && matchesStatus) ? 'block' : 'none';
        });
    };

    if (searchInput) searchInput.addEventListener('keyup', filterCards);
    if (statusSelect) statusSelect.addEventListener('change', filterCards);
});
</script>
