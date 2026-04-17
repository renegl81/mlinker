<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Store } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
</script>

<template>
    <div>
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
            <Store class="h-6 w-6 text-primary" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-foreground">
            {{ t('panel.onboarding.basics_title', { userName: props.userName, tenantName: props.tenantName }) }}
        </h1>
        <p class="mb-6 text-sm text-muted-foreground">
            {{ t('panel.onboarding.basics_subtitle') }}
        </p>

        <!-- Nombre del local (solo lectura, prerellenado) -->
        <div class="mb-5 rounded-xl border border-primary/20 bg-primary/5 px-4 py-3">
            <p class="text-xs font-medium text-primary/70 uppercase tracking-wider mb-1">Tu local</p>
            <p class="font-semibold text-foreground text-lg">{{ props.tenantName }}</p>
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <Label for="ob-city">{{ t('panel.onboarding.city') }}</Label>
                    <Input
                        id="ob-city"
                        v-model="form.city"
                        type="text"
                        :placeholder="t('panel.onboarding.city_placeholder')"
                    />
                </div>
                <div class="space-y-2">
                    <Label for="ob-phone">{{ t('panel.onboarding.phone') }}</Label>
                    <Input
                        id="ob-phone"
                        v-model="form.phone"
                        type="text"
                        placeholder="+34 600 000 000"
                    />
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-1">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full"
                    size="lg"
                >
                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                    <span>{{ t('panel.onboarding.continue') }}</span>
                    <span v-if="!form.processing">→</span>
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="text-muted-foreground"
                    @click="skip"
                >
                    {{ t('panel.onboarding.skip') }}
                </Button>
            </div>
        </form>
    </div>
</template>
