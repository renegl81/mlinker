<script setup lang="ts">
import { Check, Loader2, AlertCircle } from 'lucide-vue-next';

type Status = 'idle' | 'saving' | 'saved' | 'error';

interface Props {
    status: Status;
    errorMessage?: string;
}

const props = withDefaults(defineProps<Props>(), {
    errorMessage: '',
});
</script>

<template>
    <div
        class="save-indicator flex items-center gap-1.5 text-xs transition-all duration-300"
        aria-live="polite"
        role="status"
    >
        <template v-if="status === 'saving'">
            <Loader2 class="h-3.5 w-3.5 animate-spin text-teal-600" />
            <span class="text-teal-700">Guardando…</span>
        </template>
        <template v-else-if="status === 'saved'">
            <Check class="h-3.5 w-3.5 text-teal-600" />
            <span class="text-teal-700">Guardado</span>
        </template>
        <template v-else-if="status === 'error'">
            <AlertCircle class="h-3.5 w-3.5 text-destructive" />
            <span class="text-destructive">{{ errorMessage || 'Error al guardar' }}</span>
        </template>
    </div>
</template>
