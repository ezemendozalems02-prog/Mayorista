# STORAGE_PLAN.md

Análisis de `config/filesystems.php` y recomendación para cuándo se conecte Supabase Storage. **No se subió ningún archivo real ni se activó ninguna credencial en esta fase.**

## Qué hay hoy

`config/filesystems.php` ya define tres discos:

| Disco | Driver | Uso actual |
|---|---|---|
| `local` | filesystem local | `storage/app/private` — certificados ARCA temporales, archivos internos |
| `public` | filesystem local | `storage/app/public` — accesible vía `/storage` |
| `s3` | S3-compatible | Ya trae `endpoint` y `use_path_style_endpoint` configurables por `.env` — **este es exactamente el mecanismo que usa Supabase Storage**, no hace falta escribir ningún disco nuevo. |

## S3-compatible vs. API propia de Supabase Storage — recomendación

| Opción | Ventaja | Desventaja |
|---|---|---|
| **S3-compatible (recomendada)** | Laravel ya tiene el disco `s3` armado en `config/filesystems.php`; se usa el driver oficial `league/flysystem-aws-s3-v3` que es el estándar de facto; el código de la app llama `Storage::disk('s3')->put(...)` igual que con cualquier bucket S3 real; portable si el día de mañana se cambia de proveedor. | Requiere agregar el paquete Composer (ver abajo). |
| **API REST propia de Supabase Storage** | Da acceso a features específicas de Supabase (políticas RLS a nivel de bucket, transformaciones de imagen on-the-fly). | No hay un driver Flysystem oficial mantenido por Laravel/Supabase para esto — habría que escribir un adapter a mano o usar un paquete de comunidad, más superficie para mantener. |

**Recomendación: usar el disco `s3` ya existente contra el endpoint S3-compatible de Supabase Storage.** Es la opción más estable para un proyecto Laravel estándar y no agrega código nuevo, solo configuración.

## Prerrequisito detectado (pendiente, no bloqueante para esta fase)

`composer.json` **no incluye todavía** `league/flysystem-aws-s3-v3`. Sin ese paquete, el disco `s3` existe en la config pero Laravel no puede instanciarlo (`Driver [s3] is not supported` o error de clase no encontrada). Se instala recién cuando se decida activar Storage — no se instala en esta fase porque la regla 18 de esta fase pide no conectar Storage todavía salvo necesidad estricta.

## Qué archivos son candidatos a Supabase Storage (cuando se active)

| Contenido | ¿Va a Storage? | Motivo |
|---|---|---|
| Logo / branding de Mito | Sí | Público, liviano, se sirve directo desde el bucket. |
| Fotos de producto (cuando exista `Product`) | Sí | Volumen alto, crece con el catálogo — no debería vivir en el filesystem del servidor. |
| PDFs de comprobantes ARCA (`invoices.pdf_path`) | Evaluar | Hoy se generan en `storage/app` local vía `barryvdh/laravel-dompdf`. Podrían moverse a Storage para no perderlos en un redeploy, pero no es urgente. |
| Certificados fiscales ARCA (`.crt`/`.key`) | **No** | Máxima sensibilidad. Hoy `config/arca.php` los escribe en `storage/app/arca/tmp` con permisos `0600` y los borra inmediatamente después de firmar. Deben seguir fuera de cualquier bucket, aunque sea privado. |
| Logs de auditoría / aplicación | No | Quedan en `storage/logs`, no son archivos de usuario. |

## Próximo paso (cuando se decida activar, no ahora)

1. `composer require league/flysystem-aws-s3-v3`
2. Crear el bucket en el panel de Supabase Storage.
3. Completar `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` / `AWS_BUCKET` / `AWS_ENDPOINT` / `AWS_USE_PATH_STYLE_ENDPOINT=true` en `.env` con las credenciales del bucket de Supabase (nunca las de Vortex).
4. Probar con un archivo de prueba, no con datos reales, antes de conectar el flujo de fotos de producto.

**Estado:** análisis únicamente. Ningún paquete instalado, ninguna credencial de Storage configurada.
