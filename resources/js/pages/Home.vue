<script setup lang="ts">
import CtaSection from '@/components/home/CtaSection.vue';
import FeaturesSection from '@/components/home/FeaturesSection.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import PricingSection from '@/components/home/PricingSection.vue';
import TestimonialsSection from '@/components/home/TestimonialsSection.vue';
import AppLayout from '@/layouts/app/FrontLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Home',
        href: '/',
    },
];
</script>

<template>
    <Head>
        <meta name="description" content="MenuLinker - Crea menús digitales con códigos QR.">
        <title>FlowSuite - Menús Digitales</title>
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-slate-950 text-slate-200 selection:bg-purple-500 selection:text-white">
            <HeroSection />
            <FeaturesSection />
            <PricingSection />
            <TestimonialsSection />
            <CtaSection />
        </div>
    </AppLayout>
</template>
