# Catálogo del tenant — MVP brief

Sección nueva en el panel del tenant para gestionar productos e ingredientes de forma global, independiente del menú. Documento autocontenido: un agente debería poder ejecutarlo sin contexto adicional.

---

## 1. Contexto y objetivo

Hoy los productos se crean/editan dentro de un menú (`/panel/menus/{id}` → sección → producto). Estructuralmente ya son globales por tenant: `products.tenant_id`, y las relaciones con menú y sección son pivot M2M (`menu_product`, `product_section`). No hay refactor de datos.

Añadimos una sección **Catálogo** en la navegación lateral con dos páginas: **Productos** e **Ingredientes**. El flujo menú-centric actual no se toca.

**Posicionamiento**: power-user feature, gated detrás de plan Business o superior (`plans.has_multilang` se usa hoy como proxy de "plan alto"; comprobar si hay un flag más apropiado o crear `has_catalog`).

**Prioridad**: media. Ejecutar cuando se pida explícitamente, no es bloqueante del MVP general.

---

## 2. Scope

### DENTRO
- **Productos (list + bulk + edit)**
  - Tabla con filtros (búsqueda, menú, sección, tag, alérgeno).
  - Columnas: thumb, nombre, precio, menús en los que aparece (contador), tags, acciones.
  - Selección múltiple con acciones: duplicar, eliminar, añadir a menú X.
  - Click en un producto → abre el form de edición existente (`resources/js/pages/admin/tenant/products/Edit.vue`), reusando el mismo flujo y URL (`/panel/products/{id}/edit`).
- **Ingredientes (list + edit inline + merge)**
  - Tabla: nombre, usado en N productos, badges de estado por idioma.
  - Editar nombre inline (con validación de unicidad por tenant).
  - Editar traducciones inline (expand/drawer).
  - **Merge**: seleccionar 2+ filas con nombres similares y fusionarlas en una sola (ej. "Tomate" + "tomate" → escoger cuál sobrevive; el resto se elimina y sus asociaciones en `ingredient_product` se reasignan al sobreviviente).
- **Navegación**: nuevo item "Catálogo" en el sidebar con sub-items "Productos" e "Ingredientes". Visible solo si el plan del tenant tiene catálogo habilitado.
- **i18n**: todos los textos nuevos van en `resources/js/locales/{es,en}.json` bajo la clave `catalog.*`.
- **Plan gate**: middleware o verificación en controller que redirija a `/panel/billing/plans` con mensaje si el plan no cubre catálogo.

### FUERA (NO hacer)
- ❌ **Overrides por menú** (precio distinto de un producto en menús distintos). Si surgen, crear dos productos.
- ❌ **Importar CSV**. Queda para v2.
- ❌ **Stock/inventario**. Fuera de alcance. No es ERP.
- ❌ **Proveedores, costes, márgenes**. Idem.
- ❌ **Modificar el onboarding**. El nuevo usuario sigue creando menús como siempre.
- ❌ **Refactorizar `ProductController` ni `Product/Edit.vue`**. Reutilizar tal cual.
- ❌ **Añadir columnas nuevas a `products` o `ingredients`**. Usar los campos existentes.

---

## 3. Plan técnico

### 3.1. Plan gate

Verificar cómo identificar planes con catálogo. Opciones:
1. Reusar `plans.has_multilang` como proxy temporal (rápido pero semánticamente sucio).
2. Crear una columna booleana `plans.has_catalog` vía nueva migración y actualizar el `PlanSeeder` (limpio).

**Recomendación**: Opción 2. Migración:
```
./vendor/bin/sail artisan make:migration add_has_catalog_to_plans_table
```
Añade `boolean('has_catalog')->default(false)`. Actualiza `PlanSeeder` para que Business y Enterprise lo tengan en `true`. Crea un helper en `TenantController` o en una clase `PlanChecker` reutilizable.

### 3.2. Backend — Productos

