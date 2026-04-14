# Onboarding Redesign — Brief para agente UX/UI + Ingeniero

> Este documento es el plan maestro para rediseñar el onboarding del tenant.
> Persiste entre sesiones: si el trabajo se interrumpe, retomar desde la sección **Estado actual / Checklist**.
> Antes de editar código, leer primero todo este documento y después el estado vigente de los archivos citados (pueden haber cambiado).

---

## 1. Objetivo

Rediseñar el wizard de onboarding (`/panel/onboarding`) para que sea:
- **Más corto**: eliminar preguntas que ya conocemos o que no aportan valor en el primer minuto.
- **Más visual**: primero plantilla (con preview), luego sección (con tarjetas), luego productos.
- **Más conversacional**: textos guía amigables en cada paso, enfatizando que "esto es solo un comienzo, luego puedes personalizar todo".
- **Más intuitivo**: todo seleccionable a golpe de click (tarjetas grandes, sin forms largos).

El objetivo de conversión: que el usuario vea su primer menú publicado en < 2 minutos desde que entra al panel.

---

## 2. Contexto técnico (NO confiar ciegamente — verificar antes de editar)

Archivos clave actuales:
- **Backend**: `app/Http/Controllers/Admin/Tenant/OnboardingController.php`
- **Frontend**: `resources/js/pages/admin/tenant/onboarding/Wizard.vue` (~599 líneas, monolítico)
- **Tests**: `tests/Feature/Tenant/OnboardingTest.php`
- **Registro** (origen del `tenant_name`): `app/Http/Requests/Auth/RegisterRequest.php`
- **Modelo Template** (plantillas con `component_name`, `preview_image_url`): `app/Models/Template.php`
- **Plantillas disponibles** (seeder): `database/seeders/TemplateSeeder.php` — 8 plantillas: Basic, Modern, Minimalist, Trattoria, Neon, Botanica, Riviera, Chapter.

El `tenant_name` capturado en registro se usa actualmente como id del tenant (slug) pero NO se guarda en una columna "nombre del local" aparte. Validar si debe persistirse como `tenant.name` o si se usa directamente el id. Si no existe columna apropiada, añadirla vía migración o usar `tenant()->id` capitalizado como nombre mostrado.

---

## 3. Flujo actual (a reemplazar)

Pasos actuales del wizard según `OnboardingController`:
0. **Website** — pregunta si quiere web personalizada + `business_type` (restaurant/cafe/bar/...).
1. **Location** — pide nombre, dirección, ciudad, teléfono del local.
2. **Menu** — pide nombre, descripción, idioma del menú (asigna plantilla Basic por defecto).
3. **Products** — form libre para añadir productos con `section_name` manual.
4. **Complete** — genera QR, envía WelcomeMail, redirige a la location.

Problemas:
- Paso 0 es fricción pura (business_type y website no se necesitan para empezar).
- Paso 1 pide nombre del local: redundante con `tenant_name` del registro.
- Paso 2 no deja escoger plantilla visualmente.
- Paso 3 es un form seco, sin guía por sección.
- Textos neutros, sin tono cercano.

---

## 4. Flujo propuesto (nuevo)

Total: **3 pasos visuales + confetti final**. Nunca más de 1 decisión por pantalla.

### Paso 1 — Confirmar local (mínimo)
- Título: **"¡Hola, {nombre_usuario}! Vamos a dar vida a {tenant_name} 🎉"**
- Subtítulo: *"Solo necesitamos unos datos mínimos. Todo lo demás lo podrás personalizar después desde tu panel."*
- Prefill automático del nombre del local desde `tenant_name`.
- Campos opcionales (no bloqueantes): ciudad y teléfono. Pueden omitirse.
- Eliminar: business_type, has_website, dirección/província/CP (se pedirán luego en el panel si el usuario entra a Settings).
- CTA: **"Continuar"**. Atajo **"Saltar este paso"** (usa solo el nombre).

### Paso 2 — Elige tu plantilla
- Título: **"Ahora escoge tu plantilla para que tu menú luzca increíble ✨"**
- Subtítulo: *"No te preocupes, podrás cambiarla cuando quieras."*
- Grid de tarjetas (2 col móvil, 3-4 desktop) con las 8 plantillas:
  - Cada card: preview image grande, nombre de la plantilla, chip con tags (ej. "Clásico", "Oscuro", "Italiano").
  - Click en card = selección inmediata con borde teal + check animado.
  - Hover desktop: escala ligera + overlay "Previsualizar" que abre modal con screenshot a pantalla completa.
