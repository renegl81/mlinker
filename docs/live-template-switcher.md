# Selector de plantilla con preview en vivo — Brief para agente

> Añadir selector de plantilla con preview en tiempo real en dos lugares del panel admin:
> 1. Panel de "Preview" en `Show.vue` del menú.
> 2. Página `Customize.vue` del menú.
> Persiste entre sesiones: si el trabajo se interrumpe, retomar desde el checklist de la sección 7.

---

## 1. Objetivo

El usuario quiere cambiar de plantilla y ver el resultado sin recargar ni cambiar de pantalla:

- **Preview panel de `Show.vue`**: actualmente muestra la web pública en un `<iframe>` con toggles mobile/desktop. Añadir una strip superior o lateral de thumbnails con las plantillas disponibles. Click en thumbnail → PATCH optimista de `template_id` + refresh del iframe (con `?v=timestamp` para cache-bust). Resaltar la plantilla activa.
- **Customize.vue**: añadir al inicio una sección "Plantilla" con grid visual. Reemplazar (o complementar) el iframe actual —si lo hay— con una zona fija/sticky que renderice `TemplatePreview` con la `componentName` actual, el `menu` real y la `customization` actual. Al cambiar plantilla o cualquier ajuste de color/fuente/layout, el preview se redibuja al instante sin round-trip al backend. Cambiar plantilla se persiste vía PATCH optimista.

Criterio de éxito:
- Cambio de plantilla en Show preview: iframe recarga con la nueva plantilla sin reload del panel. Feedback visual inmediato.
- Cambio de plantilla en Customize: preview (no iframe) se redibuja al instante.
- Cualquier cambio de color/fuente/layout en Customize se refleja en el preview al instante (sin iframe, sin round-trip).
- Tests siguen verdes.

---

## 2. Contexto técnico

Componentes/archivos ya existentes:
- `resources/js/components/template-bodies/TemplatePreview.vue` — wrapper `componentName → XxxBody`. Actualmente inyecta `fakeMenu`. Hay que extenderlo para aceptar `menu` y `customization` opcionales.
- `resources/js/components/template-bodies/<Name>Body.vue` × 8 — bodies visuales. Un agente paralelo (`docs/template-previews.md`) está trabajando para que acepten `customization?` y `labels?`. Coordinar.
- `resources/js/pages/admin/tenant/menus/Show.vue` (~1284 líneas) — ya tiene un preview-panel con iframe + toggles mobile/desktop + refresh + open-in-new-tab (líneas ~1121 en adelante, variables `showPreview`, `previewMode`, `refreshPreview`).
- `resources/js/pages/admin/tenant/menus/Customize.vue` — la página con tabs de colors/fonts/layout. Hoy no tiene selector de plantilla.
- `resources/js/pages/admin/tenant/onboarding/components/TemplateCard.vue` — card reusable con body vivo escalado. Util para el grid en ambos sitios (puede requerir variante compacta).
- Backend rutas PATCH ya existentes: `tenant.menus.patch` (`MenuController::patch`) + `MenuPatchRequest`. Verificar que acepta `template_id`. Si no, añadir regla `'template_id' => ['sometimes', 'integer', 'exists:templates,id']`.
- `MenuCustomizationController` (si existe, o donde se resuelva la vista Customize): debe pasar `templates` al frontend.

---

## 3. Implementación

### 3.1 Extender `TemplatePreview.vue`

```ts
interface Props {
    componentName: string;
    menu?: BodyMenu;                    // si se omite, usa fakeMenu
    customization?: MenuCustomization;  // opcional
    interactive?: boolean;              // default false
    labels?: Record<string, string>;
}
```

Propagar todas al Body correspondiente. Si el Body aún no acepta `customization` (agente paralelo pendiente), al menos declarar el prop y pasarlo; el Body puede ignorarlo temporalmente.

### 3.2 Preview panel de `Show.vue`

Requiere pasar `templates` al frontend:
- En `MenuController::show()` añadir `'templates' => Template::where('is_active', true)->get([...])` a la respuesta Inertia (puede que ya lo haga gracias a trabajos anteriores — verificar).

En `Show.vue`:
- Añadir variable `templates: Template[]` en props.
- Dentro del `preview-header` o como strip adicional debajo, renderizar un carrusel horizontal de thumbnails: cada uno es un `TemplateCard` compacto (height reducida) o un botón con `TemplatePreview` a escala ~0.15 + nombre. La plantilla activa (`menu.template_id`) destacada con borde teal + check.
- Click en thumbnail:
  1. Optimista: actualizar `menu.template_id` localmente.
  2. `router.patch('/panel/menus/{id}/inline', { template_id })` (o endpoint `menus.patch`). On success: refrescar iframe con `?v=Date.now()`. On error: revertir.
