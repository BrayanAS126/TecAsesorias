<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');

// Usamos ruta relativa para que el IDE reconozca las funciones
require_once __DIR__ . '/../../queries_alumno.php';

$pageTitle  = 'Solicitar asesoría | ' . APP_NAME;
$activeMenu = 'solicitar';

// 1. Obtener materias que tienen al menos un asesor
$materiasRaw = ql_get_materias_disponibles();

// 2. Preparar estructuras para JavaScript
$asesoresPorMateria = [];
$horariosAbiertos   = [];

foreach ($materiasRaw as $m) {
    $idM = (int)$m['id_materia'];
    $asesores = ql_get_asesores_por_materia($idM);
    $asesoresPorMateria[$idM] = $asesores;

    foreach ($asesores as $ase) {
        $idA = $ase['id'];
        if (!isset($horariosAbiertos[$idA])) {
            $disponibilidad = ql_get_disponibilidad_asesor($idA);
            $horariosAbiertos[$idA] = array_map(function($d) {
                return [
                    'id'    => (int)$d['id_disponibilidad'],
                    'dia'   => $d['dia_semana'],
                    'hora'  => substr($d['hora_inicio'], 0, 5) . ' - ' . substr($d['hora_fin'], 0, 5),
                    'salon' => $d['nombre_salon'] ?? 'Por definir',
                    'sede'  => $d['ubicacion'] ?? 'Virtual',
                    'alumnos' => 0 // Esto podría cruzarse con solicitudes_asesoria si quieres cupos reales
                ];
            }, $disponibilidad);
        }
    }
}

$diasOrden = ['Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5];
?>

<!-- ENCABEZADO -->
<div class="mb-4">
    <h2 class="page-section-title">Solicitar asesoría</h2>
    <p class="page-section-subtitle">Sigue los pasos para agendar tu asesoría.</p>
</div>

<!-- ALERTAS DE ERROR -->
<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] === 'error'): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>
            <strong>Error:</strong> No se pudo procesar la solicitud. Verifica que todos los datos sean correctos.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif ($_GET['status'] === 'duplicate'): ?>
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="border-radius: 15px;">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Solicitud duplicada:</strong> Ya tienes una asesoría pendiente o confirmada para este mismo horario.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- INDICADOR DE PASOS -->
<div style="display:flex;align-items:center;gap:0;margin-bottom:32px;" id="stepIndicator">
    <?php
    $pasos = ['Materia', 'Asesor', 'Horario'];
    foreach ($pasos as $i => $paso):
        $num = $i + 1;
    ?>
    <div style="display:flex;align-items:center;flex:1;">
        <div style="display:flex;align-items:center;gap:10px;flex:1;">
            <div class="step-circle" id="stepCircle<?= $num ?>" style="
                width:36px;height:36px;border-radius:50%;
                display:flex;align-items:center;justify-content:center;
                font-weight:700;font-size:.85rem;flex-shrink:0;
                transition:.3s ease;
                background:<?= $num === 1 ? 'var(--primary)' : 'var(--border-color)' ?>;
                color:<?= $num === 1 ? '#fff' : 'var(--text-gray)' ?>;
            "><?= $num ?></div>
            <span style="
                font-size:.85rem;font-weight:600;
                color:<?= $num === 1 ? 'var(--primary)' : 'var(--text-gray)' ?>;
                transition:.3s ease;
            " id="stepLabel<?= $num ?>"><?= $paso ?></span>
        </div>
        <?php if ($num < 3): ?>
        <div style="
            flex:1;height:2px;
            background:var(--border-color);
            margin:0 8px;
            transition:.3s ease;
        " id="stepLine<?= $num ?>"></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- ══ PASO 1: MATERIA ══════════════════════════════════════════════════ -->
