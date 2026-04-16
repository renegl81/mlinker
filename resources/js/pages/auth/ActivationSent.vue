<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Head, usePage } from '@inertiajs/vue3';
import { Mail } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage();
const messages = computed(() => page.props.messages as any);
const tActivation = (key: string) => messages.value.auth.register.activation[key] || key;
</script>

<template>
    <AuthBase
        :title="t('auth.activation_sent.title')"
        :description="t('auth.activation_sent.description')"
    >
        <Head :title="t('auth.activation_sent.title')" />

        <div class="flex flex-col items-center gap-6 text-center">
            <div
                class="flex h-16 w-16 items-center justify-center rounded-full bg-teal-50 ring-1 ring-teal-100"
            >
                <Mail class="h-8 w-8 text-teal-600" />
            </div>

            <div class="space-y-2">
                <p class="text-slate-600">{{ tActivation('sent') }}</p>
                <p class="text-sm text-slate-500">{{ tActivation('line2') }}</p>
            </div>

            <Button
                as-child
                class="h-11 w-full rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/20 hover:bg-teal-600"
            >
                <TextLink :href="login()">
                    {{ tActivation('back_login') }}
                </TextLink>
            </Button>
        </div>
    </AuthBase>
</template>
