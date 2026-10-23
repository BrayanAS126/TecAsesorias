<?php
/**
 * exportar_asesores.php
 * Genera un archivo CSV con la lista de asesores del docente logueado.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_docente.php';
mb_internal_encoding("UTF-8"); // Asegura que PHP maneje las cadenas como UTF-8

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Validar sesión y rol
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'docente') {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Limpiar cualquier salida previa (espacios o saltos de línea accidentales)
if (ob_get_length()) ob_clean();

$materiaId = $_SESSION['materia_docente_id'] ?? 0;
$asesores  = qd_get_asesores_materia($materiaId);

// 2. Configurar cabeceras para descarga de archivo
$filename = "asesores_" . date('Ymd_His') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 3. Crear el archivo en memoria (output stream)
$output = fopen('php://output', 'w');

// Agregar BOM para que Excel reconozca UTF-8 (acentos y ñ) correctamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// 4. Escribir la cabecera del CSV
fputcsv($output, ['Nombre Completo', 'Correo Institucional', 'N° Control', 'Carrera', 'Estado', 'Sesiones Impartidas']);

// 5. Escribir los datos de los asesores
foreach ($asesores as $a) {
    fputcsv($output, [
        $a['nombre_completo'],
        $a['correo'],
        $a['no_control'],
        $a['carrera'],
        $a['validado'] ? 'Validado' : 'Pendiente',
        $a['sesiones_dadas']
    ]);
}

fclose($output);
exit;