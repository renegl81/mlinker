<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="py-32 bg-slate-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-950 via-teal-900/15 to-slate-950"></div>
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-[300px] bg-gradient-to-t from-teal-600/8 to-transparent blur-3xl pointer-events-none"></div>

        <div class="container mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl md:text-6xl font-bold mb-6 text-white tracking-tight">
                ¿Listo para digitalizar tu carta?
            </h2>
            <p class="text-xl mb-10 text-slate-400 max-w-2xl mx-auto">
                Únete a restaurantes de toda España que ya usan MenuLinker.
                Crea tu cuenta en 2 minutos. Sin tarjeta.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <Link href="/register"
                      class="inline-block bg-white text-slate-950 font-bold py-4 px-12 rounded-full transition transform hover:scale-105 shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_35px_rgba(255,255,255,0.5)]">
                    Crear mi cuenta gratis →
                </Link>
            </div>

            <p class="mt-8 text-slate-500 text-sm flex justify-center items-center gap-4 flex-wrap">
                <span class="flex items-center"><span class="text-teal-400 mr-1">✓</span> Plan gratuito para siempre</span>
                <span class="flex items-center"><span class="text-teal-400 mr-1">✓</span> Sin permanencia</span>
                <span class="flex items-center"><span class="text-teal-400 mr-1">✓</span> Cancela cuando quieras</span>
            </p>
        </div>
    </section>
</template>
