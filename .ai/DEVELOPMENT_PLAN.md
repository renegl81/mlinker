# MenuLinker Core — Plan de desarrollo para agentes

> Este documento es la **fuente de verdad** del orden de trabajo restante para llegar al MVP.
> Lo deben leer todos los agentes (`menulinker-builder` y otros) antes de empezar cualquier tarea no trivial.
> Última actualización: **2026-04-09**.

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

**Estado actual:** casi completa. Solo falta verificar resolución de subdominio en local (1.11).

| # | Tarea | Estado |
|---|---|---|
| 1.1 | Refactor `QRCode.menu_card_id → menu_id` + migración renombrada | ✅ |
| 1.2 | Instalación `endroid/qr-code` v6 | ✅ |
| 1.3 | Action `App\Actions\QrCode\GenerateQrCode` (build PNG, persistencia, prune) | ✅ |
| 1.4 | Endpoints admin: `POST/GET/DELETE /panel/menus/{menu}/qr-code[/download]` | ✅ |
| 1.5 | UI en `admin/tenant/menus/Show.vue` con card de QR (generar/descargar/eliminar) | ✅ |
| 1.6 | `Tenant/MenuController` público carga `sections.products.allergens|ingredients`, respeta `is_active` | ✅ |
| 1.7 | Página `tenant/templates/Basic.vue` (ya existía, bug menor de allergens corregido) | ✅ |
| 1.8 | Seeder con un `Template` `Basic` (`component_name='Basic'`) — ya existía, + fix `component_name` en `$fillable` del modelo y factory | ✅ |
| 1.9 | Arreglar `LocationFactory.php` (import vacío + `Country::factory()` faltante) | ✅ |
| 1.10 | Tests Pest: `PublicMenuTest` (3 tests) + `GenerateQrCodeTest` (7 tests) | ✅ |
| 1.10b | Fix `GenerateQrCode` action para endroid/qr-code v6 API (`Builder::create()` → `new Builder(...)`) | ✅ |
| 1.11 | **Pendiente:** Verificar resolución del subdominio del tenant en local (`*.flowsuite.com → host-gateway`) y ajustar `buildPublicMenuUrl` si hace falta | ❌ |

### ✅ FASE 2 — Modelo freemium + límites por plan (HECHO)

> **Motivación de negocio:** sin un plan gratuito no hay freemium. Sin límites por plan no hay motivo para pagar. Esta fase es prerequisito de todo lo demás porque define qué se regala, qué se cobra y cómo se controla.

| # | Tarea | Estado |
|---|---|---|
| 2.1 | Migración `add_limits_to_plans_table`: 14 columnas (slug, stripe_price_id, max_*, has_*, show_branding, trial_days, sort_order) + migración `make_stripe_id_nullable_in_subscriptions_table` | ✅ |
| 2.2 | Modelo `Plan`: `$fillable`/`$casts` ampliados, `scopeFree()`, `allows(feature)`, `Plan::free()` con cache 60min | ✅ |
| 2.3 | `PlanSeeder` con 4 planes reales (Free/Pro/Business/Enterprise) via `updateOrCreate` por `slug` | ✅ |
| 2.4 | Registro asigna plan Free: `RegisteredUserController::store()` inserta subscription con `stripe_status='free'` | ✅ |
| 2.5 | Middleware `EnsurePlanLimits`: inyecta `tenantPlan` en request, registrado en grupo auth de `tenant.php` | ✅ |
| 2.6 | Action `Plan/CheckLimit` + `PlanLimitExceededException` (HTTP 403, mensajes en español, `max=0` = ilimitado) | ✅ |
| 2.7 | `LocationController::store()`, `MenuController::store()`, `ProductController::store()` integran `CheckLimit` | ✅ |
| 2.8 | Componente `PlanLimitBanner.vue` (banner rojo/amarillo según uso, CTA "Mejorar plan") | ✅ |
| 2.9 | `PricingSection.vue` actualizado: 4 planes, Free primero con "Empezar gratis", Enterprise con "Contactar" | ✅ |
| 2.10 | `PlanLimitTest.php`: 9 tests (limits, unlimited, throw, HTTP Free/Pro, registro crea subscription) | ✅ |

---

### ✅ FASE 3 — Menú público: crecimiento orgánico + SEO + compartir (HECHO)

