<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Head, usePage } from '@inertiajs/vue3';
import { Mail } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const messages = computed(() => page.props.messages as any);
const t = (key: string) => messages.value.auth.register.activation[key] || key;
</script>

<template>
    <AuthBase
        title="Revisa tu correo"
        description="Te hemos enviado un enlace de activación"
    >
        <Head title="Activación de cuenta" />

        <div class="flex flex-col items-center gap-6 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                <Mail class="h-8 w-8 text-primary" />
            </div>

            <div class="space-y-2">
                <p class="text-muted-foreground">
                    {{ t('sent') }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ t('line2') }}
                </p>
            </div>

            <Button
                as-child
                variant="outline"
                class="w-full border-b-purple-500"
            >
                <TextLink :href="login()">
                    {{ t('back_login') }}
                </TextLink>
            </Button>
        </div>
    </AuthBase>
</template>
