<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/app/FrontLayout.vue';
import { BreadcrumbItemType } from '@/types';
import HeroSection from '@/components/home/HeroSection.vue';
import FeaturesSection from '@/components/home/FeaturesSection.vue';
import PricingSection from '@/components/home/PricingSection.vue';
import TestimonialsSection from '@/components/home/TestimonialsSection.vue';
import CtaSection from '@/components/home/CtaSection.vue';

const breadcrumbs = <BreadcrumbItemType[]>{
    'Home': 'Home',
}
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
            <HeroSection />
            <FeaturesSection />
            <PricingSection />
            <TestimonialsSection />
            <CtaSection />
        </div>
    </AppLayout>
</template>
