# SUPABASE_ENV_PLAN.md

Plan de variables de entorno para migrar Mito Yamile de MySQL a Supabase PostgreSQL.

**Este documento no contiene ningún valor secreto.** Todos los ejemplos usan placeholders. El `.env` real del proyecto no fue modificado — este plan se aplicará recién cuando el usuario entregue credenciales reales de un proyecto Supabase propio de Mito (nunca de Vortex).

---

## 1. Cómo conectarse: dos formas, una recomendación

Supabase expone dos rutas de conexión a Postgres para un mismo proyecto:

| Modo | Puerto típico | Cuándo usarlo |
|---|---|---|
| **Conexión directa** | 5432 | Apps de larga vida con un pool de conexiones persistente propio (como un backend Laravel tradicional en un servidor/VPS). |
| **Connection Pooler (PgBouncer) — modo *transaction*** | 6543 | Entornos serverless con muchísimas conexiones cortas (funciones edge, lambdas). |
| **Connection Pooler — modo *session*** | 6543 (config. session) | Alternativa cuando se necesita pooling pero el driver requiere sesión completa (prepared statements). |

**Recomendación para este proyecto:** conexión directa (puerto 5432) o pooler en modo *session*. Laravel/PDO usa **prepared statements** por defecto, y el pooler en modo *transaction* es conocido por romperlos (error típico: `prepared statement "pdo_stmt_00000001" already exists`) porque reutiliza la conexión física entre transacciones de distintos clientes. Si en el futuro hace falta el pooler por límite de conexiones concurrentes, hay que forzarlo a modo *session* o desactivar prepared statements vía `PDO::ATTR_EMULATE_PREPARES => true` en `config/database.php`. No lo configuramos ahora — se decide cuando haya tráfico real que lo justifique.

---

## 2. Variables a modificar en `.env`

| Variable | Propósito | Quién la provee | Formato (sin credenciales) |
|---|---|---|---|
| `DB_CONNECTION` | Motor de base de datos | Fijo | `pgsql` |
| `DB_HOST` | Host del proyecto Supabase | Panel de Supabase → Project Settings → Database | `db.xxxxxxxxxxxx.supabase.co` |
| `DB_PORT` | Puerto de conexión | Panel de Supabase | `5432` (directa) |
| `DB_DATABASE` | Nombre de la base | Supabase lo fija por defecto | `postgres` |
| `DB_USERNAME` | Usuario de conexión | Panel de Supabase | `postgres` (o el rol que se cree) |
| `DB_PASSWORD` | Contraseña de Postgres | La define el usuario al crear el proyecto | *(nunca se escribe en este documento)* |

## 3. Variable opcional — connection string única

| Variable | Propósito | Formato (sin credenciales) |
|---|---|---|
| `DB_URL` | Alternativa a las 6 variables de arriba en una sola URL. `config/database.php` ya la soporta (`'url' => env('DB_URL')`) para la conexión `pgsql`. Si se define, Laravel la prioriza sobre las variables sueltas. | `postgresql://usuario:password@host:5432/postgres?sslmode=require` |

Recomendación: usar las variables discretas (`DB_HOST`, `DB_PORT`, etc.) en vez de `DB_URL` — son más fáciles de rotar/auditar una por una y es el patrón que ya sigue el resto del `.env` del proyecto.

## 4. SSL

Supabase exige SSL en la conexión. `config/database.php` ya trae `'sslmode' => 'prefer'` en el bloque `pgsql`. Con Supabase conviene subirlo a `'require'` para no permitir downgrade silencioso a conexión sin cifrar. Es un cambio de una palabra cuando se apliquen las credenciales reales — no se toca todavía.

## 5. Variables nuevas — no relacionadas a la conexión a datos

| Variable | Propósito | Fase en que se activa |
|---|---|---|
| `APP_KEY` | Clave de cifrado de Laravel. **Debe regenerarse de cero** para Mito (`php artisan key:generate`) — nunca reutilizar la de Vortex. | Fase 4 (branding) o antes si se necesita levantar el proyecto |
| `PLATFORM_MODE` | `single_license` — desactiva el flujo SaaS (ver Fase 1, §15) | Fase 4 |
| `AWS_ENDPOINT`, `AWS_USE_PATH_STYLE_ENDPOINT`, `AWS_BUCKET`, `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY` | Conexión a Supabase Storage (compatible S3) | Diferido — ver `STORAGE_PLAN.md`, no se activa en esta fase |

## 6. Variables que NO cambian

`APP_TIMEZONE`, `APP_LOCALE`, `SESSION_*`, `CACHE_*`, `QUEUE_CONNECTION`, `MAIL_*`, `ARCA_WSAA_*`, `ARCA_WSFE_*` — no dependen del motor de base de datos ni de Supabase.

## 7. Requisito de entorno detectado (bloqueante hasta resolverse)

El PHP local (`php -v` → 8.4.22) **no tiene habilitada la extensión `pdo_pgsql`** (`php -m` solo lista `pdo_mysql` y `pdo_sqlite`). Sin esa extensión, Laravel no puede abrir ninguna conexión PostgreSQL sin importar qué credenciales se carguen en `.env`. Antes de la Parte 5/6 de esta fase hace falta:

1. Habilitar `extension=pdo_pgsql` (y `extension=pgsql`) en el `php.ini` que usa este entorno, o
2. Confirmar que el entorno de despliegue real (servidor/hosting definitivo) ya la tiene — en ese caso solo falta habilitarla en la máquina de desarrollo local para poder probar.

---

**Estado:** documento de planificación. Ninguna variable fue escrita en `.env` todavía.
