<?php
/**
 * exportar_materias.php
 * Genera un archivo CSV con el detalle de la materia asignada al docente.
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

$materiaId = $_SESSION['materia_docente_id'] ?? 0;
$m = qd_get_detalle_materia($materiaId);

$filename = "detalle_materia_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel

fputcsv($output, ['Clave', 'Nombre de la Materia', 'Semestre', 'Asesores Activos']);

if ($m) {
    fputcsv($output, [
        $m['clave'],
        $m['nombre_materia'],
        $m['semestre_materia'] . '°',
        $m['total_asesores']
    ]);
}

fclose($output);
exit;