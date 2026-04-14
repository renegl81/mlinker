# Previews vivos de plantillas en el onboarding — Brief para agente

> Plan para renderizar miniaturas reales de cada plantilla en la card picker del paso 2 del onboarding, sin depender de screenshots, Puppeteer ni assets binarios.
> Persiste entre sesiones: si el trabajo se interrumpe, retomar desde la sección **Checklist**.
> Antes de editar código, leer primero el estado vigente de los archivos citados — pueden haber cambiado.

---

## 1. Objetivo

En el paso 2 del onboarding (`resources/js/pages/admin/tenant/onboarding/steps/StepTemplate.vue`) el usuario elige entre 8 plantillas. Hoy la `TemplateCard` muestra un placeholder estilizado si no hay `preview_image_url`. Queremos sustituir ese placeholder por una **miniatura viva** del menú real renderizado con la plantilla correspondiente.

Criterio de éxito:
- Cada card del picker muestra una miniatura legible (aunque pequeña) que refleja con fidelidad la estética real de la plantilla: colores, tipografía, estructura, densidad.
- Todas las miniaturas usan **el mismo menú falso** (mismas secciones/productos/precios) para que el usuario compare estilos, no contenidos.
- La miniatura es **prácticamente HTML+CSS estático** — sin cart, i18n, SEO, share, language switcher, ni composables pesados.
- El conjunto de 8 miniaturas no degrada el tiempo de carga del paso 2 de forma notable (<50ms de render inicial razonable).
- Funciona en producción sin dependencias externas (nada de Puppeteer, Playwright ni browsers headless).
- El modal "Previsualizar" (overlay al hover) muestra la MISMA miniatura pero en tamaño grande, sin `transform: scale`.

---

## 2. Arquitectura propuesta

### 2.1 Componentes a crear
Un componente puramente presentacional por plantilla, siguiendo la estética del original:

```
resources/js/components/template-previews/
├── BasicPreview.vue
├── ModernPreview.vue
├── MinimalistPreview.vue
├── TrattoriaPreview.vue
├── NeonPreview.vue
├── BotanicaPreview.vue
├── RivieraPreview.vue
├── ChapterPreview.vue
├── fakeMenu.ts            ← dataset compartido
└── TemplatePreview.vue    ← wrapper que resuelve component_name → Preview correcto
```

### 2.2 Dataset compartido (`fakeMenu.ts`)
Un objeto TypeScript con datos representativos para un restaurante ficticio ("La Parrilla del Mar" u otro nombre neutro). Sugerencia de contenido mínimo:
- **Entrantes**: Ensalada de temporada (8 €), Tartar de atún (14 €), Carpaccio de ternera (12 €).
- **Principales**: Risotto de setas (16 €), Lomo de bacalao (22 €), Solomillo a la pimienta (26 €).
- **Postres**: Tarta de queso (7 €), Coulant de chocolate (8 €).
- **Bebidas**: Vino de la casa (18 €/botella), Agua mineral (2.50 €).

Campos mínimos por producto: `id`, `name`, `description`, `price`, `tags` (vegano/sin gluten/etc.), `allergens` (array reducido).
Campos mínimos del menu: `name`, `description`, `sections[]`, `show_prices`, `show_currency`, `currency`.

Solo **español**. Sin translations JSON. Sin logos reales. Ingredients/allergens opcionales y mínimos.

### 2.3 Componentes Preview
Cada `XxxPreview.vue` debe:
- Recibir `menu: FakeMenu` como prop.
- Renderizar **solo markup + CSS/Tailwind**. Nada de `<Head>`, `useI18n`, `useMenuCustomization`, `useCart`, `AllergenIcon` (usar emoji o nada), ni `MenuLanguageSwitcher`, `ShareMenu`, `CartFab`, `CartDrawer`.
- Replicar visualmente la plantilla original (mirar `resources/js/pages/tenant/templates/<Name>.vue`):
  - Paleta de colores (background, text, accent, rule).
  - Tipografía (families/weights).
  - Layout (single-column, card-rows, chapter-narrow, etc.).
  - Densidad / spacing.
  - Divisores, numeraciones, ornamentos (si la plantilla original los tiene).
- **Self-contained**: sin scroll, sin lógica interactiva. Debe pintarse completo en el espacio disponible.
- Tamaño de diseño base: **600×800 px**. La TemplateCard se encargará de escalar.

### 2.4 Wrapper `TemplatePreview.vue`
```vue
<script setup lang="ts">
import BasicPreview from './BasicPreview.vue';
import ModernPreview from './ModernPreview.vue';
// ...
import { fakeMenu } from './fakeMenu';

const props = defineProps<{ componentName: string }>();

const map: Record<string, any> = {
    Basic: BasicPreview,
    Modern: ModernPreview,
    Minimalist: MinimalistPreview,
    Trattoria: TrattoriaPreview,
    Neon: NeonPreview,
    Botanica: BotanicaPreview,
    Riviera: RivieraPreview,
    Chapter: ChapterPreview,
};
</script>

<template>
    <component :is="map[props.componentName] ?? BasicPreview" :menu="fakeMenu" />
</template>
```

### 2.5 Integración en TemplateCard
En `resources/js/pages/admin/tenant/onboarding/components/TemplateCard.vue` reemplazar el placeholder (`<div v-else> ... Aa</div>`) por:

