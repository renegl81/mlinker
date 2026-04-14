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

const baseUrl = computed(() => props.seo.url.replace(/\/terms$/, '').replace(/\/$/, ''));
const cleanPath = '/terms';

const sections = [
    { id: 'objeto', label: 'Objeto del servicio' },
    { id: 'registro', label: 'Registro y cuenta' },
    { id: 'uso-aceptable', label: 'Uso aceptable' },
    { id: 'planes', label: 'Planes y facturación' },
    { id: 'cancelacion', label: 'Cancelación' },
    { id: 'propiedad', label: 'Propiedad intelectual' },
    { id: 'responsabilidad', label: 'Limitación de responsabilidad' },
    { id: 'ley', label: 'Ley aplicable' },
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
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 mb-2">{{ t('pages.terms.title') }}</h1>
                <p class="text-slate-400 text-sm">{{ t('pages.terms.last_updated') }}: {{ lastUpdated }}</p>
            </div>
        </section>

        <section class="py-14">
            <div class="container mx-auto px-4 max-w-5xl">
                <div class="flex gap-12">
                    <!-- Sidebar -->
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
                    <div class="flex-1 text-slate-600 text-sm leading-relaxed space-y-10">
                        <p>
                            Estos Términos y Condiciones regulan el acceso y uso de la plataforma MenuLinker (en adelante, "el Servicio"), operada por MenuLinker SL. Al registrarte o utilizar el Servicio, aceptas estos términos en su totalidad. Si no estás de acuerdo, no debes usar el Servicio.
                        </p>

                        <section :id="sections[0].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">1. Objeto del servicio</h2>
                            <p>MenuLinker es una plataforma SaaS (Software as a Service) que permite a negocios de hostelería crear, gestionar y publicar cartas digitales accesibles mediante códigos QR. El Servicio incluye la gestión de menús, secciones, productos, alérgenos, generación de QR y analytics, según el plan contratado.</p>
                        </section>

                        <section :id="sections[1].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">2. Registro y cuenta</h2>
                            <p class="mb-3">Para acceder al Servicio debes registrarte y crear una cuenta. Eres responsable de:</p>
                            <ul class="list-disc pl-5 space-y-1.5">
                                <li>Proporcionar información veraz y actualizada.</li>
                                <li>Mantener la confidencialidad de tu contraseña.</li>
                                <li>Notificarnos cualquier acceso no autorizado a tu cuenta.</li>
                                <li>Todas las actividades realizadas bajo tu cuenta.</li>
                            </ul>
                            <p class="mt-3">Cada cuenta puede estar asociada a un único titular. Para equipos, utiliza la funcionalidad de gestión de equipo disponible en planes Business y Enterprise.</p>
                        </section>

                        <section :id="sections[2].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">3. Uso aceptable</h2>
                            <p class="mb-3">Te comprometes a usar el Servicio exclusivamente para fines legítimos y legales. Está expresamente prohibido:</p>
                            <ul class="list-disc pl-5 space-y-1.5">
                                <li>Publicar contenido falso, engañoso, difamatorio, ilegal u ofensivo.</li>
                                <li>Usar el Servicio para fines distintos a los relacionados con tu actividad hostelera.</li>
                                <li>Intentar acceder a datos de otros usuarios o sistemas no autorizados.</li>
                                <li>Realizar ingeniería inversa, descompilar o intentar extraer el código fuente del Servicio.</li>
                                <li>Revender o sublicenciar el Servicio sin autorización expresa de MenuLinker.</li>
                            </ul>
                        </section>

                        <section :id="sections[3].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">4. Planes y facturación</h2>
                            <p class="mb-3">MenuLinker ofrece varios planes de suscripción con diferentes características y límites. Los precios se indican en euros (€) e incluyen los impuestos aplicables.</p>
                            <ul class="list-disc pl-5 space-y-1.5">
                                <li>La facturación es mensual y se realiza por adelantado mediante tarjeta de crédito a través de Stripe.</li>
                                <li>Los planes de pago incluyen un período de prueba gratuita. No se realizan cargos durante ese período.</li>
                                <li>Si superas los límites de tu plan (locales, menús, productos), se te notificará para que actualices a un plan superior.</li>
                                <li>Los precios pueden cambiar; en ese caso, te avisaremos con al menos 30 días de antelación.</li>
                                <li>No realizamos reembolsos por períodos parciales, salvo en los casos que la ley exija.</li>
                            </ul>
                        </section>

                        <section :id="sections[4].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">5. Cancelación</h2>
                            <p>Puedes cancelar tu suscripción en cualquier momento desde el panel de configuración. Al cancelar, tu cuenta pasará al plan gratuito al finalizar el período ya abonado; no perderás tus datos de inmediato. MenuLinker se reserva el derecho de cancelar o suspender cuentas que incumplan estos términos, con aviso previo siempre que sea razonablemente posible.</p>
                        </section>

                        <section :id="sections[5].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">6. Propiedad intelectual</h2>
                            <p class="mb-3">El Servicio, incluyendo su código, diseño, logotipos, marcas y contenido generado por MenuLinker, es propiedad exclusiva de MenuLinker SL y está protegido por la normativa de propiedad intelectual.</p>
                            <p>Los contenidos que introduzcas en el Servicio (nombres de platos, descripciones, imágenes) son de tu propiedad. Al subirlos, nos otorgas una licencia limitada, no exclusiva y revocable para mostrarlos a través del Servicio con el único fin de prestar el servicio contratado.</p>
                        </section>

                        <section :id="sections[6].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">7. Limitación de responsabilidad</h2>
                            <p class="mb-3">El Servicio se presta "tal cual" y MenuLinker no garantiza una disponibilidad del 100%. Nos comprometemos a mantener una disponibilidad del 99,5% mensual (SLA) en los planes de pago.</p>
                            <p>En ningún caso MenuLinker será responsable por daños indirectos, lucro cesante o pérdida de datos derivados del uso o imposibilidad de uso del Servicio, salvo dolo o negligencia grave. La responsabilidad máxima de MenuLinker se limita al importe pagado en los 3 meses anteriores al evento que originó la reclamación.</p>
                        </section>

                        <section :id="sections[7].id">
                            <h2 class="text-xl font-bold text-slate-900 mb-3">8. Ley aplicable y jurisdicción</h2>
                            <p>Estos términos se rigen por la legislación española. Para la resolución de conflictos, las partes se someten a los Juzgados y Tribunales de la ciudad donde MenuLinker SL tiene su domicilio social, renunciando a cualquier otro fuero que pudiera corresponderles.</p>
                        </section>
                    </div>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
