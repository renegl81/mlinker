<script setup lang="ts">
const features = [
    {
        emoji: '📲',
        title: 'QR Personalizados',
        description: 'Genera códigos únicos con los colores de tu marca. Imprímelos en mesas, cartelería o compártelos en redes.',
    },
    {
        emoji: '🎨',
        title: '8 Plantillas de diseño',
        description: 'Desde carta editorial hasta menú degustación. Elige la que mejor represente tu restaurante y cámbiala cuando quieras.',
    },
    {
        emoji: '📊',
        title: 'Analytics en Tiempo Real',
        description: 'Descubre qué platos miran más, en qué horarios y desde qué dispositivos. Toma decisiones basadas en datos reales.',
    },
    {
        emoji: '🌾',
        title: 'Alérgenos e Ingredientes',
        description: 'Información clara para tus clientes. Cumple la normativa europea de alérgenos (Reglamento UE 1169/2011) sin esfuerzo.',
    },
    {
        emoji: '⚡',
        title: 'Actualización Instantánea',
        description: 'Cambia precios, añade platos del día o desactiva productos agotados. Los cambios se reflejan al instante en el QR.',
    },
    {
        emoji: '🌍',
        title: 'Multi-idioma Automático',
        description: 'Traduce tu carta a 7 idiomas con un clic: inglés, francés, alemán, italiano, portugués, catalán y más.',
    },
];
</script>

<template>
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Header -->
            <div class="text-center mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">Funcionalidades</span>
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-5 leading-tight">
                    Todo lo que tu restaurante<br class="hidden sm:block"> necesita
                </h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">
                    Herramientas diseñadas para hostelería, no para ingenieros. Sin curva de aprendizaje.
                </p>
            </div>

            <!-- Features grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="group relative bg-white p-7 rounded-2xl border border-slate-100 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-500/5 transition-all duration-300"
                >
                    <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors text-2xl">
                        {{ feature.emoji }}
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2.5 group-hover:text-teal-700 transition-colors">
                        {{ feature.title }}
                    </h3>
                    <p class="text-slate-500 leading-relaxed text-sm">{{ feature.description }}</p>
                </div>
            </div>

            <!-- Highlight band -->
            <div class="mt-16 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 p-8 lg:p-12 flex flex-col lg:flex-row items-center gap-8 lg:gap-12 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-[400px] h-[300px] bg-teal-600/10 blur-[80px] rounded-full pointer-events-none" aria-hidden="true"></div>

                <div class="flex-1 text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-teal-500/10 text-teal-300 text-xs font-bold uppercase tracking-wider mb-4">Vista pública del menú</span>
                    <h3 class="text-2xl lg:text-3xl font-bold text-white mb-4 leading-snug">
                        Tu carta, hermosa en cualquier dispositivo
                    </h3>
                    <p class="text-slate-400 mb-6 leading-relaxed">
                        Los clientes escanean el QR y ven una carta digital optimizada para móvil. Sin apps, sin instalaciones. Solo escanear y disfrutar.
                    </p>
                    <div class="flex flex-wrap justify-center lg:justify-start gap-3 text-sm">
                        <span class="flex items-center gap-1.5 text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                            Responsive mobile-first
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                            Carga en &lt; 1 segundo
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                            Sin cuenta necesaria
                        </span>
                    </div>
                </div>

                <!-- Simplified menu preview -->
                <div class="w-52 lg:w-60 bg-white rounded-2xl shadow-2xl overflow-hidden flex-shrink-0">
                    <div class="bg-teal-500 p-4">
                        <p class="text-white font-bold text-sm">El Rincón de la Abuela</p>
                        <p class="text-teal-100 text-xs">Menú del día · 13,50€</p>
                    </div>
                    <div class="p-3 space-y-2">
                        <p class="text-[10px] font-bold text-teal-600 uppercase tracking-widest">Primer plato</p>
                        <div v-for="(d, i) in ['Ensalada verde', 'Gazpacho andaluz', 'Crema de verduras']" :key="i"
                            class="flex items-center justify-between py-1.5 border-b border-slate-50">
                            <span class="text-slate-700 text-xs">{{ d }}</span>
                            <span class="text-teal-600 text-xs font-medium">incl.</span>
                        </div>
                        <p class="text-[10px] font-bold text-teal-600 uppercase tracking-widest pt-1">Segundo plato</p>
                        <div v-for="(d, i) in ['Pollo al horno', 'Merluza a la plancha', 'Ternera guisada']" :key="i"
                            class="flex items-center justify-between py-1.5 border-b border-slate-50 last:border-0">
                            <span class="text-slate-700 text-xs">{{ d }}</span>
                            <span class="text-teal-600 text-xs font-medium">incl.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
