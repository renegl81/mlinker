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
    <section class="py-20 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">
                    Lo que dicen nuestros clientes
                </h2>
                <p class="text-xl text-gray-600">
                    Miles de restaurantes confían en nosotros
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <div v-for="testimonial in testimonials" :key="testimonial.name"
                     class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition">
                    <div class="flex mb-4">
                        <span v-for="n in testimonial.rating" :key="n" class="text-yellow-400 text-xl">★</span>
                    </div>

                    <p class="text-gray-700 mb-6 italic">"{{ testimonial.content }}"</p>

                    <div class="flex items-center">
                        <img :src="testimonial.image" :alt="testimonial.name" class="w-12 h-12 rounded-full mr-4" />
                        <div>
                            <p class="font-bold text-gray-900">{{ testimonial.name }}</p>
                            <p class="text-sm text-gray-600">{{ testimonial.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
