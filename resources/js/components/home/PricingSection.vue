<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/solid';

const plans = [
    {
        name: 'Básico',
        price: '9.99',
        description: 'Perfecto para empezar',
        features: [
            '1 menú digital',
            'Códigos QR ilimitados',
            'Hasta 50 productos',
            'Soporte por email',
        ],
        highlighted: false,
    },
    {
        name: 'Profesional',
        price: '24.99',
        description: 'Para negocios en crecimiento',
        features: [
            '5 menús digitales',
            'Códigos QR personalizados',
            'Productos ilimitados',
            'Analytics avanzado',
            'Multi-idioma',
            'Soporte prioritario',
        ],
        highlighted: true,
    },
    {
        name: 'Empresa',
        price: '49.99',
        description: 'Para cadenas de restaurantes',
        features: [
            'Menús ilimitados',
            'API completa',
            'Branding personalizado',
            'Manager dedicado',
            'Integración POS',
            'Soporte 24/7',
        ],
        highlighted: false,
    },
];
</script>

<template>
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Planes para cada negocio
                </h2>
                <p class="text-xl text-gray-600">
                    Elige el plan que mejor se adapte a tus necesidades
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div v-for="plan in plans" :key="plan.name"
                     :class="[
                         'rounded-2xl p-8 border-2 transition duration-300',
                         plan.highlighted
                             ? 'border-blue-600 shadow-2xl transform scale-105 bg-gradient-to-br from-blue-50 to-indigo-50'
                             : 'border-gray-200 hover:border-blue-300 hover:shadow-lg'
                     ]">
                    <div v-if="plan.highlighted" class="bg-blue-600 text-white text-sm font-bold px-4 py-1 rounded-full inline-block mb-4">
                        MÁS POPULAR
                    </div>

                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
                    <p class="text-gray-600 mb-6">{{ plan.description }}</p>

                    <div class="mb-6">
                        <span class="text-5xl font-bold text-gray-900">€{{ plan.price }}</span>
                        <span class="text-gray-600">/mes</span>
                    </div>

                    <ul class="space-y-4 mb-8">
                        <li v-for="feature in plan.features" :key="feature" class="flex items-start">
                            <CheckIcon class="w-5 h-5 text-green-500 mr-3 flex-shrink-0 mt-0.5" />
                            <span class="text-gray-700">{{ feature }}</span>
                        </li>
                    </ul>

                    <Link :href="`/register?plan=${plan.name.toLowerCase()}`"
                          :class="[
                              'block w-full text-center py-3 px-6 rounded-lg font-bold transition',
                              plan.highlighted
                                  ? 'bg-blue-600 text-white hover:bg-blue-700'
                                  : 'bg-gray-100 text-gray-900 hover:bg-gray-200'
                          ]">
                        Comenzar ahora
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
