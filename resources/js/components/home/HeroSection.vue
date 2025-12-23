<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <section class="relative bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white py-20 overflow-hidden">
        <div class="absolute inset-0 bg-[url('/images/pattern.svg')] opacity-10"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        Digitaliza tu <span class="text-yellow-300">Menú</span> en segundos
                    </h1>
                    <p class="text-xl mb-8 text-blue-100">
                        Genera códigos QR para tus menús digitales. Rápido, fácil y profesional.
                        Aumenta tus ventas y mejora la experiencia de tus clientes.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <Link href="/register" class="bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold py-4 px-8 rounded-lg transition transform hover:scale-105 shadow-lg">
                            Comenzar gratis
                        </Link>
                        <Link href="/demo" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm border-2 border-white/50 text-white font-bold py-4 px-8 rounded-lg transition">
                            Ver demo
                        </Link>
                    </div>
                </div>

                <div class="relative z-10 bg-white rounded-xl shadow-2xl p-8 transform hover:scale-105 transition duration-300">
                    <svg viewBox="0 0 300 250" class="w-full h-auto" xmlns="http://www.w3.org/2000/svg">
                        <!-- Smartphone frame -->
                        <rect x="50" y="20" width="250" height="460" rx="30" fill="#1F2937" stroke="#374151" stroke-width="3"/>
                        <rect x="65" y="35" width="230" height="430" rx="20" fill="white"/>

                        <!-- QR Code -->
                        <rect x="130" y="100" width="140" height="140" fill="#3B82F6" opacity="0.1" rx="10"/>
                        <path d="M150 120 h40 v40 h-40 z M210 120 h40 v40 h-40 z M150 180 h40 v40 h-40 z M210 180 h40 v40 h-40 z" fill="#3B82F6"/>

                        <!-- Menu items -->
                        <rect x="90" y="280" width="220" height="20" rx="5" fill="#E5E7EB"/>
                        <rect x="90" y="315" width="180" height="20" rx="5" fill="#E5E7EB"/>
                        <rect x="90" y="350" width="200" height="20" rx="5" fill="#E5E7EB"/>
                        <rect x="90" y="385" width="160" height="20" rx="5" fill="#E5E7EB"/>

                        <!-- Decorative elements -->
                        <circle cx="200" cy="180" r="80" fill="#FBBF24" opacity="0.2"/>
                        <circle cx="280" cy="120" r="30" fill="#3B82F6" opacity="0.3"/>
                    </svg>
                </div>

            </div>
        </div>
    </section>
</template>
