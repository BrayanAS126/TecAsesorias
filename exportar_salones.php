<?php
/**
 * exportar_salones.php
 * Genera un archivo CSV con el catálogo de salones y su disponibilidad.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_docente.php';
mb_internal_encoding("UTF-8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

if (ob_get_length()) ob_clean();

$salones = qd_get_todos_salones();

$filename = "reporte_salones_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel

fputcsv($output, ['Nombre Salón', 'Ubicación', 'Capacidad', 'Horarios Asignados']);

foreach ($salones as $s) {
    fputcsv($output, [
        $s['nombre_salon'],
        $s['ubicacion'],
        $s['capacidad'] . ' personas',
        $s['usos']
    ]);
}

fclose($output);
exit;