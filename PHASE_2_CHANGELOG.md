# PHASE_2_CHANGELOG.md

Registro de cambios de la Fase 2. Como `Mito copia` todavía no tiene Git (por diseño — Git se crea recién en la Fase 3), este archivo es el único rastro de qué se tocó y cómo deshacerlo a mano si hiciera falta.

## Cambios efectivamente aplicados en esta fase

| Archivo | Cambio | Motivo | Riesgo | Rollback |
|---|---|---|---|---|
| `SUPABASE_ENV_PLAN.md` (nuevo) | Creado | Documentar variables de entorno necesarias sin exponer secretos | Ninguno — archivo nuevo, no toca código existente | Borrar el archivo |
| `MIGRATION_COMPATIBILITY.md` (nuevo) | Creado | Clasificar las 37 migraciones para PostgreSQL | Ninguno | Borrar el archivo |
| `STORAGE_PLAN.md` (nuevo) | Creado | Documentar la estrategia de Supabase Storage a futuro | Ninguno | Borrar el archivo |
| `PHASE_2_CHANGELOG.md` (este archivo) | Creado | Registro de la fase | Ninguno | Borrar el archivo |

**Ningún archivo de código (`.php`, `.env`, `config/*.php`, `database/migrations/*`, `app/*`) fue modificado.** No se ejecutó ningún comando `artisan`. No se instaló ningún paquete Composer nuevo.

## Cambios pendientes — SOLO se aplican cuando el usuario entregue credenciales reales de un proyecto Supabase propio de Mito

| Archivo | Cambio previsto | Motivo | Riesgo | Rollback |
|---|---|---|---|---|
| `.env` | `DB_CONNECTION=mysql` → `pgsql`, más `DB_HOST`/`DB_PORT`/`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD` con valores de Supabase, `sslmode` a `require` | Apuntar Laravel a PostgreSQL | Bajo — `.env` no está versionado, un `.env.bak` previo alcanza para revertir | Restaurar desde copia de seguridad manual del `.env` actual (se recomienda copiarlo antes de tocarlo) |
| `php.ini` local | Habilitar `extension=pdo_pgsql` y `extension=pgsql` | Sin esto, PHP no puede abrir ninguna conexión Postgres (detectado en esta fase — ver `SUPABASE_ENV_PLAN.md §7`) | Ninguno — habilitar una extensión no rompe nada existente | Comentar la línea de nuevo en `php.ini` |
| Base de datos Supabase | Ejecutar `php artisan migrate` contra la base vacía | Crear el esquema de Mito en PostgreSQL | Bajo, **solo si la base está confirmada vacía antes de migrar** (regla explícita de esta fase) | `php artisan migrate:rollback` mientras no haya datos reales cargados; o borrar el proyecto Supabase y crear uno nuevo, ya que no habrá datos que perder |

## Qué NO se tocó (y por qué)

- **`Vortex C Phone,` (carpeta original):** no se accedió, no se leyó, no se escribió nada ahí en ningún momento de esta fase.
- **Base de datos MySQL actual:** no se ejecutó ningún `DROP`, `DELETE` ni conexión de escritura. `DB_CONNECTION` en `.env` sigue en `mysql`.
- **Certificados ARCA:** no existen certificados cargados todavía (`storage/app` solo tiene los `.gitignore` de placeholder, confirmado en la Fase 1), así que no había nada que tocar.
- **Mercado Pago:** sin credenciales configuradas, como pide la regla 17.
- **Git:** no se ejecutó `git init` ni ningún otro comando Git.

## Punto de rollback general

Como no hay Git todavía, el rollback más simple de toda la Fase 2 es: **borrar los 4 archivos `.md` listados arriba** (son aditivos, no modifican nada preexistente) y, si ya se llegó a tocar `.env`, restaurar el `.env` original desde la copia de seguridad manual que se recomienda hacer antes de ese paso. No hay ninguna operación de esta fase que afecte una base de datos con datos reales.
