<?php

// Entrypoint para Vercel (Fase 22, despliegue): el runtime vercel-community/php
// necesita un .php dentro de api/. Esto reenvia al front controller real de
// Laravel, que es public/index.php -- sin duplicar su logica.
require __DIR__ . '/../public/index.php';
