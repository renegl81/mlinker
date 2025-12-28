<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="py-32 bg-slate-950 relative overflow-hidden">

        <div class="absolute inset-0 bg-gradient-to-b from-slate-950 via-purple-900/20 to-slate-950"></div>
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-[300px] bg-gradient-to-t from-purple-600/10 to-transparent blur-3xl pointer-events-none"></div>

        <div class="container mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl md:text-6xl font-bold mb-6 text-white tracking-tight">
                ¿Listo para digitalizar?
            </h2>
            <p class="text-xl mb-10 text-slate-400 max-w-2xl mx-auto">
                Únete a miles de restaurantes que ya están usando MenuLinker.
                Sin contratos a largo plazo.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <Link href="/register"
                      class="inline-block bg-white text-slate-950 font-bold py-4 px-12 rounded-full transition transform hover:scale-105 shadow-[0_0_20px_rgba(255,255,255,0.3)] hover:shadow-[0_0_35px_rgba(255,255,255,0.5)]">
                    Crear mi cuenta gratis
                </Link>
            </div>

            <p class="mt-8 text-slate-500 text-sm flex justify-center items-center gap-4">
                <span class="flex items-center"><span class="text-purple-400 mr-1">✓</span> Sin permanencia</span>
                <span class="flex items-center"><span class="text-purple-400 mr-1">✓</span> Cancela cuando quieras</span>
            </p>
        </div>
    </section>
</template>
