<?php
/**
 * layout/layout.php
 * Template maestro de TecAsesora.
 */

defined('TECASESORA') or exit('Acceso directo no permitido.');

if (!isset($pageTitle)) {
    $pageTitle = APP_NAME;
}
if (!isset($pageFile)) {
    $pageFile = '';
}

// Brandon: $rolActivo viene de $_SESSION['rol'] que él guarda al autenticar
$rolActivo = $_SESSION['rol'] ?? 'administrador';

// ── Menú por rol ──────────────────────────────────────────────────────────────
// Para agregar una nueva página: añadir entrada aquí Y en ALLOWED_PAGES (config.php)
$menusPorRol = [
    'administrador' => [
        'dashboard' => ['icon' => 'bi-grid-fill',      'label' => 'Panel principal'],
        'usuarios'  => ['icon' => 'bi-people-fill',    'label' => 'Usuarios'],
        'materias'  => ['icon' => 'bi-book-fill',      'label' => 'Materias'],
        'salones'   => ['icon' => 'bi-building',       'label' => 'Salones'],
        'reportes'  => ['icon' => 'bi-bar-chart-fill', 'label' => 'Reportes'],
    ],
    'docente' => [
        'dashboard'           => ['icon' => 'bi-grid-fill',                   'label' => 'Panel principal'],
        'materias'            => ['icon' => 'bi-book-fill',                   'label' => 'Materias'],
        'salones'             => ['icon' => 'bi-building',                    'label' => 'Salones'],
        'solicitudes_horario' => ['icon' => 'bi-calendar2-week-fill',         'label' => 'Solicitudes'],
        'usuarios'            => ['icon' => 'bi-people-fill',                 'label' => 'Usuarios'],
        'reportes'            => ['icon' => 'bi-file-earmark-bar-graph-fill', 'label' => 'Reportes'],
    ],
    'asesor' => [

        'dashboard'    => ['icon' => 'bi-grid-fill',           'label' => 'Panel principal'],
        'solicitudes'  => ['icon' => 'bi-bell-fill',           'label' => 'Solicitudes'],
        'horarios'     => ['icon' => 'bi-calendar2-week-fill', 'label' => 'Mis horarios'],
        'asesorias'    => ['icon' => 'bi-journal-check',       'label' => 'Mis asesorías'],
        'creditos'     => ['icon' => 'bi-award-fill',          'label' => 'Créditos / SS'],
        'estadisticas' => ['icon' => 'bi-bar-chart-fill',      'label' => 'Estadísticas'],
    ],
    'alumno' => [
        'dashboard'    => ['icon' => 'bi-grid-fill',        'label' => 'Panel principal'],
        'solicitar'    => ['icon' => 'bi-plus-circle-fill', 'label' => 'Solicitar asesoría'],
        'solicitudes'  => ['icon' => 'bi-bell-fill',        'label' => 'Mis solicitudes'],
        'materias'     => ['icon' => 'bi-book-fill',        'label' => 'Materias y asesores'],
        'historial'    => ['icon' => 'bi-journal-check',    'label' => 'Historial'],
        'salones'      => ['icon' => 'bi-building',         'label' => 'Salones'],
    ],
];

$navItems = $menusPorRol[$rolActivo] ?? $menusPorRol['administrador'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <!-- Anti-flash: aplica tema ANTES de que el navegador pinte nada -->
    <script>
        (function() {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 0;
            display: block !important;
            background: #F4F7FB;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 260px;
            height: 100vh;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 900;
            flex-shrink: 0;
            width: 100%;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .content-wrapper {
            flex: 1 1 auto;
            padding: 30px;
        }

        @media (max-width: 991px) {
            .sidebar        { transform: translateX(-100%); transition: transform .3s ease; }
            .sidebar.show   { transform: translateX(0); }
            .main-content   { margin-left: 0; }
            .menu-toggle    { display: block !important; }
            .content-wrapper { padding: 20px; }
        }
    </style>
</head>

<body>

    <?php require BASE_PATH . 'components/sidebar.php'; ?>

    <main class="main-content">

        <?php require BASE_PATH . 'components/navbar.php'; ?>

        <div class="content-wrapper">
            <?php
            if (!empty($pageFile) && file_exists($pageFile)) {
                require $pageFile;
            } else {
                echo '<div class="alert alert-warning">La página solicitada no está disponible.</div>';
            }
            ?>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // ── Dark mode ─────────────────────────────────────────────────────────────
    const html        = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon   = document.getElementById('themeIcon');

    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    updateThemeIcon(savedTheme);

    themeToggle.addEventListener('click', () => {
        const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        updateThemeIcon(next);
    });

    function updateThemeIcon(theme) {
        if (!themeIcon) return;
        themeIcon.className = theme === 'light'
            ? 'bi bi-moon-stars-fill'
            : 'bi bi-sun-fill';
    }

    // ── Sidebar móvil ─────────────────────────────────────────────────────────
    const menuToggle = document.getElementById('menuToggle');
    const sidebar    = document.getElementById('sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('show');
        });
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    </script>

</body>
</html>