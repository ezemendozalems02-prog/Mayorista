# SUPABASE_SCHEMA_AUDIT.md

Verificación del esquema real creado en Supabase PostgreSQL, después de correr las 37 migraciones heredadas. Todas las pruebas se hicieron dentro de transacciones con `DB::rollBack()` al final — la base quedó confirmada vacía antes y después.

## Conexión

- Motor: PostgreSQL **17.6** (Supabase)
- Conexión Laravel: `pgsql`, `sslmode=require`
- Extensiones PHP habilitadas para esto: `pdo_pgsql`, `pgsql` (no estaban activas al empezar la Fase 2 — se habilitaron en `php.ini` de este entorno local)

## Esquema esperado vs. real

| Esperado | Real | Resultado |
|---|---|---|
| 37 migraciones, todas `DONE` | 37/37 `DONE`, exit code 0 | ✅ |
| Tablas nuevas en `public` | 37 tablas (ver lista abajo) | ✅ |
| Schema `public` vacío antes de migrar | 0 tablas confirmado antes de `migrate` | ✅ |
| Sin colisión con schemas propios de Supabase (`auth`, `storage`, `realtime`, `vault`) | Ninguna migración tocó esos schemas — Laravel escribe todo en `public` por `search_path` | ✅ |

**Tablas creadas:** `affiliates`, `arca_certificates`, `arca_logs`, `arca_tokens`, `audit_logs`, `branches`, `cache`, `cache_locks`, `clients`, `commission_rules`, `commissions`, `failed_jobs`, `fiscal_settings`, `inventory_items`, `invoice_items`, `invoices`, `job_batches`, `jobs`, `migrations`, `notifications`, `organizations`, `password_reset_tokens`, `payments`, `personal_access_tokens`, `plan_features`, `platform_settings`, `referrals`, `repair_parts`, `repairs`, `sale_items`, `sales`, `sessions`, `spare_parts`, `subscription_plans`, `technicians`, `trade_ins`, `users`.

## Eloquent — modelos probados

Cada modelo se creó, se relacionó con los demás y se verificó dentro de una transacción con rollback:

| Modelo | Prueba | Resultado |
|---|---|---|
| `Organization` | Crear | ✅ |
| `User` | Crear, `belongsTo(Organization)` | ✅ (`user->organization->name` resolvió bien) |
| `Client` | Crear, `SoftDeletes` | ✅ (`delete()` → `trashed()=true`, excluido de conteo normal, presente en `withTrashed()`) |
| `Sale` + `SaleItem` | Crear, `hasMany(SaleItem)` | ✅ (`sale->items()->count()=1`) |
| `Payment` | Crear, relación con `Sale`/`Client` | ✅ |
| `FiscalSetting` | Crear | ✅ |
| `Invoice` + `InvoiceItem` | Crear, `hasMany(InvoiceItem)` | ✅ (`invoice->items()->count()=1`) |
| `Branch` | Crear | ✅ |

Un primer intento falló por un dato de prueba mío mal armado (usé `subtotal` en vez de `total` para `InvoiceItem`) — no es un problema de Postgres ni del esquema, quedó corregido y confirmado en el segundo intento.

**Nota menor detectada (no bloqueante):** al crear registros aparecen dos warnings `DEPRECATED` de PHP 8.4 en `App\Services\MonitoringService::audit()` — parámetros con tipo nullable implícito, sin el `?` explícito. Es una advertencia de compatibilidad con PHP 8.4 (no de Postgres), no rompe nada, queda anotada para una futura limpieza de tipos.

## Sanctum — probado

- Emisión de token (`$user->createToken()`) — ✅
- Resolución del token de vuelta al usuario correcto (lo que hace el middleware `auth:sanctum` en cada request) — ✅
- Revocación del token — ✅
- Todo funciona igual que contra MySQL; la tabla `personal_access_tokens` no tiene nada específico de un motor u otro.

## Transacciones y rollback

Cada bloque de prueba corrió dentro de `DB::beginTransaction()` / `DB::rollBack()`. Al finalizar, todas las tablas de negocio (`organizations`, `users`, `clients`, `sales`, `sale_items`, `payments`, `invoices`, `invoice_items`, `fiscal_settings`, `branches`, `personal_access_tokens`) volvieron a **0 filas** — confirmado con una consulta final después de todas las pruebas.

**Nota técnica:** los ids autoincrementales (`id=1`, `id=2`...) quedaron con saltos entre pruebas — es comportamiento normal de Postgres: las secuencias (`SERIAL`) no retroceden cuando se hace rollback de una transacción, a diferencia del `AUTO_INCREMENT` de MySQL en algunos casos. No afecta nada funcional, es solo una diferencia de implementación a tener presente.

## Conclusión

**Laravel 11 + Sanctum + Eloquent funcionan correctamente contra PostgreSQL 17.6 en Supabase.** El esquema heredado de 37 migraciones migró sin errores, sin ajustes de código, y sin ningún dato de Vortex cargado — la base quedó exactamente como se dejó: vacía, lista para el modelo de datos de Mito.
