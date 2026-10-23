<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_admin.php';

$pageTitle  = 'Gestión de Usuarios | ' . APP_NAME;
$activeMenu = 'usuarios';

$successMessage = '';
$errorMessage   = '';
$generatedPassword = '';

// Manejo de mensajes Flash mediante sesión para no perder datos tras redirección
if (isset($_SESSION['flash_success'])) {
    $successMessage = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_SESSION['flash_error'])) {
    $errorMessage = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

$materias = qa_get_materias();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'insert_usuario') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $errorMessage = 'Token de seguridad inválido. Recarga la página e inténtalo de nuevo.';
    } else {
        $correo   = trim($_POST['correo'] ?? '');
        $tipo     = trim($_POST['tipo'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $rolesValidos = ['Alumno', 'Asesor', 'Docente', 'Administrador'];

        if (empty($correo) || empty($tipo) || !filter_var($correo, FILTER_VALIDATE_EMAIL) || !in_array($tipo, $rolesValidos, true)) {
            $errorMessage = 'Por favor completa todos los campos con datos válidos.';
        } else {
            if ($password === '') {
                $password = substr(bin2hex(random_bytes(4)), 0, 8);
                $generatedPassword = $password;
            }

            $pdo = db_connect();
            $pdo->beginTransaction();
            try {
                $idUsuario = qa_insert_usuario($correo, $password, $tipo);

                if ($tipo === 'Alumno' || $tipo === 'Asesor') {
                    $idAlumno = qa_insert_alumno(
                        $idUsuario,
                        trim($_POST['alumno_nombre_completo'] ?? ''),
                        (int)($_POST['alumno_semestre'] ?? 0),
                        trim($_POST['alumno_no_control'] ?? ''),
                        trim($_POST['alumno_genero'] ?? 'Otro'),
                        trim($_POST['alumno_carrera'] ?? ''),
                        $_POST['alumno_fecha_nacimiento'] ?? date('Y-m-d'),
                        trim($_POST['alumno_telefono'] ?? null)
                    );

                    if ($tipo === 'Asesor') {
                        qa_insert_asesor($idUsuario, $idAlumno, $_POST['asesor_tipo_compensacion'] ?? 'creditos');
                    }
                } elseif ($tipo === 'Docente') {
                    qa_insert_docente(
                        $idUsuario,
                        trim($_POST['docente_nombre_completo'] ?? ''),
                        trim($_POST['docente_telefono'] ?? null),
                        trim($_POST['docente_horario'] ?? null),
                        (int)($_POST['docente_id_materia_encargada'] ?? 0)
                    );
                }

                $pdo->commit();
                $msg = 'Usuario creado correctamente.';
                if ($generatedPassword) $msg .= ' Contraseña generada: ' . $generatedPassword;
                $_SESSION['flash_success'] = $msg;
                
                header('Location: ' . BASE_URL . 'index.php?page=usuarios');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['flash_error'] = $e->getMessage();
                header('Location: ' . BASE_URL . 'index.php?page=usuarios');
                exit;
            }
        }
    }
}

$usuarios = qa_get_usuarios();
$rolClases = [
    'Administrador' => 'admin',
    'Docente'       => 'teacher',
    'Asesor'        => 'advisor',
    'Alumno'        => 'student'
];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="page-section-title">Gestión de usuarios</h2>
        <p class="page-section-subtitle">Administra los accesos y perfiles del sistema.</p>
    </div>
    <button type="button" class="btn-add-user" data-bs-toggle="modal" data-bs-target="#modalUsuario">
        <i class="bi bi-person-plus-fill"></i> Nuevo usuario
    </button>
</div>

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

<!-- MODAL NUEVO USUARIO -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content" style="border-radius:20px;border:none;padding:8px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Registrar nuevo usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.88rem;color:var(--text-gray);margin-bottom:16px;">
                    Crea un alumno, asesor o docente y asigna una contraseña o deja el campo vacío para generar una.
                </p>
                <form action="index.php?page=usuarios" method="POST">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="action" value="insert_usuario">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">Correo institucional</label>
                        <input
                            type="email"
                            name="correo"
                            class="form-control"
                            placeholder="usuario@tectijuana.edu.mx"
                            required
                            style="border-radius:12px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">Rol de usuario</label>
                        <select name="tipo" class="form-select" required style="border-radius:12px;">
                            <option value="">Selecciona un rol</option>
                            <option value="Alumno">Alumno</option>
                            <option value="Asesor">Asesor</option>
                            <option value="Docente">Docente</option>
                            <option value="Administrador">Administrador</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:.85rem;">Contraseña</label>
                        <input
                            type="text"
                            name="password"
                            class="form-control"
                            placeholder="Ingresa una contraseña o deja vacío para generar"
                            style="border-radius:12px;">
                    </div>

                    <div class="mb-4">
                        <small class="text-muted">Los campos que se muestran cambian según el rol seleccionado.</small>
                    </div>

                    <div id="usuario-datos-alumno" class="role-section d-none">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Nombre completo</label>
                            <input type="text" name="alumno_nombre_completo" class="form-control" placeholder="Nombre del alumno" style="border-radius:12px;">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;">Semestre</label>
                                <input type="number" min="1" max="15" name="alumno_semestre" class="form-control" placeholder="Semestre" style="border-radius:12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;">No. de control</label>
                                <input type="text" name="alumno_no_control" class="form-control" placeholder="Ej. 202300123" style="border-radius:12px;">
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;">Género</label>
                                <select name="alumno_genero" class="form-select" style="border-radius:12px;">
                                    <option value="">Selecciona género</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;">Carrera</label>
                                <input type="text" name="alumno_carrera" class="form-control" placeholder="Ej. Ingeniería en Software" style="border-radius:12px;">
                            </div>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;">Fecha de nacimiento</label>
                                <input type="date" name="alumno_fecha_nacimiento" class="form-control" style="border-radius:12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" style="font-size:.85rem;">Teléfono</label>
                                <input type="tel" name="alumno_telefono" class="form-control" placeholder="Opcional" style="border-radius:12px;">
                            </div>
                        </div>
                    </div>

                    <div id="usuario-datos-asesor" class="role-section d-none">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Tipo de compensación</label>
                            <select name="asesor_tipo_compensacion" class="form-select" style="border-radius:12px;">
                                <option value="creditos">Créditos</option>
                                <option value="servicio_social">Servicio social</option>
                            </select>
                        </div>
                        <div class="alert alert-info py-2" role="alert" style="border-radius:12px; font-size:.9rem;">
                            El horario de asesoría podrá definirse después desde el módulo de docente o administración.
                        </div>
                    </div>

                    <div id="usuario-datos-docente" class="role-section d-none">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Nombre completo</label>
                            <input type="text" name="docente_nombre_completo" class="form-control" placeholder="Nombre del docente" style="border-radius:12px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Teléfono</label>
                            <input type="tel" name="docente_telefono" class="form-control" placeholder="Opcional" style="border-radius:12px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Materia encargada</label>
                            <select name="docente_id_materia_encargada" class="form-select" style="border-radius:12px;">
                                <option value="">Selecciona materia</option>
                                <?php foreach ($materias as $materia): ?>
                                    <option value="<?= htmlspecialchars($materia['id_materia']) ?>"><?= htmlspecialchars($materia['clave'] . ' - ' . $materia['nombre_materia']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size:.85rem;">Horario</label>
                            <input type="text" name="docente_horario" class="form-control" placeholder="Ej. Lunes 8:00-10:00" style="border-radius:12px;">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-add-user" style="padding:10px 22px;">
                            <i class="bi bi-save"></i> Guardar usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoField = document.querySelector('select[name="tipo"]');
        const alumnoSection = document.getElementById('usuario-datos-alumno');
        const asesorSection = document.getElementById('usuario-datos-asesor');
        const docenteSection = document.getElementById('usuario-datos-docente');

        const alumnoFields = alumnoSection ? Array.from(alumnoSection.querySelectorAll('input, select')) : [];
        const asesorFields = asesorSection ? Array.from(asesorSection.querySelectorAll('input, select')) : [];
        const docenteFields = docenteSection ? Array.from(docenteSection.querySelectorAll('input, select')) : [];

        function setRequired(inputs, value) {
            inputs.forEach(field => {
                if (value) {
                    field.setAttribute('required', 'required');
                } else {
                    field.removeAttribute('required');
                }
            });
        }

        function updateSections() {
            const tipo = tipoField ? tipoField.value : '';

            if (alumnoSection) {
                alumnoSection.classList.toggle('d-none', tipo !== 'Alumno' && tipo !== 'Asesor');
            }
            if (asesorSection) {
                asesorSection.classList.toggle('d-none', tipo !== 'Asesor');
            }
            if (docenteSection) {
                docenteSection.classList.toggle('d-none', tipo !== 'Docente');
            }

            setRequired(alumnoFields, tipo === 'Alumno' || tipo === 'Asesor');
            setRequired(asesorFields, tipo === 'Asesor');
            setRequired(docenteFields, tipo === 'Docente');
        }

        const filterRole = document.querySelector('select[name="filter_role"]');
        const filterStatus = document.querySelector('select[name="filter_status"]');
        const usuariosRows = document.querySelectorAll('table[name="usuarios_table"] tbody tr');

        function filterTable() {
            const roleValue = filterRole ? filterRole.value.toLowerCase() : '';
            const statusValue = filterStatus ? filterStatus.value.toLowerCase() : '';

            usuariosRows.forEach(row => {
                const rowRole = (row.dataset.rol || '').toLowerCase();
                const rowStatus = (row.dataset.estado || 'activo').toLowerCase();
                const matchesRole = roleValue === '' || rowRole === roleValue;
                const matchesStatus = statusValue === '' || rowStatus === statusValue;
                row.style.display = (matchesRole && matchesStatus) ? '' : 'none';
            });
        }

        if (filterRole) {
            filterRole.addEventListener('change', filterTable);
        }
        if (filterStatus) {
            filterStatus.addEventListener('change', filterTable);
        }

        const exportButton = document.getElementById('btnExportPdf');
        if (exportButton) {
            exportButton.addEventListener('click', function () {
                const visibleRows = Array.from(usuariosRows)
                    .filter(row => row.style.display !== 'none' && row.dataset.rol);

                const headers = ['Usuario', 'Correo', 'Rol', 'Estado', 'Fecha registro'];
                const rows = visibleRows.map(row => [
                    row.querySelector('td:nth-child(1) h6')?.textContent.trim() || '',
                    row.querySelector('td:nth-child(2)')?.textContent.trim() || '',
                    row.querySelector('td:nth-child(3)')?.textContent.trim() || '',
                    row.querySelector('td:nth-child(4)')?.textContent.trim() || '',
                    row.querySelector('td:nth-child(5)')?.textContent.trim() || '',
                ]);

                const jsPDF = window.jspdf?.jsPDF || window.jsPDF;
                if (!jsPDF) {
                    alert('No se pudo generar el PDF porque jsPDF no está disponible.');
                    return;
                }

                const doc = new jsPDF({ unit: 'pt', format: 'letter' });
                const pageWidth = doc.internal.pageSize.getWidth();
                const margin = 40;
                let y = 40;

                doc.setFontSize(14);
                doc.text('Listado de usuarios', margin, y);
                y += 24;
                doc.setFontSize(10);
                doc.text('Estado actual de la tabla', margin, y);
                y += 24;

                const lineHeight = 18;
                const cellWidths = [120, 160, 90, 70, 90];

                function drawRow(texts, rowY, isHeader) {
                    let x = margin;
                    const fontStyle = isHeader ? 'bold' : 'normal';
                    doc.setFont(undefined, fontStyle);
                    texts.forEach((text, index) => {
                        doc.text(String(text), x + 2, rowY + 12, { maxWidth: cellWidths[index] - 4 });
                        x += cellWidths[index];
                    });
                    doc.setDrawColor(200);
                    doc.line(margin, rowY + lineHeight, margin + cellWidths.reduce((a,v) => a+v, 0), rowY + lineHeight);
                }

                drawRow(headers, y, true);
                y += lineHeight;

                if (rows.length === 0) {
                    doc.text('No hay usuarios visibles para exportar.', margin, y);
                } else {
                    rows.forEach(rowData => {
                        if (y + lineHeight > doc.internal.pageSize.getHeight() - margin) {
                            doc.addPage();
                            y = margin;
                        }
                        drawRow(rowData, y, false);
                        y += lineHeight;
                    });
                }

                const roleName = filterRole && filterRole.value ? filterRole.value : 'Todos';
                const statusName = filterStatus && filterStatus.value ? filterStatus.value : 'Todos';
                const now = new Date();
                const pad = (value) => String(value).padStart(2, '0');
                const fecha = pad(now.getDate()) + pad(now.getMonth() + 1) + String(now.getFullYear()).slice(-2);
                const hora = pad(now.getHours()) + pad(now.getMinutes()) + pad(now.getSeconds());
                const fileName = `${roleName}${statusName}${fecha}${hora}`
                    .replace(/\s+/g, '')
                    .replace(/[^A-Za-z0-9_-]/g, '');

                doc.save(`${fileName || 'usuarios'}.pdf`);
            });
        }

        if (tipoField) {
            tipoField.addEventListener('change', updateSections);
            updateSections();
        }
    });
