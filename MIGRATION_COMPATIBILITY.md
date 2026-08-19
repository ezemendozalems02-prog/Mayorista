# MIGRATION_COMPATIBILITY.md

Análisis migración por migración (37 archivos en `database/migrations/`, no 35 como se había estimado en la Fase 1 — se corrige el conteo aquí). Ninguna migración fue ejecutada ni modificada.

Clasificación:
- **COMPATIBLE** — corre igual en PostgreSQL sin ningún ajuste.
- **REQUIERE AJUSTE** — corre en Postgres pero Laravel lo traduce con un mecanismo distinto al de MySQL (se documenta cuál); no requiere reescribir la migración.
- **RIESGO** — funciona pero conviene revisarlo con atención (dato o comportamiento distinto entre motores).
- **BLOQUEANTE** — no correría tal cual contra Postgres.

| # | Migración | PostgreSQL | Detalle |
|---|---|---|---|
| 1 | `0001_01_01_000000_create_users_table.php` | COMPATIBLE | Tabla estándar de Laravel. |
| 2 | `0001_01_01_000001_create_cache_table.php` | COMPATIBLE | Tabla estándar de Laravel (cache driver `database`). |
| 3 | `0001_01_01_000002_create_jobs_table.php` | REQUIERE AJUSTE | `unsignedTinyInteger('attempts')` → Postgres no tiene `tinyint`; Laravel lo mapea a `smallint`. `useCurrent()` en `failed_at` → soportado nativamente. |
| 4 | `2024_03_09_000001_create_organizations_table.php` | COMPATIBLE | Columnas simples + `softDeletes()`. |
| 5 | `2024_03_09_000002_update_users_table.php` | COMPATIBLE | Agrega FK a `organizations`; usa `dropForeign` en el `down()` — soportado igual en ambos motores. |
| 6 | `2024_03_09_000003_create_clients_table.php` | COMPATIBLE | — |
| 7 | `2024_03_09_000004_create_inventory_items_table.php` | COMPATIBLE | Se recuerda: este modelo queda fuera del sistema de Mito (Fase 1, §16) pero la migración en sí no tiene nada MySQL-específico. |
| 8 | `2024_03_09_000005_create_sales_table.php` | COMPATIBLE | — |
| 9 | `2024_03_09_000006_create_trade_ins_table.php` | COMPATIBLE | FKs con `nullOnDelete`/`set null` — portable. |
| 10 | `2024_03_09_000007_create_technicians_table.php` | COMPATIBLE | — |
| 11 | `2024_03_09_000008_create_repairs_table.php` | REQUIERE AJUSTE | `useCurrent()` en `received_at` — soportado nativamente en Postgres. |
| 12 | `2024_03_09_000009_create_spare_parts_table.php` | COMPATIBLE | — |
| 13 | `2024_03_09_000010_create_payments_table.php` | COMPATIBLE | — |
| 14 | `2026_03_09_150935_create_personal_access_tokens_table.php` | COMPATIBLE | Tabla estándar de Sanctum. |
| 15 | `2026_03_09_214110_add_details_to_trade_ins_table.php` | COMPATIBLE | — |
| 16 | `2026_03_13_153538_add_subscription_fields_to_organizations_table.php` | COMPATIBLE | — |
| 17 | `2026_03_13_194001_add_plan_to_organizations_table.php` | COMPATIBLE | — |
| 18 | `2026_03_15_000001_create_branches_table.php` | COMPATIBLE | Usa `dropForeign` tres veces en el `down()` — portable. |
| 19 | `2026_03_16_215725_create_subscription_system_tables.php` | REQUIERE AJUSTE | `json('features_json')` → Postgres tiene tipo `json`/`jsonb` nativo, incluso más rico que MySQL. |
| 20 | `2026_03_16_215740_create_affiliate_system_tables.php` | COMPATIBLE | — |
| 21 | `2026_03_16_215826_create_platform_settings_table.php` | COMPATIBLE | — |
| 22 | `2026_03_19_113203_add_is_demo_to_organizations_and_users_table.php` | COMPATIBLE | — |
| 23 | `2026_03_20_014754_add_user_id_and_type_to_technicians_table.php` | COMPATIBLE | `dropForeign(['user_id'])` en `down()` — portable. |
| 24 | `2026_03_20_015622_add_promo_fields_to_subscription_plans.php` | COMPATIBLE | — |
| 25 | `2026_03_20_111025_create_audit_logs_table.php` | REQUIERE AJUSTE | `json('old_values')`, `json('new_values')` — nativo en Postgres. `useCurrent()->index()` en `created_at` — soportado. |
| 26 | `2026_03_20_114607_add_subscription_plan_id_to_organizations_table.php` | COMPATIBLE | `dropForeign` en `down()` — portable. |
| 27 | `2026_03_20_125451_create_notifications_table.php` | COMPATIBLE | Tabla estándar de notificaciones de Laravel. |
| 28 | `2026_03_20_145422_add_email_settings_to_organizations_table.php` | COMPATIBLE | — |
| 29 | `2026_04_27_000001_create_fiscal_settings_table.php` | REQUIERE AJUSTE | 3 columnas `enum()` (`condicion_iva`, `tipo_comprobante_default`, `ambiente`) → Postgres no tiene tipo `ENUM` nativo vía Schema Builder; Laravel las traduce a `CHECK constraint`. El comportamiento funcional es idéntico (rechaza valores fuera de la lista), solo cambia el mecanismo interno. |
| 30 | `2026_04_27_000002_create_arca_certificates_table.php` | COMPATIBLE | — |
| 31 | `2026_04_27_000003_create_arca_tokens_table.php` | REQUIERE AJUSTE | `enum('environment', ['TESTING','PRODUCTION'])` → mismo caso que la fila 29. |
| 32 | `2026_04_27_000004_create_invoices_table.php` | REQUIERE AJUSTE | `enum('tipo_comprobante', [...])`, `enum('estado', [...])`, `json('arca_response')` → mismos casos anteriores. |
| 33 | `2026_04_27_000005_create_invoice_items_table.php` | COMPATIBLE | — |
| 34 | `2026_04_27_000006_create_arca_logs_table.php` | REQUIERE AJUSTE | `json('request_payload')`, `json('response_payload')` — nativo en Postgres. |
| 35 | `2026_04_27_000007_add_pdf_columns_to_invoices_table.php` | COMPATIBLE | — |
| 36 | `2026_04_28_000001_add_arca_billing_to_pro_plan.php` | COMPATIBLE | — |
| 37 | `2026_05_05_123751_add_motor_integracion_to_fiscal_settings_table.php` | REQUIERE AJUSTE | `enum('motor_integracion', ['manual','afip_sdk'])` — mismo caso. |

