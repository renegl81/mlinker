<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { Pencil } from 'lucide-vue-next';

interface Props {
    modelValue: string | number | null;
    currency?: string;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    currency: '€',
    disabled: false,
});

const emit = defineEmits<{
    'save': [value: number | null];
}>();

const editing = ref(false);
const inputValue = ref<string>('');
const inputRef = ref<HTMLInputElement | null>(null);

function toDisplayString(val: string | number | null): string {
    if (val === null || val === undefined || val === '') return '';
    const n = typeof val === 'string' ? parseFloat(val) : val;
    if (Number.isNaN(n)) return '';
    return n.toFixed(2);
}

watch(() => props.modelValue, (v) => {
    if (!editing.value) inputValue.value = toDisplayString(v);
}, { immediate: true });

async function startEdit() {
    if (props.disabled) return;
    inputValue.value = toDisplayString(props.modelValue);
    editing.value = true;
    await nextTick();
    inputRef.value?.focus();
    inputRef.value?.select();
}

function commit() {
    if (!editing.value) return;
    editing.value = false;
    const parsed = parseFloat(inputValue.value.replace(',', '.'));
    const newValue = Number.isNaN(parsed) ? null : parsed;
    const currentNum = props.modelValue === null ? null : (typeof props.modelValue === 'string' ? parseFloat(props.modelValue) : props.modelValue);
    if (newValue !== currentNum) {
        emit('save', newValue);
    }
}

function cancel() {
    editing.value = false;
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') cancel();
    if (e.key === 'Enter') { e.preventDefault(); commit(); }
}
</script>

<template>
    <span class="inline-editable-price group relative">
        <template v-if="editing">
            <span class="inline-flex items-center gap-1">
                <input
                    ref="inputRef"
                    v-model="inputValue"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-20 rounded border border-teal-400 bg-background px-2 py-1 text-sm outline-none ring-1 ring-teal-400/50 focus:ring-teal-500 text-right"
                    @blur="commit"
                    @keydown="onKeydown"
                />
                <span class="text-xs text-muted-foreground">{{ currency }}</span>
            </span>
        </template>
        <template v-else>
            <span
                role="button"
                :tabindex="disabled ? -1 : 0"
                class="inline-flex items-center gap-0.5"
                :class="disabled ? 'cursor-default' : 'cursor-text rounded px-1 -mx-1 hover:bg-teal-50/60 dark:hover:bg-teal-900/20'"
                @click="startEdit"
                @keydown.enter="startEdit"
            >
                <span class="font-semibold text-primary tabular-nums">
                    {{ modelValue !== null && modelValue !== '' ? `${toDisplayString(modelValue)} ${currency}` : '–' }}
                </span>
                <Pencil
                    v-if="!disabled"
                    class="ml-0.5 h-3 w-3 shrink-0 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-60"
                    aria-hidden="true"
                />
            </span>
        </template>
    </span>
</template>
