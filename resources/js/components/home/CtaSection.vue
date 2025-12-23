<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                ¿Listo para digitalizar tu restaurante?
            </h2>
            <p class="text-xl mb-8 text-blue-100 max-w-2xl mx-auto">
                Únete a miles de restaurantes que ya están usando MenuLinker.
                Comienza gratis, sin tarjeta de crédito.
            </p>
            <Link href="/register"
                  class="inline-block bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-4 px-12 rounded-lg transition transform hover:scale-105 shadow-xl">
                Crear mi cuenta gratis
            </Link>
            <p class="mt-4 text-blue-200 text-sm">
                ✓ Sin permanencia ✓ Cancela cuando quieras ✓ Soporte en español
            </p>
        </div>
    </section>
</template>