<div id="step1">
    <h3 class="section-title">Paso 1 — Elige la materia</h3>
    <div class="row g-3">
        <?php foreach ($materiasRaw as $m): ?>
        <div class="col-md-6 col-xl-4">
            <button
                class="action-card w-100 text-start"
                style="border:none;cursor:pointer;"
                onclick="elegirMateria(<?= $m['id_materia'] ?>, '<?= htmlspecialchars($m['nombre_materia'], ENT_QUOTES) ?>')">
                <div class="action-icon"><i class="bi bi-book-fill"></i></div>
                <div class="action-title"><?= htmlspecialchars($m['nombre_materia']) ?></div>
                <div class="action-text">
                    <i class="bi bi-person-fill me-1"></i>
                    <?= $m['num_asesores'] ?> asesor<?= $m['num_asesores'] !== 1 ? 'es' : '' ?> disponible<?= $m['num_asesores'] !== 1 ? 's' : '' ?>
                </div>
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ══ PASO 2: ASESOR ═══════════════════════════════════════════════════ -->
<div id="step2" style="display:none;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <button onclick="volverPaso(1)"
            style="background:none;border:none;cursor:pointer;
                   color:var(--primary-light);font-size:.88rem;font-weight:600;
                   display:flex;align-items:center;gap:5px;">
            <i class="bi bi-arrow-left"></i> Volver
        </button>
        <h3 class="section-title mb-0">Paso 2 — Elige el asesor</h3>
    </div>

    <div style="
        background:rgba(59,130,246,.07);
        border-radius:12px;padding:10px 16px;
        font-size:.85rem;color:var(--primary);
        font-weight:600;margin-bottom:20px;
    ">
        <i class="bi bi-book-fill me-2"></i>
        Materia seleccionada: <span id="labelMateria">—</span>
    </div>

    <div class="row g-3" id="listaAsesores"></div>
</div>

<!-- ══ PASO 3: HORARIO ══════════════════════════════════════════════════ -->
<div id="step3" style="display:none;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <button onclick="volverPaso(2)"
            style="background:none;border:none;cursor:pointer;
                   color:var(--primary-light);font-size:.88rem;font-weight:600;
                   display:flex;align-items:center;gap:5px;">
            <i class="bi bi-arrow-left"></i> Volver
        </button>
        <h3 class="section-title mb-0">Paso 3 — Elige el horario</h3>
    </div>

    <!-- Resumen selección -->
    <div style="
        background:rgba(59,130,246,.07);border-radius:12px;
        padding:10px 16px;font-size:.85rem;color:var(--primary);
        font-weight:600;margin-bottom:20px;
        display:flex;flex-wrap:wrap;gap:16px;
    ">
        <span><i class="bi bi-book-fill me-1"></i><span id="resumenMateria">—</span></span>
        <span><i class="bi bi-person-fill me-1"></i><span id="resumenAsesor">—</span></span>
    </div>

    <!-- Leyenda -->
    <div style="display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--text-gray);">
            <i class="bi bi-person-fill" style="color:var(--primary);"></i>
            Individual (serás el único alumno)
        </div>
        <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;color:var(--text-gray);">
            <i class="bi bi-people-fill" style="color:var(--primary);"></i>
            En grupo (ya hay otros alumnos)
        </div>
    </div>

    <!-- Calendario semanal -->
    <div id="calendarioHorarios" style="
        display:grid;
        grid-template-columns:repeat(5,1fr);
        gap:12px;
        margin-bottom:24px;
    "></div>

    <div id="sinHorarios" style="display:none;">
        <div class="table-card" style="padding:36px;text-align:center;">
            <div style="font-size:2rem;opacity:.3;margin-bottom:10px;">📅</div>
            <div style="font-weight:700;color:var(--primary);">Sin horarios disponibles</div>
            <div style="font-size:.85rem;color:var(--text-gray);margin-top:4px;">
                Este asesor no tiene grupos abiertos en este momento.
            </div>
        </div>
    </div>

    <!-- Botón enviar -->
    <!-- Brandon: action="enviar_solicitud.php" method="POST" -->
    <form action="enviar_solicitud.php" method="POST" id="formSolicitud">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
        <input type="hidden" name="materia_id" id="inputMateriaId" value="">
        <input type="hidden" name="asesor_id"  id="inputAsesorId"  value="">
        <input type="hidden" name="bloque_id"  id="inputBloqueId"  value="">

        <div id="bloqueSeleccionado" style="display:none;
            background:rgba(16,185,129,.08);
            border:1px solid rgba(16,185,129,.3);
            border-radius:14px;padding:14px 18px;
            margin-bottom:16px;font-size:.88rem;
        ">
            <i class="bi bi-check-circle-fill me-2" style="color:#059669;"></i>
            Horario seleccionado: <strong id="labelBloqueSeleccionado">—</strong>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" id="btnEnviar" class="btn-add-user"
                style="padding:12px 28px;opacity:.4;pointer-events:none;"
                disabled>
                <i class="bi bi-send-fill"></i> Enviar solicitud
            </button>
        </div>
    </form>
