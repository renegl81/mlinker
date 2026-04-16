# MenuLinker Core — Análisis del proyecto

> Documento de referencia para los agentes de IA que trabajen en este repo.
> Fecha del análisis: **2026-04-08**.

---

## 1. Qué es MenuLinker

Plataforma SaaS multi-tenant para hostelería (cafeterías, restaurantes, bares).

**Flujo de negocio:**
1. Un negocio se registra como **tenant** y crea sus **ubicaciones** (locations).
2. Para cada ubicación define **menús** digitales con secciones, productos, precios, alérgenos, ingredientes y calorías.
3. La plataforma genera **códigos QR** que los clientes finales escanean desde el móvil para visualizar el menú público.
4. El negocio paga una **suscripción** (Plans + Stripe vía Cashier).

**Repo:** `/home/renegl/Proyectos/MenuLinker/menulinker-core`  
**Branch principal:** `main` (trabajo en `develop`)

---

## 2. Stack técnico

### Backend
- **Laravel 13.0** / PHP **^8.4**
- **PostgreSQL** (postgres:18-alpine, puerto host 5433)
- **Stancl Tenancy 3.9/4.0** — multi-tenancy por dominio, single database
- **Laravel Fortify 1.36** — auth + 2FA TOTP + email verification
- **Laravel Cashier 16** — Stripe billing
- **Inertia.js 2.0** + **Laravel Wayfinder 0.1** + **Ziggy 2.6**
- **Pest 4.4** + **Laravel Pint** + **Laravel Boost 2.4** + **Pail**

### Frontend
- **Vue 3.5** + **TypeScript** estricto
- **Vite 7** con **SSR** habilitado
- **Tailwind CSS 4** (`@tailwindcss/vite`)
- **Radix Vue + Reka UI** (componentes shadcn-style en `resources/js/components/ui/`)
- **Lucide Vue Next** (iconos)

### Infraestructura local (Docker / Sail)
Servicios definidos en `compose.yaml`:
- `flowsuite-php` (Sail PHP 8.5, puerto 8086:80)
- `flowsuite-pgsql` (postgres:18-alpine, puerto 5433:5432)
- `flowsuite-mailpit` (1025 SMTP, 8025 dashboard)
- Red `flowsuite` con `extra_hosts` apuntando `flowsuite.com` y `*.flowsuite.com` a host-gateway (necesario para resolución de subdominios de tenants en local)

> **Importante:** todos los comandos `artisan`, `composer`, `npm`, `pest` deben ejecutarse dentro del contenedor:
> ```bash
> ./vendor/bin/sail up -d
> ./vendor/bin/sail artisan <cmd>
> ./vendor/bin/sail composer <cmd>
> ./vendor/bin/sail npm <cmd>
> docker compose exec laravel.test php artisan <cmd>   # alternativa
> ```

---

## 3. Modelo de dominio

18 modelos en `app/Models/`. Relaciones principales:

```
User ──┬─ belongsToMany ──> Tenant     (pivot tenant_user: role, permissions, is_active, invited_at, joined_at)
       └─ hasMany ─────── > Location

Tenant ─ hasOne ────────> Subscription
       └ belongsToMany ── User

Location ─ belongsTo ── User
         ├ belongsTo ── Country
         ├ hasMany ─── Menu
         ├ hasMany ─── OpeningHour
         └ belongsToMany ── Category

Menu ─ belongsTo ── Location
     ├ belongsTo ── Template
     ├ hasMany ──── Section
     ├ hasOne ───── QRCode
     └ belongsToMany ── Product

Section ─ belongsTo ── Menu
        └ belongsToMany ── Product

Product ─ belongsToMany ── Menu
        ├ belongsToMany ── Section
        ├ belongsToMany ── Allergen
        └ belongsToMany ── Ingredient

QRCode ─ belongsTo ── Menu  (campo: menu_id)

Plan ─ hasMany ──── Subscription
Subscription ─ belongsTo Tenant, Plan; hasMany Payment
```

Modelos tenant-scoped usan el trait **`Stancl\Tenancy\Database\Concerns\BelongsToTenant`** y su tabla incluye una columna `tenant_id` con FK a `tenants.id`.

---

## 4. Estructura de carpetas backend (convenciones)

```
app/
├── Actions/                    ← lógica de negocio (capa de servicios)
│   ├── Location/{Create,Update,Delete,GetLocations}.php
│   ├── Menu/{Create,Update,Delete,GetMenus}.php
│   ├── QrCode/GenerateQrCode.php
│   └── User/{Create,Update,Get…}.php
├── Http/
│   ├── Controllers/
│   │   ├── Admin/Core/         ← admin global (root)
│   │   ├── Admin/Tenant/       ← admin del tenant (panel/)
│   │   ├── Admin/Menu/         ← QR, productos, ingredientes, alérgenos
│   │   ├── Auth/               ← Fortify-style
│   │   ├── Settings/           ← perfil, password, 2FA
│   │   └── Tenant/             ← rutas públicas del tenant (Home, MenuController público)
│   ├── Requests/{Store,Update}*Request.php
│   └── Middleware/InitializeTenancyByDomainOptional.php
├── Models/
└── Support/ImageHelper.php     ← helper de subida base64

routes/
├── web.php       ← admin global (/admin/...)
├── tenant.php    ← rutas por dominio del tenant (/, /menu/{menu}, /panel/...)
├── auth.php
├── settings.php
└── api.php       ← VACÍO (pendiente)

database/
├── migrations/   ← schema central
├── migrations/tenant/  ← schema por tenant (si aplica)
└── factories/
```

