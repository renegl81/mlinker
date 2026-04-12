<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { usePage } from '@inertiajs/vue3';

defineOptions({
    inheritAttrs: false,
});

const appName = usePage().props.name as string;
</script>

<template>
    <div class="flex items-center gap-2" v-bind="$attrs">
        <AppLogoIcon class="h-8 w-8 object-contain" />
        <div class="grid flex-1 text-left text-sm">
            <span class="truncate leading-tight font-semibold">{{ appName }}</span>
        </div>
    </div>
</template>
