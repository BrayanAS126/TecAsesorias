<?php
// create_hash.php — Ejecutar desde CLI para obtener password_hash()
if (PHP_SAPI !== 'cli') {
    echo "Run from CLI: php create_hash.php 'TuContraseña'\n";
    exit(1);
}
$pw = $argv[1] ?? '';
if ($pw === '') {
    echo "Uso: php create_hash.php 'TuContraseña'\n";
    exit(1);
}
echo password_hash($pw, PASSWORD_DEFAULT) . PHP_EOL;