```vue
<div class="relative h-32 w-full overflow-hidden bg-white sm:h-36" aria-hidden="true">
    <div
        class="absolute left-0 top-0 origin-top-left"
        style="width: 600px; height: 800px; transform: scale(0.3); transform-origin: top left;"
    >
        <TemplatePreview :component-name="props.template.component_name" />
    </div>
</div>
```
Ajustar el `scale(0.3)` según el tamaño real del card (typically `w-44` → escala ~0.29). Usar `pointer-events: none` para que el click siga capturándose por el `<button>` padre.

### 2.6 Modal "Previsualizar"
En `StepTemplate.vue`, el modal de preview (cuando hace hover/click en "Previsualizar") debe renderizar `<TemplatePreview :component-name="previewTemplate.component_name" />` a tamaño completo (600×800 o adaptado al viewport del modal). Mismo dataset `fakeMenu`.

---

## 3. Requisitos técnicos

- **Sin dependencias nuevas**.
- **Sin screenshots, sin Puppeteer, sin Browsershot, sin imagenes binarias**.
- Los componentes Preview pueden copiar CSS inline o Tailwind según la plantilla original. Si la plantilla original usa utilidades Tailwind complejas o CSS scoped, replicarlo.
- Si una plantilla usa `useMenuCustomization` para colores dinámicos, el Preview debe **hard-codear** la paleta por defecto (no leer props de customization).
- Usar `<script setup lang="ts">` y tipado estricto.
- Respetar design system del panel (teal / Inter / rounded-lg) SOLO en la card picker, no en el preview interno (el preview debe respetar su propia plantilla).
- Accesibilidad: las miniaturas son puramente decorativas, usar `aria-hidden="true"` en el wrapper. El nombre de la plantilla + chips ya comunican la info esencial.
- Rendimiento: si al renderizar las 8 el paso se siente lento, envolver cada card en un `IntersectionObserver` para lazy-render. Pero probar primero sin optimización — 8 bloques de HTML estático deberían ir fluidos.

---

## 4. Plan de ejecución

1. Leer las 8 plantillas actuales (`resources/js/pages/tenant/templates/*.vue`) para identificar colores, fuentes, layout de cada una.
2. Crear `fakeMenu.ts` con el dataset compartido.
3. Crear los 8 componentes `XxxPreview.vue` uno a uno, probando visualmente comparando con la plantilla real.
4. Crear `TemplatePreview.vue` (wrapper).
5. Integrar en `TemplateCard.vue` — sustituir el placeholder actual.
6. Integrar en el modal de preview de `StepTemplate.vue` — reemplazar el `<img v-if="preview_image_url">` y el fallback por `<TemplatePreview :component-name="previewTemplate.component_name" />`.
7. Si ya no se usa, eliminar la columna `preview_image_url` del modelo Template y su seeder (opcional; se puede dejar por si en el futuro quieren screenshots custom).
8. Probar manualmente: abrir el paso 2 del onboarding, verificar que las 8 miniaturas se ven correctamente y son distinguibles.
9. Revisar rendimiento en mobile. Si lag, añadir `IntersectionObserver`.
10. Actualizar checklist y reportar.

---

## 5. Restricciones

- NO importar `<Head>`, `useI18n`, `useMenuCustomization`, `useCart`, `useMenuFormatter` dentro de los Preview components.
- NO añadir dependencias.
- NO tocar las plantillas reales en `resources/js/pages/tenant/templates/`.
- NO tocar el flujo de onboarding más allá de la integración en TemplateCard + modal.
- NO romper los tests actuales (`php artisan test --filter=Onboarding`).

---

## 6. Checklist

Marcar con `[x]` al completar. Si se interrumpe, continuar desde el primer `[ ]`.

- [ ] 8 plantillas originales leídas; notas de paleta/fuente/layout tomadas
- [ ] `fakeMenu.ts` creado con dataset compartido
- [ ] `BasicPreview.vue` creado y visualmente fiel
- [ ] `ModernPreview.vue` creado y visualmente fiel
- [ ] `MinimalistPreview.vue` creado y visualmente fiel
- [ ] `TrattoriaPreview.vue` creado y visualmente fiel
- [ ] `NeonPreview.vue` creado y visualmente fiel
- [ ] `BotanicaPreview.vue` creado y visualmente fiel
- [ ] `RivieraPreview.vue` creado y visualmente fiel
- [ ] `ChapterPreview.vue` creado y visualmente fiel
- [ ] `TemplatePreview.vue` wrapper creado
- [ ] TemplateCard integrado — placeholder reemplazado por miniatura viva
- [ ] Modal de preview de StepTemplate reemplazado por miniatura a tamaño grande
- [ ] Prueba manual del paso 2 OK en desktop y mobile
- [ ] Suite de tests verde
- [ ] Reporte de cambios entregado

---

## 7. Reporte final (al terminar)

- Archivos creados / modificados.
- Capturas o descripción de cómo se ven las 8 miniaturas.
- Rendimiento percibido (fluido / algo lento / necesitó IntersectionObserver).
- Decisiones sobre detalles ambiguos (qué componentes originales hiciste qué simplificación).
