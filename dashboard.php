<?php
defined('TECASESORA') or exit('Acceso directo no permitido.');
require_once BASE_PATH . 'queries_admin.php';

$pageTitle  = 'Dashboard | ' . APP_NAME;
$activeMenu = 'dashboard';

// ── Datos reales desde la BD ──────────────────────────────────────────────────
$stats     = qa_dashboard_stats();
$actividad = qa_get_actividad_reciente();

$userName = $_SESSION['nombre'] ?? 'Administrador';
?>

<!-- WELCOME CARD -->
<div class="welcome-card">
    <h2>Bienvenido, <?= htmlspecialchars($userName) ?></h2>
    <p>Resumen general del sistema TecAsesora.</p>
</div>

<!-- STATS CARDS -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stats-number"><?= $stats['total_usuarios'] ?></div>
            <div class="stats-label">Usuarios registrados</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-book-fill"></i></div>
            <div class="stats-number"><?= $stats['total_materias'] ?></div>
            <div class="stats-label">Materias activas</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-calendar-check-fill"></i></div>
            <div class="stats-number"><?= $stats['asesorias_pend'] ?></div>
            <div class="stats-label">Asesorías pendientes</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon"><i class="bi bi-building"></i></div>
            <div class="stats-number"><?= $stats['total_salones'] ?></div>
            <div class="stats-label">Salones disponibles</div>
        </div>
    </div>
</div>

<!-- QUICK ACTIONS -->
<h3 class="section-title">Gestión rápida</h3>
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=usuarios" class="action-card">
            <div class="action-icon"><i class="bi bi-people-fill"></i></div>
            <div class="action-title">Usuarios</div>
            <div class="action-text">Gestionar usuarios del sistema.</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=materias" class="action-card">
            <div class="action-icon"><i class="bi bi-book-fill"></i></div>
            <div class="action-title">Materias</div>
            <div class="action-text">Administrar materias registradas.</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=salones" class="action-card">
            <div class="action-icon"><i class="bi bi-building"></i></div>
            <div class="action-title">Salones</div>
            <div class="action-text">Gestionar salones disponibles.</div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="<?= BASE_URL ?>index.php?page=reportes" class="action-card">
            <div class="action-icon"><i class="bi bi-bar-chart-fill"></i></div>
            <div class="action-title">Reportes</div>
            <div class="action-text">Ver estadísticas y exportaciones.</div>
        </a>
    </div>
</div>

<!-- ACTIVITY CARD -->
<div class="activity-card">
    <h3 class="section-title mb-4">Actividad reciente</h3>
    <?php if (empty($actividad)): ?>
        <p class="text-muted small">No hay actividad reciente en el sistema.</p>
    <?php else: ?>
        <?php foreach ($actividad as $item): ?>
        <div class="activity-item">
            <div class="activity-dot" style="background: <?= $item['tipo'] === 'usuario' ? 'var(--primary-light)' : '#059669' ?>;"></div>
            <div class="activity-content">
                <h6 style="color:var(--text-main);">
                    <?= $item['tipo'] === 'usuario' ? 'Nuevo usuario: ' : 'Solicitud de: ' ?>
                    <?= htmlspecialchars($item['titulo']) ?>
                </h6>
                <span><?= date('d/m/Y H:i', strtotime($item['fecha'])) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
