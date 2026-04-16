<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/app/FrontLayout.vue';

interface Props {
    tenant: {
        id: string
        name?: string
    }
}

defineProps<Props>()
</script>

<template>
    <Head :title="`Bienvenido a ${tenant.name || 'nuestro sitio'}`" />
    <AppLayout>
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-4 rounded-xl p-4">
            <h1 class="text-4xl font-bold">
                Bienvenido a {{ tenant.name }}
            </h1>
            <p class="text-muted-foreground">
                Esta es la página principal de tu tenant
            </p>
        </div>
    </AppLayout>
</template>
