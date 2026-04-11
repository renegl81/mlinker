<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

defineOptions({
    inheritAttrs: false,
});
</script>

<template>
    <div class="flex items-center gap-2" v-bind="$attrs">
        <AppLogoIcon class="h-8 w-8 object-contain" />
        <div class="grid flex-1 text-left text-sm">
            <span class="truncate leading-tight font-semibold">MenuLinker</span>
        </div>
    </div>
</template>
