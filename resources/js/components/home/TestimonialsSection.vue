<script setup lang="ts">
const testimonials = [
    {
        name: 'María González',
        role: 'Propietaria de La Taberna del Puerto',
        image: 'https://ui-avatars.com/api/?name=Maria+Gonzalez&background=random',
        content: 'MenuLinker ha revolucionado nuestro servicio. Los clientes adoran poder ver el menú en su móvil y nosotros ahorramos en impresión.',
        rating: 5,
    },
    {
        name: 'Carlos Ruiz',
        role: 'Gerente de Restaurante El Mirador',
        image: 'https://ui-avatars.com/api/?name=Carlos+Ruiz&background=random',
        content: 'La facilidad para actualizar precios y platos del día es increíble. Lo recomiendo 100%.',
        rating: 5,
    },
    {
        name: 'Ana Martínez',
        role: 'Chef y propietaria de Sabores',
        image: 'https://ui-avatars.com/api/?name=Ana+Martinez&background=random',
        content: 'Los analytics nos ayudan a saber qué platos funcionan mejor. Una herramienta imprescindible.',
        rating: 5,
    },
];
</script>

<template>
    <section class="py-24 bg-slate-950 border-t border-slate-900 relative overflow-hidden">

        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-purple-900/10 blur-[120px] rounded-full pointer-events-none"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">
                    Lo que dicen nuestros clientes
                </h2>
                <p class="text-xl text-slate-400">
                    Miles de restaurantes confían en nosotros
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div v-for="testimonial in testimonials" :key="testimonial.name"
                     class="group bg-slate-900/50 backdrop-blur-sm p-8 rounded-2xl border border-slate-800 hover:border-purple-500/30 transition duration-300">

                    <div class="flex mb-4">
                        <span v-for="n in testimonial.rating" :key="n" class="text-yellow-400 text-lg">★</span>
                    </div>

                    <p class="text-slate-300 mb-6 italic leading-relaxed">"{{ testimonial.content }}"</p>

                    <div class="flex items-center pt-4 border-t border-slate-800/50">
                        <img :src="testimonial.image" :alt="testimonial.name" class="w-10 h-10 rounded-full mr-4 border border-slate-700" />
                        <div>
                            <p class="font-bold text-white text-sm">{{ testimonial.name }}</p>
                            <p class="text-xs text-slate-500">{{ testimonial.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