> **Motivación de negocio:** cada menú público visto por un cliente final es un anuncio. El footer "Powered by MenuLinker" es el motor de crecimiento #1. Los meta tags SEO hacen que cada menú sea indexable por Google. El botón de compartir multiplica la distribución sin coste.

| # | Tarea | Estado |
|---|---|---|
| 3.1 | Footer "Powered by MenuLinker" condicional en `Basic.vue` (visible si `showBranding=true`) | ✅ |
| 3.2 | `Tenant/MenuController` pasa `showBranding`, `meta`, `jsonLd`, `shareUrl`, `tenantSlug` a Inertia | ✅ |
| 3.3 | Meta tags SEO (OG title/description/image/url/type) en `<Head>` de `Basic.vue` | ✅ |
| 3.4 | Schema.org JSON-LD `Restaurant` + `Menu` + `MenuSection` + `MenuItem` | ✅ |
| 3.5 | Componente `ShareMenu.vue` (FAB flotante: WhatsApp, copiar enlace, Twitter, Facebook) | ✅ |
| 3.6 | Ruta corta `/m/{menu}` + `GenerateQrCode::buildPublicMenuUrl()` usa URL corta | ✅ |
| 3.7 | 6 tests nuevos en `PublicMenuTest`: branding free/pro/sin-sub, SEO meta, JSON-LD, short URL | ✅ |

---

### ✅ FASE 4 — Onboarding guiado (time-to-value < 5 min) (HECHO)

> **Motivación de negocio:** un restaurante que se registra necesita su QR en la mano en menos de 5 minutos.

| # | Tarea | Estado |
|---|---|---|
| 4.1 | Migración `add_onboarding_to_tenants_table` (`onboarding_completed_at`, `onboarding_step`) + modelo Tenant actualizado | ✅ |
| 4.2 | Middleware `RedirectToOnboarding` — redirige a wizard si onboarding incompleto, excluye rutas de onboarding/auth | ✅ |
| 4.3 | `OnboardingController` con 5 métodos: show, storeLocation, storeMenu, storeProducts, complete | ✅ |
| 4.4 | `admin/tenant/onboarding/Wizard.vue` — wizard 4 pasos, mobile-first, sin sidebar, con UI components existentes | ✅ |
| 4.5 | Rutas `tenant.onboarding.*` en `tenant.php`, fuera del middleware RedirectToOnboarding | ✅ |
| 4.6 | Auto-crear location al registrarse — OMITIDO (mejor UX guiada paso a paso) | ⏭️ |
| 4.7 | `OnboardingTest.php` — 12 tests: redirect, cada paso, complete genera QR, no-redirect si completo | ✅ |

---

### ✅ FASE 5 — Subida de imágenes (HECHO)

> **Motivación de negocio:** un menú sin fotos de platos es significativamente menos atractivo. En el plan Free se limita a 0 imágenes (incentivo de upgrade). En Pro se permiten hasta 50.

| # | Tarea | Estado |
|---|---|---|
| 5.1 | `ImageUploadController` + `ImageUploadRequest` + ruta `POST panel/uploads/image` con CheckLimit | ✅ |
| 5.2 | Thumbnails 300x300 cover con GD nativo (sin dependencia extra) en `tenant{id}/images/thumbs/` | ✅ |
| 5.3 | Componente `ImageUpload.vue` — drag & drop, preview, spinner, validación client-side, CSRF | ✅ |
| 5.4 | `ImageHelper::resolve()` — discrimina base64 / URL absoluta / path relativo de storage | ✅ |
| 5.5 | Trait `HasImage` con listener `deleting` — borra imagen + thumbnail. Usado en Menu, Product, Location, Section | ✅ |
| 5.6 | `ImageUploadTest.php` — 7 tests: upload válido, Free 403, Pro OK, borrado físico, rechazo no-imagen | ✅ |

---

### ✅ FASE 6 — Flujo Stripe / Suscripciones (HECHO)

> **Motivación de negocio:** sin pagos no hay negocio. Esta fase conecta el modelo freemium con Stripe.