</div>

<!-- Datos de horarios para JS -->
<script>
const horariosData  = <?= json_encode($horariosAbiertos) ?>;
const asesoresData  = <?= json_encode($asesoresPorMateria) ?>;
const diasOrden     = <?= json_encode($diasOrden) ?>;
const diasSemana    = ['Lunes','Martes','Miércoles','Jueves','Viernes'];

let materiaSeleccionada = null;
let asesorSeleccionado  = null;
let bloqueSeleccionado  = null;

// ── Pasos ────────────────────────────────────────────────────────────────────
function irPaso(n) {
    [1,2,3].forEach(i => {
        document.getElementById('step' + i).style.display = i === n ? 'block' : 'none';
        const circle = document.getElementById('stepCircle' + i);
        const label  = document.getElementById('stepLabel' + i);
        if (i < n) {
            circle.style.background = '#059669';
            circle.style.color      = '#fff';
            circle.innerHTML        = '<i class="bi bi-check-lg"></i>';
        } else if (i === n) {
            circle.style.background = 'var(--primary)';
            circle.style.color      = '#fff';
            circle.innerHTML        = i;
        } else {
            circle.style.background = 'var(--border-color)';
            circle.style.color      = 'var(--text-gray)';
            circle.innerHTML        = i;
        }
        if (i < 3) {
            const line = document.getElementById('stepLine' + i);
            if (line) line.style.background = i < n ? '#059669' : 'var(--border-color)';
        }
    });
}

function volverPaso(n) { irPaso(n); }

// ── Paso 1 → 2 ───────────────────────────────────────────────────────────────
function elegirMateria(id, nombre) {
    materiaSeleccionada = id;
    document.getElementById('labelMateria').textContent   = nombre;
    document.getElementById('resumenMateria').textContent = nombre;
    document.getElementById('inputMateriaId').value       = id;

    // Generar cards de asesores
    const lista    = document.getElementById('listaAsesores');
    const asesores = asesoresData[id] || [];
    lista.innerHTML = '';

    if (asesores.length === 0) {
        lista.innerHTML = '<div class="col-12"><p style="color:var(--text-gray);">No hay asesores disponibles para esta materia.</p></div>';
    } else {
        asesores.forEach(a => {
            lista.innerHTML += `
            <div class="col-md-6 col-xl-4">
                <button class="action-card w-100 text-start"
                    style="border:none;cursor:pointer;"
                    onclick="elegirAsesor(${a.id}, '${a.nombre.replace(/'/g,"\\'")}')">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                        <div style="
                            width:46px;height:46px;border-radius:50%;
                            background:var(--primary);color:#fff;
                            display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:.85rem;flex-shrink:0;
                        ">${a.iniciales}</div>
                        <div>
                            <div class="action-title" style="margin-bottom:2px;">${a.nombre}</div>
                            <div class="action-text" style="font-size:.78rem;">${a.materia}</div>
                        </div>
                    </div>
                </button>
            </div>`;
        });
    }

    irPaso(2);
}

// ── Paso 2 → 3 ───────────────────────────────────────────────────────────────
function elegirAsesor(id, nombre) {
    asesorSeleccionado = id;
    document.getElementById('resumenAsesor').textContent = nombre;
    document.getElementById('inputAsesorId').value       = id;
    renderCalendario(id);
    irPaso(3);
}

