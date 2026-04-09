<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/solid';

interface Plan {
    name: string;
    price: string | null;
    period: string;
    description: string;
    features: string[];
    highlighted: boolean;
    cta: string;
    ctaHref: string;
    badge?: string;
}

const plans: Plan[] = [
    {
        name: 'Free',
        price: '0',
        period: 'mes',
        description: 'Para empezar sin coste',
        features: [
            '1 local',
            '1 menú por local',
            '25 productos',
            'QR básico',
            'Branding MenuLinker',
        ],
        highlighted: false,
        cta: 'Empezar gratis',
        ctaHref: '/register',
    },
    {
        name: 'Pro',
        price: '14.99',
        period: 'mes',
        description: 'Para negocios en crecimiento',
        features: [
            '3 locales',
            '5 menús por local',
            'Productos ilimitados',
            'QR personalizado',
            'Analytics',
            '14 días de prueba gratuita',
        ],
        highlighted: true,
        badge: 'MÁS POPULAR',
        cta: 'Empezar prueba gratuita',
        ctaHref: '/register?plan=pro',
    },
    {
        name: 'Business',
        price: '34.99',
        period: 'mes',
        description: 'Para cadenas y grupos',
        features: [
            '10 locales',
            'Menús ilimitados',
            'Productos ilimitados',
            'Dominio personalizado',
            'Multi-idioma',
            'Analytics avanzados',
            '14 días de prueba gratuita',
        ],
        highlighted: false,
        cta: 'Empezar prueba gratuita',
        ctaHref: '/register?plan=business',
    },
    {
        name: 'Enterprise',
        price: null,
        period: 'mes',
        description: 'Para grandes corporaciones',
        features: [
            'Locales ilimitados',
            'Menús ilimitados',
            'Acceso API completo',
            'Dominio personalizado',
            'Multi-idioma',
            'Manager dedicado',
            'SLA 99.9%',
        ],
        highlighted: false,
        cta: 'Contactar',
        ctaHref: 'mailto:hello@menulinker.com',
    },
];
</script>

<template>
    <section class="py-24 bg-slate-950 border-t border-slate-900">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">Planes Simples</h2>
                <p class="text-xl text-slate-400">Escala tu negocio sin fricción</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto items-start">
                <div
                    v-for="plan in plans"
                    :key="plan.name"
                    :class="[
                        'relative rounded-3xl p-8 border transition-all duration-300',
                        plan.highlighted
                            ? 'bg-slate-900/80 border-purple-500 shadow-[0_0_30px_rgba(168,85,247,0.15)] z-10 lg:-translate-y-4'
                            : 'bg-slate-950 border-slate-800 hover:border-slate-700',
                    ]"
                >
                    <div
                        v-if="plan.badge"
                        class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg whitespace-nowrap"
                    >
                        {{ plan.badge }}
                    </div>

                    <h3 class="text-xl font-bold text-white mb-2">{{ plan.name }}</h3>
                    <p class="text-slate-400 text-sm mb-6">{{ plan.description }}</p>

                    <div class="mb-8 flex items-baseline">
                        <template v-if="plan.price !== null">
                            <span class="text-4xl font-bold text-white">€{{ plan.price }}</span>
                            <span class="text-slate-500 ml-2">/{{ plan.period }}</span>
                        </template>
                        <template v-else>
                            <span class="text-2xl font-bold text-white">A medida</span>
                        </template>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start">
                            <CheckIcon class="w-5 h-5 text-purple-400 mr-3 mt-0.5 shrink-0" />
                            <span class="text-slate-300 text-sm">{{ feature }}</span>
                        </li>
                    </ul>

                    <Link
                        :href="plan.ctaHref"
                        :class="[
                            'block w-full text-center py-3 px-6 rounded-xl font-bold transition-all',
                            plan.highlighted
                                ? 'bg-white text-slate-950 hover:bg-slate-200'
                                : 'bg-slate-800 text-white hover:bg-slate-700',
                        ]"
                    >
                        {{ plan.cta }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