**Ruta**: nueva sección en `routes/tenant.php` dentro del grupo con `auth` + `EnsurePlanLimits` + `RedirectToOnboarding`:
```
Route::prefix('panel/catalog')->as('catalog.')->group(function () {
    Route::get('products', [CatalogProductController::class, 'index'])->name('products.index');
    Route::post('products/bulk-delete', [CatalogProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::post('products/bulk-duplicate', [CatalogProductController::class, 'bulkDuplicate'])->name('products.bulk-duplicate');
    Route::post('products/bulk-attach-menu', [CatalogProductController::class, 'bulkAttachMenu'])->name('products.bulk-attach-menu');

    Route::get('ingredients', [CatalogIngredientController::class, 'index'])->name('ingredients.index');
    Route::put('ingredients/{ingredient}', [CatalogIngredientController::class, 'update'])->name('ingredients.update');
    Route::put('ingredients/{ingredient}/translations', [CatalogIngredientController::class, 'updateTranslations'])->name('ingredients.translations');
    Route::delete('ingredients/{ingredient}', [CatalogIngredientController::class, 'destroy'])->name('ingredients.destroy');
    Route::post('ingredients/merge', [CatalogIngredientController::class, 'merge'])->name('ingredients.merge');
});
```

Aplicar middleware `EnsurePlanFeature:catalog` o check inline en cada método: `abort_unless($tenantHasCatalog, 403)`.

**Controller**: `app/Http/Controllers/Admin/Tenant/CatalogProductController.php`
- `index(Request)`: paginación de productos del tenant con eager-load de `sections.menu`, `menus`, `allergens`, `ingredients`, `tags`. Filtros por query params: `q`, `menu_id`, `section_id`, `tag`, `allergen_id`. Devuelve Inertia `admin/tenant/catalog/Products` con `products`, `menus`, `sections`, `allergens`, `filters`.
- `bulkDelete(Request)`: valida `product_ids: array, product_ids.*: exists`, reutiliza `DeleteProduct` action por cada uno. Devuelve back con flash.
- `bulkDuplicate(Request)`: valida `product_ids`, reutiliza `DuplicateProduct` por cada uno, aplicando `CheckLimit` antes.
- `bulkAttachMenu(Request)`: valida `product_ids` + `menu_id` + opcional `section_id`. Inserta filas en `product_section` y `menu_product` pivots con `insertOrIgnore`. Calcula `sort_order` como `MAX + 1`.

Reutiliza las actions existentes (`DeleteProduct`, `DuplicateProduct`, `CheckLimit`). No las dupliques.

### 3.3. Backend — Ingredientes

**Controller**: `app/Http/Controllers/Admin/Tenant/CatalogIngredientController.php`
- `index(Request)`: lista de `Ingredient::withCount('products')->orderBy('name')->paginate(50)` del tenant actual. Filtro opcional por `q`. Devuelve Inertia `admin/tenant/catalog/Ingredients` con `ingredients`, `supportedLocales` (desde `config('menulinker.supported_locales')`), `filters`.
- `update(Request, Ingredient)`: valida `name` unique-per-tenant (excluyendo el propio id). Actualiza solo `name`.
- `updateTranslations(Request, Ingredient)`: valida `translations: array`. Actualiza `translations`.
- `destroy(Ingredient)`: elimina el ingrediente. Las filas en `ingredient_product` caen por FK cascade (verificar constraint en DB; si no existe, hacer DELETE manual antes).
- `merge(Request)`: valida `ingredient_ids: array, min:2` + `survivor_id: required|integer`. En una transacción:
  1. Verifica que todos pertenecen al tenant actual.
  2. Reasigna `UPDATE ingredient_product SET ingredient_id = survivor_id WHERE ingredient_id IN (otros)`.
  3. Fusiona las `translations` de los descartados en el superviviente (merge deep, el superviviente gana en caso de conflicto).
  4. Elimina los descartados.

**Auto-traducción en creación**: ya está cubierta por `IngredientCatalog::findOrImport` y no se toca. Cuando el usuario crea un ingrediente manualmente desde el catálogo, NO pasa por `findOrImport` → debe invocarlo explícitamente si queremos el beneficio cross-tenant. Añadir esa llamada si hace falta (opcional en MVP).

