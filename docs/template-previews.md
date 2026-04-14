# Previews vivos de plantillas en el onboarding — Brief para agente

> Plan para renderizar miniaturas reales de cada plantilla en la card picker del paso 2 del onboarding, sin depender de screenshots, Puppeteer ni assets binarios.
> Persiste entre sesiones: si el trabajo se interrumpe, retomar desde la sección **Checklist**.
> Antes de editar código, leer primero el estado vigente de los archivos citados — pueden haber cambiado.

---

## 1. Objetivo

En el paso 2 del onboarding (`resources/js/pages/admin/tenant/onboarding/steps/StepTemplate.vue`) el usuario elige entre 8 plantillas. La `TemplateCard` muestra una miniatura viva del menú real renderizado con la plantilla correspondiente.

Criterio de éxito:
- Cada card del picker muestra una miniatura legible (aunque pequeña) que refleja con fidelidad la estética real de la plantilla: colores, tipografía, estructura, densidad.
- Todas las miniaturas usan **el mismo menú falso** (mismas secciones/productos/precios) para que el usuario compare estilos, no contenidos.
- La miniatura es **prácticamente HTML+CSS estático** — sin cart, i18n, SEO, share, language switcher, ni composables pesados.
- El conjunto de 8 miniaturas no degrada el tiempo de carga del paso 2 de forma notable.
- Funciona en producción sin dependencias externas.
- El modal "Previsualizar" (overlay al hover) muestra la MISMA miniatura pero en tamaño grande.

---

## 2. Arquitectura "Shared Body"

Para cada plantilla, se extrae el bloque puramente visual a un componente `<Name>Body.vue` en `resources/js/components/template-bodies/`. Ese componente:

- Recibe `menu: BodyMenu` como prop (tipo mínimo compatible con el Menu real y con FakeMenu).
- Renderiza solo el cuerpo visual: hero, secciones, productos, footer.
- No depende de Inertia (`<Head>`), no depende de `useI18n`, no depende de `useMenuCustomization`.
- No incluye `AddToCartButton`, `CartFab`, `CartDrawer`, `ShareMenu`, `MenuLanguageSwitcher`.
- Tiene prop `interactive?: boolean` (reservado para uso futuro; actualmente ignorado).
- Auto-contenido: su propio CSS scoped con la paleta y tipografía de la plantilla.

### Estructura de archivos

```
resources/js/components/template-bodies/
├── fakeMenu.ts              ← dataset compartido + tipos BodyMenu, BodyMenuSection, etc.
├── TemplatePreview.vue      ← wrapper que resuelve component_name → Body correcto
├── BasicBody.vue
├── ModernBody.vue
├── MinimalistBody.vue
├── TrattoriaBody.vue
├── NeonBody.vue
├── BotanicaBody.vue
├── RivieraBody.vue
└── ChapterBody.vue
```

### Tipos en fakeMenu.ts

`BodyMenu` es el tipo mínimo que los Body components necesitan. Es compatible estructuralmente con el tipo `Menu` real del proyecto. `FakeMenu` sigue siendo el tipo específico del dataset de preview.

### TemplatePreview.vue

Wrapper genérico que:
- Recibe `componentName: string` (ej. `"Basic"`, `"TemplateModern"`, etc.)
- Resuelve el Body component correcto (acepta nombres con o sin prefijo "Template")
- Le pasa el `fakeMenu` dataset
- Lo renderiza con `interactive={false}`

### Integración en TemplateCard

En `TemplateCard.vue`: el área de preview contiene un `<div>` de 600×800 px escalado con `transform: scale(0.24)` con `pointer-events: none` y `aria-hidden="true"`.

### Integración en modal de StepTemplate

En `StepTemplate.vue`: el modal de preview renderiza `<TemplatePreview>` en una ventana de 600×480 px visible (escala 0.8 del body de 600×800).

### Plantillas reales (Basic.vue, TemplateModern.vue, etc.)

Las plantillas reales NO se refactorizan para usar los Body components. Mantienen su template completo con cart, i18n, SEO, share, etc. Los Body components son exclusivamente para preview estático.

**Consecuencia de diseño**: si se cambia el CSS de un Body component, el preview se actualiza automáticamente. Si se cambia el CSS de la plantilla real, la página pública se actualiza. Son dos lugares separados. Para sincronizar cambios visuales, hay que actualizarlos manualmente en ambos. En la práctica, el Body component es la fuente de verdad visual para el onboarding.

---

## 3. Requisitos técnicos

- **Sin dependencias nuevas**.
- **Sin screenshots, sin Puppeteer, sin Browsershot, sin imagenes binarias**.
- Usar `<script setup lang="ts">` y tipado estricto.
- Los Body components tienen todo el CSS en `<style scoped>`, self-contained.
- Accesibilidad: las miniaturas son puramente decorativas, usar `aria-hidden="true"` en el wrapper. El nombre de la plantilla + chips ya comunican la info esencial.
- Rendimiento: 8 bloques de HTML estático, sin composables pesados. Fluido sin IntersectionObserver.

