<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/solid';

const plans = [
    {
        name: 'Básico',
        price: '9.99',
        description: 'Para empezar',
        features: ['1 menú digital', 'QRs ilimitados', '50 productos', 'Soporte email'],
        highlighted: false,
    },
    {
        name: 'Profesional',
        price: '24.99',
        description: 'Crecimiento',
        features: ['5 menús digitales', 'QRs personalizados', 'Productos ilimitados', 'Analytics', 'Multi-idioma'],
        highlighted: true,
    },
    {
        name: 'Empresa',
        price: '49.99',
        description: 'Cadenas',
        features: ['Ilimitado', 'API completa', 'Branding total', 'Manager dedicado', 'POS', '24/7'],
        highlighted: false,
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

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto items-start">
                <div v-for="plan in plans" :key="plan.name"
                     :class="[
                         'relative rounded-3xl p-8 border transition-all duration-300',
                         plan.highlighted
                             ? 'bg-slate-900/80 border-purple-500 shadow-[0_0_30px_rgba(168,85,247,0.15)] z-10 transform md:-translate-y-4'
                             : 'bg-slate-950 border-slate-800 hover:border-slate-700'
                     ]">

                    <div v-if="plan.highlighted" class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-purple-600 to-pink-600 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg">
                        MÁS POPULAR
                    </div>

                    <h3 class="text-xl font-bold text-white mb-2">{{ plan.name }}</h3>
                    <p class="text-slate-400 text-sm mb-6">{{ plan.description }}</p>

                    <div class="mb-8 flex items-baseline">
                        <span class="text-4xl font-bold text-white">€{{ plan.price }}</span>
                        <span class="text-slate-500 ml-2">/mes</span>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start">
                            <CheckIcon class="w-5 h-5 text-purple-400 mr-3 mt-0.5" />
                            <span class="text-slate-300 text-sm">{{ feature }}</span>
                        </li>
                    </ul>

                    <Link :href="`/register?plan=${plan.name.toLowerCase()}`"
                          :class="[
                              'block w-full text-center py-3 px-6 rounded-xl font-bold transition-all',
                              plan.highlighted
                                  ? 'bg-white text-slate-950 hover:bg-slate-200'
                                  : 'bg-slate-800 text-white hover:bg-slate-700'
                          ]">
                        Seleccionar
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
