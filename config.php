<?php
/**
 * config.php
 * Constantes globales del sistema TecAsesora.
 */
defined('TECASESORA') or define('TECASESORA', true);

// ── Configuración de Región ───────────────────────────────────────────────────
date_default_timezone_set('America/Tijuana');

// ── BASE_URL ──────────────────────────────────────────────────────────────────
$protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL',  $protocol . '://' . $host . $scriptDir . '/');
define('BASE_PATH', rtrim(str_replace('\\', '/', __DIR__), '/') . '/');

// ── Configuración segura de sesión ────────────────────────────────────────────
$secureCookie = ($protocol === 'https');
$hostname = explode(':', $_SERVER['HTTP_HOST'] ?? $host)[0];
if (!ini_get('session.name')) {
    session_name('TECASESORA_SESSION');
}

// En localhost, lo más seguro es dejar el dominio como NULL
$sessionDomain = ($hostname === 'localhost' || $hostname === '127.0.0.1') ? null : $hostname;

ini_set('session.use_strict_mode', '1');
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => $sessionDomain,
        'secure'   => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
} else {
    session_set_cookie_params(0, '/', $sessionDomain, $secureCookie, true);
}

// ── Información del sistema ───────────────────────────────────────────────────
define('APP_NAME',    'TecAsesora');
define('APP_VERSION', '1.0.0');

// ── Páginas permitidas por rol ────────────────────────────────────────────────
define('ALLOWED_PAGES', [
    'administrador' => [
        'dashboard',
        'usuarios',
        'materias',
        'salones',
        'reportes',
    ],
    'docente' => [
        'dashboard',
        'materias',
        'salones',
        'solicitudes_horario',
        'usuarios',
        'reportes',
    ],
    'asesor' => [
        'dashboard',
        'solicitudes',
        'horarios_gestion', // Nueva página para gestionar horarios
        'horarios',
        'asesorias',
        'creditos',
        'estadisticas',
    ],
    // ── AGREGADO: páginas del alumno ─────────────────────────────────────────
    // Solo se añadió este bloque — nada más cambió en este archivo
    'alumno' => [
        'dashboard',
        'solicitar',
        'solicitudes',
        'materias',
        'historial',
        'salones',
    ],
]);

// ── Metas de Horas ────────────────────────────────────────────────────────────
define('META_CREDITOS', 20);
define('META_SERVICIO_SOCIAL', 480);

// ── Conexión a base de datos (credenciales separadas) ─────────────────────────
// Sin cambios — Brayan maneja conexion.php
require_once __DIR__ . '/conexion.php';

// ── CSRF helpers ──────────────────────────────────────────────────────────────
// Sin cambios — Brandon implementó estas funciones
function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception $e) {
            $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
        }
    }
    return $_SESSION['csrf_token'];
}

function csrf_check(?string $token): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Traduce el nombre corto del mes al español.
 * @param string $fecha Fecha en formato Y-m-d
 */
function obtener_mes_corto(string $fecha): string {
    $meses = ['Jan'=>'Ene','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Abr','May'=>'May','Jun'=>'Jun',
              'Jul'=>'Jul','Aug'=>'Ago','Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dic'];
    return $meses[date('M', strtotime($fecha))] ?? date('M', strtotime($fecha));
}