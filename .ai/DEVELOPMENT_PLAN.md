# MenuLinker Core — Plan de desarrollo para agentes

> Este documento es la **fuente de verdad** del orden de trabajo restante para llegar al MVP.
> Lo deben leer todos los agentes (`menulinker-builder` y otros) antes de empezar cualquier tarea no trivial.
> Última actualización: **2026-04-08**.

---

## Contexto rápido

- Documento hermano: **`.ai/PROJECT_ANALYSIS.md`** (estado del proyecto, stack, dominio).
- Agente principal: **`.claude/agents/menulinker-builder.md`** — invocarlo para cualquier feature/refactor/bugfix en este repo.
- La app corre **dockerizada** con Sail. **NUNCA** ejecutar `php`, `composer`, `npm`, `pest` directamente en el host.

---

## Principios de trabajo

1. **Siempre leer antes de editar.** Nada de modificar a ciegas.
2. **Cambios pequeños y enfocados.** Una feature a la vez. No mezclar refactors no relacionados.
3. **Tests al lado del código.** Cada feature nueva debe llevar al menos un Pest test que la cubra.
4. **Pint + ESLint** antes de marcar una tarea como completada (`./vendor/bin/sail composer pint && ./vendor/bin/sail npm run lint`).
5. **Actions + FormRequests + Inertia pages.** Respetar la arquitectura existente.
6. **Confirmar acciones destructivas** (drops, force-push, borrar archivos no creados por el agente).
7. **Preguntar** sólo cuando una decisión sea genuinamente ambigua. Si no, decidir y explicar brevemente.
8. **Spanish-first** en la comunicación con el usuario y en mensajes de UI; código y nombres de variables en inglés.

---

## Hoja de ruta hacia MVP (orden recomendado)

### ✅ FASE 0 — Base (HECHO)
- Auth completo, multi-tenancy, CRUD admin de entidades core, layouts y UI base.

### 🟡 FASE 1 — Vista pública del menú + QR (EN CURSO)

**Estado actual:** lo crítico está implementado. Falta tests + seeders.

| # | Tarea | Estado |
|---|---|---|
| 1.1 | Refactor `QRCode.menu_card_id → menu_id` + migración renombrada | ✅ |
| 1.2 | Instalación `endroid/qr-code` v6 | ✅ |
| 1.3 | Action `App\Actions\QrCode\GenerateQrCode` (build PNG, persistencia, prune) | ✅ |
| 1.4 | Endpoints admin: `POST/GET/DELETE /panel/menus/{menu}/qr-code[/download]` | ✅ |
| 1.5 | UI en `admin/tenant/menus/Show.vue` con card de QR (generar/descargar/eliminar) | ✅ |
| 1.6 | `Tenant/MenuController` público carga `sections.products.allergens|ingredients`, respeta `is_active` | ✅ |
| 1.7 | Página `tenant/templates/Basic.vue` (ya existía, bug menor de allergens corregido) | ✅ |
| 1.8 | **Pendiente:** Seeder con un `Template` `Basic` (`component_name='Basic'`) listo para que cualquier menú funcione | ❌ |
| 1.9 | **Pendiente:** Arreglar `LocationFactory.php` (sintaxis rota del scaffolding) | ❌ |
| 1.10 | **Pendiente:** Tests Pest del flujo público + generación QR | ❌ |
| 1.11 | **Pendiente:** Verificar resolución del subdominio del tenant en local (`*.flowsuite.com → host-gateway`) y ajustar `buildPublicMenuUrl` si hace falta | ❌ |

### 🔜 FASE 2 — Subida de imágenes
Los campos `image_url` existen en Menu/Product/Location/Section pero solo se aceptan strings base64 vía `App\Support\ImageHelper`. Hace falta:

- Endpoint `POST /panel/uploads/image` que valide y persista (max size, mime, dimensiones)
- Componente Vue `ImageUpload.vue` reutilizable con preview, drag&drop, eliminar
- Refactor de `ImageHelper` para soportar tanto upload directo como base64
- Generación de **thumbnails** (intervention/image)
- Borrado físico al eliminar el modelo (eventos de Eloquent)

### 🔜 FASE 3 — Flujo Stripe / Subscripciones
Cashier ya está instalado y los modelos `Plan/Subscription/Payment` existen. Falta:

1. Página `tenant/billing/Plans.vue` — selección de plan tras registro / desde panel
2. Action `Subscription/StartSubscription` — crea checkout session de Stripe
3. Webhook handler para `customer.subscription.*`, `invoice.*` (Cashier ya provee `WebhookController`, sólo añadir métodos custom si hace falta)
4. Página `tenant/billing/Manage.vue` — upgrade, downgrade, cancelar, ver facturas
5. Middleware `EnsureActiveSubscription` para bloquear features premium
6. Mailables: `SubscriptionStarted`, `PaymentFailed`, `SubscriptionCancelled`
7. Tests de integración con Stripe en modo test

### 🔜 FASE 4 — Dashboard con métricas
- Action `Analytics/GetTenantOverview` (vistas de menú, items más mostrados, ratio de QR escaneados, suscripción)
- Tracking de visitas en `Tenant/MenuController` (incremento async vía queue)
- Tabla nueva `menu_views` (`menu_id, viewed_at, ip, user_agent`)
- Página `admin/tenant/Dashboard.vue` con cards y gráficos (chart library: `chartjs-vue` o similar)
- Filtros por rango de fechas