| # | Tarea | Estado |
|---|---|---|
| 6.1 | `docs/stripe-setup.md` — guía de configuración Stripe (productos, precios, env vars, seeder) | ✅ |
| 6.2 | `BillingController` (plans/checkout/success/manage/cancel/resume) + `Plans.vue` con grid comparativo | ✅ |
| 6.3 | Action `StartCheckout` — Cashier checkout session con trial, metadata y success/cancel URLs | ✅ |
| 6.4 | Action `ChangeSubscription` — swap, cancel, resume (busca subscription en BD directamente) | ✅ |
| 6.5 | `StripeWebhookController` extiende Cashier — sincroniza plan_id en updated, degrada a Free en deleted | ✅ |
| 6.6 | `Manage.vue` (estado suscripción, cancel/resume) + `Success.vue` (post-checkout) | ✅ |
| 6.7 | Degradación a Free en webhook `subscription.deleted` — actualiza plan_id, CheckLimit aplica automáticamente | ✅ |
| 6.8 | 4 mailables: SubscriptionStarted, TrialEnding, PaymentFailed, SubscriptionCancelled (clases + vistas) | ✅ |
| 6.9 | `BillingTest.php` — 12 tests: pages, checkout validation, actions, webhook handlers (sin Stripe real) | ✅ |

---

### ✅ FASE 7 — Dashboard con métricas (feature premium) (HECHO)

> **Motivación de negocio:** "¿Quieres saber cuántas personas ven tu carta? Pasa a Pro."

| # | Tarea | Estado |
|---|---|---|
| 7.1 | Migración `create_menu_views_table` + modelo `MenuView` con `BelongsToTenant` | ✅ |
| 7.2 | Tracking en `Tenant/MenuController` — dispatch `TrackMenuView` solo cuando hay tenant activo | ✅ |
| 7.3 | Job `TrackMenuView` — deduplicación 30min por IP+menú, `withoutGlobalScopes()` para insert | ✅ |
| 7.4 | Action `GetTenantOverview` — total_views, views_by_day, top_menus, views_by_source, current_period | ✅ |
| 7.5 | Dashboard Pro: cards métricas + gráfico barras CSS/Tailwind + tabla top menús (sin chart.js) | ✅ |
| 7.6 | Dashboard Free: total visitas real (teaser) + overlay blur + CTA "Ver planes" | ✅ |
| 7.7 | `AnalyticsTest.php` — 7 tests: tracking, deduplicación, IPs distintas, overview, dashboard Pro/Free | ✅ |

---

### ✅ FASE 8 — i18n frontend (feature premium) (HECHO)

> **Motivación de negocio:** muy valorado en zonas turísticas. Feature premium (`has_multilang`).

| # | Tarea | Estado |
|---|---|---|
| 8.1 | vue-i18n 11 instalado + configurado (app.ts + ssr.ts) + locales `es.json`/`en.json` | ✅ |
| 8.2 | Strings comunes (nav, common, plans, billing) en archivos de locale — migración parcial | ✅ |
| 8.3 | `LanguageSelector.vue` en sidebar footer, persiste en localStorage | ✅ |
| 8.4 | Columna `translations` (JSONB) en menus/sections/products + trait `HasTranslations` + `?lang=en` en menú público | ✅ |
| 8.5 | `TranslationController` + `Translations.vue` — UI de traducción por menú (campos EN editables), verificación de plan | ✅ |
| 8.6 | `TranslationTest.php` — 7 tests: ?lang=en, fallback, plan sin multilang, 403 Free/Pro, guardado Business | ✅ |

---

### ✅ FASE 9 — Mailables transaccionales (HECHO)

| # | Tarea | Estado |
|---|---|---|
| 9.1 | `WelcomeMail` integrado en `OnboardingController::complete()` con try/catch | ✅ |
| 9.2 | (Las vistas vendor de mail están personalizadas con branding MenuLinker — cubre el template activation) | ✅ |
| 9.3 | `TenantInvitationMail` con enlace de invitación + rol | ✅ |
| 9.4 | `MenuPublishedMail` + evento `MenuActivated` + listener `SendMenuPublishedMail` | ✅ |
| 9.5 | Vistas `vendor/mail/html/header.blade.php`, `footer.blade.php` y `themes/default.css` con logo + color purple | ✅ |
| 9.6 | `MailableTest.php` — 8 tests: envío onboarding, construcción de mailables, evento MenuActivated | ✅ |

