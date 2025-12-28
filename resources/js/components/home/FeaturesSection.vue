<script setup lang="ts">
import { QrCodeIcon, DevicePhoneMobileIcon, ChartBarIcon, ShieldCheckIcon, BoltIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const features = [
    {
        icon: QrCodeIcon,
        title: 'QR Personalizados',
        description: 'Genera códigos únicos que se alinean con la identidad visual de tu marca.',
    },
    {
        icon: DevicePhoneMobileIcon,
        title: 'Diseño Adaptativo',
        description: 'Menús que fluyen perfectamente en cualquier dispositivo móvil.',
    },
    {
        icon: ChartBarIcon,
        title: 'Analytics Avanzado',
        description: 'Métricas en tiempo real para optimizar tu oferta gastronómica.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Control de Alérgenos',
        description: 'Seguridad alimentaria con información clara para tus clientes.',
    },
    {
        icon: BoltIcon,
        title: 'Tiempo Real',
        description: 'Sincronización instantánea de precios y disponibilidad.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Internacional',
        description: 'Soporte multi-idioma automático para turistas.',
    },
];
</script>

<template>
    <section class="py-24 bg-slate-950 relative border-t border-slate-900">
        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">
                    Todo lo que necesitas
                </h2>
                <p class="text-xl text-slate-400 max-w-2xl mx-auto">
                    Potencia tu restaurante con herramientas diseñadas para el futuro.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="feature in features" :key="feature.title"
                     class="group relative bg-slate-900/50 p-8 rounded-2xl border border-slate-800 hover:border-purple-500/50 transition duration-300 overflow-hidden">

                    <div class="absolute inset-0 bg-gradient-to-br from-purple-600/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="relative z-10 w-12 h-12 bg-slate-800 rounded-lg flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <component :is="feature.icon" class="w-6 h-6 text-purple-400" />
                    </div>
                    <h3 class="relative z-10 text-xl font-bold text-white mb-3 group-hover:text-purple-300 transition-colors">{{ feature.title }}</h3>
                    <p class="relative z-10 text-slate-400 leading-relaxed">{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