// ── Calendario ───────────────────────────────────────────────────────────────
function renderCalendario(asesorId) {
    const cal     = document.getElementById('calendarioHorarios');
    const sinH    = document.getElementById('sinHorarios');
    const bloques = horariosData[asesorId] || [];
    cal.innerHTML = '';

    if (bloques.length === 0) {
        cal.style.display = 'none';
        sinH.style.display = 'block';
        return;
    }

    cal.style.display = 'grid';
    sinH.style.display = 'none';

    // Crear columnas vacías para los 5 días
    diasSemana.forEach(dia => {
        const bloque = bloques.find(b => b.dia === dia);
        const col    = document.createElement('div');

        col.style.cssText = `
            border-radius:16px;
            border:2px solid var(--border-color);
            overflow:hidden;
            transition:.2s ease;
            min-height:140px;
        `;

        // Cabecera del día
        const cab = document.createElement('div');
        cab.style.cssText = `
            padding:10px;text-align:center;
            background:var(--body-bg);
            border-bottom:1px solid var(--border-color);
        `;
        cab.innerHTML = `<div style="font-size:.7rem;font-weight:700;
            text-transform:uppercase;letter-spacing:1px;
            color:var(--text-gray);">${dia}</div>`;
        col.appendChild(cab);

        // Cuerpo
        const body = document.createElement('div');
        body.style.cssText = 'padding:12px;';

        if (bloque) {
            const esGrupo = bloque.alumnos > 0;
            const icono   = esGrupo
                ? '<i class="bi bi-people-fill"></i>'
                : '<i class="bi bi-person-fill"></i>';
            const labelTipo = esGrupo ? 'En grupo' : 'Individual';
            const colorTipo = esGrupo
                ? 'rgba(59,130,246,.1);color:var(--primary)'
                : 'rgba(16,185,129,.1);color:#059669';

            body.innerHTML = `
                <div style="font-size:.75rem;font-weight:700;
                    color:var(--primary);margin-bottom:8px;">
                    <i class="bi bi-clock me-1"></i>${bloque.hora}
                </div>
                <div style="font-size:.72rem;color:var(--text-gray);margin-bottom:10px;">
                    <i class="bi bi-building me-1"></i>${bloque.salon}
                    <br><span style="font-size:.68rem;">${bloque.sede}</span>
                </div>
                <div style="
                    display:inline-flex;align-items:center;gap:5px;
                    background:${colorTipo};
                    border-radius:20px;padding:3px 10px;
                    font-size:.72rem;font-weight:700;
                    margin-bottom:10px;
                ">${icono} ${labelTipo}</div>`;

            // Botón elegir
            const btn = document.createElement('button');
            btn.type      = 'button';
            btn.dataset.id    = bloque.id;
            btn.dataset.dia   = bloque.dia;
            btn.dataset.hora  = bloque.hora;
            btn.dataset.salon = bloque.salon;
            btn.style.cssText = `
                width:100%;border:none;cursor:pointer;
                background:var(--primary);color:#fff;
                border-radius:10px;padding:7px;
                font-size:.78rem;font-weight:700;
                transition:.2s ease;
            `;
            btn.textContent = 'Elegir';
            btn.addEventListener('click', () => elegirBloque(btn));
            body.appendChild(btn);

            col.addEventListener('mouseenter', () => {
                col.style.borderColor = 'var(--primary-light)';
                col.style.transform   = 'translateY(-2px)';
            });
            col.addEventListener('mouseleave', () => {
                if (bloqueSeleccionado !== bloque.id) {
                    col.style.borderColor = 'var(--border-color)';
                    col.style.transform   = 'translateY(0)';
                }
            });

        } else {
            body.innerHTML = `
                <div style="text-align:center;margin-top:18px;
                    color:var(--text-gray);font-size:.8rem;opacity:.4;">
                    <i class="bi bi-dash-circle"
                       style="font-size:1.2rem;display:block;margin-bottom:4px;"></i>
                    No disponible
                </div>`;
        }

        col.appendChild(body);
        col.dataset.bloqueId = bloque ? bloque.id : '';
        cal.appendChild(col);
    });
}

// ── Elegir bloque ────────────────────────────────────────────────────────────
function elegirBloque(btn) {
    bloqueSeleccionado = btn.dataset.id;
    document.getElementById('inputBloqueId').value = bloqueSeleccionado;

    // Resaltar columna seleccionada
    document.querySelectorAll('#calendarioHorarios > div').forEach(col => {
        if (col.dataset.bloqueId === bloqueSeleccionado) {
            col.style.borderColor = '#059669';
            col.style.background  = 'rgba(16,185,129,.04)';
        } else {
            col.style.borderColor = 'var(--border-color)';
            col.style.background  = '';
            col.style.transform   = 'translateY(0)';
        }
    });

    // Mostrar resumen
    const label = `${btn.dataset.dia} · ${btn.dataset.hora} · ${btn.dataset.salon}`;
    document.getElementById('labelBloqueSeleccionado').textContent = label;
    document.getElementById('bloqueSeleccionado').style.display    = 'flex';

    // Habilitar botón enviar
    const btnEnviar = document.getElementById('btnEnviar');
    btnEnviar.disabled                = false;
    btnEnviar.style.opacity           = '1';
    btnEnviar.style.pointerEvents     = 'auto';
}
</script>