- CTA: **"Usar esta plantilla"** (deshabilitado hasta seleccionar una).
- Si faltan `preview_image_url` en BD, el agente debe generar placeholders o añadir imágenes reales (ver `public/images/templates/`).

### Paso 3 — Crea tu primera sección
- Título: **"Tu menú tiene secciones. Empecemos por crear una 🍽️"**
- Subtítulo: *"Escoge el tipo de sección que quieres empezar. Luego podrás añadir todas las que necesites."*
- Grid de presets de sección como tarjetas clickables con icono + nombre:
  - Entrantes 🥗 · Primeros 🍝 · Segundos 🥩 · Postres 🍰 · Bebidas 🥤 · Vinos 🍷 · Cafés ☕ · Menú del día 📋
  - Plus un botón **"Otra"** que abre un input de texto.
- Selección por click → transición al Paso 4 (mismo step lógico, subpantalla).

### Paso 3b — Añade productos a "{sección}"
- Título: **"Ahora agreguemos productos a {sección} 🍴"**
- Subtítulo: *"Añade al menos 1 producto para empezar. Luego puedes añadir imágenes, ingredientes, alérgenos y más desde el editor."*
- Lista de filas inline compactas: nombre + precio. Botón **"+ Añadir otro"** para duplicar fila.
- Botón secundario **"+ Añadir otra sección"** que vuelve a 3 (permite iterar).
- CTA primario: **"Publicar mi menú"**.

### Final — Éxito
- Pantalla de celebración (confetti opcional, icono check grande).
- Título: **"¡Tu menú está listo! 🎊"**
- Muestra QR + URL pública + 3 CTAs:
  1. **"Ver mi menú público"** → abrir URL en nueva pestaña.
  2. **"Descargar QR"**.
  3. **"Ir al panel para personalizar"**.
- Subtexto: *"Esto es solo el principio: desde tu panel puedes añadir imágenes, traducciones, más secciones, gestionar tu equipo y mucho más."*

---

## 5. Requisitos de UX transversales

- **Barra de progreso** discreta arriba: 1 · 2 · 3.
- **Botón "Atrás"** siempre visible (excepto en éxito).
- **Autosave** del paso actual en el backend tras cada submit (`onboarding_step` ya existe).
- **Responsive**: móvil-first. Cards en 1 columna bajo `sm`.
- **Accesibilidad**: cada tarjeta es un `<button>` o `<a>` con `aria-pressed`, focus ring visible.
- **Idioma**: todos los textos via `vue-i18n` (claves bajo `onboarding.*`). Añadir claves a los 5 locales (es, en, ca, gl, eu).
- **Diseño**: respetar design system memoria (`design_system.md`) — teal, Inter, rounded-lg, sin purple/pink.

---

## 6. Requisitos técnicos

### Backend
- Refactor de `OnboardingController`:
  - `storeWebsite` → **eliminar** (y su ruta). Paso 0 desaparece.
  - `storeLocation` → renombrar a `storeBasics`; aceptar solo `city` y `phone` opcionales. El nombre del local se toma de `tenant_name` (verificar dónde vive ese valor: posiblemente `tenant()->id` o una columna a añadir).
  - `storeMenu` → recibir `template_id` obligatorio; el nombre del menú se autogenera (`"Menú principal"` o nombre del tenant).
  - `storeProducts` → sin cambios de contrato salvo aceptar múltiples llamadas (una por sección).
  - Nueva acción `storeTemplate(tenant, template_id)` si se separa visualmente del menú.
- Rutas en `routes/tenant.php` (verificar nombre del archivo real) — actualizar paths.
- Mantener backwards-compat para tenants con `onboarding_step` intermedio al momento del deploy: mapear steps antiguos a los nuevos o forzar reinicio.

### Frontend
- **Partir** `Wizard.vue` (599 líneas) en componentes por paso:
  - `Wizard.vue` (shell + progreso + routing entre pasos)
  - `steps/StepBasics.vue`
  - `steps/StepTemplate.vue`
  - `steps/StepSectionPicker.vue`
  - `steps/StepProducts.vue`
  - `steps/StepSuccess.vue`
  - `components/TemplateCard.vue`, `components/SectionCard.vue`
