<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Head, router, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const page = usePage();
const messages = computed(() => page.props.messages as any);
const t = (key: string) => messages.value.auth.register[key] || key;

const form = ref({
    name: '',
    last_name: '',
    email: '',
    tenant_name: '',
    tenant_domain: '',
    password: '',
    password_confirmation: '',
});

const processing = ref(false);
const errors = ref<Record<string, string>>({});

// Generar dominio automáticamente
watch(
    () => form.value.tenant_name,
    (newValue) => {
        if (newValue) {
            form.value.tenant_domain = newValue
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    },
);

const submit = () => {
    processing.value = true;
    errors.value = {};

    router.post('/register', form.value, {
        onError: (responseErrors) => {
            errors.value = responseErrors;
        },
        onSuccess: () => {
            form.value.password = '';
            form.value.password_confirmation = '';
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <AuthBase :title="t('title')" :description="t('description')">
        <Head title="Registro" />

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">{{ t('name') }}</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="given-name"
                        name="name"
                        :placeholder="t('name_placeholder')"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="last_name">{{ t('last_name') }}</Label>
                    <Input
                        id="last_name"
                        v-model="form.last_name"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="family-name"
                        name="last_name"
                        :placeholder="t('last_name_placeholder')"
                    />
                    <InputError :message="errors.last_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{ t('email') }}</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        :tabindex="3"
                        autocomplete="email"
                        name="email"
                        :placeholder="t('email_placeholder')"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="tenant_name">{{ t('tenant_name') }}</Label>
                    <Input
                        id="tenant_name"
                        v-model="form.tenant_name"
                        type="text"
                        required
                        :tabindex="4"
                        name="tenant_name"
                        :placeholder="t('tenant_name_placeholder')"
                    />
                    <InputError :message="errors.tenant_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="tenant_domain">{{ t('tenant_domain') }}</Label>
                    <Input
                        id="tenant_domain"
                        v-model="form.tenant_domain"
                        type="text"
                        required
                        :tabindex="5"
                        name="tenant_domain"
                        :placeholder="t('tenant_domain_placeholder')"
                    />
                    <p class="text-xs text-muted-foreground">
                        {{ t('tenant_domain_help') }}
                    </p>
                    <InputError :message="errors.tenant_domain" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{ t('password') }}</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        :tabindex="6"
                        autocomplete="new-password"
                        name="password"
                        :placeholder="t('password_placeholder')"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">{{
                        t('password_confirmation')
                    }}</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        name="password_confirmation"
                        :placeholder="t('password_confirmation_placeholder')"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    variant="outline"
                    type="submit"
                    class="mt-2 w-full border-purple-500"
                    :tabindex="8"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
                    {{ t('submit') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ t('already_registered') }}
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="9"
                    >{{ t('login_link') }}</TextLink
                >
            </div>
        </form>
    </AuthBase>
</template>
