<script setup lang="ts">
import { Check } from 'lucide-vue-next';

const props = defineProps<{
    label: string;
    emoji: string;
    selected: boolean;
}>();

const emit = defineEmits<{
    select: [];
}>();
</script>

<template>
    <button
        type="button"
        :aria-pressed="props.selected"
        class="flex flex-col items-center gap-2 rounded-xl border-2 px-3 py-4 text-sm font-medium transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900"
        :class="
            props.selected
                ? 'border-teal-500 bg-teal-500/15 text-white'
                : 'border-slate-700 bg-slate-800/60 text-slate-400 hover:border-slate-500 hover:text-white'
        "
        @click="emit('select')"
    >
        <span class="relative text-2xl leading-none">
            {{ props.emoji }}
            <span
                v-if="props.selected"
                class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-teal-500"
            >
                <Check class="h-2.5 w-2.5 text-white" />
            </span>
        </span>
        <span class="text-xs leading-tight text-center">{{ props.label }}</span>
    </button>
</template>
