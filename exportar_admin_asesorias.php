<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_admin.php';
mb_internal_encoding("UTF-8");

if (session_status() === PHP_SESSION_NONE) session_start();
if ($_SESSION['rol'] !== 'administrador') exit;

if (ob_get_length()) ob_clean();

$data = qa_get_global_solicitudes_reporte();

$filename = "bitacora_global_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel

fputcsv($output, ['Fecha Asesoría', 'Materia', 'Asesor', 'Alumno', 'Lugar', 'Estado', 'Solicitado el']);

foreach ($data as $r) {
    fputcsv($output, [
        date('d/m/Y', strtotime($r['fecha_asesoria'])),
        $r['nombre_materia'],
        $r['asesor'],
        $r['alumno'],
        $r['lugar'],
        $r['estado'],
        date('d/m/Y H:i', strtotime($r['fecha_creacion']))
    ]);
}
fclose($output);
exit;