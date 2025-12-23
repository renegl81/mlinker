<script setup lang="ts">
import { QrCodeIcon, DevicePhoneMobileIcon, ChartBarIcon, ShieldCheckIcon, BoltIcon, GlobeAltIcon } from '@heroicons/vue/24/outline';

const features = [
    {
        icon: QrCodeIcon,
        title: 'Códigos QR Personalizados',
        description: 'Genera códigos QR únicos con tu logo y colores corporativos.',
    },
    {
        icon: DevicePhoneMobileIcon,
        title: 'Diseño Responsive',
        description: 'Menús perfectamente optimizados para cualquier dispositivo móvil.',
    },
    {
        icon: ChartBarIcon,
        title: 'Analytics en Tiempo Real',
        description: 'Conoce qué platos son más populares y optimiza tu oferta.',
    },
    {
        icon: ShieldCheckIcon,
        title: 'Gestión de Alérgenos',
        description: 'Información clara sobre alérgenos para tus clientes.',
    },
    {
        icon: BoltIcon,
        title: 'Actualización Instantánea',
        description: 'Modifica precios y platos al instante sin reimprimir nada.',
    },
    {
        icon: GlobeAltIcon,
        title: 'Multi-idioma',
        description: 'Menús en varios idiomas para clientes internacionales.',
    },
];
</script>

<template>
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Todo lo que necesitas para tu negocio
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Una solución completa para digitalizar tu restaurante y mejorar la experiencia de tus clientes
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="feature in features" :key="feature.title"
                     class="bg-white p-8 rounded-xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center mb-6">
                        <component :is="feature.icon" class="w-7 h-7 text-blue-600" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ feature.title }}</h3>
                    <p class="text-gray-600">{{ feature.description }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
