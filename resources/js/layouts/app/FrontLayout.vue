<script setup lang="ts">
import FooterSection from '@/components/home/FooterSection.vue';
import FrontHeader from '@/components/FrontHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="front-page flex min-h-screen flex-col bg-white text-slate-900 antialiased">
        <FrontHeader :breadcrumbs="breadcrumbs" />
        <main class="flex-1">
            <slot />
        </main>
        <FooterSection />
    </div>
</template>

<style>
.front-page {
    font-family: 'Plus Jakarta Sans', 'Inter', ui-sans-serif, system-ui, sans-serif;
}
</style>
