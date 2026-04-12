<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const features = [
    {
        emoji: '📲',
        titleKey: 'home.features.items.qr.title',
        descKey: 'home.features.items.qr.description',
    },
    {
        emoji: '🎨',
        titleKey: 'home.features.items.templates.title',
        descKey: 'home.features.items.templates.description',
    },
    {
        emoji: '📊',
        titleKey: 'home.features.items.analytics.title',
        descKey: 'home.features.items.analytics.description',
    },
    {
        emoji: '🌾',
        titleKey: 'home.features.items.allergens.title',
        descKey: 'home.features.items.allergens.description',
    },
    {
        emoji: '⚡',
        titleKey: 'home.features.items.realtime.title',
        descKey: 'home.features.items.realtime.description',
    },
    {
        emoji: '🌍',
        titleKey: 'home.features.items.multilang.title',
        descKey: 'home.features.items.multilang.description',
    },
];
</script>

<template>
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Header -->
            <div class="text-center mb-16">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">{{ t('home.features.badge') }}</span>
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-5 leading-tight">
                    {{ t('home.features.title') }}<br class="hidden sm:block"> {{ t('home.features.title2') }}
                </h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">
                    {{ t('home.features.subtitle') }}
                </p>
            </div>

            <!-- Features grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="feature in features"
                    :key="feature.titleKey"
                    class="group relative bg-white p-7 rounded-2xl border border-slate-100 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-500/5 transition-all duration-300"
                >
                    <div class="w-12 h-12 rounded-xl bg-teal-50 flex items-center justify-center mb-5 group-hover:bg-teal-100 transition-colors text-2xl">
                        {{ feature.emoji }}
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2.5 group-hover:text-teal-700 transition-colors">
                        {{ t(feature.titleKey) }}
                    </h3>
                    <p class="text-slate-500 leading-relaxed text-sm">{{ t(feature.descKey) }}</p>
                </div>
            </div>

            <!-- Highlight band -->
            <div class="mt-16 rounded-3xl bg-gradient-to-br from-slate-900 to-slate-800 p-8 lg:p-12 flex flex-col lg:flex-row items-center gap-8 lg:gap-12 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-[400px] h-[300px] bg-teal-600/10 blur-[80px] rounded-full pointer-events-none" aria-hidden="true"></div>

                <div class="flex-1 text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-teal-500/10 text-teal-300 text-xs font-bold uppercase tracking-wider mb-4">{{ t('home.features.highlight.badge') }}</span>
                    <h3 class="text-2xl lg:text-3xl font-bold text-white mb-4 leading-snug">
                        {{ t('home.features.highlight.title') }}
                    </h3>
                    <p class="text-slate-400 mb-6 leading-relaxed">
                        {{ t('home.features.highlight.description') }}
                    </p>
                    <div class="flex flex-wrap justify-center lg:justify-start gap-3 text-sm">
                        <span class="flex items-center gap-1.5 text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                            {{ t('home.features.highlight.point1') }}
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                            {{ t('home.features.highlight.point2') }}
                        </span>
                        <span class="flex items-center gap-1.5 text-slate-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                            {{ t('home.features.highlight.point3') }}
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
