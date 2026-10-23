<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_admin.php';
mb_internal_encoding("UTF-8");

if (session_status() === PHP_SESSION_NONE) session_start();
if ($_SESSION['rol'] !== 'administrador') exit;

if (ob_get_length()) ob_clean();

$usuarios = qa_get_usuarios();

$filename = "usuarios_global_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel

fputcsv($output, ['ID', 'Correo Institucional', 'Nombre Completo', 'Rol', 'N° Control', 'Carrera', 'Registro']);

foreach ($usuarios as $u) {
    fputcsv($output, [
        $u['id_usuario'],
        $u['correo_institucional'],
        $u['nombre_completo'] ?: 'Sin perfil',
        $u['tipo_usuario'],
        $u['no_control'],
        $u['carrera'],
        $u['fecha_registro']
    ]);
}
fclose($output);
exit;