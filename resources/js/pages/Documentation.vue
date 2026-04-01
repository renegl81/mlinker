<script setup lang="ts">
import AppLayout from '@/layouts/app/FrontLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
    {
        title: 'Documentation',
        href: '/doc',
    },
];
</script>

<template>
    <Head>
        <meta name="description" content="MenuLinker - Crea menús digitales con códigos QR para tu restaurante. Aumenta ventas y mejora la experiencia del cliente.">
        <meta name="keywords" content="menu digital, codigo qr, restaurante, hosteleria, menu online, qr menu">
        <meta name="author" content="MenuLinker">
        <title>FlowSuite - Menús Digitales con Código QR para Restaurantes</title>
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-white">
            <h1>Documentación</h1>
        </div>
    </AppLayout>
</template>
