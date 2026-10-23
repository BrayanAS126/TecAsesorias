<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_asesor.php';

$pageTitle  = 'Solicitudes | ' . APP_NAME;
$activeMenu = 'solicitudes';

$asesorId = $_SESSION['asesor_id'] ?? 0;
$solicitudes = qs_get_todas_solicitudes($asesorId);

// Obtenemos disponibilidad para el estado de grupos
$disponibilidad = qs_get_mi_disponibilidad($asesorId);

$estadoClases = [
    'Pendiente' => 'inactive',
    'Aceptada'  => 'active',
    'Rechazada' => 'teacher',
    'Cancelada' => 'admin',
    'Completada'=> 'active',
];
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Solicitudes recibidas</h2>
        <p class="page-section-subtitle">Acepta o rechaza solicitudes. Puedes tener varios alumnos por horario.</p>
    </div>
</div>

<!-- ALERTAS DE STATUS -->
<?php if (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> El estado de la solicitud ha sido actualizado.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- ESTADO DE GRUPOS -->
<div class="filters-card mb-4">
    <div style="font-size:.85rem;font-weight:700;color:var(--primary);margin-bottom:12px;">
        Estado de tus grupos
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php foreach ($disponibilidad as $d): ?>
        <?php 
            $dia = $d['dia_semana'];
            $hora = substr($d['hora_inicio'], 0, 5) . ' - ' . substr($d['hora_fin'], 0, 5);
            $cerrado = $d['cupo'] == 0; 
        ?>
        <div style="
            display:flex;align-items:center;gap:10px;
            background:var(--body-bg);
            border:1px solid var(--border-color);
            border-radius:14px;padding:10px 16px;
        ">
            <div>
                <div style="font-size:.82rem;font-weight:700;color:var(--primary);">
                    <?= htmlspecialchars($dia) ?> · <?= htmlspecialchars($hora) ?>
                </div>
                <div style="display:flex;align-items:center;gap:6px;margin-top:4px;">
                    <?php if ($cerrado): ?>
                        <span style="
                            display:inline-flex;align-items:center;gap:4px;
                            background:rgba(220,38,38,.1);color:#DC2626;
                            border-radius:20px;padding:2px 10px;
                            font-size:.72rem;font-weight:700;
                        ">
                            <i class="bi bi-lock-fill"></i> Cerrado
                        </span>
                    <?php else: ?>
                        <span style="
                            display:inline-flex;align-items:center;gap:4px;
                            background:rgba(16,185,129,.1);color:#059669;
                            border-radius:20px;padding:2px 10px;
                            font-size:.72rem;font-weight:700;
                        ">
                            <i class="bi bi-unlock-fill"></i> Abierto
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Botón cerrar/abrir grupo -->
            <!-- Brandon: POST a toggle_grupo.php con bloque_id -->
            <form action="toggle_grupo.php" method="POST" style="margin-left:8px;">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="id_disponibilidad" value="<?= $d['id_disponibilidad'] ?>">
                <input type="hidden" name="cerrado" value="<?= $cerrado ? '1' : '0' ?>">
                <button type="submit"
                    title="<?= $cerrado ? 'Abrir grupo' : 'Cerrar grupo' ?>"
                    style="
                        border:none;cursor:pointer;border-radius:10px;
                        padding:6px 12px;font-size:.78rem;font-weight:700;
                        background:<?= $cerrado ? 'rgba(16,185,129,.1)' : 'rgba(220,38,38,.1)' ?>;
                        color:<?= $cerrado ? '#059669' : '#DC2626' ?>;
                        display:flex;align-items:center;gap:5px;
                    ">
                    <i class="bi bi-<?= $cerrado ? 'unlock' : 'lock' ?>-fill"></i>
                    <?= $cerrado ? 'Abrir' : 'Cerrar' ?>
                </button>
            </form>

        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- FILTROS -->
<div class="filters-card">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Buscar alumno...">
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <select class="form-select custom-select">
                <option value="">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="aceptada">Aceptada</option>
                <option value="rechazada">Rechazada</option>
                <option value="cancelada">Cancelada</option>
                <option value="completada">Completada</option>
            </select>
        </div>
    </div>
</div>

<!-- TABLA -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Día</th>
                    <th>Hora</th>
                    <th>Salón</th>
                    <th>Grupo</th>
                    <th>Fecha solicitud</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitudes as $s): ?>
                <?php
                    $estadoClass = $estadoClases[$s['estado']] ?? 'inactive';
                    
                    // Buscar estado del grupo en la lista de disponibilidad
                    $dispActual = array_filter($disponibilidad, fn($d) => $d['id_disponibilidad'] == $s['id_disponibilidad']);
                    $dispActual = reset($dispActual);
                    $grupoCerrado = $dispActual && $dispActual['cupo'] == 0;

                    $p = explode(' ', trim($s['nombre']));
                    $ini = strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
                ?>
                <tr>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar"><?= $ini ?></div>
                            <div>
                                <h6><?= htmlspecialchars($s['nombre']) ?></h6>
                                <span><?= htmlspecialchars($s['correo']) ?></span>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($s['dia']) ?></td>
                    <td>
                        <i class="bi bi-clock me-1" style="color:var(--primary-light);"></i>
                        <?= htmlspecialchars($s['hora']) ?>
                    </td>
                    <td>
                        <i class="bi bi-building me-1" style="color:var(--primary-light);"></i>
                        <?= htmlspecialchars($s['salon']) ?>
                    </td>

                    <!-- Estado del grupo del horario solicitado -->
                    <td>
                        <?php if ($grupoCerrado): ?>
                            <span style="
                                display:inline-flex;align-items:center;gap:4px;
                                background:rgba(220,38,38,.1);color:#DC2626;
                                border-radius:20px;padding:2px 10px;
                                font-size:.72rem;font-weight:700;
                            ">
                                <i class="bi bi-lock-fill"></i> Cerrado
                            </span>
                        <?php else: ?>
                            <span style="
                                display:inline-flex;align-items:center;gap:4px;
                                background:rgba(16,185,129,.1);color:#059669;
                                border-radius:20px;padding:2px 10px;
                                font-size:.72rem;font-weight:700;
                            ">
                                <i class="bi bi-unlock-fill"></i> Abierto
                            </span>
                        <?php endif; ?>
                    </td>

                    <td style="color:var(--text-gray);font-size:.85rem;">
                        <?= date('d/m/Y', strtotime($s['fecha'])) ?>
                    </td>
                    <td>
                        <span class="role-badge <?= $estadoClass ?>">
                            <?= ucfirst($s['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <button
                                class="action-btn view"
                                title="Ver detalle"
                                data-bs-toggle="modal"
                                data-bs-target="#modalDetalle"
                                data-nombre="<?= htmlspecialchars($s['nombre']) ?>"
                                data-dia="<?= htmlspecialchars($s['dia']) ?>"
                                data-hora="<?= htmlspecialchars($s['hora']) ?>"
                                data-salon="<?= htmlspecialchars($s['salon']) ?>"
                                data-estado="<?= htmlspecialchars($s['estado']) ?>"
                                data-nota="<?= htmlspecialchars($s['nota']) ?>">
                                <i class="bi bi-eye"></i>
                            </button>

                            <?php if ($s['estado'] === 'Pendiente'): ?>
                            <button
                                class="action-btn"
                                title="Aceptar solicitud"
                                style="background:rgba(16,185,129,.1);color:#059669;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAceptar"
                                data-id="<?= $s['id'] ?>"
                                data-nombre="<?= htmlspecialchars($s['nombre']) ?>">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button
                                class="action-btn delete"
                                title="Rechazar solicitud"
                                data-bs-toggle="modal"
                                data-bs-target="#modalRechazar"
                                data-id="<?= $s['id'] ?>"
                                data-nombre="<?= htmlspecialchars($s['nombre']) ?>">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── MODAL VER DETALLE ──────────────────────────────────────────────── -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:20px;border:none;padding:8px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Detalle de solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div style="display:flex;flex-direction:column;gap:0;">
                    <?php foreach ([
                        'detNombre' => 'Alumno',
                        'detDia'    => 'Día',
                        'detHora'   => 'Hora',
                        'detSalon'  => 'Salón',
                        'detEstado' => 'Estado',
                    ] as $id => $label): ?>
                    <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-color);">
                        <span style="font-size:.85rem;color:var(--text-gray);"><?= $label ?></span>
                        <span style="font-size:.85rem;font-weight:600;" id="<?= $id ?>">—</span>
                    </div>
                    <?php endforeach; ?>
                    <div id="detNotaWrap" style="display:none;padding:10px 0;">
                        <span style="font-size:.85rem;color:var(--text-gray);">Nota:</span>
                        <p style="font-size:.85rem;margin:4px 0 0;" id="detNota"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── MODAL ACEPTAR ─────────────────────────────────────────────────── -->
<div class="modal fade" id="modalAceptar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px;">
        <div class="modal-content" style="border-radius:20px;border:none;padding:8px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Aceptar solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.9rem;color:var(--text-gray);">
                    ¿Confirmas que aceptas la solicitud de
                    <strong id="aceptarNombre">—</strong>?
                    El alumno se unirá al grupo de ese horario.
                </p>
                <form action="<?= BASE_URL ?>cambiar_estatus.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="solicitud_id" id="aceptarId" value="">
                    <input type="hidden" name="estado" value="Aceptada">
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-add-user" style="padding:10px 22px;">
                            <i class="bi bi-check-lg"></i> Aceptar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── MODAL RECHAZAR ────────────────────────────────────────────────── -->
<div class="modal fade" id="modalRechazar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content" style="border-radius:20px;border:none;padding:8px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Rechazar solicitud</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.9rem;color:var(--text-gray);">
                    Indica el motivo del rechazo para
                    <strong id="rechazarNombre">—</strong>:
                </p>
                <form action="<?= BASE_URL ?>cambiar_estatus.php" method="POST">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="solicitud_id" id="rechazarId" value="">
                    <input type="hidden" name="estado" value="Rechazada">
                    <div class="mb-3">
                        <textarea
                            name="nota"
                            class="form-control"
                            rows="3"
                            placeholder="Ej. El grupo ya está cerrado..."
                            style="border-radius:12px;"
                            required></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-add-user"
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
document.addEventListener('DOMContentLoaded', () => {

    document.getElementById('modalDetalle')
        .addEventListener('show.bs.modal', e => {
            const b = e.relatedTarget;
            document.getElementById('detNombre').textContent = b.dataset.nombre;
            document.getElementById('detDia').textContent    = b.dataset.dia;
            document.getElementById('detHora').textContent   = b.dataset.hora;
            document.getElementById('detSalon').textContent  = b.dataset.salon;
            document.getElementById('detEstado').textContent = b.dataset.estado;
            const nota = b.dataset.nota;
            const wrap = document.getElementById('detNotaWrap');
            wrap.style.display = nota ? 'block' : 'none';
            document.getElementById('detNota').textContent = nota || '';
        });

    document.getElementById('modalAceptar')
        .addEventListener('show.bs.modal', e => {
            const b = e.relatedTarget;
            document.getElementById('aceptarId').value           = b.dataset.id;
            document.getElementById('aceptarNombre').textContent = b.dataset.nombre;
        });

    document.getElementById('modalRechazar')
        .addEventListener('show.bs.modal', e => {
            const b = e.relatedTarget;
            document.getElementById('rechazarId').value           = b.dataset.id;
            document.getElementById('rechazarNombre').textContent = b.dataset.nombre;
        });

    // Buscador y Filtro en tiempo real
    const searchInput = document.querySelector('.search-box input');
    const filterSelect = document.querySelector('.custom-select');
    const rows = document.querySelectorAll('tbody tr');

    function filtrar() {
        const term = searchInput.value.toLowerCase();
        const status = filterSelect.value.toLowerCase();

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const matchesSearch = text.includes(term);
            const matchesStatus = status === "" || text.includes(status);
            row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filtrar);
    filterSelect.addEventListener('change', filtrar);
});
</script>