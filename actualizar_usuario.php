<?php
/**
 * actualizar_usuario.php
 * Procesa la actualización de un usuario y su perfil desde el panel de Admin.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/queries_admin.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['rol'] === 'administrador') {
    if (!csrf_check($_POST['csrf'] ?? '')) {
        die("Error de seguridad: Token inválido.");
    }

    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $data = [
        'correo'     => filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL),
        'tipo'       => $_POST['tipo'] ?? 'Alumno',
        'nombre'     => trim($_POST['nombre'] ?? ''),
        'no_control' => trim($_POST['no_control'] ?? ''),
        'semestre'   => (int)($_POST['semestre'] ?? 0),
        'genero'     => $_POST['genero'] ?? 'Otro',
        'carrera'    => trim($_POST['carrera'] ?? ''),
        'fecha_nacimiento' => !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : date('Y-m-d'),
        'id_materia' => filter_input(INPUT_POST, 'id_materia', FILTER_VALIDATE_INT),
        'tipo_compensacion' => $_POST['tipo_compensacion'] ?? 'creditos'
    ];

    if ($id_usuario && $data['correo']) {
        if (qa_update_usuario_perfil($id_usuario, $data)) {
            $_SESSION['flash_success'] = "Los datos del usuario han sido actualizados correctamente.";
            header('Location: ' . BASE_URL . 'index.php?page=usuarios');
            exit;
        }
    }
}

$_SESSION['flash_error'] = "Hubo un error al intentar actualizar el usuario. Por favor, verifica los campos.";
header('Location: ' . BASE_URL . 'index.php?page=usuarios');
exit;