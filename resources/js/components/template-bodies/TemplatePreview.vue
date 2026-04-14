<script setup lang="ts">
import BasicBody from './BasicBody.vue';
import ModernBody from './ModernBody.vue';
import MinimalistBody from './MinimalistBody.vue';
import TrattoriaBody from './TrattoriaBody.vue';
import NeonBody from './NeonBody.vue';
import BotanicaBody from './BotanicaBody.vue';
import RivieraBody from './RivieraBody.vue';
import ChapterBody from './ChapterBody.vue';
import { fakeMenu } from './fakeMenu';

const props = defineProps<{
    componentName: string;
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
</script>

<template>
    <component :is="resolveComponent(props.componentName)" :menu="fakeMenu" :interactive="false" />
</template>
