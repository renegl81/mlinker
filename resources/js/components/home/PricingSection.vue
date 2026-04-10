<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/solid';

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

interface Props {
    plans: Plan[];
}

const props = defineProps<Props>();

function features(plan: Plan): string[] {
    const f: string[] = [];

    if (plan.max_locations === 0) f.push('Locales ilimitados');
    else f.push(`${plan.max_locations} ${plan.max_locations === 1 ? 'local' : 'locales'}`);

    if (plan.max_menus_per_location === 0) f.push('Menús ilimitados');
    else f.push(`${plan.max_menus_per_location} ${plan.max_menus_per_location === 1 ? 'menú' : 'menús'} por local`);

    if (plan.max_products === 0 && plan.slug !== 'free') f.push('Productos ilimitados');
    else if (plan.max_products > 0) f.push(`${plan.max_products} productos`);

    if (plan.has_custom_qr) f.push('QR personalizado');
    else f.push('QR básico');

    if (plan.has_analytics) f.push('Analytics');
    if (plan.has_multilang) f.push('Multi-idioma automático');
    if (plan.has_catalog) f.push('Catálogo centralizado');
    if (plan.has_team) f.push('Gestión de equipo');
    if (plan.has_custom_domain) f.push('Dominio personalizado');
    if (plan.has_api_access) f.push('Acceso API completo');
    if (plan.show_branding) f.push('Branding MenuLinker');
    if (plan.trial_days > 0) f.push(`${plan.trial_days} días de prueba gratuita`);

    return f;
}

function isHighlighted(plan: Plan): boolean {
    return plan.slug === 'pro';
}

function ctaText(plan: Plan): string {
    if (plan.slug === 'free') return 'Empezar gratis';
    if (plan.slug === 'enterprise') return 'Contactar';
    if (plan.trial_days > 0) return 'Empezar prueba gratuita';
    return 'Suscribirse';
}

function ctaHref(plan: Plan): string {
    if (plan.slug === 'enterprise') return 'mailto:hello@menulinker.com';
    if (plan.slug === 'free') return '/register';
    return `/register?plan=${plan.slug}`;
}
</script>

<template>
    <section id="pricing" class="py-24 bg-slate-950 border-t border-slate-900">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-4">Planes Simples</h2>
                <p class="text-xl text-slate-400">Escala tu negocio sin fricción</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 max-w-7xl mx-auto items-start">
                <div
                    v-for="plan in plans"
                    :key="plan.slug"
                    :class="[
                        'relative rounded-3xl p-8 border transition-all duration-300',
                        isHighlighted(plan)
                            ? 'bg-slate-900/80 border-teal-500 shadow-[0_0_30px_rgba(20,184,166,0.15)] z-10 lg:-translate-y-4'
                            : 'bg-slate-950 border-slate-800 hover:border-slate-700',
                    ]"
                >
                    <div
                        v-if="isHighlighted(plan)"
                        class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-teal-600 to-cyan-600 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg whitespace-nowrap"
                    >
                        MÁS POPULAR
                    </div>

                    <h3 class="text-xl font-bold text-white mb-2">{{ plan.name }}</h3>
                    <p class="text-slate-400 text-sm mb-6">{{ plan.description }}</p>

                    <div class="mb-8 flex items-baseline">
                        <template v-if="plan.price !== null && Number(plan.price) > 0">
                            <span class="text-4xl font-bold text-white">€{{ plan.price }}</span>
                            <span class="text-slate-500 ml-2">/{{ plan.period }}</span>
                        </template>
                        <template v-else-if="plan.slug === 'enterprise'">
                            <span class="text-2xl font-bold text-white">A medida</span>
                        </template>
                        <template v-else>
                            <span class="text-4xl font-bold text-white">Gratis</span>
                        </template>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li v-for="feature in features(plan)" :key="feature" class="flex items-start">
                            <CheckIcon class="w-5 h-5 text-teal-400 mr-3 mt-0.5 shrink-0" />
                            <span class="text-slate-300 text-sm">{{ feature }}</span>
                        </li>
                    </ul>

                    <Link
                        :href="ctaHref(plan)"
                        :class="[
                            'block w-full text-center py-3 px-6 rounded-xl font-bold transition-all',
                            isHighlighted(plan)
                                ? 'bg-white text-slate-950 hover:bg-slate-200'
                                : 'bg-slate-800 text-white hover:bg-slate-700',
                        ]"
                    >
                        {{ ctaText(plan) }}
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
