<?php
/**
 * components/sidebar.php
 * Sidebar de navegación de TecAsesora.
 *
 * Variables que usa (vienen del layout):
 *   $navItems   — array  — Lista de ítems del menú
 *   $activeMenu — string — Clave del ítem activo
 *
 * Para agregar un ítem nuevo:
 *   1. Añádelo en $navItems en layout/layout.php
 *   2. Crea pages/tu-pagina.php
 *   3. Agrégalo a ALLOWED_PAGES en config.php
 */

defined('TECASESORA') or exit('Acceso directo no permitido.');

if (!isset($navItems) || !is_array($navItems)) {
    $navItems = [];
}
if (!isset($activeMenu)) {
    $activeMenu = '';
}
?>

<aside class="sidebar" id="sidebar">

    <!-- MARCA -->
    <div class="sidebar-brand">
        <!--
            Si la imagen no existe aún, no rompe la página.
            Brayan / Brandon: reemplazar el src cuando el logo esté disponible.
        -->
        <img
            src="<?= BASE_URL ?>assets/img/tecnm-logo.png"
            alt="TecNM Logo"
            onerror="this.style.display='none'">
        <h4><?= APP_NAME ?></h4>
    </div>

    <!-- MENÚ PRINCIPAL -->
    <ul class="sidebar-menu">
        <?php foreach ($navItems as $key => $item): ?>
            <li>
                <a
                    href="<?= BASE_URL ?>index.php?page=<?= $key ?>"
                    class="sidebar-link <?= ($activeMenu === $key) ? 'active' : '' ?>"
                >
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- CERRAR SESIÓN (parte inferior del sidebar) -->
    <ul class="sidebar-menu mt-auto" style="padding-top: 20px; border-top: 1px solid rgba(255,255,255,.07); margin-top: auto;">
        <li>
            <a href="<?= BASE_URL ?>logout.php" class="sidebar-link">
                <i class="bi bi-box-arrow-left"></i>
                Cerrar sesión
            </a>
        </li>
    </ul>

</aside>
