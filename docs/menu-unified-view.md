# Unificación de vistas Detalle + Edición de Menú — Brief para agente UX/UI + Ingeniero

> Plan maestro para fusionar las vistas "Show" y "Edit" de un menú en una única pantalla con edición inline.
> Persiste entre sesiones: si el trabajo se interrumpe, retomar desde la sección **Estado actual / Checklist**.
> Antes de editar código, leer primero todo este documento y después el estado vigente de los archivos citados (pueden haber cambiado).

---

## 1. Objetivo

Eliminar la fricción de saltar entre pantallas distintas para consultar vs. modificar un menú. Queremos **una sola vista** (la actual de detalle) desde la que el usuario edite todo inline:

- Texto editable al hacer click (título del menú, descripción, nombres de sección, nombres de producto, precios, descripciones de producto).
- Switches inline para flags booleanos (`is_active`, `show_prices`, `show_currency`, `show_calories`, etc.).
- Drag & drop de secciones y productos (si ya existe en Show.vue, reforzarlo; si no, añadirlo).
- Botones contextuales mínimos (⋯ por fila) con acciones rápidas: duplicar, eliminar, traducir.
- Guardado automático con debounce + indicador sutil ("Guardado ✓" / "Guardando…").
- Sin cambios de ruta entre "ver" y "editar": la URL canónica es `/panel/menus/{menu}` (o la que exista), y la actual `/edit` deja de tener UI propia (redirige a `show` o se elimina).

Criterio de éxito: un usuario puede cambiar el nombre, añadir una sección, renombrar un producto y cambiar su precio sin cambiar de pantalla y sin abrir ningún modal de formulario extenso.

---

## 2. Contexto técnico (verificar antes de editar — el código es la fuente de verdad)

Archivos actuales (tamaños orientativos, pueden variar):
- **Backend controller**: `app/Http/Controllers/Admin/Tenant/MenuController.php`
  - Métodos `show(Menu $menu)`, `edit(Menu $menu)`, `update(MenuUpdateRequest, Menu, UpdateMenu)`, `create`, `store`.
- **FormRequests**: `app/Http/Requests/Menu/MenuUpdateRequest.php` (verificar ruta real).
- **Action**: `app/Actions/Menu/UpdateMenu.php`.
- **Frontend**:
  - `resources/js/pages/admin/tenant/menus/Show.vue` (~1284 líneas) — vista de detalle actual, ya rica.
  - `resources/js/pages/admin/tenant/menus/Edit.vue` (~112 líneas) — wrapper que renderiza `Form.vue`.
  - `resources/js/pages/admin/tenant/menus/Form.vue` (~514 líneas) — formulario de creación/edición.
  - `resources/js/pages/admin/tenant/menus/Create.vue` — mantiene Form.vue para crear (no tocar el flujo de creación en este scope).
  - `resources/js/pages/admin/tenant/menus/Customize.vue`, `Translations.vue`, `Filters.vue` — subvistas dedicadas (quedan fuera de scope, siguen accesibles por tabs o botones).
- **Rutas**: probablemente `routes/tenant.php` o similar. Verificar los nombres `tenant.menus.show`, `tenant.menus.edit`, `tenant.menus.update`.

Relaciones de modelo relevantes: `Menu hasMany Sections`, `Section belongsToMany Products`, `Menu belongs to Location/Template`.

---

## 3. Estado actual (a reemplazar)

- `Show.vue` muestra el menú con sus secciones y productos, probablemente con drag & drop y acciones puntuales.
- `Edit.vue` / `Form.vue` presenta un formulario clásico con campos de nombre, descripción, plantilla, idioma, flags de visualización, etc. Es donde viven los toggles.
- El usuario debe ir a `/edit` para cambiar un toggle o renombrar el menú y luego volver a `/show` para ver la estructura.
- Hay duplicación de lógica: ambos pages cargan el menú, ambos tienen subtítulos/headers parecidos.

Problemas:
- Fricción de contexto: renombrar implica navegar, escribir, guardar y volver.
- Los campos booleanos viven sólo en Edit, invisibles desde Show.
- Los cambios no se reflejan en la previsualización estructural hasta volver a `Show`.

---

## 4. Flujo propuesto (nuevo)

### 4.1 Ruta y navegación
- `tenant.menus.show` sigue existiendo y es la pantalla única.
- `tenant.menus.edit` redirige a `show` (o se elimina junto con `Edit.vue` si ninguna otra cosa la usa).
- Los tabs o botones a `Customize`, `Translations`, `Filters`, `Import/Export`, `Public view`, `QR` se mantienen accesibles desde la cabecera de la vista unificada.

### 4.2 Estructura de la pantalla unificada (`Show.vue` renovado)

Tres zonas verticales:

**A. Header del menú (sticky)**
- Breadcrumb pequeño: Location › Menús › {menú}.
- Título editable inline: click → se convierte en `<input>` con autofocus, blur o Enter guarda, Esc cancela.
- Descripción editable inline (una línea con "+ Añadir descripción" si está vacía).
- Badges/chips a la derecha:
  - Switch "Activo" (`is_active`) — toggle inmediato.
  - Indicador de plantilla actual (click → abre Customize en nueva pestaña o drawer).
  - Indicador de idioma principal (click → link a Translations).
