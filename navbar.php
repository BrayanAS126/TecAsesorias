<?php
/**
 * components/navbar.php
 * Barra de navegación superior de TecAsesora.
 *
 * Variables que usa:
 *   $pageTitle  — string — Se muestra como título de la sección actual
 *   $navItems   — array  — Para obtener el label del ítem activo
 *   $activeMenu — string — Clave de la sección activa
 *
 * Los datos del usuario (nombre, avatar) los conectará Brandon
 * desde $_SESSION cuando el sistema de autenticación esté listo.
 */

defined('TECASESORA') or exit('Acceso directo no permitido.');

// Si el componente se evalúa fuera del layout, evitamos avisos.
if (!isset($navItems) || !is_array($navItems)) {
    $navItems = [];
}
if (!isset($activeMenu)) {
    $activeMenu = '';
}

// Obtener el label legible del menú activo para mostrarlo en la navbar
$currentLabel = $navItems[$activeMenu]['label'] ?? APP_NAME;

$userName = $_SESSION['nombre'] ?? 'Usuario';
$userRole = ucfirst($_SESSION['rol'] ?? 'Sin rol');
$userInitial = strtoupper(substr($userName, 0, 1));
?>

<nav class="top-navbar">

    <!-- IZQUIERDA: toggle móvil + título de la página -->
    <div class="navbar-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="page-title">
            <?= htmlspecialchars($currentLabel) ?>
        </h1>
    </div>

    <!-- DERECHA: tema, notificaciones, perfil -->
    <div class="navbar-right">

        <!-- Cambiar tema oscuro/claro -->
        <button class="icon-btn" id="themeToggle" title="Cambiar tema" aria-label="Cambiar tema">
            <!--
                El ícono lo actualiza el JS en layout.php según
                el tema guardado en localStorage.
            -->
            <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
        </button>

        <!-- Notificaciones -->
        <!-- Brandon: agregar lógica de conteo de notificaciones desde BD -->
        <button class="icon-btn" title="Notificaciones" aria-label="Notificaciones">
            <i class="bi bi-bell-fill"></i>
        </button>

        <!-- Perfil de usuario -->
        <div class="profile-box">
            <div class="profile-avatar" title="<?= htmlspecialchars($userName) ?>">
                <?= htmlspecialchars($userInitial) ?>
            </div>
            <div class="profile-info">
                <h6><?= htmlspecialchars($userName) ?></h6>
                <span><?= htmlspecialchars($userRole) ?></span>
            </div>
        </div>

    </div>

</nav>
