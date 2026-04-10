<script setup lang="ts">
import FrontLayout from '@/layouts/app/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Seo {
    title: string;
    description: string;
    url: string;
}

defineProps<{ seo: Seo }>();

const faqs = [
    {
        question: '¿Cuánto cuesta MenuLinker?',
        answer: 'MenuLinker tiene un plan gratuito para siempre que incluye 1 local y 1 menú con hasta 30 productos y QR básico. Los planes de pago empiezan en 14,99€/mes (facturación mensual) e incluyen prueba gratuita de 14 días sin tarjeta de crédito.',
    },
    {
        question: '¿Cómo funciona el menú digital con QR?',
        answer: 'Creas tu carta desde el panel de control, organizas las secciones y productos, eliges una plantilla de diseño y generas tu código QR personalizado. Tus clientes escanean el QR con la cámara de su móvil y acceden al menú actualizado al instante. No hace falta descargar ninguna app.',
    },
    {
        question: '¿Puedo traducir mi menú a varios idiomas?',
        answer: 'Sí, con los planes Business y Enterprise MenuLinker traduce automáticamente tu carta a inglés, francés, alemán, italiano, portugués, catalán y más idiomas. Puedes revisar y editar cada traducción manualmente para garantizar la calidad. El cliente elige el idioma en la carta digital.',
    },
    {
        question: '¿Se muestran los alérgenos en el menú?',
        answer: 'Sí. Puedes asignar los 14 alérgenos de declaración obligatoria según el Reglamento UE 1169/2011 (gluten, crustáceos, huevos, pescado, cacahuetes, soja, lácteos, frutos de cáscara, apio, mostaza, sésamo, dióxido de azufre, altramuces y moluscos) a cada plato. Los alérgenos se muestran con iconos claros en la carta digital.',
    },
    {
        question: '¿Puedo cambiar precios y platos en tiempo real?',
        answer: 'Sí, cualquier cambio que hagas en el panel (precio, disponibilidad, descripción, foto) se refleja inmediatamente en todos los QR activos. No hay que reimprimir ni regenerar el código QR. Ideal para platos del día o temporada.',
    },
    {
        question: '¿Qué plantillas de menú hay disponibles?',
        answer: 'MenuLinker incluye 8 plantillas de diseño para la carta pública: desde estilos minimalistas y modernos hasta diseños más clásicos con tipografía elegante. Puedes cambiar de plantilla en cualquier momento sin perder tus datos.',
    },
    {
        question: '¿Puedo gestionar varios locales desde una sola cuenta?',
        answer: 'Sí. Dependiendo del plan, puedes gestionar múltiples locales (ubicaciones) desde un único panel. Cada local puede tener sus propias cartas, horarios y QR. Perfecto para cadenas o grupos de restauración.',
    },
    {
        question: '¿Cómo funciona la prueba gratuita de pago?',
        answer: 'Los planes Pro y Business incluyen 14 días de prueba gratuita sin necesidad de introducir tarjeta de crédito. Al finalizar el período de prueba, puedes suscribirte o pasar automáticamente al plan gratuito sin perder tus datos.',
    },
    {
        question: '¿Puedo cancelar mi suscripción en cualquier momento?',
        answer: 'Sí, sin permanencia ni penalizaciones. Cancelas desde el panel en cualquier momento y tu cuenta pasa al plan gratuito al finalizar el período ya pagado. Nunca te cobraremos de más.',
    },
    {
        question: '¿Mis datos están seguros?',
        answer: 'Sí. MenuLinker opera conforme al RGPD (Reglamento General de Protección de Datos). Todos los datos se almacenan en servidores dentro de la Unión Europea, con cifrado en tránsito (TLS) y en reposo. Realizamos copias de seguridad diarias.',
    },
    {
        question: '¿Qué soporte ofrece MenuLinker?',
        answer: 'Todos los planes incluyen soporte por email con respuesta en menos de 48h laborables. Los planes Business y Enterprise tienen acceso a soporte prioritario y chat en horario laboral (lunes a viernes, 9h–18h CET). También tienes acceso a nuestra documentación y FAQ.',
    },
    {
        question: '¿Necesito saber programar para usar MenuLinker?',
        answer: 'No. MenuLinker está diseñado para que cualquier persona pueda usarlo sin conocimientos técnicos. Crear tu primera carta lleva menos de 5 minutos. Si necesitas integraciones avanzadas, los planes Business y Enterprise incluyen acceso API con documentación completa.',
    },
];

const openIndex = ref<number | null>(null);

function toggle(index: number) {
    openIndex.value = openIndex.value === index ? null : index;
}

const jsonLdFaq = JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqs.map((f) => ({
        '@type': 'Question',
        name: f.question,
        acceptedAnswer: {
            '@type': 'Answer',
            text: f.answer,
        },
    })),
});
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.url" />
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="jsonLdFaq" />
    </Head>

    <FrontLayout>
        <!-- Page hero -->
        <section class="bg-slate-50 border-b border-slate-100 py-16">
            <div class="container mx-auto px-4 max-w-3xl text-center">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">FAQ</span>
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">Preguntas frecuentes</h1>
                <p class="text-lg text-slate-500">
                    Todo lo que necesitas saber sobre MenuLinker. ¿No encuentras tu respuesta?
                    <Link href="/contact" class="text-teal-600 hover:text-teal-700 underline underline-offset-2">Escríbenos</Link>.
                </p>
            </div>
        </section>

        <!-- Accordion -->
        <section class="py-16">
            <div class="container mx-auto px-4 max-w-3xl">
                <div class="space-y-3">
                    <div
                        v-for="(faq, index) in faqs"
                        :key="index"
                        class="rounded-xl border border-slate-200 bg-white overflow-hidden"
                    >
                        <button
                            class="w-full flex items-center justify-between px-6 py-5 text-left hover:bg-slate-50 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-inset"
                            :aria-expanded="openIndex === index"
                            @click="toggle(index)"
                        >
                            <span class="font-semibold text-slate-900 pr-4 text-base leading-snug">{{ faq.question }}</span>
                            <span
                                class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center transition-transform duration-200"
                                :class="openIndex === index ? 'rotate-45 bg-teal-50' : ''"
                                aria-hidden="true"
                            >
                                <svg class="w-3.5 h-3.5" :class="openIndex === index ? 'text-teal-600' : 'text-slate-500'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </span>
                        </button>
                        <div
                            v-show="openIndex === index"
                            class="px-6 pb-5 text-slate-600 leading-relaxed text-sm"
                        >
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="mt-14 rounded-2xl bg-teal-50 border border-teal-100 p-8 text-center">
                    <h2 class="text-xl font-bold text-slate-900 mb-2">¿Aún tienes dudas?</h2>
                    <p class="text-slate-500 mb-5 text-sm">El equipo de MenuLinker responde en menos de 48h laborables.</p>
                    <Link
                        href="/contact"
                        class="inline-flex items-center gap-2 px-7 py-3 rounded-full bg-teal-500 hover:bg-teal-600 text-white font-bold text-sm transition-colors"
                    >
                        Contactar con soporte
                    </Link>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
