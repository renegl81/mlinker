<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative py-28 overflow-hidden">
        <!-- Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-teal-900/30 to-slate-900" aria-hidden="true"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[500px] bg-teal-600/12 blur-[120px] rounded-full pointer-events-none" aria-hidden="true"></div>

        <!-- Subtle dot grid -->
        <div class="absolute inset-0 opacity-[0.04]" aria-hidden="true"
            style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 30px 30px;">
        </div>

        <div class="container mx-auto px-4 text-center relative z-10 max-w-4xl">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-teal-500/10 border border-teal-500/25 text-teal-300 text-xs font-semibold mb-7 uppercase tracking-wide">
                <span class="w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                Empieza hoy, gratis
            </div>

            <h2 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight tracking-tight">
                ¿Listo para digitalizar<br>tu carta?
            </h2>
            <p class="text-xl text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
                Únete a más de 500 restaurantes de toda España que ya usan MenuLinker para ofrecer una experiencia moderna a sus clientes.
            </p>

            <!-- CTA -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-10">
                <Link
                    href="/register"
                    class="inline-flex items-center justify-center gap-2 px-10 py-4 rounded-full bg-teal-500 hover:bg-teal-400 text-white font-bold text-lg transition-all shadow-xl shadow-teal-500/20 hover:-translate-y-0.5"
                >
                    Crear mi cuenta gratis
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </Link>
                <Link
                    href="/contact"
                    class="inline-flex items-center justify-center gap-2 px-10 py-4 rounded-full border border-slate-600 hover:border-slate-400 text-slate-300 hover:text-white font-medium text-lg transition-all"
                >
                    Hablar con el equipo
                </Link>
            </div>

            <!-- Trust signals -->
            <div class="flex justify-center items-center flex-wrap gap-6 text-sm text-slate-500">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Plan gratuito para siempre
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Sin tarjeta de crédito
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Sin permanencia
                </span>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    Cancela cuando quieras
                </span>
            </div>
        </div>
    </section>
</template>