</script>

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
            <select class="form-select custom-select" name="filter_role">
                <option value="">Todos los roles</option>
                <option>Alumno</option>
                <option>Asesor</option>
                <option>Docente</option>
                <option>Administrador</option>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <select class="form-select custom-select" name="filter_status">
                <option value="">Estado</option>
                <option>Activo</option>
                <option>Inactivo</option>
            </select>
        </div>
        <div class="col-md-4 col-lg-2">
            <button type="button" id="btnExportPdf" class="btn-export w-100">
                <i class="bi bi-download"></i> Exportar
            </button>
        </div>
    </div>
</div>

<!-- TABLA -->
<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle" name="usuarios_table">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Fecha registro</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <?php $rolClass = $rolClases[$u['tipo_usuario']] ?? 'student'; ?>
                <tr data-rol="<?= htmlspecialchars($u['tipo_usuario']) ?>" data-estado="Activo">
                    <td>
                        <div class="user-info">
                            <div class="user-avatar"><?= htmlspecialchars($u['iniciales']) ?></div>
                            <div>
                                <h6 style="color:var(--text-main);"><?= htmlspecialchars($u['correo_institucional']) ?></h6>
                                <span class="small text-muted" style="font-size:.75rem;">
                                    <?= $u['nombre_completo'] ? htmlspecialchars($u['nombre_completo']) : 'Perfil incompleto' ?></span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge <?= $rolClass ?>">
                            <?= htmlspecialchars($u['tipo_usuario']) ?>
                        </span>
                    </td>
                    <td><span class="status-badge active">Activo</span></td>
                    <td style="font-size:.85rem; color:var(--text-gray);">
                        <?= htmlspecialchars($u['fecha_registro']) ?>
                    </td>
                    <td>
                        <div class="actions justify-content-center">
                            <button class="action-btn edit" 
                                    title="Editar perfil" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEditar"
                                    data-id="<?= $u['id_usuario'] ?>"
                                    data-correo="<?= htmlspecialchars($u['correo_institucional']) ?>"
                                    data-tipo="<?= $u['tipo_usuario'] ?>"
                                    data-nombre="<?= htmlspecialchars($u['nombre_completo']) ?>"
                                    data-control="<?= htmlspecialchars($u['no_control']) ?>"
                                    data-semestre="<?= $u['semestre'] ?>"
                                    data-carrera="<?= htmlspecialchars($u['carrera']) ?>"
                                    data-genero="<?= $u['genero'] ?>"
                                    data-fecha="<?= $u['fecha_nacimiento'] ?>"
                                    data-materia="<?= $u['id_materia'] ?>"
                                    data-compensacion="<?= $u['tipo_compensacion'] ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="action-btn delete" 
                                    title="Eliminar" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEliminar" 
                                    data-id="<?= $u['id_usuario'] ?>" 
                                    data-correo="<?= htmlspecialchars($u['correo_institucional']) ?>">
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

