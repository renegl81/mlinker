<script setup lang="ts">
import FrontLayout from '@/layouts/app/FrontLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

interface Seo {
    title: string;
    description: string;
    url: string;
}

interface Props {
    seo: Seo;
    locale: string;
    availableLocales: string[];
}

const props = defineProps<Props>();

const { t, locale: i18nLocale } = useI18n();

onMounted(() => {
    if (props.locale && i18nLocale.value !== props.locale) {
        i18nLocale.value = props.locale;
    }
});

const baseUrl = computed(() => props.seo.url.replace(/\/privacy$/, '').replace(/\/$/, ''));
const cleanPath = '/privacy';

const sections = [
    { id: 'responsable', label: 'Responsable del tratamiento' },
    { id: 'datos', label: 'Datos que recopilamos' },
    { id: 'finalidades', label: 'Finalidades y base legal' },
    { id: 'conservacion', label: 'Conservación de datos' },
    { id: 'destinatarios', label: 'Destinatarios' },
    { id: 'derechos', label: 'Tus derechos' },
    { id: 'cookies', label: 'Cookies' },
    { id: 'seguridad', label: 'Seguridad' },
    { id: 'menores', label: 'Menores de edad' },
    { id: 'cambios', label: 'Cambios en la política' },
];

const lastUpdated = '10 de abril de 2026';
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.url" />
        <meta name="robots" content="noindex" />

        <!-- hreflang SEO -->
        <link rel="alternate" hreflang="es" :href="baseUrl + cleanPath" />
        <link rel="alternate" hreflang="en" :href="baseUrl + '/en' + cleanPath" />
        <link rel="alternate" hreflang="ca" :href="baseUrl + '/ca' + cleanPath" />
        <link rel="alternate" hreflang="gl" :href="baseUrl + '/gl' + cleanPath" />
        <link rel="alternate" hreflang="eu" :href="baseUrl + '/eu' + cleanPath" />
        <link rel="alternate" hreflang="x-default" :href="baseUrl + cleanPath" />
    </Head>

    <FrontLayout>
        <section class="bg-slate-50 border-b border-slate-100 py-12">
            <div class="container mx-auto px-4 max-w-5xl">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">Legal</span>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-2">{{ t('pages.privacy.title') }}</h1>
                <p class="text-slate-400 text-sm">{{ t('pages.privacy.last_updated') }}: {{ lastUpdated }}</p>
            </div>
        </section>

        <section class="py-14">
            <div class="container mx-auto px-4 max-w-5xl">
                <div class="flex gap-12">
                    <!-- Sidebar nav (desktop) -->
                    <aside class="hidden lg:block w-56 flex-shrink-0">
                        <div class="sticky top-24">
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Contenido</p>
                            <nav class="space-y-1">
                                <a
                                    v-for="s in sections"
                                    :key="s.id"
                                    :href="`#${s.id}`"
                                    class="block text-sm text-slate-500 hover:text-teal-600 py-1.5 transition-colors border-l-2 border-transparent hover:border-teal-400 pl-3"
                                >
                                    {{ s.label }}
                                </a>
                            </nav>
                        </div>
                    </aside>

                    <!-- Content -->
                    <div class="flex-1 prose prose-slate max-w-none prose-headings:font-bold prose-headings:text-slate-900 prose-p:text-slate-600 prose-p:leading-relaxed prose-li:text-slate-600 prose-a:text-teal-600">
                        <p class="text-slate-600 leading-relaxed mb-8">
                            En MenuLinker nos tomamos muy en serio la privacidad de tus datos. Esta política describe qué datos recopilamos, para qué los usamos y cuáles son tus derechos conforme al Reglamento (UE) 2016/679 General de Protección de Datos (RGPD) y la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD).
                        </p>

                        <section :id="sections[0].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">1. Responsable del tratamiento</h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                El responsable del tratamiento de tus datos personales es <strong>MenuLinker SL</strong> (en adelante, "MenuLinker" o "nosotros"), con domicilio social en España. Para cualquier cuestión relacionada con la protección de datos, puedes contactarnos en <a href="mailto:privacy@menulinker.com" class="text-teal-600 hover:underline">privacy@menulinker.com</a>.
                            </p>
                        </section>

                        <section :id="sections[1].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">2. Datos que recopilamos</h2>
                            <p class="text-slate-600 text-sm leading-relaxed mb-3">Recopilamos los siguientes tipos de datos:</p>
                            <ul class="text-slate-600 text-sm space-y-2 list-disc pl-5">
                                <li><strong>Datos de cuenta:</strong> nombre, dirección de correo electrónico, contraseña (almacenada de forma cifrada).</li>
                                <li><strong>Datos del negocio:</strong> nombre del establecimiento, localización, carta (secciones, productos, precios, alérgenos).</li>
                                <li><strong>Datos de facturación:</strong> método de pago (gestionado por Stripe), historial de facturas. No almacenamos datos de tarjeta en nuestros servidores.</li>
                                <li><strong>Datos de uso:</strong> páginas visitadas, acciones en el panel, dirección IP, agente de usuario, estadísticas de visualización de menús.</li>
                                <li><strong>Comunicaciones:</strong> mensajes de soporte que nos envíes.</li>
                            </ul>
                        </section>

                        <section :id="sections[2].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">3. Finalidades y base legal</h2>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50">
                                            <th class="text-left p-3 border border-slate-200 font-semibold text-slate-700">Finalidad</th>
                                            <th class="text-left p-3 border border-slate-200 font-semibold text-slate-700">Base legal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, i) in [
                                            ['Prestación del servicio (cuenta, menús, QR)', 'Ejecución de contrato (art. 6.1.b RGPD)'],
                                            ['Facturación y gestión de suscripciones', 'Ejecución de contrato y obligación legal'],
                                            ['Análisis de uso para mejorar el producto', 'Interés legítimo (art. 6.1.f RGPD)'],
                                            ['Comunicaciones de soporte', 'Ejecución de contrato'],
                                            ['Comunicaciones de marketing (si las aceptas)', 'Consentimiento (art. 6.1.a RGPD)'],
                                        ]" :key="i">
                                            <td class="p-3 border border-slate-200 text-slate-600">{{ row[0] }}</td>
                                            <td class="p-3 border border-slate-200 text-slate-600">{{ row[1] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section :id="sections[3].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">4. Conservación de datos</h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Conservamos tus datos durante el tiempo en que mantengas una cuenta activa en MenuLinker. Tras la cancelación de tu cuenta, los datos se eliminan en un plazo máximo de 30 días, salvo los datos de facturación que debemos conservar durante el plazo legalmente exigible (5 años en España). Los logs de acceso se eliminan a los 12 meses.
                            </p>
                        </section>

                        <section :id="sections[4].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">5. Destinatarios</h2>
                            <p class="text-slate-600 text-sm leading-relaxed mb-3">
                                No vendemos tus datos a terceros. Podemos compartirlos con los siguientes proveedores de servicio, que actúan como encargados del tratamiento y ofrecen garantías adecuadas de conformidad con el RGPD:
                            </p>
                            <ul class="text-slate-600 text-sm space-y-2 list-disc pl-5">
                                <li><strong>Stripe Inc.</strong> — Procesamiento de pagos (EE.UU., bajo cláusulas contractuales estándar)</li>
                                <li><strong>Hetzner Online GmbH</strong> — Alojamiento de servidores (UE)</li>
                                <li><strong>Postmark / Mailgun</strong> — Envío de emails transaccionales</li>
                            </ul>
                        </section>

                        <section :id="sections[5].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">6. Tus derechos</h2>
                            <p class="text-slate-600 text-sm leading-relaxed mb-3">Conforme al RGPD, tienes los siguientes derechos:</p>
                            <ul class="text-slate-600 text-sm space-y-2 list-disc pl-5">
                                <li><strong>Acceso:</strong> obtener confirmación de si tratamos tus datos y una copia de los mismos.</li>
                                <li><strong>Rectificación:</strong> corregir datos inexactos.</li>
                                <li><strong>Supresión:</strong> solicitar la eliminación de tus datos ("derecho al olvido").</li>
                                <li><strong>Oposición:</strong> oponerte al tratamiento basado en interés legítimo.</li>
                                <li><strong>Portabilidad:</strong> recibir tus datos en formato estructurado.</li>
                                <li><strong>Limitación:</strong> solicitar la restricción del tratamiento en determinadas circunstancias.</li>
                            </ul>
                            <p class="text-slate-600 text-sm leading-relaxed mt-3">
                                Para ejercer estos derechos, escríbenos a <a href="mailto:privacy@menulinker.com" class="text-teal-600 hover:underline">privacy@menulinker.com</a>. Tienes también el derecho a presentar una reclamación ante la Agencia Española de Protección de Datos (aepd.es).
                            </p>
                        </section>

                        <section :id="sections[6].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">7. Cookies</h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Utilizamos cookies propias y de terceros para el funcionamiento del servicio y el análisis de uso. Consulta nuestra
                                <a href="/cookies" class="text-teal-600 hover:underline">política de cookies</a> para información detallada.
                            </p>
                        </section>

                        <section :id="sections[7].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">8. Seguridad</h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Aplicamos medidas técnicas y organizativas adecuadas para proteger tus datos: cifrado TLS en todas las comunicaciones, contraseñas almacenadas con bcrypt, accesos restringidos por roles, copias de seguridad cifradas diarias y auditorías periódicas de seguridad.
                            </p>
                        </section>

                        <section :id="sections[8].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">9. Menores de edad</h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                El servicio de MenuLinker está dirigido a empresas y profesionales. No recopilamos de manera consciente datos de personas menores de 16 años. Si eres padre, madre o tutor y crees que un menor nos ha proporcionado datos, contáctanos en <a href="mailto:privacy@menulinker.com" class="text-teal-600 hover:underline">privacy@menulinker.com</a>.
                            </p>
                        </section>

                        <section :id="sections[9].id" class="mb-10">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">10. Cambios en la política</h2>
                            <p class="text-slate-600 text-sm leading-relaxed">
                                Podemos actualizar esta política periódicamente. Cuando realicemos cambios materiales, te notificaremos por email o mediante un aviso visible en el panel antes de que entren en vigor. La fecha de la última actualización figura al inicio de este documento.
                            </p>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
