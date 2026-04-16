<script setup lang="ts">
import { ref, watch, nextTick } from 'vue';
import { Pencil } from 'lucide-vue-next';

interface Props {
    modelValue: string;
    placeholder?: string;
    multiline?: boolean;
    as?: string;
    disabled?: boolean;
    emptyLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: '',
    multiline: false,
    as: 'span',
    disabled: false,
    emptyLabel: '',
});

const emit = defineEmits<{
    'save': [value: string];
}>();

const editing = ref(false);
const inputValue = ref(props.modelValue);
const inputRef = ref<HTMLInputElement | HTMLTextAreaElement | null>(null);

watch(() => props.modelValue, (v) => {
    if (!editing.value) inputValue.value = v;
});

async function startEdit() {
    if (props.disabled) return;
    inputValue.value = props.modelValue;
    editing.value = true;
    await nextTick();
    inputRef.value?.focus();
    if (inputRef.value instanceof HTMLInputElement || inputRef.value instanceof HTMLTextAreaElement) {
        const len = inputRef.value.value.length;
        inputRef.value.setSelectionRange(len, len);
    }
}

function commit() {
    if (!editing.value) return;
    editing.value = false;
    if (inputValue.value !== props.modelValue) {
        emit('save', inputValue.value);
    }
}

function cancel() {
    editing.value = false;
    inputValue.value = props.modelValue;
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
        cancel();
    } else if (e.key === 'Enter' && !props.multiline) {
        e.preventDefault();
        commit();
    } else if (e.key === 'Enter' && props.multiline && e.ctrlKey) {
        e.preventDefault();
        commit();
    }
}
</script>

<template>
    <span class="inline-editable-text group relative" :class="{ 'w-full block': multiline }">
        <template v-if="editing">
            <textarea
                v-if="multiline"
                ref="inputRef"
                v-model="inputValue"
                :placeholder="placeholder"
                class="w-full resize-none rounded border border-teal-400 bg-background px-2 py-1 text-sm outline-none ring-1 ring-teal-400/50 focus:ring-teal-500"
                rows="3"
                @blur="commit"
                @keydown="onKeydown"
            />
            <input
                v-else
                ref="inputRef"
                v-model="inputValue"
                type="text"
                :placeholder="placeholder"
                class="w-full rounded border border-teal-400 bg-background px-2 py-1 text-sm outline-none ring-1 ring-teal-400/50 focus:ring-teal-500"
                @blur="commit"
                @keydown="onKeydown"
            />
        </template>
        <template v-else>
            <span
                role="button"
                :tabindex="disabled ? -1 : 0"
                class="inline-edit-display"
                :class="[disabled ? 'cursor-default' : 'cursor-text hover:bg-teal-50/60 dark:hover:bg-teal-900/20 rounded px-1 -mx-1']"
                @click="startEdit"
                @keydown.enter="startEdit"
                @keydown.space.prevent="startEdit"
            >
                <slot>
                    <span :class="modelValue ? '' : 'text-muted-foreground italic'">
                        {{ modelValue || emptyLabel || placeholder }}
                    </span>
                </slot>
                <Pencil
                    v-if="!disabled"
                    class="ml-1 inline h-3 w-3 shrink-0 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-60"
                    aria-hidden="true"
                />
            </span>
        </template>
    </span>
</template>
