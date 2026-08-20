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
        printf("%-16s %s\n", $key, var_export(getenv($key), true));
    }

    // Los secretos solo se reportan como presente/ausente, nunca su valor.
    foreach (['APP_KEY', 'DB_USERNAME', 'DB_PASSWORD'] as $secret) {
        $value = getenv($secret) ?: ($_SERVER[$secret] ?? null);
        printf("%-16s %s\n", $secret, $value ? 'PRESENTE (' . strlen($value) . ' chars)' : 'AUSENTE o VACIO');
    }

    echo "\n=== VARIABLES VACIAS ===\n";
    foreach (array_keys($_ENV) as $name) {
        if ($_ENV[$name] === '') {
            echo "  $name\n";
        }
    }

    exit;
}

// Vercel importa el .env.example completo cuando se cargan variables en bloque,
// y deja en cadena vacia todas las claves que la plantilla trae sin valor. Para
// Laravel una cadena vacia NO es lo mismo que ausente: env('DB_CONNECTION')
// devuelve '' en vez del default, y la app revienta al arrancar. Las eliminamos
// aca, antes de que el framework lea el entorno, para que apliquen los defaults
// declarados en config/*.php.
foreach (array_keys($_ENV) as $name) {
    if ($_ENV[$name] !== '') {
        continue;
    }

    unset($_ENV[$name], $_SERVER[$name]);
    putenv($name);
}

// Entrypoint para Vercel (Fase 22, despliegue): el runtime vercel-community/php
// necesita un .php dentro de api/. Esto reenvia al front controller real de
// Laravel, que es public/index.php -- sin duplicar su logica.
require __DIR__ . '/../public/index.php';
