<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { PasswordInput } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <AuthLayout
        :title="t('auth.confirm.title')"
        :description="t('auth.confirm.description')"
    >
        <Head :title="t('auth.confirm.head')" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-2">
                <Label htmlFor="password">{{ t('auth.confirm.password_label') }}</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError :message="errors.password" />
            </div>

            <Button
                class="mt-2 h-11 w-full rounded-full bg-teal-500 text-white shadow-md shadow-teal-500/20 hover:bg-teal-600"
                :disabled="processing"
                data-test="confirm-password-button"
            >
                <LoaderCircle
                    v-if="processing"
                    class="h-4 w-4 animate-spin"
                />
                {{ t('auth.confirm.submit') }}
            </Button>
        </Form>
    </AuthLayout>
</template>