---

## 4. Plan de ejecución (completado)

1. Leer las 8 plantillas actuales para identificar colores, fuentes, layout de cada una. ✓
2. Crear `fakeMenu.ts` con el dataset compartido y tipos. ✓
3. Crear los 8 Body components, uno por plantilla. ✓
4. Crear `TemplatePreview.vue` (wrapper). ✓
5. Integrar en `TemplateCard.vue` — sustituir el placeholder actual. ✓
6. Integrar en el modal de preview de `StepTemplate.vue`. ✓
7. Verificar build y tests. ✓

---

## 5. Restricciones

- NO importar `<Head>`, `useI18n`, `useMenuCustomization`, `useCart` dentro de los Body components.
- NO añadir dependencias.
- NO tocar las plantillas reales en `resources/js/pages/tenant/templates/`.
- NO tocar el flujo de onboarding más allá de la integración en TemplateCard + modal.
- NO romper los tests actuales.

---

## 6. Checklist

- [x] `docs/template-previews.md` actualizado con el nuevo enfoque (Shared Body)
- [x] `fakeMenu.ts` creado con dataset compartido y tipo `BodyMenu`
- [x] `BasicBody.vue` creado con estética fiel a Basic.vue
- [x] `ModernBody.vue` creado con estética fiel a TemplateModern.vue
- [x] `MinimalistBody.vue` creado con estética fiel a TemplateMinimalist.vue
- [x] `TrattoriaBody.vue` creado con estética fiel a TemplateTrattoria.vue
- [x] `NeonBody.vue` creado con estética fiel a TemplateNeon.vue
- [x] `BotanicaBody.vue` creado con estética fiel a TemplateBotanica.vue
- [x] `RivieraBody.vue` creado con estética fiel a TemplateRiviera.vue
- [x] `ChapterBody.vue` creado con estética fiel a TemplateChapter.vue
- [x] `TemplatePreview.vue` wrapper creado
- [x] Integrado en TemplateCard (con scale 0.24)
- [x] Integrado en modal de StepTemplate (scale 0.8, height 480px)
- [x] Tests verdes (PublicMenu y Onboarding — 25 passed)
- [x] Plantillas reales intactas (Basic.vue restaurado, demás sin tocar)

---

## 7. Reporte final

### Archivos creados

- `resources/js/components/template-bodies/fakeMenu.ts`
- `resources/js/components/template-bodies/BasicBody.vue`
- `resources/js/components/template-bodies/ModernBody.vue`
- `resources/js/components/template-bodies/MinimalistBody.vue`
- `resources/js/components/template-bodies/TrattoriaBody.vue`
- `resources/js/components/template-bodies/NeonBody.vue`
- `resources/js/components/template-bodies/BotanicaBody.vue`
- `resources/js/components/template-bodies/RivieraBody.vue`
- `resources/js/components/template-bodies/ChapterBody.vue`
- `resources/js/components/template-bodies/TemplatePreview.vue`

### Archivos modificados

- `resources/js/pages/admin/tenant/onboarding/components/TemplateCard.vue` — importa `TemplatePreview`, reemplaza placeholder por miniatura viva escalada, elimina funciones `paletteFor`/`fontFor` ya inutilizadas
- `resources/js/pages/admin/tenant/onboarding/steps/StepTemplate.vue` — importa `TemplatePreview`, reemplaza el `<img>` del modal por el componente live

### Decisiones de diseño

1. **Tipo `BodyMenu` en lugar de `FakeMenu` para los Body**: los Body components aceptan `BodyMenu`, un tipo mínimo compatible estructuralmente con el tipo `Menu` real y con `FakeMenu`. Esto permite reutilizarlos en el futuro si se quiere mostrar un preview del menú real en el panel admin.

2. **Plantillas reales no refactorizadas**: tras analizar la complejidad de integrar el carrito (AddToCartButton, callbacks de cart, state management), se decidió no refactorizar las plantillas reales. Los Body components son exclusivamente para preview estático. Esta es la decisión más pragmática: no se rompe ninguna funcionalidad existente.

3. **Nombres con/sin prefijo "Template"**: el wrapper `TemplatePreview.vue` acepta tanto `"Basic"` como `"TemplateModern"` — elimina el prefijo "Template" antes de buscar en el mapa.

4. **Scale factor**: cards del onboarding usan `scale(0.24)` sobre un contenedor de 600×800px. El modal usa `scale(0.8)` sobre 600×800px, visible en una ventana de 480×640px.

5. **CSS auto-contenido en cada Body**: cada Body tiene su CSS scoped completo. Las fuentes se referencian con sus nombres de Google Fonts — fallback a serif/sans genéricas si no están cargadas en el contexto del admin.

### Tests

- `PublicMenuTest` (9 tests): todos verdes — las plantillas reales no se modificaron
- `OnboardingTest` (13 tests): todos verdes — el cambio en TemplateCard es puramente visual
