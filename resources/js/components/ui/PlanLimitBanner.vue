<script setup lang="ts">
import { computed } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ExclamationTriangleIcon, XCircleIcon } from '@heroicons/vue/24/solid';

const props = defineProps<{
    resource: string;
    current: number;
    max: number;
    upgradeUrl: string;
}>();

const isUnlimited = computed(() => props.max === 0);
const isAtLimit = computed(() => props.current >= props.max);
const isNearLimit = computed(() => props.current >= props.max * 0.8);

const resourceLabel = computed(() => {
    const labels: Record<string, string> = {
        locations: 'locales',
        menus: 'menús',
        products: 'productos',
        images: 'imágenes',
    };
    return labels[props.resource] ?? props.resource;
});
</script>

<template>
    <template v-if="!isUnlimited">
        <Alert v-if="isAtLimit" variant="destructive" class="mb-4">
            <XCircleIcon class="size-4" />
            <AlertTitle>Límite alcanzado</AlertTitle>
            <AlertDescription class="flex items-center justify-between gap-4">
                <span>
                    Has alcanzado el límite de {{ max }} {{ resourceLabel }} de tu plan actual.
                </span>
                <a
                    :href="upgradeUrl"
                    class="shrink-0 rounded-lg bg-destructive px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90 transition-opacity"
                >
                    Mejorar plan
                </a>
            </AlertDescription>
        </Alert>

        <Alert v-else-if="isNearLimit" class="mb-4 border-yellow-500 bg-yellow-50 text-yellow-900 dark:bg-yellow-950 dark:text-yellow-200">
            <ExclamationTriangleIcon class="size-4 text-yellow-600 dark:text-yellow-400" />
            <AlertTitle>Casi en el límite</AlertTitle>
            <AlertDescription class="flex items-center justify-between gap-4">
                <span>
                    Llevas {{ current }} de {{ max }} {{ resourceLabel }} disponibles en tu plan.
                </span>
                <a
                    :href="upgradeUrl"
                    class="shrink-0 rounded-lg bg-yellow-600 px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90 transition-opacity"
                >
                    Mejorar plan
                </a>
            </AlertDescription>
        </Alert>
    </template>
</template>