- Panel colapsable "Opciones de visualización":
  - Switches inline: `show_prices`, `show_currency`, `show_calories`, otros flags relevantes del modelo.
  - Solo visible al hacer click en un botón "Opciones" o chevron para no abrumar.
- Indicador de guardado flotante en esquina (top-right): estado idle / saving / saved.

**B. Cuerpo — Secciones y productos (scrollable)**
- Lista de secciones en orden con handle de drag. Cada sección es una tarjeta:
  - Nombre de sección editable inline.
  - Descripción editable inline debajo.
  - Switch "Visible" por sección (si existe el flag en el modelo).
  - Menú ⋯ con: Duplicar, Eliminar, Traducir.
  - Lista de productos con drag handle, edición inline de nombre + precio + descripción corta.
  - Botón "+ Añadir producto" al final.
  - Menú ⋯ por producto: Editar detalle completo (abre drawer/dialog), Duplicar, Eliminar.
- Botón "+ Nueva sección" al final de la lista.

**C. Detalle extendido de producto (drawer lateral)**
- Para campos ricos de un producto (imagen, ingredientes, alérgenos, tags, descripciones largas, variantes si existen) se abre un drawer lateral con form completo — no una página aparte.
- Al cerrar drawer, la vista refresca en sitio.

### 4.3 Patrón de edición inline (consistente)
- Componente reutilizable `InlineEditableText` (o similar) con:
  - Prop `modelValue`, `placeholder`, `multiline`, `as` (h1/h2/p…), `disabled`.
  - Estados: visual (render estático) ↔ editing (input/textarea con autofocus).
  - Commit: Enter (singleline), Ctrl+Enter (multiline), blur.
  - Cancel: Esc.
  - Emite `@save(value)`; el padre dispara patch al backend.
- Componente `InlineEditablePrice` con input numérico + currency.
- Componente `InlineSwitch` sobre el switch del design system existente.
- Indicador de estado: un pequeño toast / bubble "Guardado" de 1.5s al confirmar; spinner mientras.

### 4.4 Guardado (estrategia recomendada)
- **Atomic patch endpoints** por entidad: un `PATCH /menus/{menu}` que acepta campos parciales (`name?`, `description?`, `is_active?`, flags…). Idem `PATCH /sections/{section}` y `PATCH /products/{product}`.
- Debounce 600ms para texto, inmediato para switches.
- En caso de error, revertir el valor local y mostrar toast error con texto de la validación.
- Evitar sobrescribir cambios concurrentes: si el backend soporta `updated_at` como version token, enviarlo en el patch (opcional, mencionar como nota).

---

## 5. Requisitos UX transversales

- Diseño respeta design system del proyecto (teal, Inter, rounded-lg, sin purple/pink).
- Cada elemento editable muestra un affordance sutil en hover: borde teal discreto + icono de lápiz a la derecha.
- Touch: en móvil, tap una vez edita. Sin dobles taps.
- Accesibilidad:
  - `role="button"` y `tabindex="0"` sobre los wrappers editables.
  - Enter activa el modo edición cuando está focused.
  - Labels asociadas a switches con `aria-label` si el texto está aparte.
  - Anuncios vía `aria-live="polite"` en el indicador de guardado.
- Respuesta visual inmediata (optimista) antes de recibir el ack del servidor.
- Confirmación en acciones destructivas (eliminar sección, eliminar producto) — reutilizar `ConfirmDialog` existente.
- i18n via `vue-i18n` (claves nuevas bajo `menus.show.*`). Añadir al menos `es` + `en`.

---

## 6. Requisitos técnicos

### Backend
- Revisar `MenuController::update` y su `MenuUpdateRequest`: asegurarse de que acepta payloads **parciales** (usar `sometimes` en reglas). Si no, permitirlo sin romper el form de creación.
- Añadir (o adaptar) endpoints para:
  - `PATCH /sections/{section}` — nombre, descripción, orden, is_visible.
  - `PATCH /products/{product}` — nombre, precio, descripción, flags.
  - Si ya existen endpoints equivalentes, reusarlos.
- Las acciones deben estar en la capa `App\Actions\Menu|Section|Product\*` con sus FormRequests respectivos.
- Verificar políticas / Gates (isolation por tenant) para cada patch.

### Frontend
- Reescribir `Show.vue` incorporando edición inline. Partir en sub-componentes:
  - `Show.vue` (shell + header + save indicator + eventos globales).
  - `MenuHeaderEditable.vue`.
  - `MenuVisibilityOptions.vue`.
  - `SectionCard.vue`.
  - `ProductRow.vue`.
  - `ProductDetailDrawer.vue`.
  - `components/inline/InlineEditableText.vue`, `InlineEditablePrice.vue`, `InlineSwitch.vue`, `SaveIndicator.vue`.
