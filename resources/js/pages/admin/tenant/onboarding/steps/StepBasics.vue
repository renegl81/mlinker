<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Store } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    tenantName: string;
    userName: string;
}>();

const emit = defineEmits<{
    skip: [];
}>();

const form = useForm({
    city: '',
    phone: '',
});

function submit() {
    form.post(route('tenant.onboarding.basics'));
}

function skip() {
    emit('skip');
    form.post(route('tenant.onboarding.basics'));
}

const inputClass =
    'flex h-11 w-full rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-2 text-sm text-white placeholder:text-slate-500 transition-colors focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30';
const labelClass = 'text-sm font-medium text-slate-300';
</script>

<template>
    <div>
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
            <Store class="h-6 w-6 text-teal-400" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-white">
            {{ t('panel.onboarding.basics_title', { userName: props.userName, tenantName: props.tenantName }) }}
        </h1>
        <p class="mb-6 text-sm text-slate-400">
            {{ t('panel.onboarding.basics_subtitle') }}
        </p>

        <!-- Nombre del local (solo lectura, prerellenado) -->
        <div class="mb-5 rounded-xl border border-teal-500/30 bg-teal-500/5 px-4 py-3">
            <p class="text-xs font-medium text-teal-400/70 uppercase tracking-wider mb-1">Tu local</p>
            <p class="font-semibold text-white text-lg">{{ props.tenantName }}</p>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="ob-city" :class="labelClass">{{ t('panel.onboarding.city') }}</label>
                    <input
                        id="ob-city"
                        v-model="form.city"
                        type="text"
                        :placeholder="t('panel.onboarding.city_placeholder')"
                        :class="inputClass"
                    />
                </div>
                <div class="space-y-2">
                    <label for="ob-phone" :class="labelClass">{{ t('panel.onboarding.phone') }}</label>
                    <input
                        id="ob-phone"
                        v-model="form.phone"
                        type="text"
                        placeholder="+34 600 000 000"
                        :class="inputClass"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-1">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span>{{ t('panel.onboarding.continue') }}</span>
                    <span v-if="!form.processing">→</span>
                </button>
                <button
                    type="button"
                    class="text-sm text-slate-500 transition-colors hover:text-slate-300 underline underline-offset-2"
                    @click="skip"
                >
                    {{ t('panel.onboarding.skip') }}
                </button>
            </div>
        </form>
    </div>
</template>
