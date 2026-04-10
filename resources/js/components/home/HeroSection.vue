<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative bg-slate-950 py-24 lg:py-32 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-teal-600/15 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[400px] bg-cyan-600/10 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-900/30 border border-teal-500/30 text-teal-300 text-sm font-medium mb-6">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                        </span>
                        8 plantillas de menú · Multi-idioma automático
                    </div>

                    <h1 class="text-5xl lg:text-7xl font-bold mb-6 leading-tight tracking-tight text-white">
                        Tu carta digital<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-400 via-cyan-400 to-teal-300">
                            en minutos
                        </span>
                    </h1>
                    <p class="text-lg lg:text-xl mb-10 text-slate-400 max-w-xl mx-auto lg:mx-0">
                        Crea menús digitales con QR personalizado, gestiona alérgenos, traduce a 7 idiomas y actualiza precios en tiempo real. Sin código. Sin complicaciones.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <Link href="/register" class="group relative px-8 py-4 bg-white text-slate-950 font-bold rounded-full hover:bg-slate-200 transition-all shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.5)]">
                            Crear mi carta gratis →
                        </Link>
                    </div>

                    <p class="mt-6 text-slate-500 text-sm flex flex-wrap justify-center lg:justify-start gap-x-4 gap-y-1">
                        <span class="flex items-center"><span class="text-teal-400 mr-1.5">✓</span> Plan gratuito para siempre</span>
                        <span class="flex items-center"><span class="text-teal-400 mr-1.5">✓</span> Sin tarjeta de crédito</span>
                        <span class="flex items-center"><span class="text-teal-400 mr-1.5">✓</span> Listo en 5 minutos</span>
                    </p>
                </div>

                <!-- Mockup del menú -->
                <div class="relative z-10 mx-auto lg:mx-0 w-full max-w-sm">
                    <div class="relative bg-slate-900 border border-slate-800 rounded-3xl p-2 shadow-2xl rotate-2 hover:rotate-0 transition duration-500">
                        <div class="absolute -inset-1 bg-gradient-to-r from-teal-600 to-cyan-500 rounded-3xl blur opacity-20"></div>
                        <div class="relative bg-slate-950 rounded-2xl overflow-hidden border border-slate-800/50 p-6">
                            <!-- Mini menú mockup -->
                            <div class="mb-4">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500 to-cyan-400 mb-3"></div>
                                <div class="h-3 w-32 bg-slate-800 rounded mb-1.5"></div>
                                <div class="h-2 w-20 bg-slate-800/60 rounded"></div>
                            </div>
                            <div class="space-y-3">
                                <div v-for="i in 4" :key="i" class="flex items-center gap-3 p-3 bg-slate-900/60 rounded-xl border border-slate-800/50">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br" :class="[
                                        i === 1 ? 'from-amber-600 to-orange-500' :
                                        i === 2 ? 'from-rose-600 to-pink-500' :
                                        i === 3 ? 'from-emerald-600 to-green-500' :
                                        'from-violet-600 to-purple-500'
                                    ]" />
                                    <div class="flex-1">
                                        <div class="h-2.5 w-24 bg-slate-700 rounded mb-1.5"></div>
                                        <div class="h-2 w-16 bg-slate-800 rounded"></div>
                                    </div>
                                    <div class="h-3 w-10 bg-teal-500/30 rounded text-center">
                                        <span class="text-[8px] text-teal-300 font-bold">€12.50</span>
                                    </div>
                                </div>
                            </div>
                            <!-- QR -->
                            <div class="mt-4 flex justify-center">
                                <div class="w-16 h-16 bg-white rounded-lg grid grid-cols-5 grid-rows-5 gap-px p-1.5">
                                    <div v-for="j in 25" :key="j"
                                        class="rounded-[1px]"
                                        :class="[3,4,5,8,11,13,15,17,19,21,23].includes(j) ? 'bg-slate-900' : 'bg-transparent'"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
