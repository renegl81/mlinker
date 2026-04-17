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
        class="flex flex-col items-center gap-2 rounded-xl border-2 px-3 py-4 text-sm font-medium transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:ring-offset-2 focus-visible:ring-offset-background"
        :class="
            props.selected
                ? 'border-primary bg-primary/10 text-foreground'
                : 'border-border bg-card text-muted-foreground hover:border-border/80 hover:text-foreground hover:bg-accent'
        "
        @click="emit('select')"
    >
        <span class="relative text-2xl leading-none">
            {{ props.emoji }}
            <span
                v-if="props.selected"
                class="absolute -right-1.5 -top-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-primary"
            >
                <Check class="h-2.5 w-2.5 text-primary-foreground" />
            </span>
        </span>
        <span class="text-xs leading-tight text-center">{{ props.label }}</span>
    </button>
</template>