- Borrar `Edit.vue` si ya no se usa (o dejar una redirección en el controller y eliminar el page file).
- Mantener `Form.vue` solo para `Create.vue` (no romper creación).
- Mantener el drag & drop actual si ya existe; si no, añadirlo con la librería ya usada en el repo (verificar qué se usa en `Show.vue` hoy — no introducir deps nuevas).
- Gestión de estado local del menú: un composable `useMenuEditor(menu)` que exponga `patchMenu`, `patchSection`, `patchProduct`, estado `isSaving`, `lastSavedAt`, queue de cambios pendientes.

### Tests
- Actualizar tests que dependan de `MenuController::edit` (si los hay).
- Añadir tests para los PATCH endpoints nuevos/adaptados (validación parcial + policy).
- Mantener verde el resto de la suite (`php artisan test --filter=Menu`).

### i18n
- Claves nuevas `menus.show.*` y `menus.inline.*` en `es` + `en` como mínimo. Resto de locales pueden heredar en inglés temporalmente si se documenta.

---

## 7. Plan de ejecución (en orden)

1. Leer en detalle: `MenuController.php`, `Show.vue`, `Edit.vue`, `Form.vue`, `Customize.vue`, `MenuUpdateRequest.php`, `UpdateMenu.php` action, tests en `tests/Feature/Http/Admin/Tenant/MenuControllerTest.php`.
2. Mapear campos realmente existentes en el modelo `Menu`, `Section`, `Product` (no asumir). Crear una tabla en este documento (sección 9 — Notas) con: qué es editable inline vs. qué queda en drawer/subvistas.
3. Confirmar endpoints PATCH existentes para secciones y productos. Si faltan, diseñarlos respetando convenciones (Actions + FormRequests).
4. Construir el composable `useMenuEditor` con cola de patches y debounce.
5. Construir los componentes inline (`InlineEditableText`, `InlineEditablePrice`, `InlineSwitch`, `SaveIndicator`).
6. Refactor de `Show.vue` → componentes por zona (Header, SectionCard, ProductRow, Drawer).
7. Mover todos los toggles de `Form.vue`/`Edit.vue` al header unificado.
8. Eliminar `Edit.vue` (o convertir en redirect) y limpiar rutas.
9. i18n + estados de guardado + accesibilidad.
10. Tests backend + un smoke test frontend si existe infraestructura (Pest/PHPUnit + Dusk si aplica — si no, documentar prueba manual).
11. Prueba manual end-to-end vía Sail.
12. Marcar checklist y reportar según sección 10.

---

## 8. Restricciones

- **No** tocar el flujo de creación de menú (`Create.vue` + `Form.vue` siguen funcionando).
- **No** introducir librerías de rich text / WYSIWYG nuevas.
- **No** añadir dependencias si las existentes resuelven el caso.
- **No** reescribir `Customize.vue`, `Translations.vue`, `Filters.vue` — deben seguir accesibles con botones/tabs desde la nueva vista.
- **Sí** respetar convenciones del proyecto (Actions + FormRequests + Inertia pages, design system teal/Inter/rounded-lg).
- **Sí** mantener multi-tenant isolation en cada endpoint patch.

---

## 9. Estado actual / Checklist

Marcar con `[x]` al completar. Si el trabajo se interrumpe, continuar desde el primer `[ ]`.

- [ ] Leídos `MenuController`, `Show.vue`, `Edit.vue`, `Form.vue`, `MenuUpdateRequest`, `UpdateMenu`
- [ ] Tabla de campos editables documentada (en Notas abajo)
- [ ] Endpoints PATCH de Section/Product verificados o diseñados
- [ ] `MenuUpdateRequest` acepta payloads parciales sin romper creación
- [ ] Composable `useMenuEditor` implementado con debounce + queue + indicador
- [ ] Componentes inline creados (`InlineEditableText`, `InlineEditablePrice`, `InlineSwitch`, `SaveIndicator`)
- [ ] `Show.vue` partido en sub-componentes y refactorizado
- [ ] Header con edición inline + switches de visualización integrados
- [ ] Secciones y productos con edición inline y drag & drop funcionando
- [ ] Drawer de detalle de producto (campos ricos) funcional
- [ ] `Edit.vue` eliminado o redirigiendo a `show`
- [ ] i18n `menus.show.*` y `menus.inline.*` añadidos en es + en
- [ ] Tests backend actualizados y verdes
- [ ] Prueba manual end-to-end OK
- [ ] Resumen de cambios reportado al usuario

### Notas (rellenar durante el trabajo)

- Campos editables inline del menú: _pendiente_
- Campos editables inline de sección: _pendiente_
- Campos editables inline de producto: _pendiente_
- Campos que quedan en drawer de producto: _pendiente_
- Endpoints PATCH existentes encontrados: _pendiente_
- Decisiones tomadas en puntos ambiguos: _pendiente_

---

## 10. Reporte final

Al terminar, el agente debe reportar:
- Archivos creados / modificados / eliminados.
- Tests ejecutados y resultado.
- Decisiones sobre puntos ambiguos (ej. si se optó por PATCH único vs. múltiples endpoints).
- Cambios de API (nuevas rutas) y si rompen clientes existentes.
- Indicaciones para probar manualmente el flujo completo.
