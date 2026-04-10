<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative py-24 sm:py-32 overflow-hidden bg-slate-900">
        <!-- Background -->
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[500px] bg-teal-600/15 blur-[140px] rounded-full"></div>
            <div class="absolute bottom-0 right-0 w-[400px] h-[300px] bg-cyan-700/10 blur-[100px] rounded-full"></div>
        </div>

        <!-- Top edge transition from white sections above -->
        <div class="absolute top-0 left-0 right-0 h-20 bg-gradient-to-b from-white to-transparent pointer-events-none" aria-hidden="true"></div>

        <div class="container mx-auto px-4 text-center relative z-10 max-w-3xl pt-8">
            <!-- Icon -->
            <div class="mx-auto mb-8 flex h-16 w-16 items-center justify-center rounded-2xl bg-teal-500/15 ring-1 ring-teal-500/25">
                <svg class="w-7 h-7 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                </svg>
            </div>

            <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-5 leading-tight tracking-tight">
                ¿Listo para digitalizar<br class="hidden sm:block"> tu carta?
            </h2>
            <p class="text-lg text-slate-400 max-w-xl mx-auto mb-10 leading-relaxed">
                Crea tu carta digital en minutos. Sin compromisos, sin tarjeta de crédito. Únete a más de 500 restaurantes que ya confían en MenuLinker.
            </p>

            <!-- CTA buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-3 mb-8">
                <Link
                    href="/register"
                    class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full bg-teal-500 hover:bg-teal-400 text-white font-bold text-base transition-all shadow-lg shadow-teal-500/25 hover:shadow-teal-400/30 hover:-translate-y-0.5"
                >
                    Crear mi cuenta gratis
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </Link>
                <Link
                    href="/contact"
                    class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-full border border-slate-600 hover:border-slate-400 text-slate-300 hover:text-white font-medium text-base transition-all"
                >
                    Hablar con el equipo
                </Link>
            </div>

            <!-- Trust signals -->
            <div class="flex justify-center items-center flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Plan gratuito para siempre
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Sin tarjeta de crédito
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Sin permanencia
                </span>
            </div>
        </div>
    </section>
</template>
