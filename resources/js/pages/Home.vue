<script setup lang="ts">
import CtaSection from '@/components/home/CtaSection.vue';
import FeaturesSection from '@/components/home/FeaturesSection.vue';
import HeroSection from '@/components/home/HeroSection.vue';
import PricingSection from '@/components/home/PricingSection.vue';
import TestimonialsSection from '@/components/home/TestimonialsSection.vue';
import FrontLayout from '@/layouts/app/FrontLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Plan {
    name: string;
    slug: string;
    price: string | null;
    period: string;
    description: string;
    max_locations: number;
    max_menus_per_location: number;
    max_products: number;
    has_analytics: boolean;
    has_custom_qr: boolean;
    has_multilang: boolean;
    has_catalog: boolean;
    has_team: boolean;
    has_api_access: boolean;
    has_custom_domain: boolean;
    show_branding: boolean;
    trial_days: number;
}

interface Seo {
    title: string;
    description: string;
    url: string;
    image: string;
}

interface Props {
    plans: Plan[];
    seo: Seo;
}

const props = defineProps<Props>();

const jsonLdOrganization = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'Organization',
    name: 'MenuLinker',
    url: props.seo.url,
    logo: `${props.seo.url}/favicon.svg`,
    description: props.seo.description,
    sameAs: [],
}));

const jsonLdWebSite = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: 'MenuLinker',
    url: props.seo.url,
    description: props.seo.description,
    potentialAction: {
        '@type': 'SearchAction',
        target: `${props.seo.url}/search?q={search_term_string}`,
        'query-input': 'required name=search_term_string',
    },
}));

const jsonLdFaq = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: [
        {
            '@type': 'Question',
            name: '¿Cuánto cuesta MenuLinker?',
            acceptedAnswer: {
                '@type': 'Answer',
                text: 'MenuLinker tiene un plan gratuito para siempre con 1 local y 1 menú. Los planes de pago empiezan en 14,99€/mes con prueba gratuita de 14 días.',
            },
        },
        {
            '@type': 'Question',
            name: '¿Cómo funciona el menú digital con QR?',
            acceptedAnswer: {
                '@type': 'Answer',
                text: 'Creas tu carta desde el panel, eliges una plantilla y generas un código QR personalizado. Tus clientes escanean el QR con su móvil y ven tu menú actualizado al instante, sin necesidad de instalar ninguna app.',
            },
        },
        {
            '@type': 'Question',
            name: '¿Puedo traducir mi menú a varios idiomas?',
            acceptedAnswer: {
                '@type': 'Answer',
                text: 'Sí. Con los planes Business y Enterprise, MenuLinker traduce automáticamente tu carta a inglés, francés, alemán, italiano, portugués y catalán. Puedes editar cada traducción manualmente.',
            },
        },
        {
            '@type': 'Question',
            name: '¿Se muestran los alérgenos en el menú?',
            acceptedAnswer: {
                '@type': 'Answer',
                text: 'Sí. Puedes asignar alérgenos oficiales (gluten, crustáceos, huevos, etc.) a cada plato y se muestran con iconos claros en la carta digital, cumpliendo la normativa europea.',
            },
        },
    ],
}));
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.url" />

        <!-- Open Graph -->
        <meta property="og:type" content="website" />
        <meta property="og:title" :content="seo.title" />
        <meta property="og:description" :content="seo.description" />
        <meta property="og:url" :content="seo.url" />
        <meta property="og:image" :content="seo.image" />
        <meta property="og:site_name" content="MenuLinker" />
        <meta property="og:locale" content="es_ES" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="seo.title" />
        <meta name="twitter:description" :content="seo.description" />
        <meta name="twitter:image" :content="seo.image" />

        <!-- JSON-LD -->
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="jsonLdOrganization" />
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="jsonLdWebSite" />
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="jsonLdFaq" />
    </Head>

    <FrontLayout>
        <HeroSection />
        <FeaturesSection />
        <PricingSection :plans="plans" />
        <TestimonialsSection />
        <CtaSection />
    </FrontLayout>
</template>
