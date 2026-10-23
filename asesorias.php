<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_asesor.php';

$pageTitle  = 'Mis asesorías | ' . APP_NAME;
$activeMenu = 'asesorias';

$asesorId = $_SESSION['asesor_id'] ?? 0;
$sesiones = qs_get_sesiones_bitacora($asesorId);

$estadoClases = [
    'Completada' => 'active',
    'Aceptada'   => 'inactive',
    'Cancelada'  => 'admin',
    'Pendiente'  => 'inactive'
];
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Mis asesorías</h2>
        <p class="page-section-subtitle">Historial de sesiones y registro de temas.</p>
    </div>
</div>

<!-- ALERTAS DE STATUS -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> El tema de la asesoría ha sido registrado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- FILTROS -->
<div class="filters-card">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Buscar tema o alumno...">
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <select class="form-select custom-select">
                <option value="">Todos los estados</option>
                <option value="completada">Completada</option>
                <option value="pendiente">Pendiente</option>
                <option value="cancelada">Cancelada</option>
            </select>
        </div>
    </div>
</div>

<!-- SESIONES AGRUPADAS -->
<?php foreach ($sesiones as $s): ?>
<?php $estadoClass = $estadoClases[$s['estado']] ?? 'inactive'; ?>

<div class="table-card mb-3" style="padding:22px;">

    <!-- Cabecera de la sesión -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">

        <div style="display:flex;align-items:center;gap:16px;">

            <!-- Fecha -->
            <div style="
                background:var(--primary);
                color:#fff;
                border-radius:14px;
                padding:8px 14px;
                text-align:center;
                min-width:60px;
                flex-shrink:0;
            ">
                <div style="font-size:1.2rem;font-weight:700;line-height:1;">
                    <?= date('d', strtotime($s['fecha'])) ?>
                </div>
                <div style="font-size:.65rem;text-transform:uppercase;opacity:.8;margin-top:2px;">
                    <?= obtener_mes_corto($s['fecha']) ?>
                </div>
            </div>

            <div>
                <div style="font-weight:700;font-size:.95rem;color:var(--text-main);">
                    <?= htmlspecialchars($s['dia']) ?>
                    <span style="color:var(--text-gray);font-weight:400;">
                        · <?= htmlspecialchars($s['hora']) ?>
                    </span>
                </div>
                <div style="font-size:.82rem;color:var(--text-gray);margin-top:2px;">
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($s['salon']) ?>
                    <span style="margin:0 6px;">·</span>
                    <i class="bi bi-people-fill me-1"></i><?= count($s['alumnos']) ?> alumno(s)
                </div>
            </div>

        </div>

        <div style="display:flex;align-items:center;gap:8px;">
            <span class="role-badge <?= $estadoClass ?>">
                <?= ucfirst($s['estado']) ?>
            </span>

            <?php if ($s['estado'] === 'Completada' || $s['estado'] === 'Aceptada'): ?>
            <button
                class="action-btn edit"
                title="<?= $s['tema'] ? 'Editar tema' : 'Registrar tema' ?>"
                data-bs-toggle="modal"
                data-bs-target="#modalTema"
                data-ids="<?= $s['ids_solicitudes'] ?>"
                data-fecha="<?= $s['fecha'] ?>"
                data-dia="<?= htmlspecialchars($s['dia']) ?>"
                data-hora="<?= htmlspecialchars($s['hora']) ?>"
                data-tema="<?= htmlspecialchars($s['tema']) ?>">
                <i class="bi bi-journal-plus"></i>
            </button>
            <?php endif; ?>
        </div>

    </div>

    <!-- Tema visto -->
    <div style="
        background:rgba(148,163,184,.08);
        border-radius:12px;
        padding:12px 16px;
        margin-bottom:14px;
        font-size:.875rem;
    ">
        <span style="font-weight:700;color:var(--text-main);">
            <i class="bi bi-journal-text me-1"></i>Tema:
        </span>
        <?php if ($s['tema']): ?>
            <span style="margin-left:6px;color:var(--text-main);opacity:.9;"><?= htmlspecialchars($s['tema']) ?></span>
        <?php else: ?>
            <span style="margin-left:6px;color:var(--text-gray);font-style:italic;">
                Sin registrar
                <?php if ($s['estado'] === 'Completada' || $s['estado'] === 'Aceptada'): ?>
                — <a href="#" style="color:var(--primary-light);text-decoration:none;"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTema"
                    data-ids="<?= $s['ids_solicitudes'] ?>"
                    data-fecha="<?= $s['fecha'] ?>"
                    data-dia="<?= htmlspecialchars($s['dia']) ?>"
                    data-hora="<?= htmlspecialchars($s['hora']) ?>"
                    data-tema="">
                    Registrar ahora
                </a>
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Lista de alumnos de la sesión -->
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php foreach ($s['alumnos'] as $alumno): ?>
        <div style="
            display:inline-flex;align-items:center;gap:8px;
            background:rgba(59, 130, 246, 0.04);
            border:1px solid var(--border-color);
            border-radius:20px;
            padding:4px 12px 4px 4px;
        ">
            <div style="
                width:26px;height:26px;border-radius:50%;
                background:var(--primary-light);color:#fff;
                display:flex;align-items:center;justify-content:center;
                font-size:.65rem;font-weight:800;flex-shrink:0;
            "><?= htmlspecialchars($alumno['iniciales']) ?></div>
            <span style="font-size:.82rem;font-weight:700;color:var(--text-main);">
                <?= htmlspecialchars($alumno['nombre']) ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<?php endforeach; ?>


<!-- ── MODAL REGISTRAR / EDITAR TEMA ─────────────────────────────────── -->
<div class="modal fade" id="modalTema" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content" style="border-radius:20px;border:none;padding:8px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Registrar tema visto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.88rem;color:var(--text-gray);margin-bottom:16px;">
                    Sesión: <strong id="temaDia">—</strong>
                    · <strong id="temaHora">—</strong>
                </p>
                <form action="<?= BASE_URL ?>registrar_tema.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="solicitudes_ids" id="temaIds" value="">
                    <input type="hidden" name="fecha" id="temaFecha" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">
                            Tema visto en la sesión
                        </label>
                        <textarea
                            name="tema"
                            id="temaTexto"
                            class="form-control"
                            rows="4"
                            placeholder="Describe brevemente el tema abordado..."
                            style="border-radius:12px;"
                            required></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-add-user" style="padding:10px 22px;">
                            <i class="bi bi-journal-check"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('modalTema')
        .addEventListener('show.bs.modal', e => {
            const b = e.relatedTarget;
            document.getElementById('temaIds').value   = b.dataset.ids;
            document.getElementById('temaFecha').value = b.dataset.fecha;
            document.getElementById('temaDia').textContent  = b.dataset.dia;
            document.getElementById('temaHora').textContent = b.dataset.hora;
            document.getElementById('temaTexto').value = b.dataset.tema || '';
        });

    // Buscador en tiempo real
    const searchInput = document.querySelector('.search-box input');
    const filterSelect = document.querySelector('.custom-select');
    const cards = document.querySelectorAll('.table-card');

    function filtrar() {
        const term = searchInput.value.toLowerCase();
        const status = filterSelect.value.toLowerCase();

        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            const matchesSearch = text.includes(term);
            const matchesStatus = status === "" || text.includes(status);
            card.style.display = (matchesSearch && matchesStatus) ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filtrar);
    filterSelect.addEventListener('change', filtrar);
});
</script>