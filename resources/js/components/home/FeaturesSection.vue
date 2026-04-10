<script setup lang="ts">
import { QrCodeIcon, DevicePhoneMobileIcon, ChartBarIcon, ShieldCheckIcon, BoltIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const features = [
    {
        icon: QrCodeIcon,
        title: 'QR Personalizados',
        description: 'Genera códigos únicos con los colores de tu marca. Imprímelos o compártelos digitalmente.',
    },
    {
        icon: DevicePhoneMobileIcon,
        title: '8 Plantillas',
        description: 'Desde carta editorial hasta menú degustación. Elige la que mejor represente tu restaurante.',
    },
    {
        icon: ChartBarIcon,
        title: 'Analytics en Tiempo Real',
        description: 'Descubre qué platos miran más, en qué horarios y desde qué dispositivos.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Alérgenos e Ingredientes',
        description: 'Información clara para tus clientes. Cumple la normativa europea sin esfuerzo.',
    },
    {
        icon: BoltIcon,
        title: 'Actualización Instantánea',
        description: 'Cambia precios, añade platos del día o desactiva productos agotados al momento.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Multi-idioma Automático',
        description: 'Traduce tu carta a 7 idiomas con un clic. Perfecto para zonas turísticas.',
    },
];
</script>

<template>
    <section id="features" class="py-24 bg-slate-950 relative border-t border-slate-900">
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">
                    Todo lo que tu carta necesita
                </h2>
                <p class="text-xl text-slate-400 max-w-2xl mx-auto">
                    Herramientas diseñadas para hostelería, no para ingenieros.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="feature in features" :key="feature.title"
                     class="group relative bg-slate-900/50 p-8 rounded-2xl border border-slate-800 hover:border-teal-500/50 transition duration-300 overflow-hidden">

                    <div class="absolute inset-0 bg-gradient-to-br from-teal-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="relative z-10 w-12 h-12 bg-slate-800 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <component :is="feature.icon" class="w-6 h-6 text-teal-400" />
                    </div>
                    <h3 class="relative z-10 text-xl font-bold text-white mb-3 group-hover:text-teal-300 transition-colors">{{ feature.title }}</h3>
                    <p class="relative z-10 text-slate-400 leading-relaxed">{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
