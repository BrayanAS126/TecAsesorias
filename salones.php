<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_admin.php';

$pageTitle  = 'Salones | ' . APP_NAME;
$activeMenu = 'salones';
// Mensajes de operación
$successMessage = '';
$errorMessage = '';

// Capturar estatus desde la URL tras el redireccionamiento (Patrón PRG)
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') $successMessage = 'Salón procesado correctamente.';
    if ($_GET['status'] === 'deleted') $successMessage = 'Salón eliminado correctamente.';
    if ($_GET['status'] === 'error') $errorMessage = 'Ocurrió un error al procesar la solicitud.';
}

/**
 * Procesamiento de formularios (Crear y Editar)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errorMessage = 'Token de seguridad inválido. Recarga la página e inténtalo de nuevo.';
    } else {
        $action    = $_POST['action'];
        $id_salon  = filter_input(INPUT_POST, 'id_salon', FILTER_VALIDATE_INT);
        $nombre    = trim($_POST['nombre'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $capacidad = trim($_POST['capacidad'] ?? '');

        if ($nombre === '' || $capacidad === '' || !ctype_digit($capacidad) || (int)$capacidad < 1) {
            $errorMessage = 'Por favor completa correctamente el nombre y la capacidad del salón.';
        } else {
            try {
                if ($action === 'insert_salon') {
                    qa_insert_salon($nombre, $ubicacion, (int)$capacidad);
                    header('Location: ' . BASE_URL . 'index.php?page=salones&status=success');
                    exit;
                } elseif ($action === 'update_salon' && $id_salon) {
                    qa_update_salon($id_salon, $nombre, $ubicacion, (int)$capacidad);
                    header('Location: ' . BASE_URL . 'index.php?page=salones&status=success');
                    exit;
                }
            } catch (Throwable $e) {
                header('Location: ' . BASE_URL . 'index.php?page=salones&status=error');
                exit;
            }
        }
    }
}

// Obtener salones usando la función centralizada
$salones = [];
try {
    $salones = qa_get_salones();
} catch (Throwable $e) {
    $salones = [];
}
?>

<!-- ENCABEZADO -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Gestión de salones</h2>
        <p class="page-section-subtitle">Administra los salones disponibles para asesorías.</p>
    </div>
    <button class="btn-add-user" data-bs-toggle="modal" data-bs-target="#modalSalon">
        <i class="bi bi-plus-lg"></i> Nuevo salón
    </button>
</div>

<!-- ALERTAS -->
<?php if ($successMessage): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>¡Éxito!</strong> <?= htmlspecialchars($successMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php elseif ($errorMessage): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        <strong>Error:</strong> <?= htmlspecialchars($errorMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
<?php endif; ?>

<!-- BÚSQUEDA -->
<div class="filters-card">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" id="salonSearch" class="form-control" placeholder="Buscar por nombre o ubicación...">
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
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salones as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['nombre_salon']) ?></td>
                    <td><?= htmlspecialchars($s['ubicacion'] ?? '—') ?></td>
                    <td><?= (int) $s['capacidad'] ?> personas</td>
                    <td>
                        <span class="status-badge <?= $s['estado'] === 'Disponible' ? 'active' : 'inactive' ?>">
                            <?= htmlspecialchars($s['estado']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions justify-content-center">
                            <button class="action-btn edit" 
                                    title="Editar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalSalon"
                                    data-id="<?= $s['id_salon'] ?>"
                                    data-nombre="<?= htmlspecialchars($s['nombre_salon']) ?>"
                                    data-ubicacion="<?= htmlspecialchars($s['ubicacion'] ?? '') ?>"
                                    data-capacidad="<?= $s['capacidad'] ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="action-btn delete" 
                                    title="Eliminar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminar"
                                    data-id="<?= $s['id_salon'] ?>"
                                    data-nombre="<?= htmlspecialchars($s['nombre_salon']) ?>">
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
<div class="modal fade" id="modalSalon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px; border:none; padding:10px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Nuevo Salón</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formSalon" action="index.php?page=salones" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="action" id="salon_action" value="insert_salon">
                    <input type="hidden" name="id_salon" id="salon_id">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nombre del Salón / Aula</label>
                        <input type="text" name="nombre" id="salon_nombre" class="form-control" placeholder="Ej. Salón A-101" required style="border-radius:12px;">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ubicación / Campus</label>
                        <select name="ubicacion" id="salon_ubicacion" class="form-select custom-select" required>
                            <option value="Tomás Aquino">Tomás Aquino</option>
                            <option value="Otay">Otay</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Capacidad Máxima</label>
                        <input type="number" name="capacidad" id="salon_capacidad" class="form-control" min="1" max="100" value="20" required style="border-radius:12px;">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-add-user" id="btnSubmit">Crear Salón</button>
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
                <h5 class="modal-title fw-bold">Eliminar salón</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>eliminar_salon.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id_salon" id="delete_id">
                    <p class="text-muted small">
                        ¿Estás seguro que deseas eliminar el <strong><span id="delete_nombre" class="text-dark"></span></strong>?
                        <br><br>
                        <span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Si este salón está asignado a un horario activo, la acción podría fallar.</span>
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
    const modalSalon = document.getElementById('modalSalon');
    const modalEliminar = document.getElementById('modalEliminar');

    if (modalSalon) {
        modalSalon.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            const id = btn.getAttribute('data-id');
            const form = document.getElementById('formSalon');
            
            if (id) {
                document.getElementById('modalTitle').textContent = 'Editar Salón';
                document.getElementById('btnSubmit').textContent = 'Guardar Cambios';
                document.getElementById('salon_action').value = 'update_salon';
                document.getElementById('salon_id').value = id;
                document.getElementById('salon_nombre').value = btn.getAttribute('data-nombre');
                document.getElementById('salon_ubicacion').value = btn.getAttribute('data-ubicacion');
                document.getElementById('salon_capacidad').value = btn.getAttribute('data-capacidad');
            } else {
                document.getElementById('modalTitle').textContent = 'Nuevo Salón';
                document.getElementById('btnSubmit').textContent = 'Crear Salón';
                document.getElementById('salon_action').value = 'insert_salon';
                form.reset();
                document.getElementById('salon_id').value = '';
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
    document.getElementById('salonSearch')?.addEventListener('keyup', (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
});
</script>
