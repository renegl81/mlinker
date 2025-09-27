<script setup lang="ts">

import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/FrontLayout.vue';
import { defineComponent } from 'vue';

const breadcrumbs = {
    'Home': 'Home',
}
</script>

<template>
    <Head>
        <meta name="description" content="Descripción de la página de inicio">
        <meta name="keywords" content="inicio, vue, inertia, ejemplo">
        <meta name="author" content="Tu Nombre">
        <title>Página de Inicio</title>
    </Head>
    <AppLayout :breadcrumbs="breadcrumbs">

    </AppLayout>
</template>

<style scoped>

</style>