- Permitir navegación con flechas ← → cuando el preview panel está abierto (añadir keydown listener que solo actúe si `showPreview`).

### 3.3 Customize.vue

**Backend**:
- Localizar el controller que renderiza Customize (buscar `Inertia::render('admin/tenant/menus/Customize'` — probablemente `MenuCustomizationController@show` o `MenuController@customize`).
- Añadir `'templates' => Template::where('is_active', true)->get([...])` a los props.

**Frontend**:
- Añadir `templates` a la interfaz `Props`.
- Al inicio (antes de los tabs), nueva sección "Plantilla" con grid `TemplateCard` en 2-4 columnas según viewport. La seleccionada resalta con borde/check.
- Al hacer click:
  - Actualizar `menu.template_id` local.
  - PATCH optimista vía `router.patch('/panel/menus/{id}/inline', { template_id }, { preserveScroll: true, preserveState: true })`.
  - El preview panel de la derecha re-renderiza con el nuevo `componentName`.
- Layout recomendado (desktop): columna izquierda con toda la customización (tabs existentes + nueva sección Plantilla arriba), columna derecha sticky con `TemplatePreview` a tamaño natural (600×800 o adaptado, con scroll propio).
- `currentCustomization` = computed combinando refs `colors`, `fonts`, `layout`, etc. Se pasa como prop al preview.
- `currentComponentName` = computed que busca en `templates` por `menu.template_id` y devuelve `component_name`.
- Quitar el iframe si existe en la página actual — el preview es ahora vivo con `TemplatePreview`.

### 3.4 Rendimiento

- Si cambiar color/fuente en Customize produce lag visible al redibujar el Body, debounce 150ms antes de propagar cambios al preview.
- `shallowRef` donde aplique.
- No virtualización.

---

## 4. Validación de props en backend

`MenuPatchRequest`:
```php
public function rules(): array {
    return [
        // ...
        'template_id' => ['sometimes', 'integer', 'exists:templates,id'],
    ];
}
```

`PatchMenu` action debe aceptar `template_id`. Verificar que lo incluye en los campos permitidos (casting / fillable / assign). Si no, añadirlo.

---

## 5. Accesibilidad

- Grid de plantillas con `role="radiogroup"`, cada card con `role="radio" aria-checked tabindex=0`.
- Teclado en Show preview: ← → navega plantillas, Esc cierra preview.
- Indicador aria-live cuando cambia plantilla: "Plantilla cambiada a {name}".

---

## 6. Restricciones

- Sin dependencias nuevas.
- Sin tocar la arquitectura shared-body.
- Sin romper el iframe actual de Show preview — si se mantiene, solo añadir selector encima; si se sustituye, asegurar que funcional equivalente (device toggle, open-in-new-tab, etc.) se mantiene.
- Coordinar con agente paralelo que toca `<Name>Body.vue`. Si aún no acepta `customization`, hacer que `TemplatePreview` reciba el prop y lo pase al Body (aunque el Body lo ignore temporalmente). Documentar.

---

## 7. Checklist

- [ ] `TemplatePreview.vue` extendido con `menu`, `customization`, `interactive`, `labels`
- [ ] `MenuPatchRequest` acepta `template_id`
- [ ] `PatchMenu` action procesa `template_id`
- [ ] Backend: `Show.vue` recibe `templates`
- [ ] Backend: `Customize.vue` recibe `templates`
- [ ] Show.vue: strip/grid de thumbnails en el preview-panel
- [ ] Show.vue: click en thumbnail → PATCH optimista + refresh iframe con `?v=`
- [ ] Show.vue: navegación con flechas ← → cuando preview abierto
- [ ] Customize.vue: sección "Plantilla" con grid y selección activa visible
- [ ] Customize.vue: PATCH optimista de `template_id`
- [ ] Customize.vue: preview vivo lateral con `TemplatePreview` + customization reactiva
- [ ] Customize.vue: eliminado iframe si lo había (o justificar mantenerlo)
- [ ] Tests verdes: `php artisan test --filter=Menu`
- [ ] Prueba manual: 3 cambios en Show preview + 3 en Customize sin errores
- [ ] Reporte final

---

## 8. Reporte final

- Archivos creados / modificados.
- Cómo se siente el rendimiento al cambiar plantilla + customization en vivo.
- Decisión sobre iframe de Customize (eliminado / mantenido + por qué).
- Ajustes a los Body components (si los hizo falta).
- Instrucciones de prueba manual.
