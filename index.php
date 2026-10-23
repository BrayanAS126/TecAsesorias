<?php
/**
 * index.php — Enrutador central de TecAsesora.
 */

// 1. Configuración global
require_once __DIR__ . '/config.php';

// 2. Sesión
// config.php ya configuró los parámetros de sesión, solo iniciamos
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ── Determinar el rol activo ──────────────────────────────────────────────────
if (empty($_SESSION['rol'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$rolActivo = $_SESSION['rol'];

// Validar que el rol exista en la lista de páginas permitidas
$paginasPorRol = ALLOWED_PAGES;
if (!array_key_exists($rolActivo, $paginasPorRol)) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// 3. Determinar qué página cargar
$requestedPage = isset($_GET['page'])
    ? preg_replace('/[^a-z_\-]/', '', strtolower(trim($_GET['page'])))
    : 'dashboard';

// Validar contra las páginas permitidas para este rol
if (!in_array($requestedPage, $paginasPorRol[$rolActivo], true)) {
    $requestedPage = 'dashboard';
}

// Ruta absoluta al fragmento: pages/{rol}/{page}.php
$pageFile = BASE_PATH . 'pages/' . $rolActivo . '/' . $requestedPage . '.php';

if (!file_exists($pageFile)) {
    http_response_code(404);
    exit('<h2>Página no encontrada.</h2>');
}

// 4. Valores por defecto
$pageTitle  = APP_NAME;
$activeMenu = $requestedPage;

// Cargar variables de la página sin imprimir su HTML todavía
ob_start();
require $pageFile;
ob_end_clean();

// 5. Renderizar el layout
require BASE_PATH . 'layout/layout.php';