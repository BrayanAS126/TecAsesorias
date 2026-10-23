<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_admin.php';

$pageTitle  = 'Materias | ' . APP_NAME;
$activeMenu = 'materias';
// Mensajes de operación
$successMessage = '';
$errorMessage = '';

// Capturar estatus desde la URL (Patrón PRG)
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') $successMessage = 'Materia procesada correctamente.';
    if ($_GET['status'] === 'error') $errorMessage = 'Error al procesar la materia.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errorMessage = 'Token de seguridad inválido. Recarga la página e inténtalo de nuevo.';
    } else {
        $action   = $_POST['action'];
        $id       = filter_input(INPUT_POST, 'id_materia', FILTER_VALIDATE_INT);
        $clave    = trim($_POST['clave'] ?? '');
        $nombre   = trim($_POST['nombre'] ?? '');
        $semestre = (int)($_POST['semestre'] ?? 0);

        if ($clave && $nombre && $semestre > 0) {
            if ($action === 'insert_materia') {
                qa_insert_materia($clave, $nombre, $semestre);
                header('Location: ' . BASE_URL . 'index.php?page=materias&status=success');
                exit;
            } elseif ($action === 'update_materia' && $id) {
                qa_update_materia($id, $clave, $nombre, $semestre);
                header('Location: ' . BASE_URL . 'index.php?page=materias&status=success');
                exit;
            }
        }
    }
}

$materias = qa_get_materias();
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Gestión de materias</h2>
        <p class="page-section-subtitle">Administra el catálogo de asignaturas y monitorea la oferta de asesores.</p>
    </div>
    <button class="btn-add-user" data-bs-toggle="modal" data-bs-target="#modalMateria">
        <i class="bi bi-book-half"></i> Nueva materia
    </button>
</div>

<!-- ALERTAS -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> <?= htmlspecialchars($successMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php elseif ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        <strong>Error:</strong> <?= htmlspecialchars($errorMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- BÚSQUEDA -->
<div class="filters-card">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="materiaSearch" class="form-control" placeholder="Buscar por clave o nombre de materia...">
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
                <?php foreach ($materias as $m): ?>
                <tr>
                    <td><code><?= htmlspecialchars($m['clave']) ?></code></td>
                    <td style="color:var(--text-main); font-weight:600;"><?= htmlspecialchars($m['nombre_materia']) ?></td>
                    <td><?= htmlspecialchars($m['semestre_materia']) ?>° Semestre</td>
                    <td>
                        <span class="role-badge advisor">
                            <?= (int) $m['total_asesores'] ?> asesor<?= $m['total_asesores'] == 1 ? '' : 'es' ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions justify-content-center">
                            <button class="action-btn edit" 
                                    title="Editar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalMateria"
                                    data-id="<?= $m['id_materia'] ?>"
                                    data-clave="<?= htmlspecialchars($m['clave']) ?>"
                                    data-nombre="<?= htmlspecialchars($m['nombre_materia']) ?>"
                                    data-semestre="<?= $m['semestre_materia'] ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="action-btn delete" 
                                    title="Eliminar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $m['id_materia'] ?>"
                                    data-nombre="<?= htmlspecialchars($m['nombre_materia']) ?>">
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

<!-- MODAL GUARDAR / EDITAR -->
<div class="modal fade" id="modalMateria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:none; padding:10px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Nueva Materia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMateria" action="index.php?page=materias" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="action" id="materia_action" value="insert_materia">
                    <input type="hidden" name="id_materia" id="materia_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Clave de la Materia</label>
                        <input type="text" name="clave" id="materia_clave" class="form-control" placeholder="Ej. ACF-0901" required style="border-radius:12px;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre de la Asignatura</label>
                        <input type="text" name="nombre" id="materia_nombre" class="form-control" placeholder="Ej. Cálculo Diferencial" required style="border-radius:12px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Semestre</label>
                        <select name="semestre" id="materia_semestre" class="form-select custom-select" required>
                            <?php for($i=1; $i<=9; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?>° Semestre</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-add-user" id="btnSubmit">Crear Materia</button>
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
                <h5 class="modal-title fw-bold">Eliminar materia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>eliminar_materia.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id_materia" id="delete_id">
                    <p class="text-muted small">
                        ¿Estás seguro que deseas eliminar <strong><span id="delete_nombre" class="text-dark"></span></strong>?
                        <br><br>
                        <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Esta acción podría fallar si la materia tiene asesores vinculados.</span>
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
    const modalMateria = document.getElementById('modalMateria');
    const modalEliminar = document.getElementById('modalEliminar');

    if (modalMateria) {
        modalMateria.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            const id = btn.getAttribute('data-id');
            const form = document.getElementById('formMateria');
            
            if (id) {
                document.getElementById('modalTitle').textContent = 'Editar Materia';
                document.getElementById('btnSubmit').textContent = 'Guardar Cambios';
                document.getElementById('materia_action').value = 'update_materia';
                document.getElementById('materia_id').value = id;
                document.getElementById('materia_clave').value = btn.getAttribute('data-clave');
                document.getElementById('materia_nombre').value = btn.getAttribute('data-nombre');
                document.getElementById('materia_semestre').value = btn.getAttribute('data-semestre');
            } else {
                document.getElementById('modalTitle').textContent = 'Nueva Materia';
                document.getElementById('btnSubmit').textContent = 'Crear Materia';
                document.getElementById('materia_action').value = 'insert_materia';
                form.reset();
                document.getElementById('materia_id').value = '';
            }
        });
    }

    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            document.getElementById('delete_id').value = btn.getAttribute('data-id');
            document.getElementById('delete_nombre').textContent = btn.getAttribute('data-nombre');
        });
    }

    // Buscador en tiempo real
    document.getElementById('materiaSearch')?.addEventListener('keyup', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
});
</script>