### 3.4. Navegación lateral

**Componente**: `resources/js/layouts/app/NavMain.vue` o similar — buscar el componente que renderiza el sidebar del panel tenant.

Añadir un item "Catálogo" con `children` colapsables:
- Productos → `/panel/catalog/products`
- Ingredientes → `/panel/catalog/ingredients`

Solo visible si `usePage().props.tenant.plan_features?.catalog === true` (requiere exponer el flag en el middleware `HandleInertiaRequests` o similar).

Icono sugerido: `BookOpenCheck` o `Library` de `lucide-vue-next`.

### 3.5. Frontend — `admin/tenant/catalog/Products.vue`

**Layout**: `AppLayout` con breadcrumb `Catálogo > Productos`.

**Estructura**:
- Header con título + botón "Nuevo plato" (abre el flujo actual, pidiendo primero elegir menú y sección en un modal — o redirige a `/panel/menus` para que el usuario elija, lo más simple).
- Barra de filtros sticky: input de búsqueda, selects para menú/sección/tag/alérgeno.
- Tabla con selección múltiple:
  - Checkbox global
  - Thumb (40×40 con fallback a inicial gradiente como ya hacemos en menus/Show)
  - Nombre (link que va al edit actual `/panel/products/{id}/edit`)
  - Precio (formateado con currency del location principal)
  - Menús (badges con contador, tooltip con lista)
  - Tags (glifos como en public menu Basic)
  - Menú de acciones (editar, duplicar, eliminar)
- Barra sticky inferior cuando hay selección: `{count} seleccionados · [Duplicar] [Eliminar] [Añadir a menú...]`
- Paginación al final.

**Estilo**: usa `panel-input`, `panel-label` y `text-card-foreground` en contenedores `bg-card` (ver `resources/css/app.css`). NO usar `bg-card` sin `text-card-foreground` — el body fuerza slate-200 y queda texto blanco invisible en claro.

**i18n**: añadir keys `catalog.*` en `es.json` y `en.json`.

### 3.6. Frontend — `admin/tenant/catalog/Ingredients.vue`

**Estructura**:
- Header con título + buscador.
- Tabla:
  - Checkbox global
  - Nombre (editable inline con click)
  - Usado en N productos (badge clicable → filtro por este ingrediente en catálogo de productos)
  - Badges de idiomas traducidos (es 🇪🇸, en 🇬🇧...) — verde si tiene traducción, gris si no
  - Acciones: editar traducciones (abre drawer/modal), eliminar
- Selección múltiple con acción **"Fusionar"** disponible cuando hay ≥2 seleccionados:
  - Modal con radio buttons para elegir el superviviente.
  - Preview de "Se eliminarán X, y todas las asociaciones pasarán a Y".
- Drawer de edición de traducciones: reutiliza la lógica del `MenuLanguageSwitcher` como referencia visual. Por cada locale en `supportedLocales`, un input para `translations[code].name`.

**Reutiliza** el componente `panel-input` y `panel-label`.

### 3.7. i18n — keys nuevas

En `resources/js/locales/es.json` y `en.json`, bajo el root:
```jsonc
"catalog": {
    "title": "Catálogo",
    "products": {
        "title": "Productos",
        "subtitle": "Gestiona todos los platos de tu restaurante",
        "search_placeholder": "Buscar por nombre...",
        "filter_menu": "Menú",
        "filter_section": "Sección",
        "filter_tag": "Tag",
        "filter_allergen": "Alérgeno",
        "column_name": "Nombre",
        "column_price": "Precio",
        "column_menus": "Menús",
        "column_tags": "Tags",
        "column_actions": "Acciones",
        "bulk_selected": "{count} seleccionado | {count} seleccionados",
        "bulk_duplicate": "Duplicar",
        "bulk_delete": "Eliminar",
        "bulk_attach": "Añadir a menú",
        "confirm_delete": "¿Eliminar los platos seleccionados? Esta acción no se puede deshacer.",
        "empty": "Aún no tienes platos. Crea uno desde cualquier menú."
    },
    "ingredients": {
        "title": "Ingredientes",
        "subtitle": "Gestiona el catálogo de ingredientes y sus traducciones",
        "search_placeholder": "Buscar ingrediente...",
        "column_name": "Nombre",
        "column_usage": "En productos",
        "column_translations": "Traducciones",
        "used_in_products": "Usado en {count} producto | Usado en {count} productos",
        "edit_translations": "Editar traducciones",
        "merge": "Fusionar",
        "merge_title": "Fusionar ingredientes",
        "merge_survivor": "¿Cuál conservar?",
        "merge_description": "Los demás se eliminarán y sus asociaciones pasarán al elegido.",
        "confirm_delete": "¿Eliminar este ingrediente? Se desasociará de todos los productos.",
        "empty": "Aún no tienes ingredientes."
    },
    "upgrade_required": "El catálogo está disponible en planes Business y Enterprise."
}
```