---

### ✅ FASE 10 — API REST + Sanctum (HECHO)

> **Motivación de negocio:** feature Business/Enterprise. Integraciones con TPV, apps propias, Google Business.

| # | Tarea | Estado |
|---|---|---|
| 10.1 | Sanctum instalado, `HasApiTokens` en Tenant, migración ajustada (tokenable_id → varchar para IDs string) + middleware `EnsureApiAccess` + `InitializeTenancyFromSanctum` | ✅ |
| 10.2 | Resources: Menu, Section, Product, Location, Allergen, Ingredient (con `whenLoaded`) | ✅ |
| 10.3 | `ApiMenuController` + `ApiLocationController` con endpoints v1 (index/show/qr-code) | ✅ |
| 10.4 | Endpoint público `public/menus/{id}` con throttle 60/min, sin auth | ✅ |
| 10.5 | Documentación API — OMITIDO para MVP (sin scribe/swagger) | ⏭️ |
| 10.6 | `ApiMenuTest.php` — 9 tests: 401, 403 sin API access, listados, show, QR, público, 404 inactivo | ✅ |

---

### ✅ FASE 11 — Cobertura de tests (HECHO)

| # | Tarea | Estado |
|---|---|---|
| 11.1 | Tests Actions: `LocationActionsTest.php` (9) + `MenuActionsTest.php` (9) | ✅ |
| 11.2 | Tests HTTP CRUD: `LocationControllerTest.php` (11) + `MenuControllerTest.php` (10) | ✅ |
| 11.3 | **Tests de aislamiento** `TenantIsolationTest.php` (9) — locations/menus/sections/products/API tokens/admin dashboard | ✅ |
| 11.4 | `EndToEndTest.php` (6) — registro→onboarding→QR→menú público, límite Free, webhook downgrade | ✅ |
| 11.5 | Todos los tests existentes siguen pasando | ✅ |
| bug | Fixed: `UpdateMenu.php` accedía a `$data['template_id']` sin null-safety | ✅ |

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

## Estado actual (2026-04-10)

**🎉 MVP técnico completado.** Todas las fases del plan (0-11) están implementadas.

- **194 tests pasando, 763 assertions**
- Pint + ESLint limpios
- FASES completas: Auth/Multi-tenancy → Menú público + QR → Freemium + límites → SEO + branding → Onboarding guiado → Imágenes → Stripe → Analytics → i18n → Mailables → API REST → Cobertura de tests

### Tareas pendientes antes del lanzamiento

1. **Tarea 1.11**: Verificar resolución de subdominio del tenant en local (`*.flowsuite.com → host-gateway`) y ajustar `GenerateQrCode::buildPublicMenuUrl` si hace falta
2. **Config Stripe real**: Seguir `docs/stripe-setup.md` para crear productos/precios en Stripe dashboard y actualizar `PlanSeeder` con los `stripe_price_id` reales
3. **Posible ajuste Cashier**: Si Cashier no persiste `stripe_id` en el JSONB del tenant, añadirlo a `getCustomColumns()` de `App\Models\Tenant` (ver nota en `docs/stripe-setup.md`)
4. **Documentación API**: Opcionalmente, instalar `scribe` o escribir `docs/api.md` manual para clientes externos (omitido en FASE 10)
5. **Datos de producción**: seed de Countries, Allergens, Ingredients comunes si no están ya
6. **Domain/DNS en producción**: configurar dominio real + SSL + DNS wildcard para subdominios de tenants
7. **Rebranding**: completar el rebranding MenuFlow → MenuLinker en containers Docker (`flowsuite-*`), database name, network, etc. (tarea de deuda técnica)
8. **Mailables conectados a webhooks**: actualmente los mailables de billing existen pero no se disparan desde los webhooks de Stripe — conectarlos

### Siguientes mejoras (post-MVP)

- UI de gestión de tokens API en settings del tenant
- Tabs de idioma en formularios existentes de Menu/Section/Product (en lugar de página aparte)
- Integración con Google Business Profile (publicar menús automáticamente)
- App móvil o PWA para el panel admin
- Analytics más avanzados (funnel de conversión, heatmap de productos)
- Marketplace de templates visuales premium
- Integración con TPV (Square, Lightspeed)
