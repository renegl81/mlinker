<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <section class="relative bg-slate-900 overflow-hidden py-20 lg:py-28">
        <!-- Background gradients -->
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[600px] bg-teal-600/10 blur-[140px] rounded-full"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[400px] bg-cyan-700/8 blur-[100px] rounded-full"></div>
            <!-- Subtle grid pattern -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 60px 60px;"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10 max-w-7xl">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                <!-- Left: copy -->
                <div class="text-center lg:text-left">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/25 text-teal-300 text-xs font-semibold mb-7 uppercase tracking-wide">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-teal-400"></span>
                        </span>
                        {{ t('home.hero.badge') }}
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold text-white leading-[1.08] tracking-tight mb-6">
                        {{ t('home.hero.title_line1') }}<br>
                        {{ t('home.hero.title_line2') }}<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 to-cyan-300">
                            {{ t('home.hero.title_line3') }}
                        </span>
                    </h1>

                    <p class="text-lg text-slate-400 max-w-lg mx-auto lg:mx-0 mb-9 leading-relaxed">
                        {{ t('home.hero.subtitle') }}
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start mb-8">
                        <Link
                            href="/register"
                            class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-teal-500 hover:bg-teal-400 text-white font-bold text-base transition-all shadow-lg shadow-teal-500/25 hover:shadow-teal-400/30 hover:-translate-y-0.5"
                        >
                            {{ t('home.hero.cta_primary') }}
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </Link>
                        <a
                            href="#features"
                            class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full border border-slate-600 hover:border-slate-400 text-slate-300 hover:text-white font-medium text-base transition-all"
                        >
                            {{ t('home.hero.cta_secondary') }}
                        </a>
                    </div>

                    <!-- Trust signals -->
                    <p class="text-slate-500 text-sm flex flex-wrap justify-center lg:justify-start gap-x-5 gap-y-1.5">
                        <span class="flex items-center gap-1.5">
                            <span class="inline-flex w-4 h-4 rounded-full bg-teal-500/15 items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>
                            {{ t('home.hero.trust_free') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-flex w-4 h-4 rounded-full bg-teal-500/15 items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>
                            {{ t('home.hero.trust_no_card') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="inline-flex w-4 h-4 rounded-full bg-teal-500/15 items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </span>
                            {{ t('home.hero.trust_ready') }}
                        </span>
                    </p>
                </div>

                <!-- Right: phone mockup -->
                <div class="relative mx-auto lg:mx-0 w-full max-w-xs lg:max-w-sm">
                    <!-- Glow behind phone -->
                    <div class="absolute inset-0 bg-teal-500/10 blur-[60px] rounded-full scale-75" aria-hidden="true"></div>

                    <!-- Phone shell -->
                    <div class="relative mx-auto w-64 lg:w-72">
                        <!-- Phone frame -->
                        <div class="relative bg-slate-800 rounded-[2.5rem] p-2 shadow-2xl ring-1 ring-white/10">
                            <!-- Screen -->
                            <div class="bg-white rounded-[2rem] overflow-hidden min-h-[520px]">
                                <!-- Status bar -->
                                <div class="bg-slate-50 px-5 pt-3 pb-2 flex items-center justify-between">
                                    <span class="text-[10px] text-slate-400 font-medium">9:41</span>
                                    <div class="flex items-center gap-1">
                                        <div class="w-3 h-1.5 rounded-sm bg-slate-300"></div>
                                        <div class="w-0.5 h-1 rounded-sm bg-slate-300"></div>
                                    </div>
                                </div>

                                <!-- Menu header -->
                                <div class="bg-teal-500 px-5 py-4">
                                    <p class="text-teal-100 text-xs mb-0.5">Restaurante</p>
                                    <h2 class="text-white font-bold text-base">Casa Pepe</h2>
                                    <p class="text-teal-100/80 text-xs mt-0.5">Cocina tradicional española</p>
                                </div>

                                <!-- Section label -->
                                <div class="px-4 pt-4 pb-2">
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-teal-600">Entrantes</span>
                                </div>

                                <!-- Menu items -->
                                <div class="px-4 space-y-3 pb-4">
                                    <div v-for="(item, i) in [
                                        { name: 'Croquetas caseras', desc: 'Jamón ibérico · Bechamel', price: '8,50', color: 'bg-amber-100' },
                                        { name: 'Ensalada mixta', desc: 'Tomate · Lechuga · Atún', price: '7,00', color: 'bg-green-100' },
                                        { name: 'Gambas al ajillo', desc: 'Ajo · Guindilla · EVOO', price: '12,50', color: 'bg-orange-100' },
                                    ]" :key="i"
                                        class="flex items-center gap-3 p-2.5 rounded-xl border border-slate-100 bg-white shadow-sm"
                                    >
                                        <div class="w-10 h-10 rounded-lg flex-shrink-0" :class="item.color"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-slate-800 font-semibold text-xs leading-tight">{{ item.name }}</p>
                                            <p class="text-slate-400 text-[10px] mt-0.5 truncate">{{ item.desc }}</p>
                                        </div>
                                        <span class="text-teal-600 font-bold text-xs flex-shrink-0">{{ item.price }}€</span>
                                    </div>
                                </div>

                                <!-- Allergen bar -->
                                <div class="mx-4 mb-4 px-3 py-2 bg-amber-50 rounded-xl border border-amber-100 flex items-center gap-2">
                                    <svg class="w-3 h-3 text-amber-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-amber-700 text-[9px] font-medium">Contiene: Gluten · Huevo · Marisco</span>
                                </div>

                                <!-- QR section -->
                                <div class="mx-4 mb-4 p-3 bg-slate-50 rounded-xl flex items-center gap-3">
                                    <div class="w-12 h-12 bg-white rounded-lg p-1.5 shadow-sm flex-shrink-0">
                                        <div class="w-full h-full grid grid-cols-4 gap-px">
                                            <div v-for="n in 16" :key="n"
                                                class="rounded-[1px]"
                                                :class="[1,2,4,5,6,9,11,12,13,16].includes(n) ? 'bg-slate-800' : 'bg-transparent'"
                                            />
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-slate-700 text-[10px] font-semibold">Escanea el QR</p>
                                        <p class="text-slate-400 text-[9px]">para ver la carta completa</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Floating badge: "EN / ES / FR" -->
                        <div class="absolute -right-6 top-16 bg-white rounded-xl shadow-xl border border-slate-100 px-3 py-2 flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-700">EN</span>
                            <span class="text-slate-300 text-xs">·</span>
                            <span class="text-[10px] font-bold text-teal-600">ES</span>
                            <span class="text-slate-300 text-xs">·</span>
                            <span class="text-[10px] font-bold text-slate-700">FR</span>
                        </div>

                        <!-- Floating badge: analytics -->
                        <div class="absolute -left-8 bottom-20 bg-white rounded-xl shadow-xl border border-slate-100 px-3 py-2.5 flex items-center gap-2">
                            <div class="w-6 h-6 rounded-md bg-teal-50 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3.5 h-3.5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-slate-800">+238 vistas</p>
                                <p class="text-[9px] text-slate-400">hoy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
