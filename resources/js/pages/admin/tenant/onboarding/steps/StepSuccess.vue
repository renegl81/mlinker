<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { LayoutDashboard, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Menu {
    id: number;
    name: string;
}

const props = defineProps<{
    menu: Menu | null;
}>();

const processing = ref(false);

function finish() {
    if (!props.menu) return;
    processing.value = true;
    router.post(
        route('tenant.onboarding.complete'),
        { menu_id: props.menu.id },
        { onFinish: () => (processing.value = false) },
    );
}
</script>

<template>
    <div class="flex flex-col items-center text-center">
        <!-- Celebration icon -->
        <div class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 text-5xl shadow-lg shadow-teal-500/30">
            🎊
        </div>

        <h1 class="mb-2 text-2xl font-bold text-white">
            {{ t('panel.onboarding.success_title') }}
        </h1>
        <p class="mb-8 max-w-sm text-sm text-slate-400">
            {{ t('panel.onboarding.success_body') }}
        </p>

        <!-- CTAs -->
        <div class="flex w-full max-w-xs flex-col gap-3">
            <!-- Primary: publish/finish -->
            <button
                type="button"
                :disabled="processing || !props.menu"
                class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                @click="finish"
            >
                <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                <LayoutDashboard v-else class="h-4 w-4" />
                <span>{{ t('panel.onboarding.success_go_panel') }}</span>
            </button>
        </div>
    </div>
</template>