**Capas y patrón:**
- **Controllers** delegan a **Actions** (`app/Actions/<Domain>/`).
- **Validación** y **autorización** en **FormRequests**.
- **Inertia pages** en `resources/js/pages/` mirroring la estructura de rutas.
- **UI components** shadcn-style en `resources/js/components/ui/`. Layouts en `resources/js/layouts/` (`AppLayout` admin, `FrontLayout` público).

---

## 5. Estado del proyecto (snapshot 2026-04-08)

### ✅ Implementado
- **Auth completo:** registro, login, verificación de email, 2FA TOTP, reset password, activación por enlace firmado
- **Multi-tenancy** por dominio + invitación de usuarios al tenant
- CRUD admin de **Locations, Menus, Products, Sections, Allergens, Ingredients, Categories, Plans, Templates, Countries, Users**
- Capa **Actions + FormRequests** bien estructurada
- **Settings** (perfil, password, 2FA, dark/light)
- Modelos de **Plan/Subscription/Payment** con Cashier configurado
- **Landing pública** con Hero/Features/Pricing/CTA
- ~44 páginas Vue con layouts y UI base (Radix/Reka)
- **44 archivos Vue, 18 modelos, 33 migraciones, 32 FormRequests**

### 🚧 Parcial / arreglado en esta sesión (2026-04-08)
- **QRCode**: refactorizado `menu_card_id → menu_id`, migración renombrada para correr después de `menus`
- **Tenant/MenuController** público: cargaba sólo `sections`; ahora carga `sections.products.allergens|ingredients`, respeta `is_active`, fallback a template `Basic`
- **Acción real `GenerateQrCode`** (endroid/qr-code v6) generando PNG y guardándolo en `storage/app/public/tenant{id}/qr-codes/`
- **Endpoints admin** `POST/GET/DELETE /panel/menus/{menu}/qr-code[/download]` con UI en `admin/tenant/menus/Show.vue`
- **Migración huérfana `create_menu_cards_table`** eliminada (modelo MenuCard nunca existió)
- **Factories rotas** (MenuCardFactory borrada, MenuFactory + QRCodeFactory arregladas)

### ❌ Faltante (crítico para MVP)
1. **Subida de imágenes** — los campos `image_url` existen pero no hay endpoints/UI completos de upload (solo base64 vía `ImageHelper`)
2. **Flujo Stripe** completo — Cashier instalado pero sin UI de selección de plan / checkout / webhooks / gestión de subscripción
3. **Dashboard con métricas** — vistas de menú, items populares, estado de subscripción
4. **API REST + Sanctum** — `routes/api.php` vacío
5. **Frontend i18n** — `messages.menus.*` se referencia pero no hay setup de Vue I18n / archivos de traducción
6. **Mailables transaccionales** — Mailpit configurado pero sin Mailables (welcome, pagos, invitaciones)
7. **Tests** — solo cubren auth/settings/users; faltan tests de Locations, Menus, Products, multi-tenancy, billing, QR
8. **LocationFactory está rota** (`use App\Models\;` vacío y `'country_id' => ::factory()`) — bloquea tests con factories
9. **Templates seed** — necesita seeder con al menos un Template `Basic` con `component_name='Basic'`
10. **Notificaciones in-app y/o email**

### ⚠️ Inconsistencias técnicas / deuda
- `LocationFactory.php` y otras factories tienen errores de sintaxis del scaffolding original (Blueprint)
- `.blueprint` y `draft.yml` están desactualizados respecto a los modelos actuales — útiles solo como referencia histórica
- Restos de rebranding **MenuFlow → MenuLinker** (container `flowsuite-php`, base de datos `menuflow_db`, network `flowsuite`)
- `boost.json` solo lista `copilot` como agent — Laravel Boost no soporta nativamente Claude pero sus guidelines aplican igualmente a través de `CLAUDE.md`
- `routes/api.php` y `app/Jobs/` están vacíos
- Algunos controllers del scaffolding original referencian Jobs inexistentes (p.ej. `QRCodeController` original llamaba a `App\Jobs\CreateQRCode` que no existe — ya reescrito)

---

## 6. Tests existentes

Cobertura actual (`tests/`):
- `Feature/Auth/*` — 6 archivos: login, 2FA, registro, reset, verificación, rate limiting
- `Feature/Settings/*` — 3 archivos: password, profile, 2FA
- `Feature/Http/Controllers/Admin/Core/UserControllerTest.php`
- `Feature/DashboardTest.php`
- Placeholders: `Feature/ExampleTest.php`, `Unit/ExampleTest.php`

`tests/Pest.php` no usa `RefreshDatabase` por defecto (comentado). `TestCase` solo desactiva Vite. **No hay setup específico para multi-tenancy en tests.**

---

## 7. Configuración Docker — gotchas observadas

- El puerto **5433** del host puede entrar en conflicto con otros proyectos PostgreSQL (p.ej. `cycling_planner-pgsql-1`). Para resolverlo sin tocar otros proyectos: añadir `FORWARD_DB_PORT=5434` al `.env`.
- Los contenedores se llaman `flowsuite-*` (legacy del rebranding). El nombre del proyecto compose es `menulinker-core`.
- Si los contenedores quedan en mal estado tras un conflicto de red/puerto: `docker compose down && docker compose up -d` los recrea limpios.
- La resolución DNS interna (`pgsql` desde `laravel.test`) puede fallar si los contenedores están desincronizados con la network — solución: recrear con `down`/`up`.
