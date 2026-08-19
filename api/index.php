<?php

// TEMPORAL - diagnostico de despliegue. Corre ANTES de arrancar Laravel para
// ver que variables de entorno llegan realmente al proceso PHP. Borrar despues.
if (isset($_GET['__envcheck'])) {
    header('Content-Type: text/plain');

    $keys = [
        'APP_ENV', 'APP_TIMEZONE', 'APP_DEBUG', 'APP_URL', 'APP_LOCALE',
        'PLATFORM_MODE', 'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE',
    ];

    echo "=== PHP " . PHP_VERSION . " ===\n\n";

    foreach ($keys as $key) {
        printf(
            "%-16s getenv=%-28s SERVER=%-28s ENV=%s\n",
            $key,
            var_export(getenv($key), true),
            var_export($_SERVER[$key] ?? '<ausente>', true),
            var_export($_ENV[$key] ?? '<ausente>', true)
        );
    }

    // Los secretos solo se reportan como presente/ausente, nunca su valor.
    foreach (['APP_KEY', 'DB_USERNAME', 'DB_PASSWORD'] as $secret) {
        $value = getenv($secret) ?: ($_SERVER[$secret] ?? null);
        printf("%-16s %s\n", $secret, $value ? 'PRESENTE (' . strlen($value) . ' chars)' : 'AUSENTE o VACIO');
    }

    echo "\n=== VARIABLES VACIAS (hay que corregirlas) ===\n";
    foreach (array_keys($_ENV) as $name) {
        if ($_ENV[$name] === '') {
            echo "  $name\n";
        }
    }

    echo "\n=== total de vars en \$_SERVER: " . count($_SERVER) . " ===\n";
    echo "=== existe .env en el bundle: " . (file_exists(__DIR__ . '/../.env') ? 'SI' : 'NO') . " ===\n";
    echo "=== existe /tmp/config.php: " . (file_exists('/tmp/config.php') ? 'SI' : 'NO') . " ===\n";
    echo "=== extensiones: pdo_pgsql=" . (extension_loaded('pdo_pgsql') ? 'si' : 'NO')
        . " soap=" . (extension_loaded('soap') ? 'si' : 'NO') . " ===\n";

    exit;
}

// Entrypoint para Vercel (Fase 22, despliegue): el runtime vercel-community/php
// necesita un .php dentro de api/. Esto reenvia al front controller real de
// Laravel, que es public/index.php -- sin duplicar su logica.
require __DIR__ . '/../public/index.php';
