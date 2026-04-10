<script setup lang="ts">
import FrontLayout from '@/layouts/app/FrontLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';


interface Seo {
    title: string;
    description: string;
    url: string;
}

defineProps<{ seo: Seo }>();

const form = ref({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const subjects = [
    'Soporte técnico',
    'Consulta comercial',
    'Facturación y pagos',
    'Solicitud de demo',
    'Otro',
];

function handleSubmit() {
    // Mailto fallback — backend to be implemented
    const mailto = `mailto:hello@menulinker.com?subject=${encodeURIComponent(form.value.subject || 'Consulta desde web')}&body=${encodeURIComponent(`Nombre: ${form.value.name}\nEmail: ${form.value.email}\n\n${form.value.message}`)}`;
    window.location.href = mailto;
}
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.url" />
    </Head>

    <FrontLayout>
        <!-- Page hero -->
        <section class="bg-slate-50 border-b border-slate-100 py-16">
            <div class="container mx-auto px-4 max-w-5xl text-center">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">Contacto</span>
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">¿En qué podemos ayudarte?</h1>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">
                    Nuestro equipo está disponible para resolver tus dudas, acompañarte en el onboarding o escuchar tus propuestas de mejora.
                </p>
            </div>
        </section>

        <section class="py-16">
            <div class="container mx-auto px-4 max-w-5xl">
                <div class="grid lg:grid-cols-5 gap-12 lg:gap-16">

                    <!-- Form -->
                    <div class="lg:col-span-3">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">Envíanos un mensaje</h2>

                        <form class="space-y-5" @submit.prevent="handleSubmit">
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="contact-name" class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                                    <input
                                        id="contact-name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        autocomplete="name"
                                        placeholder="María García"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 placeholder-slate-400 text-sm transition-shadow"
                                    />
                                </div>
                                <div>
                                    <label for="contact-email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                                    <input
                                        id="contact-email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        autocomplete="email"
                                        placeholder="maria@restaurante.com"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 placeholder-slate-400 text-sm transition-shadow"
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="contact-subject" class="block text-sm font-medium text-slate-700 mb-1.5">Asunto</label>
                                <select
                                    id="contact-subject"
                                    v-model="form.subject"
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 text-sm bg-white transition-shadow"
                                >
                                    <option value="" disabled>Selecciona un asunto…</option>
                                    <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="contact-message" class="block text-sm font-medium text-slate-700 mb-1.5">Mensaje</label>
                                <textarea
                                    id="contact-message"
                                    v-model="form.message"
                                    required
                                    rows="5"
                                    placeholder="Cuéntanos en qué podemos ayudarte…"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 placeholder-slate-400 text-sm transition-shadow resize-none"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-teal-500 hover:bg-teal-600 text-white font-bold text-sm transition-colors w-full sm:w-auto"
                            >
                                Enviar mensaje
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                            </button>

                            <p class="text-xs text-slate-400">
                                Al enviar este formulario, aceptas nuestra
                                <a href="/privacy" class="text-teal-600 hover:underline">política de privacidad</a>.
                            </p>
                        </form>
                    </div>

                    <!-- Info -->
                    <div class="lg:col-span-2 space-y-8">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-3">Email</h3>
                            <a href="mailto:hello@menulinker.com" class="text-teal-600 hover:text-teal-700 font-medium text-sm">
                                hello@menulinker.com
                            </a>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-3">Horario de soporte</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                Lunes a viernes, 9:00 – 18:00 (CET / CEST)<br>
                                Respuesta por email en menos de 48h laborables.
                            </p>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-3">¿Buscas algo concreto?</h3>
                            <ul class="space-y-2.5 text-sm">
                                <li>
                                    <a href="/faq" class="flex items-center gap-2 text-slate-600 hover:text-teal-600 transition-colors">
                                        <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                        </svg>
                                        Preguntas frecuentes
                                    </a>
                                </li>
                                <li>
                                    <a href="/doc" class="flex items-center gap-2 text-slate-600 hover:text-teal-600 transition-colors">
                                        <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        Documentación técnica
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Note on response time -->
                        <div class="bg-teal-50 border border-teal-100 rounded-xl p-5">
                            <p class="text-teal-800 text-sm font-medium mb-1">Tiempo de respuesta</p>
                            <p class="text-teal-600 text-xs leading-relaxed">
                                Respondemos a todos los mensajes en menos de 48h laborables. Para soporte urgente en planes Business y Enterprise, tienes acceso prioritario.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
