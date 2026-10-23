<?php
/**
 * exportar_asesorias.php
 * Genera un archivo CSV con el historial de asesorías completadas de la materia.
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

// Limpiar cualquier salida previa
if (ob_get_length()) ob_clean();

$materiaId = $_SESSION['materia_docente_id'] ?? 0;
$datos     = qd_get_todas_asesorias_materia($materiaId);

$filename = "bitacora_asesorias_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel

fputcsv($output, ['Fecha', 'Asesor', 'Alumno', 'Materia', 'Tema Abordado', 'Lugar']);

foreach ($datos as $d) {
    fputcsv($output, [
        date('d/m/Y', strtotime($d['fecha_asesoria'])),
        $d['asesor'],
        $d['alumno'],
        $d['nombre_materia'],
        $d['tema'],
        $d['nombre_salon'] ?? 'Virtual'
    ]);
}

fclose($output);
exit;