<script setup lang="ts">
import BasicBody from './BasicBody.vue';
import ModernBody from './ModernBody.vue';
import MinimalistBody from './MinimalistBody.vue';
import TrattoriaBody from './TrattoriaBody.vue';
import NeonBody from './NeonBody.vue';
import BotanicaBody from './BotanicaBody.vue';
import RivieraBody from './RivieraBody.vue';
import ChapterBody from './ChapterBody.vue';
import { fakeMenu, type BodyMenu } from './fakeMenu';

// MenuCustomization mirrors what the backend stores in menu.customization
export interface MenuCustomization {
    colors?: {
        accent?: string;
        bg?: string;
        ink?: string;
        ink_soft?: string;
        rule?: string;
    };
    fonts?: {
        display?: string;
        body?: string;
    };
    layout?: {
        show_allergens?: boolean;
        show_ingredients?: boolean;
        show_product_images?: boolean;
        show_section_descriptions?: boolean;
        image_position?: 'left' | 'right' | 'top' | 'hidden';
        price_alignment?: 'right' | 'inline';
    };
    spacing?: { density?: 'compact' | 'comfortable' | 'spacious' };
    header?: {
        show_restaurant_name?: boolean;
        tagline?: string;
        name_display_style?: 'default' | 'uppercase' | 'italic';
    };
    sections?: {
        divider_style?: 'line' | 'ornament' | 'none';
        title_alignment?: 'left' | 'center';
        numbering?: 'none' | 'roman' | 'arabic';
    };
}

const props = defineProps<{
    componentName: string;
    menu?: BodyMenu;
    customization?: MenuCustomization;
    interactive?: boolean;
    labels?: Record<string, string>;
}>();

const map: Record<string, unknown> = {
    Basic: BasicBody,
    Modern: ModernBody,
    Minimalist: MinimalistBody,
    Trattoria: TrattoriaBody,
    Neon: NeonBody,
    Botanica: BotanicaBody,
    Riviera: RivieraBody,
    Chapter: ChapterBody,
};

// Extraer nombre sin prefijo "Template" si lo tiene
function resolveComponent(name: string): unknown {
    const clean = name.replace(/^Template/, '');
    return map[clean] ?? map[name] ?? BasicBody;
}

const resolvedMenu = props.menu ?? fakeMenu;
</script>

<template>
    <component
        :is="resolveComponent(props.componentName)"
        :menu="resolvedMenu"
        :interactive="props.interactive ?? false"
        :labels="props.labels"
        :customization="props.customization"
    />
</template>
