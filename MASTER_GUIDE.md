# 📱 Mito Yamile - Guía de Planes y Accesos (heredada, en revisión)

> **Nota (Fase 3):** este documento describe el sistema de planes/SaaS heredado de Vortex Control Phone. Mito Yamile corre en modo `single_license` (instalación única, sin suscripciones ni límites por plan) — el contenido de abajo sobre precios y planes ya **no aplica** y queda pendiente de reemplazo por una guía de roles propia de Mito en una fase posterior. Se conserva por ahora solo como referencia histórica de cómo funcionaban los roles y permisos en el código heredado.

Este documento resume la lógica de permisos, roles y costos configurada actualmente en el sistema. Es la fuente de verdad para entender qué puede hacer cada usuario según su plan y rol.

---

## 💎 1. Planes y Costos

El sistema calcula los precios en Pesos Argentinos (ARS) en tiempo real utilizando la API de **Dólar Blue** (Venta).

| Plan | ID Código | Precio (USD) | Precio (ARS aprox.) | Límite Stock |
| :--- | :--- | :--- | :--- | :--- |
| **Básico** | `basic` | **$97** | $116.400* | 500 Equipos |
| **Pro** | `pro` | **$147** | $176.400* | **Ilimitado** |
| **Enterprise** | `enterprise` | *Personalizado* | *Personalizado* | Ilimitado |

> \* *Calculado a una tasa de $1200 por dólar (Referencial).*

---

## 🔑 2. Accesos por Plan (Características)

La lógica de visibilidad de módulos está definida en el modelo `Organization.php`.

### 🟢 Plan Básico (`basic`)
Módulos esenciales para una tienda pequeña:
*   ✅ **Inventario Básico** (Hasta 500 items).
*   ✅ **Ventas** (Facturación y comprobantes).
*   ✅ **Clientes** (Base de datos de compradores).
*   ✅ **Reportes Básicos** (Ventas diarias y mensuales).

### 🔵 Plan Pro / Enterprise (`pro`)
Incluye todo lo anterior más herramientas de gestión avanzada:
*   🚀 **Stock Ilimitado**.
*   🛠️ **Servicio Técnico** (Módulo de reparaciones).
*   👨‍🔧 **Gestión de Técnicos** (Asignación de trabajos).
*   🔄 **Canjes / Trade-Ins** (Toma de usados).
*   📊 **Reportes Avanzados** (Métricas de rentabilidad y técnicos).
*   📍 **Multi-Sucursal** (Gestión de múltiples locales).

---

## 👥 3. Permisos por Rol de Usuario

Los roles definen qué acciones puede realizar un empleado dentro de su organización.

| Rol | Dashboard | Inventario | Ventas | Reparaciones | Config. Negocio |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Owner** | Full | Full | Full | Full | ✅ Sí |
| **Manager** | Full | Full | Full | Full | ❌ No |
| **Seller** | Limitado | Ver/Vender | ✅ Sí | Ver | ❌ No |
| **Technician** | No | Ver | ❌ No | ✅ Sí (Solo técnico) | ❌ No |

---

## 🛡️ 4. Niveles de Administración Especial

### **Super Admin (Master)**
*   **ID en DB:** `role = 'super_admin'`
*   **Organization ID:** `NULL` (No pertenece a ningún negocio).
*   **Acceso:** Panel Maestro (`/admin`).
*   **Poderes:**
    *   Ver estadísticas globales de todos los negocios.
    *   Activar/Desactivar acceso a cualquier organización.
    *   Cambiar planes de negocios manualmente.
    *   Acceso total a la base de datos sin filtros.

---

## 📝 Notas de Implementación
*   **Trial:** Los nuevos negocios tienen un periodo de prueba donde **todos los módulos están abiertos**.
*   **Vencimiento:** Si el plan expira o se cancela, el sistema bloquea el acceso a todas las funciones premium automáticamente mediante el middleware `CheckSubscription`.
*   **Seguridad:** Toda la información está segregada mediante `OrganizationScopes`, asegurando que un negocio nunca vea los datos de otro.

---
*Última actualización: Marzo 2026*