### 🔜 FASE 5 — API REST + Sanctum
- Instalar `laravel/sanctum`
- Definir endpoints `/api/v1/menus/{id}`, `/api/v1/locations/{id}`, `/api/v1/menus/{id}/qr-code` (público con cache)
- Resources de Eloquent (`MenuResource`, `ProductResource`, …)
- Versionado por prefix
- Documentación con `scribe` o `l5-swagger`
- Rate limiting + caching de menús públicos

### 🔜 FASE 6 — i18n frontend
- Instalar `vue-i18n`
- Crear `resources/lang/{es,en}/messages.json`
- Sustituir el patrón actual `messages.menus.*` (que asume props globales) por `t('menus.singular')`
- Selector de idioma en header con persistencia en localStorage
- Pasar el idioma del tenant (`location.languages`) al frontend

### 🔜 FASE 7 — Mailables transaccionales
- `WelcomeMail` (post-registro)
- `AccountActivationMail` (ya existe el flow, falta el template)
- `TenantInvitationMail`
- `PaymentReceiptMail` / `PaymentFailedMail`
- `MenuPublishedMail` (cuando un menú pasa de inactivo a activo)
- Templates con MJML o componentes de Mail de Laravel

### 🔜 FASE 8 — Cobertura de tests
**Mínimo aceptable para MVP:**
- Tests de cada Action principal
- Tests HTTP de endpoints CRUD admin
- Tests de aislamiento entre tenants (un tenant no puede ver/editar datos de otro)
- Tests del flujo de suscripción (con Stripe en test mode)
- Tests del flujo de QR + visualización pública del menú
- Mantener auth/settings tests existentes

---

## Cómo trabajar en cada fase

### Patrón estándar para añadir una feature CRUD

1. Crear migración (si hay nueva tabla/columna): `./vendor/bin/sail artisan make:migration ...`
2. Actualizar el modelo (`fillable`, `casts`, relaciones, traits)
3. Crear/actualizar la **Action** en `app/Actions/<Domain>/`
4. Crear los **FormRequests** en `app/Http/Requests/`
5. Crear/actualizar el **Controller** delegando a la Action
6. Registrar la **ruta** en el archivo correcto (`web.php`, `tenant.php`, etc.)
7. Crear/actualizar la **página Vue** en `resources/js/pages/...`
8. Si hay datos al frontend: actualizar `resources/js/types/index.d.ts`
9. Escribir el **test Pest** en `tests/Feature/...`
10. Ejecutar `pint` y `lint`
11. Marcar la tarea como completada

### Sail / Docker — comandos frecuentes

```bash
# Levantar / parar
./vendor/bin/sail up -d
./vendor/bin/sail down

# Comandos
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan tinker
./vendor/bin/sail composer require <pkg>
./vendor/bin/sail npm install
./vendor/bin/sail pest --filter=MenuPublicTest
./vendor/bin/sail composer pint

# Logs
./vendor/bin/sail logs -f laravel.test
docker compose logs -f flowsuite-pgsql
```

### Resolución de gotchas conocidos

- **Conflicto de puerto 5433** → añadir `FORWARD_DB_PORT=5434` (u otro libre) en `.env` y `sail up -d`.
- **DNS interno fallando** entre `laravel.test` y `pgsql` → `docker compose down && docker compose up -d`.
- **`composer require` se queja del lock** → asegurarse de correrlo SIEMPRE dentro de Sail (PHP 8.5 del contenedor, no del host).
- **Mailpit** dashboard accesible en http://localhost:8025.

---

## Definición de "completado" para cada tarea

Una tarea se considera **completada** cuando:

- ✅ El código está escrito siguiendo las convenciones (Actions + Requests + Inertia)
- ✅ `./vendor/bin/sail composer pint` no marca cambios
- ✅ `./vendor/bin/sail npm run lint` pasa sin errores
- ✅ Existe al menos un test Pest cubriendo el happy path (y los edge cases obvios)
- ✅ `./vendor/bin/sail pest --filter=<nombre>` pasa
- ✅ La feature funciona manualmente en el navegador (cuando es UI)
- ✅ El task del task tracker está marcado como `completed`

---

## Próximo paso inmediato (continuación de la sesión 2026-04-08)

**Antes de empezar nuevas features**, terminar la FASE 1:

1. Crear `database/seeders/TemplateSeeder.php` con un Template `Basic` (`component_name='Basic'`)
2. Reparar `database/factories/LocationFactory.php` (sintaxis rota: línea 7 `use App\Models\;` y línea 34 `'country_id' => ::factory()`)
3. Crear `tests/Feature/Tenant/PublicMenuTest.php` cubriendo:
   - Menú activo se renderiza con sus secciones y productos
   - Menú inactivo devuelve 404
4. Crear `tests/Feature/QrCode/GenerateQrCodeTest.php` cubriendo:
   - Generación crea/sobrescribe el QRCode con `image_url` válido
   - Endpoint admin `POST /panel/menus/{menu}/qr-code` requiere auth
   - Endpoint `download` devuelve el PNG
5. Verificar que la URL del QR en local funciona con `*.flowsuite.com` → ajustar `GenerateQrCode::buildPublicMenuUrl` si hace falta usar el dominio real del tenant

Después seguir con la **FASE 2 (subida de imágenes)**.
