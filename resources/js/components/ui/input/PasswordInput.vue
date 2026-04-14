<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    modelValue?: string;
    autocomplete?: string;
    placeholder?: string;
    tabindex?: number;
    name?: string;
    id?: string;
    required?: boolean;
    'aria-invalid'?: boolean | string;
    class?: string;
}

const props = defineProps<Props>();
const emits = defineEmits<{
    (e: 'update:modelValue', payload: string): void;
}>();

const show = ref(false);

const onInput = (e: Event) => {
    emits('update:modelValue', (e.target as HTMLInputElement).value);
};
</script>

<template>
    <div class="relative">
        <input
            v-bind="$attrs"
            :value="modelValue"
            :type="show ? 'text' : 'password'"
            :autocomplete="autocomplete"
            :placeholder="placeholder"
            :tabindex="tabindex"
            :name="name"
            :id="id"
            :required="required"
            :aria-invalid="$props['aria-invalid']"
            data-slot="input"
            :class="cn(
                'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-lg border bg-transparent px-3 py-1 pr-10 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                props.class,
            )"
            @input="onInput"
        />
        <button
            type="button"
            tabindex="-1"
            :aria-label="show ? $t('auth.common.hide_password') : $t('auth.common.show_password')"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-muted-foreground hover:text-foreground focus:outline-none"
            @click="show = !show"
        >
            <EyeOff v-if="show" class="h-4 w-4" aria-hidden="true" />
            <Eye v-else class="h-4 w-4" aria-hidden="true" />
        </button>
    </div>
</template>
