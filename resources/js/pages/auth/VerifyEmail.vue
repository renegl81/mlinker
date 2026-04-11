<script setup lang="ts">
import EmailVerificationNotificationController from '@/actions/App/Http/Controllers/Auth/EmailVerificationNotificationController';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
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
        :title="t('auth.verify.title')"
        :description="t('auth.verify.description')"
    >
        <Head :title="t('auth.verify.head')" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-center text-sm font-medium text-teal-700"
        >
            {{ t('auth.verify.sent') }}
        </div>

        <Form
            v-bind="EmailVerificationNotificationController.store.form()"
            class="flex flex-col gap-4 text-center"
            v-slot="{ processing }"
        >
            <Button
                :disabled="processing"
                class="h-11 w-full rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/20 hover:bg-teal-600"
            >
                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                {{ t('auth.verify.resend') }}
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm text-slate-500 hover:text-teal-600"
            >
                {{ t('auth.verify.logout') }}
            </TextLink>
        </Form>
    </AuthLayout>
</template>
