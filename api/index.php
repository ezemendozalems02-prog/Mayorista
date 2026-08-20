<?php

// En Vercel, el runtime PHP viene con display_errors activo. Los avisos nativos
// de PHP (deprecated/notice) se imprimen como salida ANTES de que Laravel
// pueda enviar sus propios headers, dejando el status code pegado en 200 sin
// importar la respuesta real (un 404 o un 500 se ven como 200 para el cliente).
// Se siguen registrando en los Logs de Vercel via log_errors, solo dejan de
// imprimirse en el cuerpo de la respuesta.
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

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