<!-- MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:24px; border:none; padding:10px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Editar perfil de usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>actualizar_usuario.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id_usuario" id="edit_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Correo institucional</label>
                            <input type="email" name="correo" id="edit_correo" class="form-control" required style="border-radius:12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Rol del usuario</label>
                            <select name="tipo" id="edit_tipo" class="form-select custom-select" onchange="toggleProfileFields(this.value)">
                                <option value="Alumno">Alumno</option>
                                <option value="Asesor">Asesor</option>
                                <option value="Docente">Docente</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>

                        <!-- Perfil Alumno/Asesor -->
                        <div class="profile-fields profile-alumno row g-3" style="display:none; margin-top:0;">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Nombre completo</label>
                                <input type="text" name="nombre" id="edit_nombre" class="form-control" style="border-radius:12px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">N° Control</label>
                                <input type="text" name="no_control" id="edit_control" class="form-control" style="border-radius:12px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Semestre</label>
                                <input type="number" name="semestre" id="edit_semestre" class="form-control" min="1" max="14" style="border-radius:12px;">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Género</label>
                                <select name="genero" id="edit_genero" class="form-select custom-select">
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Carrera</label>
                                <input type="text" name="carrera" id="edit_carrera" class="form-control" style="border-radius:12px;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Fecha Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" id="edit_fecha" class="form-control" style="border-radius:12px;">
                            </div>
                        </div>

                        <!-- Compensación (Solo Asesor) -->
                        <div class="profile-fields profile-asesor col-md-12" style="display:none;">
                            <label class="form-label small fw-bold">Tipo de Compensación</label>
                            <select name="tipo_compensacion" id="edit_compensacion" class="form-select custom-select">
                                <option value="creditos">Créditos</option>
                                <option value="servicio_social">Servicio Social</option>
                            </select>
                        </div>

                        <!-- Campos Materia (Docente y Asesor) -->
                        <div class="profile-fields profile-materia col-12">
                            <label class="form-label small fw-bold">Materia Vinculada (Docente/Asesor)</label>
                            <select name="id_materia" id="edit_materia" class="form-select custom-select">
                                <option value="">Seleccionar materia supervisada...</option>
                                <?php foreach ($materias as $m): ?>
                                    <option value="<?= $m['id_materia'] ?>"><?= htmlspecialchars($m['nombre_materia']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-add-user">Guardar Cambios</button>
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
                <h5 class="modal-title fw-bold">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>eliminar_usuario.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id_usuario" id="delete_id">
                    <p class="text-muted small">
                        ¿Estás seguro que deseas eliminar la cuenta de <strong id="delete_correo" class="text-dark"></strong>? 
                        Esta acción es irreversible.
                    </p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-export" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-add-user" style="background:#DC2626;">Eliminar permanentemente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalEditar = document.getElementById('modalEditar');
    
    window.toggleProfileFields = function(tipo) {
        const alumnoFields = document.querySelector('.profile-alumno');
        const materiaFields = document.querySelector('.profile-materia');
        const asesorFields = document.querySelector('.profile-asesor');
        
        if (tipo === 'Alumno' || tipo === 'Asesor') {
            alumnoFields.style.display = 'flex';
        } else {
            alumnoFields.style.display = 'none';
        }
        if (asesorFields) asesorFields.style.display = (tipo === 'Asesor') ? 'block' : 'none';

        // Mostrar selector de materia para Docentes y Asesores
        materiaFields.style.display = (tipo === 'Docente' || tipo === 'Asesor') ? 'block' : 'none';
    };

    if (modalEditar) {
        modalEditar.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_correo').value = btn.getAttribute('data-correo');
            document.getElementById('edit_tipo').value = btn.getAttribute('data-tipo');
            document.getElementById('edit_nombre').value = btn.getAttribute('data-nombre');
            document.getElementById('edit_control').value = btn.getAttribute('data-control');
            document.getElementById('edit_semestre').value = btn.getAttribute('data-semestre');
            document.getElementById('edit_carrera').value = btn.getAttribute('data-carrera');
            document.getElementById('edit_genero').value = btn.getAttribute('data-genero');
            document.getElementById('edit_fecha').value = btn.getAttribute('data-fecha');
            document.getElementById('edit_materia').value = btn.getAttribute('data-materia');
            document.getElementById('edit_compensacion').value = btn.getAttribute('data-compensacion');
            
            toggleProfileFields(btn.getAttribute('data-tipo'));
        });
    }

    // Manejar datos del modal eliminar
    const modalEliminar = document.getElementById('modalEliminar');
    if (modalEliminar) {
        modalEliminar.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            document.getElementById('delete_id').value = btn.getAttribute('data-id');
            document.getElementById('delete_correo').textContent = btn.getAttribute('data-correo');
        });
    }
});

// Script básico para filtrar por correo en tiempo real
document.querySelector('.search-box input')?.addEventListener('keyup', (e) => {
    const term = e.target.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
});
</script>