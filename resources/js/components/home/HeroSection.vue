<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative bg-slate-950 py-24 lg:py-32 overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-purple-600/20 blur-[120px] rounded-full pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[400px] bg-indigo-600/10 blur-[100px] rounded-full pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-900/30 border border-purple-500/30 text-purple-300 text-sm font-medium mb-6">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-500"></span>
                        </span>
                        Nuevo: Analíticas v2.0
                    </div>

                    <h1 class="text-5xl lg:text-7xl font-bold mb-6 leading-tight tracking-tight text-white">
                        Digitaliza tu <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-400 to-indigo-400">
                            Menú al Instante
                        </span>
                    </h1>
                    <p class="text-lg lg:text-xl mb-10 text-slate-400 max-w-xl mx-auto lg:mx-0">
                        Genera códigos QR para tus menús digitales. La herramienta definitiva para restaurantes modernos que buscan velocidad y diseño.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <Link href="/register" class="group relative px-8 py-4 bg-white text-slate-950 font-bold rounded-full hover:bg-slate-200 transition-all shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_30px_rgba(255,255,255,0.5)]">
                            Comenzar gratis
                        </Link>

                        <Link href="/demo" class="px-8 py-4 bg-slate-900/50 text-white font-medium rounded-full border border-slate-700 hover:border-slate-500 hover:bg-slate-800 transition backdrop-blur-sm">
                            Ver demo
                        </Link>
                    </div>
                </div>

                <div class="relative z-10 mx-auto lg:mx-0 w-full max-w-md">
                    <div class="relative bg-slate-900 border border-slate-800 rounded-3xl p-2 shadow-2xl rotate-3 hover:rotate-0 transition duration-500">
                        <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 to-pink-600 rounded-3xl blur opacity-25"></div>
                        <div class="relative bg-slate-950 rounded-2xl overflow-hidden border border-slate-800/50">
                            <svg viewBox="0 0 300 400" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
                                <rect width="300" height="400" fill="#020617"/>
                                <rect x="20" y="20" width="260" height="40" rx="8" fill="#1e293b"/>
                                <circle cx="40" cy="40" r="6" fill="#fb7185"/>
                                <rect x="60" y="35" width="100" height="10" rx="2" fill="#334155"/>

                                <rect x="20" y="80" width="260" height="60" rx="12" fill="#0f172a" stroke="#334155" stroke-width="1"/>
                                <rect x="20" y="150" width="260" height="60" rx="12" fill="#0f172a" stroke="#334155" stroke-width="1"/>
                                <rect x="20" y="220" width="260" height="60" rx="12" fill="#0f172a" stroke="#334155" stroke-width="1"/>

                                <circle cx="250" cy="350" r="25" fill="#c026d3"/>
                                <path d="M250 340 v20 M240 350 h20" stroke="white" stroke-width="3"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>
