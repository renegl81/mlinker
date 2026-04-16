<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

defineOptions({
    inheritAttrs: false,
});

const appName = usePage().props.name as string;
</script>

<template>
    <div class="flex items-center" v-bind="$attrs">
        <!-- Desktop: logo with name (light/dark) -->
        <img src="/images/logo-name.png" :alt="appName" class="hidden sm:block dark:hidden h-8 object-contain" />
        <img src="/images/logo-light.png" :alt="appName" class="hidden dark:sm:block h-8 object-contain" />
        <!-- Mobile: icon only -->
        <img src="/images/logo.png" :alt="appName" class="block sm:hidden h-8 w-8 object-contain" />
    </div>
</template>