- Reutilizar `Card`, `Button`, `Dialog` del design system.
- Sin librerías nuevas si pueden evitarse. Confetti: CSS/SVG sencillo si se añade.

### Tests
- Actualizar `tests/Feature/Tenant/OnboardingTest.php` a los nuevos endpoints y contratos.
- Añadir caso: *"un tenant recién creado llega al paso 1 con su nombre prerellenado"*.
- Añadir caso: *"seleccionar template_id inválido da 422"*.
- Mantener verde el resto de la suite.

### i18n
- Añadir claves `onboarding.*` a `lang/{es,en,ca,gl,eu}/messages.php` (o la ubicación real — verificar).

---

## 7. Plan de ejecución (en orden)

1. Leer código actual completo: `OnboardingController.php`, `Wizard.vue`, `routes/tenant.php`, `OnboardingTest.php`, `Template.php`, `TemplateSeeder.php`.
2. Confirmar dónde vive `tenant_name` post-registro y cómo recuperarlo.
3. Diseñar las claves i18n y añadirlas a los 5 locales (mínimo `es` + `en`, el resto puede quedar en inglés temporalmente si el agente lo decide documentándolo).
4. Backend: refactor del controller + rutas + tests.
5. Frontend: descomponer `Wizard.vue` en sub-componentes por paso.
6. Implementar cada paso en orden: Basics → Template → Section → Products → Success.
7. Verificar plantillas tienen `preview_image_url` y si no, indicar al usuario qué assets faltan (no bloquear la feature).
8. Ejecutar `php artisan test` y corregir hasta verde.
9. Probar flujo end-to-end manualmente vía Sail.
10. Actualizar el checklist de este archivo al final de cada hito.

---

## 8. Restricciones

- **No** introducir dependencias nuevas sin justificación.
- **No** tocar el flujo de registro / login.
- **No** añadir lógica de pago ni features fuera de scope (nada de Stripe aquí).
- **No** borrar tests — actualizarlos.
- **Sí** respetar convenciones del proyecto: Actions + FormRequests + Inertia pages (ver `memory/conventions.md` si está disponible, o seguir el estilo del resto del repo).
- **Sí** usar nombres de componentes consistentes con el resto del panel (`StepXxx.vue`).

---

## 9. Estado actual / Checklist

Marcar con `[x]` al completar. Si el trabajo se interrumpe, continuar desde el primer `[ ]`.

- [x] Leída la estructura actual de Wizard.vue y OnboardingController
- [x] Confirmada ubicación del "nombre del local" (tenant_name → columna `name` en tabla `tenants`)
- [x] Claves i18n `onboarding.*` añadidas en `es` + `en` + `ca` + `gl` + `eu`
- [x] Backend: `storeWebsite` eliminado + rutas actualizadas (nueva ruta `basics`)
- [x] Backend: `storeBasics` creado (solo city + phone opcionales)
- [x] Backend: `storeMenu` acepta `template_id` obligatorio
- [x] Frontend: `Wizard.vue` partido en sub-componentes (StepBasics, StepTemplate, StepSectionPicker, StepProducts, StepSuccess, TemplateCard, SectionCard)
- [x] Paso 1 Basics implementado con prefill del tenant_name
- [x] Paso 2 Template con grid visual + modal preview
- [x] Paso 3 SectionPicker con 8+ presets clickables
- [x] Paso 3b Products con filas inline y "Añadir otra sección"
- [x] Pantalla Success con CTA "Ir al panel" + trigger para complete
- [x] Tests actualizados y verdes (16/16 Onboarding + E2E)
- [ ] Prueba manual end-to-end OK
- [x] Resumen de cambios reportado al usuario

---

## 10. Notas de reporte final

Al terminar, el agente debe reportar:
- Lista de archivos creados / modificados / eliminados.
- Tests ejecutados y resultado (`php artisan test --filter=Onboarding`).
- Decisiones tomadas sobre puntos ambiguos (ej. dónde persiste el nombre del local).
- Assets faltantes (ej. previews de plantillas) que el usuario debe aportar.
- Screenshots o indicaciones de cómo probar manualmente.
