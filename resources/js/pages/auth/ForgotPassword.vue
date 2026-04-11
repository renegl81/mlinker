<script setup lang="ts">
import PasswordResetLinkController from '@/actions/App/Http/Controllers/Auth/PasswordResetLinkController';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        :title="t('auth.forgot.title')"
        :description="t('auth.forgot.description')"
    >
        <Head :title="t('auth.forgot.head')" />

        <div
            v-if="status"
            class="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-center text-sm font-medium text-teal-700"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form
                v-bind="PasswordResetLinkController.store.form()"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
                <div class="grid gap-2">
                    <Label for="email">{{ t('auth.forgot.email_label') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        :placeholder="t('auth.forgot.email_placeholder')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <Button
                    class="mt-2 h-11 w-full rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/20 hover:bg-teal-600"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
                    {{ t('auth.forgot.submit') }}
                </Button>
            </Form>

            <div class="text-center text-sm text-slate-500">
                {{ t('auth.forgot.back_prefix') }}
                <TextLink :href="login()" class="font-medium text-teal-600 hover:text-teal-700">
                    {{ t('auth.forgot.back_link') }}
                </TextLink>
            </div>
        </div>
    </AuthLayout>
</template>
