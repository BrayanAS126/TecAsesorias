<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once __DIR__ . '/../../queries_asesor.php';

$pageTitle  = 'Mis horarios | ' . APP_NAME;
$activeMenu = 'horarios';

$asesorId = $_SESSION['asesor_id'] ?? 0;
$horariosRaw = qs_get_mi_disponibilidad($asesorId);
$infoAcademica = qs_get_info_academica($asesorId);

$materia  = $infoAcademica['materia'];
$docente  = $infoAcademica['docente'];
$semestre = 'Enero - Junio ' . date('Y'); 
?>

<!-- ENCABEZADO -->
<div class="welcome-card">
    <h2>Mis horarios asignados</h2>
    <p>
        Estos son tus horarios y salones para el período
        <strong><?= htmlspecialchars($semestre) ?></strong>.
        No pueden modificarse durante el semestre.
    </p>
</div>

<!-- INFO GENERAL -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-book-fill"></i></div>
            <div class="stats-number" style="font-size:1.1rem;margin-bottom:2px;">
                <?= htmlspecialchars($materia) ?>
            </div>
            <div class="stats-label">Materia asignada</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-person-fill"></i></div>
            <div class="stats-number" style="font-size:1.1rem;margin-bottom:2px;">
                <?= htmlspecialchars($docente) ?>
            </div>
            <div class="stats-label">Docente encargado</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-calendar3"></i></div>
            <div class="stats-number" style="font-size:1.1rem;margin-bottom:2px;">
                <?= count($horariosRaw) ?>
            </div>
            <div class="stats-label">Días de asesoría</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-shield-check-fill"></i></div>
            <div class="stats-number" style="font-size:1rem;margin-bottom:2px;color:#059669;">
                Activo
            </div>
            <div class="stats-label">Estado del horario</div>
        </div>
    </div>
</div>

<!-- AVISO INFORMATIVO -->
<div style="
    background: rgba(59,130,246,.08);
    border: 1px solid rgba(59,130,246,.2);
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: .88rem;
    color: var(--primary);
">
    <i class="bi bi-info-circle-fill" style="font-size:1.1rem;flex-shrink:0;"></i>
    <span>
        Tu horario fue asignado por el docente encargado y es fijo para todo el semestre.
        Si necesitas hacer un cambio, contacta directamente a <strong><?= htmlspecialchars($docente) ?></strong>.
    </span>
</div>

<!-- TABLA DE HORARIOS -->
<h3 class="section-title">Detalle por día</h3>

<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Día</th>
                    <th>Hora</th>
                    <th>Salón</th>
                    <th>Sede</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($horariosRaw as $h): ?>
                <?php 
                    $horaFormateada = substr($h['hora_inicio'], 0, 5) . ' - ' . substr($h['hora_fin'], 0, 5);
                ?>
                <tr>
                    <td style="font-weight:700;">
                        <i class="bi bi-calendar3 me-2" style="color:var(--primary-light);"></i>
                        <?= htmlspecialchars($h['dia_semana']) ?>
                    </td>
                    <td>
                        <i class="bi bi-clock me-2" style="color:var(--primary-light);"></i>
                        <?= htmlspecialchars($horaFormateada) ?>
                    </td>
                    <td>
                        <span class="role-badge advisor">
                            <i class="bi bi-building me-1"></i>
                            <?= htmlspecialchars($h['nombre_salon'] ?? 'Virtual') ?>
                        </span>
                    </td>
                    <td style="color:var(--text-gray);font-size:.88rem;">
                        <?= htmlspecialchars($h['ubicacion']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