## Resumen

| Clasificación | Cantidad |
|---|---|
| COMPATIBLE | 27 |
| REQUIERE AJUSTE (automático, sin reescritura) | 10 |
| RIESGO | 0 |
| BLOQUEANTE | 0 |

**Ninguna de las 37 migraciones es bloqueante para PostgreSQL.** Ningún `DB::raw`/`DB::statement`, ningún `charset`/`collation` MySQL-específico, ninguna función propietaria dentro de la carpeta `database/migrations`.

## Hallazgo fuera del alcance de las migraciones (importante para la Parte 11 de esta fase)

La auditoría de esta fase pidió revisar también controllers y queries, no solo migraciones. Ahí sí apareció código **bloqueante** para Postgres:

`app/Http/Controllers/Web/ReportController.php` usa 17 llamadas `DB::raw(...)` con:
- **`HOUR(sold_at)`** — función exclusiva de MySQL; Postgres no la tiene (equivalente: `EXTRACT(HOUR FROM sold_at)`).
- **`DATE(sold_at)`** — MySQL la soporta como función; Postgres no expone `DATE()` como función (se debe usar `sold_at::date` o `CAST(sold_at AS DATE)`).
- **Comillas dobles como literal de texto**, ej. `COALESCE(branches.name, "Sede Principal")` — en MySQL esto funciona como string por defecto; en **PostgreSQL las comillas dobles siempre son un identificador**, nunca un literal, así que esa consulta fallaría con `column "Sede Principal" does not exist`.

También un `DB::raw` menor en `app/Http/Controllers/Web/SuperAdmin/SuperAdminDashboardController.php:33` (`SUM(sales.total) as total`) — ese sí es portable, no usa sintaxis MySQL-específica.

**Esto no bloquea esta fase**: `ReportController` ya está marcado en la Fase 1 (§9-10) como módulo a reescribir sobre el nuevo modelo de datos, y `SuperAdminDashboardController` pertenece al panel Super Admin que se desactiva en single-license (Fase 1, §15). Ninguno de los dos se ejecuta ni se prueba en esta fase. Se deja documentado para no repetir el mismo error de sintaxis cuando se reescriban esos reportes para Mito.
