<?php
/**
 * login.php
 * Página de inicio de sesión de TecAsesora.
 *
 * Este archivo ES standalone: tiene su propio HTML completo
 * porque no usa el layout del sistema (sidebar, navbar, etc.)
 *
 * Autenticación implementada con PDO. El formulario se procesa en este mismo archivo.
 */

// ── Configuración básica (necesitamos BASE_URL y APP_NAME) ────────────────────
require_once __DIR__ . '/config.php';

// Iniciar sesión UNA sola vez (config.php ya configuró el nombre y parámetros)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Lógica de autenticación ──────────────────────────────────────────────────
$correo   = '';
$recordar = false;

if (!empty($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    
    if (!csrf_check($csrf)) {
        $errorLogin = 'Solicitud inválida (CSRF).';
    } else {
        $correo     = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
        $password   = $_POST['password'] ?? '';
        $recordar   = isset($_POST['recordar']);

        if (empty($correo) || empty($password)) {
            $errorLogin = 'Por favor ingresa tu correo y contraseña.';
        } else {
            $pdo  = db_connect();
            $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE correo_institucional = ? LIMIT 1');
            $stmt->execute([$correo]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['correo_institucional'];
                $_SESSION['rol']        = strtolower($usuario['tipo_usuario']);

                // Si es Alumno o Asesor, obtenemos su id_alumno para facilitar las consultas
                if (in_array($usuario['tipo_usuario'], ['Alumno', 'Asesor'])) {
                    $stmtAl = $pdo->prepare('SELECT id_alumno FROM alumnos WHERE id_usuario = ?');
                    $stmtAl->execute([$usuario['id_usuario']]);
                    $alumnoData = $stmtAl->fetch();
                    $_SESSION['alumno_id'] = $alumnoData['id_alumno'] ?? null;

                    // Si específicamente es Asesor, obtenemos su id_asesor
                    if ($usuario['tipo_usuario'] === 'Asesor' && $_SESSION['alumno_id']) {
                        $stmtAs = $pdo->prepare('SELECT id_asesor FROM asesores WHERE id_alumno = ?');
                        $stmtAs->execute([$_SESSION['alumno_id']]);
                        $asesorData = $stmtAs->fetch();
                        $_SESSION['asesor_id'] = $asesorData['id_asesor'] ?? null;
                    }
                }
                
                // Si es Docente, obtenemos su id_docente y la materia que supervisa
                if ($usuario['tipo_usuario'] === 'Docente') {
                    $stmtDoc = $pdo->prepare('SELECT id_docente, id_materia_encargada FROM docentes WHERE id_usuario = ?');
                    $stmtDoc->execute([$usuario['id_usuario']]);
                    $docData = $stmtDoc->fetch();
                    $_SESSION['docente_id'] = $docData['id_docente'] ?? null;
                    $_SESSION['materia_docente_id'] = $docData['id_materia_encargada'] ?? null;
                }

                header('Location: ' . BASE_URL . 'index.php');
                exit;
            }

            $errorLogin = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Iniciar sesión</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ──────────────────────────────────────────────────────────────────────
           Estilos exclusivos del login (no van en style.css porque esa hoja
           pertenece al sistema autenticado con sidebar y navbar).
        ─────────────────────────────────────────────────────────────────────── */

        :root {
            --primary:   #1B396A;
            --gray:      #8B8B8B;
            --light-bg:  #F4F6F9;
            --input-bg:  #F8FAFC;
        }

        * { font-family: 'Sora', sans-serif; box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            transition: background .3s ease;
            margin: 0;
        }

        /* ── Tarjeta de login ─────────────────────────────────────────────── */
        .login-card {
            width: 100%;
            max-width: 390px;
            background: rgba(255, 255, 255, .88);
            backdrop-filter: blur(14px);
            border-radius: 28px;
            padding: 42px 34px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, .06);
            border: 1px solid rgba(255, 255, 255, .4);
            animation: fadeUp .6s ease;
        }

        .logo {
            width: 62px;
            margin-bottom: 18px;
        }

        .brand-title {
            font-size: 2.1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 6px;
            letter-spacing: -1px;
        }

        .subtitle {
            color: var(--gray);
            font-size: .92rem;
            margin-bottom: 34px;
            font-weight: 400;
        }

        /* ── Inputs ────────────────────────────────────────────────────────── */
        .input-wrapper {
            position: relative;
            margin-bottom: 18px;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 18px;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: .95rem;
            pointer-events: none;
        }

        .form-control {
            height: 54px;
            border-radius: 16px;
            border: 1px solid #E5E7EB;
            background: var(--input-bg);
            padding-left: 48px;
            font-size: .95rem;
            transition: .25s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 .15rem rgba(27, 57, 106, .08);
            background: white;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 18px;
            transform: translateY(-50%);
            color: var(--gray);
            cursor: pointer;
            font-size: 1rem;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
        }

        /* ── Opciones debajo del campo password ──────────────────────────── */
        .options-row {
            margin-top: 8px;
            margin-bottom: 26px;
        }

        .form-check-label { font-size: .88rem; }

        .forgot-link {
            font-size: .88rem;
            text-decoration: none;
            color: var(--primary);
            transition: .2s ease;
        }

        .forgot-link:hover { opacity: .8; }

        /* ── Botón de login ──────────────────────────────────────────────── */
        .btn-login {
            height: 52px;
            border-radius: 16px;
            border: none;
            background: var(--primary);
            font-weight: 600;
            font-size: .96rem;
            transition: .25s ease;
            box-shadow: 0 8px 20px rgba(27, 57, 106, .18);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            background: #16325d;
        }

        /* ── Registro ────────────────────────────────────────────────────── */
        .register-text {
            margin-top: 28px;
            font-size: .9rem;
            color: var(--gray);
        }

        .register-link {
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
        }

        .register-link:hover { opacity: .8; }

        /* ── Botón de tema ──────────────────────────────────────────────── */
        .theme-btn {
            border: none;
            background: transparent;
            color: var(--primary);
            font-size: .88rem;
            font-weight: 500;
            margin-top: 20px;
            cursor: pointer;
            padding: 4px 0;
        }

        /* ── Modal ────────────────────────────────────────────────────────── */
        .modal-content {
            border-radius: 24px;
            border: none;
            padding: 8px;
        }

        /* ── Alerta de error ─────────────────────────────────────────────── */
        .alert-login {
            border-radius: 14px;
            font-size: .88rem;
            padding: 12px 16px;
            margin-bottom: 20px;
        }

        /* ── Animación ────────────────────────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Dark mode ────────────────────────────────────────────────────── */
        [data-bs-theme="dark"] body {
            background: #020817;
        }

        [data-bs-theme="dark"] .login-card {
            background: rgba(15, 23, 42, .94);
            border: 1px solid rgba(255, 255, 255, .10);
            box-shadow: 0 10px 40px rgba(0, 0, 0, .45);
        }

        [data-bs-theme="dark"] .brand-title  { color: #60A5FA; }

        [data-bs-theme="dark"] .subtitle,
        [data-bs-theme="dark"] .register-text,
        [data-bs-theme="dark"] .form-check-label { color: #CBD5E1; }

        [data-bs-theme="dark"] .form-control {
            background: #0F172A;
            border-color: #334155;
            color: white;
        }

        [data-bs-theme="dark"] .form-control::placeholder { color: #64748B; }

        [data-bs-theme="dark"] .form-control:focus {
            background: #0F172A;
            border-color: #60A5FA;
            box-shadow: 0 0 0 .15rem rgba(96, 165, 250, .15);
        }

        [data-bs-theme="dark"] .input-icon,
        [data-bs-theme="dark"] .password-toggle  { color: #94A3B8; }

        [data-bs-theme="dark"] .forgot-link,
        [data-bs-theme="dark"] .register-link,
        [data-bs-theme="dark"] .theme-btn         { color: #60A5FA; }

        [data-bs-theme="dark"] .btn-login         { background: #2563EB; }
        [data-bs-theme="dark"] .btn-login:hover   { background: #1D4ED8; }
    </style>
</head>

<body>

    <div class="login-card text-center">

        <!-- LOGO -->
        <img
            src="<?= BASE_URL ?>assets/img/tecnm-logo.png"
            class="logo"
            alt="TecNM Logo"
            onerror="this.style.display='none'">

        <!-- TÍTULO -->
        <h1 class="brand-title"><?= APP_NAME ?></h1>
        <p class="subtitle">Sistema de Asesorías Académicas</p>

        <!-- ALERTA DE ERROR (Brandon: mostrar si $errorLogin está definido) -->
        <?php if (!empty($errorLogin)): ?>
            <div class="alert alert-danger alert-login">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($errorLogin) ?>
            </div>
        <?php endif; ?>

        <!-- FORMULARIO -->
        <!--
            El formulario publica a esta misma página.
            Descomenta la lógica PHP al inicio cuando tengas la BD.
        -->
        <form action="<?= htmlspecialchars(BASE_URL . 'login.php') ?>" method="POST" novalidate>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <!-- CORREO -->
            <div class="input-wrapper">
                <i class="bi bi-envelope input-icon"></i>
                <input
                    type="email"
                    name="correo"
                    class="form-control"
                    placeholder="Correo institucional"
                    autocomplete="username"
                    value="<?= htmlspecialchars($correo ?? '') ?>"
                    required>
            </div>

            <!-- CONTRASEÑA -->
            <div class="input-wrapper">
                <i class="bi bi-lock input-icon"></i>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Contraseña"
                    autocomplete="current-password"
                    required>
                <button
                    type="button"
                    class="password-toggle"
                    id="togglePassword"
                    aria-label="Mostrar/ocultar contraseña">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>

            <!-- OPCIONES -->
            <div class="d-flex justify-content-between align-items-center options-row">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="recordar" id="remember" <?= !empty($recordar) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#recoverModal">
                    Recuperar contraseña
                </a>
            </div>

            <!-- BOTÓN -->
            <div class="d-grid">
                <button type="submit" class="btn btn-login text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Iniciar sesión
                </button>
            </div>

        </form>

        <!-- REGISTRO -->
        <div class="register-text">
            ¿No tienes cuenta?
            <a href="<?= htmlspecialchars(BASE_URL . 'register.php') ?>" class="register-link">Crear cuenta</a>
        </div>

        <!-- TEMA -->
        <button class="theme-btn" id="themeToggle">
            <i class="bi bi-moon-stars-fill me-1" id="themeIcon"></i>
            Cambiar tema
        </button>

    </div>

    <!-- MODAL RECUPERAR CONTRASEÑA -->
    <div class="modal fade" id="recoverModal" tabindex="-1" aria-labelledby="recoverModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold" id="recoverModalLabel">
                        Recuperar contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-0">
                    <p class="text-secondary small mb-4">
                        Ingresa tu correo institucional y te enviaremos las instrucciones.
                    </p>
                    <form action="<?= htmlspecialchars(BASE_URL . 'recuperar.php') ?>" method="POST">
                        <div class="mb-3">
                            <input
                                type="email"
                                name="correo_recuperar"
                                class="form-control"
                                placeholder="usuario@tectijuana.edu.mx"
                                required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-login text-white">
                                Enviar instrucciones
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // ── Dark mode: persiste en localStorage ──────────────────────────────────
    const html        = document.documentElement;
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon   = document.getElementById('themeIcon');

    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-bs-theme', savedTheme);
    syncIcon(savedTheme);

    themeToggle.addEventListener('click', () => {
        const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        syncIcon(next);
    });

    function syncIcon(theme) {
        themeIcon.className = theme === 'light'
            ? 'bi bi-moon-stars-fill me-1'
            : 'bi bi-sun-fill me-1';
    }

    // ── Mostrar/ocultar contraseña ────────────────────────────────────────────
    document.getElementById('togglePassword').addEventListener('click', () => {
        const input   = document.getElementById('password');
        const icon    = document.getElementById('eyeIcon');
        const isPass  = input.type === 'password';
        input.type    = isPass ? 'text' : 'password';
        icon.className = isPass ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
    </script>

</body>
</html>