---

## 4. Testing

**Mínimo**:
- Test feature `CatalogProductTest` cubriendo:
  - Usuario con plan sin catálogo recibe 403 al acceder a `/panel/catalog/products`.
  - Usuario con plan Business lista solo sus productos.
  - Bulk delete elimina los productos correctos.
  - Bulk attach a menú crea pivots correctamente con `sort_order` secuencial.
- Test feature `CatalogIngredientTest`:
  - Update nombre valida unicidad por tenant.
  - Update translations persiste correctamente.
  - Merge fusiona N ingredientes en 1, reasigna pivots y borra los demás (verificar que las traducciones se mergean).
  - Tenant isolation: tenant A no puede fusionar ingredientes del tenant B (ya cubierto por `BelongsToTenant`, pero verificarlo explícitamente).

**Ejecutar**:
```bash
./vendor/bin/sail artisan test --filter="Catalog"
```

**Type check frontend**:
```bash
./vendor/bin/sail npx vue-tsc --noEmit
```

**Build** (verificar que no hay imports rotos):
```bash
./vendor/bin/sail npm run build
```

---

## 5. Criterios de aceptación

- [ ] Usuario con plan Business ve "Catálogo" en el sidebar.
- [ ] Usuario con plan Free NO ve "Catálogo" en el sidebar; si accede por URL directa, recibe 403 o redirect a billing.
- [ ] `/panel/catalog/products` lista los productos del tenant con filtros funcionales y paginación.
- [ ] Selección múltiple permite duplicar y eliminar en bloque; ambas acciones respetan los límites del plan (`CheckLimit`).
- [ ] Bulk attach a menú crea los pivots correctos y el menú muestra los productos añadidos en la sección elegida con `sort_order` incremental.
- [ ] `/panel/catalog/ingredients` lista ingredientes con contador de uso y badges de traducciones.
- [ ] Editar nombre inline valida unicidad y muestra error si colisiona.
- [ ] Drawer de traducciones permite editar todas las locales soportadas y persiste vía PUT `/panel/catalog/ingredients/{id}/translations`.
- [ ] Fusionar 2+ ingredientes deja uno solo con las asociaciones reasignadas y traducciones merged.
- [ ] Todos los strings pasan por i18n (es + en).
- [ ] Los estilos usan `panel-input`, `panel-label`, `text-card-foreground` — verificar que en modo claro todo es legible.
- [ ] Tests feature para ambos controllers en verde.
- [ ] Aislamiento por tenant garantizado en todos los endpoints.
- [ ] Nada del flujo actual menú-centric está roto.

---

## 6. Archivos implicados

### Nuevos
- `database/migrations/{timestamp}_add_has_catalog_to_plans_table.php`
- `app/Http/Controllers/Admin/Tenant/CatalogProductController.php`
- `app/Http/Controllers/Admin/Tenant/CatalogIngredientController.php`
- `app/Actions/Ingredient/MergeIngredients.php` (action dedicada para la fusión)
- `resources/js/pages/admin/tenant/catalog/Products.vue`
- `resources/js/pages/admin/tenant/catalog/Ingredients.vue`
- `resources/js/pages/admin/tenant/catalog/components/IngredientTranslationsDrawer.vue` (opcional)
- `resources/js/pages/admin/tenant/catalog/components/MergeIngredientsDialog.vue` (opcional)
- `tests/Feature/Tenant/Catalog/CatalogProductTest.php`
- `tests/Feature/Tenant/Catalog/CatalogIngredientTest.php`

