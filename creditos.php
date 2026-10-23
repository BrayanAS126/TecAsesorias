<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');

// Ruta absoluta para que el editor encuentre las funciones correctamente
require_once dirname(__DIR__, 2) . '/queries_asesor.php';

$pageTitle  = 'Mi progreso | ' . APP_NAME;
$activeMenu = 'creditos';

$asesorId = $_SESSION['asesor_id'] ?? 0;
$progreso = qs_get_progreso_asesor($asesorId);
$historial = qs_get_historial_creditos($asesorId);

$labelTipo = ($progreso['tipo'] ?? 'creditos') === 'creditos' ? 'Créditos Complementarios' : 'Servicio Social';
$esCreditos = ($progreso['tipo'] ?? 'creditos') === 'creditos';

// Movemos la lógica de estilo aquí arriba para evitar errores de validación CSS
$pctValue = $progreso['porcentaje'] ?? 0;
$barBg = $esCreditos ? 'linear-gradient(90deg,#1B396A,#3B82F6)' : 'linear-gradient(90deg,#059669,#34d399)';
?>

<!-- WELCOME -->
<div class="welcome-card">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h2 style="color:var(--text-main);">Mi progreso académico</h2>
            <p>Consulta tu avance acumulado durante el semestre actual.</p>
        </div>
        <span class="role-badge <?= $esCreditos ? 'advisor' : 'student' ?>" style="padding: 8px 16px;">
            Programa: <?= $esCreditos ? 'Créditos' : 'Servicio Social' ?>
        </span>
    </div>
</div>

<!-- TARJETAS DE PROGRESO -->
<div class="row g-4 mb-4">

    <!-- Modo Activo (Créditos o SS) -->
    <div class="col-md-7">
        <div class="table-card" style="padding:28px;">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                <div class="stats-icon" style="margin-bottom:0; <?= !$esCreditos ? 'background:rgba(52,211,153,.15);color:#34D399;' : '' ?>">
                    <i class="bi <?= $esCreditos ? 'bi-award-fill' : 'bi-clock-history' ?>"></i>
                </div>
                <div>
                    <div style="font-size:1rem;font-weight:700;color:var(--text-main);">
                        <?= $labelTipo ?>
                    </div>
                    <div style="font-size:.85rem;color:var(--text-gray);">
                        Meta: <?= $progreso['meta'] ?> <?= $esCreditos ? 'créditos' : 'horas' ?>
                    </div>
                </div>
            </div>

            <!-- Número grande -->
            <div style="display:flex;align-items:flex-end;gap:8px;margin-bottom:14px;">
                <span style="font-size:3rem;font-weight:700;color:var(--text-main);line-height:1;">
                    <?= $progreso['completadas'] ?>
                </span>
                <span style="font-size:1.1rem;color:var(--text-gray);margin-bottom:8px;">
                    / <?= $progreso['meta'] ?> <?= $esCreditos ? '' : 'hrs' ?>
                </span>
            </div>

            <!-- Barra de progreso -->
            <div style="background:var(--border-color);border-radius:20px;height:10px;margin-bottom:8px;">
                <div style="width:<?= $pctValue ?>%;height:100%;border-radius:20px;
                            background:<?= $barBg ?>;transition:.6s ease;"></div>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.8rem;color:var(--text-gray);">
                <span><?= $progreso['porcentaje'] ?>% completado</span>
                <span><?= max(0, $progreso['meta'] - $progreso['completadas']) ?> restantes</span>
            </div>
        </div>
    </div>

    <!-- Card informativa -->
    <div class="col-md-5">
        <div class="table-card" style="padding:28px; background:var(--primary); color:white;">
            <h5 class="fw-bold mb-3">Información</h5>
            <p style="font-size:.85rem; opacity:.9;">
                Cada asesoría marcada como <strong>Completada</strong> suma una hora a tu registro. 
                Asegúrate de registrar el tema visto en la bitácora para que la sesión sea válida.
            </p>
            <hr style="border-color:rgba(255,255,255,.2);">
            <div class="d-flex align-items-center gap-2" style="font-size:.8rem;">
                <i class="bi bi-info-circle"></i>
                <span>Tipo: <strong><?= $esCreditos ? 'Académico' : 'Servicio Social' ?></strong></span>
            </div>
        </div>
    </div>
</div>

<!-- HISTORIAL -->
<h3 class="section-title">Historial de créditos</h3>

<div class="table-card">
    <div class="table-responsive">
        <table class="table custom-table align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Detalle de la asesoría</th>
                    <th class="text-center">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($historial)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">Aún no tienes asesorías completadas para mostrar.</td></tr>
                <?php endif; ?>
                <?php foreach ($historial as $h): ?>
                <tr>
                    <td style="font-size:.85rem;color:var(--text-gray);"><?= date('d/m/Y', strtotime($h['fecha'])) ?></td>
                    <td style="font-size:.88rem;">Asesoría de <strong><?= htmlspecialchars($h['materia']) ?></strong> — <?= htmlspecialchars($h['alumno']) ?></td>
                    <td class="text-center">
                        <span class="role-badge <?= $esCreditos ? 'advisor' : 'student' ?>">+1 <?= $esCreditos ? 'crédito' : 'hora' ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
