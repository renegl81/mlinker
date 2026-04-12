<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const testimonials = [
    {
        name: 'María González',
        roleKey: 'home.testimonials.items.maria.role',
        business: 'La Taberna del Puerto · Málaga',
        initials: 'MG',
        color: 'bg-rose-100 text-rose-600',
        contentKey: 'home.testimonials.items.maria.content',
        rating: 5,
    },
    {
        name: 'Carlos Ruiz',
        roleKey: 'home.testimonials.items.carlos.role',
        business: 'Restaurante El Mirador · Barcelona',
        initials: 'CR',
        color: 'bg-blue-100 text-blue-600',
        contentKey: 'home.testimonials.items.carlos.content',
        rating: 5,
    },
    {
        name: 'Ana Martínez',
        roleKey: 'home.testimonials.items.ana.role',
        business: 'Sabores de Castilla · Madrid',
        initials: 'AM',
        color: 'bg-teal-100 text-teal-600',
        contentKey: 'home.testimonials.items.ana.content',
        rating: 5,
    },
];
</script>

<template>
    <section class="py-24 bg-white relative overflow-hidden">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Header -->
            <div class="text-center mb-14">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">{{ t('home.testimonials.badge') }}</span>
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">
                    {{ t('home.testimonials.title') }}
                </h2>
                <p class="text-lg text-slate-500">
                    {{ t('home.testimonials.subtitle') }}
                </p>
            </div>

            <!-- Cards: scroll horizontal en mobile, grid en desktop -->
            <div class="flex gap-6 overflow-x-auto snap-x snap-mandatory pb-4 md:grid md:grid-cols-3 md:overflow-visible md:pb-0 scrollbar-hide">
                <article
                    v-for="t_ in testimonials"
                    :key="t_.name"
                    class="flex-none w-80 md:w-auto snap-start bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-slate-200 transition-all duration-300 p-7 flex flex-col"
                >
                    <!-- Stars -->
                    <div class="flex gap-0.5 mb-5">
                        <svg
                            v-for="n in t_.rating"
                            :key="n"
                            class="w-4 h-4 text-amber-400 fill-current"
                            viewBox="0 0 20 20"
                        >
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>

                    <!-- Quote -->
                    <blockquote class="text-slate-600 leading-relaxed mb-6 flex-1 italic">
                        "{{ t(t_.contentKey) }}"
                    </blockquote>

                    <!-- Author -->
                    <div class="flex items-center gap-3 pt-5 border-t border-slate-50">
                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0', t_.color]">
                            {{ t_.initials }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-900 text-sm">{{ t_.name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ t(t_.roleKey) }} · {{ t_.business }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Social proof stat -->
            <div class="mt-14 flex flex-wrap justify-center gap-10 text-center">
                <div>
                    <p class="text-3xl font-bold text-slate-900">+500</p>
                    <p class="text-slate-500 text-sm mt-1">{{ t('home.testimonials.stats.restaurants') }}</p>
                </div>
                <div class="w-px h-12 bg-slate-100 self-center hidden sm:block"></div>
                <div>
                    <p class="text-3xl font-bold text-slate-900">4,9/5</p>
                    <p class="text-slate-500 text-sm mt-1">{{ t('home.testimonials.stats.rating') }}</p>
                </div>
                <div class="w-px h-12 bg-slate-100 self-center hidden sm:block"></div>
                <div>
                    <p class="text-3xl font-bold text-slate-900">7</p>
                    <p class="text-slate-500 text-sm mt-1">{{ t('home.testimonials.stats.languages') }}</p>
                </div>
                <div class="w-px h-12 bg-slate-100 self-center hidden sm:block"></div>
                <div>
                    <p class="text-3xl font-bold text-slate-900">5 min</p>
                    <p class="text-slate-500 text-sm mt-1">{{ t('home.testimonials.stats.setup') }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
