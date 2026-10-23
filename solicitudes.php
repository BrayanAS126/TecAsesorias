<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_alumno.php';

$pageTitle  = 'Mis solicitudes | ' . APP_NAME;
$activeMenu = 'solicitudes';

// Obtener solicitudes reales de la base de datos
$alumnoId = $_SESSION['alumno_id'] ?? 0;
$solicitudes = ql_get_mis_solicitudes($alumnoId);

$estadoClases = [
    'Pendiente'  => 'inactive',
    'Aceptada'   => 'active',
    'Rechazada'  => 'teacher',
    'Cancelada'  => 'admin',
    'Completada' => 'active',
];

$estadoIconos = [
    'Pendiente'  => 'bi-hourglass-split',
    'Aceptada'   => 'bi-check-circle-fill',
    'Rechazada'  => 'bi-x-circle-fill',
    'Cancelada'  => 'bi-slash-circle-fill',
    'Completada' => 'bi-journal-check',
];
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Mis solicitudes</h2>
        <p class="page-section-subtitle">Revisa el estado de tus solicitudes de asesoría.</p>
    </div>
    <a href="<?= BASE_URL ?>index.php?page=solicitar" class="btn-add-user" style="text-decoration:none;">
        <i class="bi bi-plus-lg"></i> Nueva solicitud
    </a>
</div>

<!-- ALERTAS DE STATUS -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> Tu solicitud de asesoría ha sido enviada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'cancelled'): ?>
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Solicitud cancelada.</strong> La asesoría se ha marcado como cancelada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'error'): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        <strong>Hubo un problema.</strong> No se pudo procesar la acción. Inténtalo de nuevo.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- FILTROS -->
<div class="filters-card">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Buscar materia o asesor...">
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <select class="form-select custom-select">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="aceptada">Aceptada</option>
                <option value="rechazada">Rechazada</option>
                <option value="cancelada">Cancelada</option>
            </select>
        </div>
    </div>
</div>

<!-- LISTA DE SOLICITUDES -->
<div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($solicitudes as $s): ?>
    <?php
        $estadoClass = $estadoClases[$s['estado']] ?? 'inactive';
        $estadoIcono = $estadoIconos[$s['estado']] ?? 'bi-circle';
    ?>
    <div class="table-card" style="padding:20px;">
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">

            <!-- Fecha -->
            <div style="
                background:var(--primary);color:#fff;
                border-radius:14px;padding:10px 16px;
                text-align:center;min-width:64px;flex-shrink:0;
            ">
                <div style="font-size:1.3rem;font-weight:700;line-height:1;">
                    <?= date('d', strtotime($s['fecha'])) ?>
                </div>
                <div style="font-size:.65rem;text-transform:uppercase;opacity:.8;margin-top:2px;">
                    <?= obtener_mes_corto($s['fecha']) ?>
                </div>
            </div>

            <!-- Info -->
            <div style="flex:1;">
                <div style="font-weight:700;font-size:.95rem;color:var(--primary);margin-bottom:4px;">
                    <?= htmlspecialchars($s['materia']) ?>
                </div>
                <div style="font-size:.83rem;color:var(--text-gray);display:flex;flex-wrap:wrap;gap:12px;">
                    <span><i class="bi bi-person-fill me-1"></i><?= htmlspecialchars($s['asesor']) ?></span>
                    <span><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($s['dia']) ?></span>
                    <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($s['hora']) ?></span>
                    <span>
                        <i class="bi bi-building me-1"></i>
                        <?= htmlspecialchars($s['salon']) ?> · <?= htmlspecialchars($s['sede']) ?>
                    </span>
                </div>
                <?php if ($s['nota']): ?>
                <div style="
                    margin-top:8px;font-size:.82rem;
                    color:var(--text-gray);font-style:italic;
                ">
                    <i class="bi bi-chat-left-text me-1"></i>
                    <?= htmlspecialchars($s['nota']) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Estado y acciones -->
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0;">
                <span class="role-badge <?= $estadoClass ?>" style="font-size:.78rem;">
                    <i class="bi <?= $estadoIcono ?> me-1"></i>
                    <?= ucfirst($s['estado']) ?>
                </span>

                <?php if ($s['estado'] === 'Pendiente' || $s['estado'] === 'Aceptada'): ?>
                <button
                    class="action-btn delete"
                    title="Solicitar cancelación"
                    style="width:auto;padding:5px 12px;border-radius:10px;font-size:.78rem;gap:5px;display:flex;align-items:center;"
                    data-bs-toggle="modal"
                    data-bs-target="#modalCancelar"
                    data-id="<?= $s['id'] ?>"
                    data-materia="<?= htmlspecialchars($s['materia']) ?>"
                    data-asesor="<?= htmlspecialchars($s['asesor']) ?>">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── MODAL CANCELAR ────────────────────────────────────────────────── -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:20px;border:none;padding:8px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Cancelar solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.9rem;color:var(--text-gray);">
                    ¿Confirmas que deseas cancelar tu solicitud de
                    <strong id="cancelarMateria">—</strong>
                    con <strong id="cancelarAsesor">—</strong>?
                </p>
                <!-- Brandon: action="cancelar_solicitud.php" method="POST" -->
                <form action="<?= BASE_URL ?>cancelar_solicitud.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="solicitud_id" id="cancelarId" value="">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">
                            Motivo (opcional)
                        </label>
                        <textarea
                            name="motivo"
                            class="form-control"
                            rows="3"
                            placeholder="¿Por qué deseas cancelar?"
                            style="border-radius:12px;"></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-export" data-bs-dismiss="modal">
                            No, mantener
                        </button>
                        <button type="submit" class="btn-add-user"
                            style="padding:10px 22px;background:#DC2626;">
                            <i class="bi bi-x-lg"></i> Sí, cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('modalCancelar')
        .addEventListener('show.bs.modal', e => {
            const b = e.relatedTarget;
            document.getElementById('cancelarId').value              = b.dataset.id;
            document.getElementById('cancelarMateria').textContent   = b.dataset.materia;
            document.getElementById('cancelarAsesor').textContent    = b.dataset.asesor;
        });

    // Buscador en tiempo real
    const searchInput = document.querySelector('.search-box input');
    const filterSelect = document.querySelector('.custom-select');
    const items = document.querySelectorAll('.table-card');

    function filtrar() {
        const term = searchInput.value.toLowerCase();
        const status = filterSelect.value.toLowerCase();

        items.forEach(item => {
            const text = item.innerText.toLowerCase();
            const matchesSearch = text.includes(term);
            const matchesStatus = status === "" || text.includes(status);
            item.style.display = (matchesSearch && matchesStatus) ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filtrar);
    filterSelect.addEventListener('change', filtrar);
});
</script>
