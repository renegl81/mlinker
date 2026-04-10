<script setup lang="ts">
import FrontLayout from '@/layouts/app/FrontLayout.vue';
import { Head } from '@inertiajs/vue3';

interface Seo {
    title: string;
    description: string;
    url: string;
}

defineProps<{ seo: Seo }>();

const cookieTypes = [
    {
        name: 'Cookies técnicas / esenciales',
        purpose: 'Imprescindibles para el funcionamiento del servicio. Gestionan la sesión del usuario, el token de autenticación y las preferencias básicas.',
        examples: 'menulinker_session, XSRF-TOKEN',
        duration: 'Sesión / 2 horas',
        thirdParty: 'No',
    },
    {
        name: 'Cookies de análisis',
        purpose: 'Nos permiten conocer cómo se usa el servicio de forma anónima y agregada, para mejorar la experiencia.',
        examples: '_ga, _gid (Google Analytics 4)',
        duration: 'Hasta 2 años',
        thirdParty: 'Sí (Google LLC)',
    },
    {
        name: 'Cookies de Stripe',
        purpose: 'Necesarias para el procesamiento seguro de pagos cuando contratas un plan de pago.',
        examples: '__stripe_mid, __stripe_sid',
        duration: 'Hasta 1 año',
        thirdParty: 'Sí (Stripe Inc.)',
    },
];

const lastUpdated = '10 de abril de 2026';
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.url" />
        <meta name="robots" content="noindex" />
    </Head>

    <FrontLayout>
        <section class="bg-slate-50 border-b border-slate-100 py-12">
            <div class="container mx-auto px-4 max-w-4xl">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">Legal</span>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-2">Política de cookies</h1>
                <p class="text-slate-400 text-sm">Última actualización: {{ lastUpdated }}</p>
            </div>
        </section>

        <section class="py-14">
            <div class="container mx-auto px-4 max-w-4xl space-y-10 text-slate-600 text-sm leading-relaxed">

                <div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">¿Qué son las cookies?</h2>
                    <p>
                        Las cookies son pequeños archivos de texto que los sitios web almacenan en tu dispositivo cuando los visitas. Permiten que el sitio recuerde tus acciones y preferencias durante un período de tiempo, de modo que no tengas que volver a introducir esa información cada vez que visites el sitio o navegues de una página a otra.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900 mb-4">Cookies que utilizamos</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50">
                                    <th class="text-left p-3 border border-slate-200 font-semibold text-slate-700">Tipo</th>
                                    <th class="text-left p-3 border border-slate-200 font-semibold text-slate-700">Finalidad</th>
                                    <th class="text-left p-3 border border-slate-200 font-semibold text-slate-700">Ejemplos</th>
                                    <th class="text-left p-3 border border-slate-200 font-semibold text-slate-700">Duración</th>
                                    <th class="text-left p-3 border border-slate-200 font-semibold text-slate-700">Terceros</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cookie in cookieTypes" :key="cookie.name">
                                    <td class="p-3 border border-slate-200 font-medium text-slate-700 align-top">{{ cookie.name }}</td>
                                    <td class="p-3 border border-slate-200 text-slate-600 align-top">{{ cookie.purpose }}</td>
                                    <td class="p-3 border border-slate-200 text-slate-500 font-mono align-top">{{ cookie.examples }}</td>
                                    <td class="p-3 border border-slate-200 text-slate-600 align-top whitespace-nowrap">{{ cookie.duration }}</td>
                                    <td class="p-3 border border-slate-200 align-top">
                                        <span :class="cookie.thirdParty === 'No' ? 'text-teal-600 font-medium' : 'text-slate-600'">
                                            {{ cookie.thirdParty }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">¿Cómo gestionar las cookies?</h2>
                    <p class="mb-4">
                        Puedes aceptar, rechazar o gestionar las cookies de análisis y de terceros en cualquier momento. Las cookies técnicas son imprescindibles para que el servicio funcione y no pueden desactivarse.
                    </p>
                    <p class="mb-4">
                        Puedes también configurar tu navegador para bloquear o eliminar cookies. Ten en cuenta que algunas partes del servicio pueden no funcionar correctamente si deshabilitas todas las cookies. Instrucciones por navegador:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5">
                        <li>
                            <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener noreferrer" class="text-teal-600 hover:underline">Google Chrome</a>
                        </li>
                        <li>
                            <a href="https://support.mozilla.org/es/kb/habilitar-y-deshabilitar-cookies-sitios-web-rastrear-preferencias" target="_blank" rel="noopener noreferrer" class="text-teal-600 hover:underline">Mozilla Firefox</a>
                        </li>
                        <li>
                            <a href="https://support.apple.com/es-es/guide/safari/sfri11471/mac" target="_blank" rel="noopener noreferrer" class="text-teal-600 hover:underline">Apple Safari</a>
                        </li>
                        <li>
                            <a href="https://support.microsoft.com/es-es/microsoft-edge/eliminar-las-cookies-en-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener noreferrer" class="text-teal-600 hover:underline">Microsoft Edge</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">Transferencias internacionales</h2>
                    <p>
                        Algunas cookies son establecidas por terceros ubicados fuera de la UE (Google, Stripe). Estas empresas ofrecen garantías adecuadas conforme al RGPD mediante mecanismos como las Cláusulas Contractuales Estándar aprobadas por la Comisión Europea.
                    </p>
                </div>

                <div>
                    <h2 class="text-xl font-bold text-slate-900 mb-3">Contacto</h2>
                    <p>
                        Si tienes preguntas sobre nuestra política de cookies, escríbenos a
                        <a href="mailto:privacy@menulinker.com" class="text-teal-600 hover:underline">privacy@menulinker.com</a>.
                        También puedes consultar nuestra
                        <a href="/privacy" class="text-teal-600 hover:underline">política de privacidad</a>
                        para más información sobre cómo tratamos tus datos.
                    </p>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
