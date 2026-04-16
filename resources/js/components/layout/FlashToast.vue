<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { CheckCircle, X, XCircle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

interface Flash {
    success?: string | null;
    error?: string | null;
}

const flash = computed(() => page.props.flash as Flash | null);

interface ToastItem {
    id: number;
    type: 'success' | 'error';
    message: string;
    visible: boolean;
}

const toasts = ref<ToastItem[]>([]);
let nextId = 0;

function addToast(type: 'success' | 'error', message: string) {
    const id = nextId++;
    toasts.value.push({ id, type, message, visible: true });
    setTimeout(() => dismissToast(id), 4000);
}

function dismissToast(id: number) {
    const toast = toasts.value.find((t) => t.id === id);
    if (toast) {
        toast.visible = false;
        setTimeout(() => {
            toasts.value = toasts.value.filter((t) => t.id !== id);
        }, 300);
    }
}

watch(
    flash,
    (newFlash) => {
        if (newFlash?.success) addToast('success', newFlash.success);
        if (newFlash?.error) addToast('error', newFlash.error);
    },
    { immediate: true },
);
</script>

<template>
    <Teleport to="body">
        <div
            class="pointer-events-none fixed inset-x-0 top-[60px] z-50 flex flex-col items-center gap-2 px-4 md:left-auto md:right-4 md:top-[72px] md:items-end md:px-0"
        >
            <TransitionGroup
                tag="div"
                class="flex flex-col items-center gap-2 md:items-end"
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-[-8px]"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 translate-y-[-8px]"
            >
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    v-show="toast.visible"
                    class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border p-4 shadow-lg"
                    :class="
                        toast.type === 'success'
                            ? 'border-green-200 bg-white text-green-900 dark:border-green-800 dark:bg-green-950 dark:text-green-100'
                            : 'border-red-200 bg-white text-red-900 dark:border-red-800 dark:bg-red-950 dark:text-red-100'
                    "
                    role="alert"
                    :aria-live="toast.type === 'error' ? 'assertive' : 'polite'"
                >
                    <CheckCircle
                        v-if="toast.type === 'success'"
                        class="mt-0.5 size-5 shrink-0 text-green-500"
                    />
                    <XCircle
                        v-else
                        class="mt-0.5 size-5 shrink-0 text-red-500"
                    />

                    <p class="flex-1 text-sm font-medium leading-snug">
                        {{ toast.message }}
                    </p>

                    <button
                        class="shrink-0 rounded-md p-0.5 opacity-60 transition-opacity hover:opacity-100"
                        :aria-label="t('common.close')"
                        @click="dismissToast(toast.id)"
                    >
                        <X class="size-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
