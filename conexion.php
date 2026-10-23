<?php
/**
 * conexion.php
 * Carga las credenciales y abre la conexión PDO.
 * No incluir directamente — config.php lo llama por ti.
 */

require_once __DIR__ . '/credenciales.php';

define('DB_HOST',    $db_host);
define('DB_NAME',    $db_name);
define('DB_USER',    $db_user);
define('DB_PASS',    $db_password);
define('DB_CHARSET', $db_charset);

function db_connect(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
                // Forzamos la codificación UTF-8 en la comunicación con MariaDB
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            // En desarrollo, es mejor ver el error real:
            if ($_SERVER['HTTP_HOST'] === 'localhost') {
                exit('Error de conexión: ' . $e->getMessage());
            } else {
                exit('Error de conexión a la base de datos. Por favor contacte al administrador.');
            }
        }
    }
    return $pdo;
}