### Modificados
- `routes/tenant.php` — nuevo grupo `panel/catalog`.
- `database/seeders/PlanSeeder.php` — marcar Business y Enterprise con `has_catalog = true`.
- `app/Http/Middleware/HandleInertiaRequests.php` — compartir `tenant.plan_features.catalog` con el frontend.
- Componente del sidebar (buscar en `resources/js/layouts/app/`) — añadir el nuevo item.
- `resources/js/locales/es.json` y `en.json` — añadir el bloque `catalog.*`.

### NO tocar
- `app/Actions/Product/{Create,Update,Delete,Duplicate}Product.php` (se reutilizan tal cual).
- `app/Http/Controllers/Admin/Tenant/ProductController.php` (sigue siendo el punto de entrada para edit individual).
- `resources/js/pages/admin/tenant/products/{Form,Edit,Create}.vue` (se reutiliza el form de edición).
- Rutas actuales de productos dentro del menú.
- Flujo de onboarding.

---

## 7. Decisiones pendientes (confirmar con el usuario antes de ejecutar)

1. ¿Crear columna `has_catalog` en `plans` o reusar `has_multilang`? — Recomendación: crear `has_catalog`.
2. ¿El botón "Nuevo plato" en la página de catálogo debe abrir un modal de "elige menú y sección" o redirigir al flujo actual? — Recomendación: modal inline.
3. ¿La fusión de ingredientes debe también fusionar las traducciones (deep merge)? — Recomendación: sí, con el superviviente ganando en conflictos.
4. ¿Exponer el catálogo de ingredientes también desde `IngredientCatalog::popular()` (cross-tenant) aquí? — **NO**. La página de ingredientes del catálogo es solo propia del tenant. Las sugerencias cross-tenant siguen apareciendo únicamente en el form de edición de productos.

---

## 8. Tiempo estimado

- Backend (migración + 2 controllers + action merge + tests): ~4h
- Frontend Products page (tabla + filtros + bulk + modal de attach): ~4h
- Frontend Ingredients page (tabla + inline edit + drawer de traducciones + merge): ~3h
- i18n + pulido + QA manual: ~2h
- **Total: ~13h** (≈1.5-2 días de trabajo continuo)

---

## 9. Instrucciones para el agente

Al ejecutar este brief, el agente debe:

1. **Leer primero** `CLAUDE.md` (raíz), `.ai/DEVELOPMENT_PLAN.md`, y este documento.
2. **Confirmar** con el usuario las decisiones pendientes de la sección 7 si no hay instrucciones explícitas en el prompt.
3. **Ejecutar por fases**, mostrando al usuario el progreso al final de cada una:
   - Fase 1: migración + plan gate + controllers backend + tests feature.
   - Fase 2: frontend Products page.
   - Fase 3: frontend Ingredients page.
   - Fase 4: sidebar + i18n + QA manual.
4. **No salirse del scope de la sección 2**. Si durante el trabajo surge la necesidad de algo listado en "FUERA", parar y preguntar.
5. **Reutilizar** actions y componentes existentes. No duplicar código.
6. **Verificar** después de cada fase:
   - `./vendor/bin/sail artisan test --filter="Catalog"`
   - `./vendor/bin/sail npx vue-tsc --noEmit`
7. **Seguir las convenciones** descritas en `.ai/` y el estilo del resto del código (Actions + FormRequests + Inertia pages).
8. **Estilos**: usar las utilities `panel-input`, `panel-input-readonly`, `panel-label`, `panel-label-muted` definidas en `resources/css/app.css`. Cualquier `bg-card` debe ir con `text-card-foreground` por el override forzado del body (slate-200).
