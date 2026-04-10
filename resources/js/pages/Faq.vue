<script setup lang="ts">
import FrontLayout from '@/layouts/app/FrontLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
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

const baseUrl = computed(() => props.seo.url.replace(/\/faq$/, '').replace(/\/$/, ''));
const cleanPath = '/faq';

const faqs = computed(() => [
    {
        question: t('pages.faq.q1'),
        answer: t('pages.faq.a1'),
    },
    {
        question: t('pages.faq.q2'),
        answer: t('pages.faq.a2'),
    },
    {
        question: t('pages.faq.q3'),
        answer: t('pages.faq.a3'),
    },
    {
        question: t('pages.faq.q4'),
        answer: t('pages.faq.a4'),
    },
    {
        question: t('pages.faq.q5'),
        answer: t('pages.faq.a5'),
    },
    {
        question: t('pages.faq.q6'),
        answer: t('pages.faq.a6'),
    },
    {
        question: t('pages.faq.q7'),
        answer: t('pages.faq.a7'),
    },
    {
        question: t('pages.faq.q8'),
        answer: t('pages.faq.a8'),
    },
    {
        question: t('pages.faq.q9'),
        answer: t('pages.faq.a9'),
    },
    {
        question: t('pages.faq.q10'),
        answer: t('pages.faq.a10'),
    },
    {
        question: t('pages.faq.q11'),
        answer: t('pages.faq.a11'),
    },
    {
        question: t('pages.faq.q12'),
        answer: t('pages.faq.a12'),
    },
]);

const openIndex = ref<number | null>(null);

function toggle(index: number) {
    openIndex.value = openIndex.value === index ? null : index;
}

const jsonLdFaq = computed(() => JSON.stringify({
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: faqs.value.map((f) => ({
        '@type': 'Question',
        name: f.question,
        acceptedAnswer: {
            '@type': 'Answer',
            text: f.answer,
        },
    })),
}));

const contactHref = computed(() => {
    const locale = props.locale;
    return locale === 'es' ? '/contact' : `/${locale}/contact`;
});
</script>

<template>
    <Head>
        <title>{{ seo.title }}</title>
        <meta name="description" :content="seo.description" />
        <link rel="canonical" :href="seo.url" />

        <!-- hreflang SEO -->
        <link rel="alternate" hreflang="es" :href="baseUrl + cleanPath" />
        <link rel="alternate" hreflang="en" :href="baseUrl + '/en' + cleanPath" />
        <link rel="alternate" hreflang="ca" :href="baseUrl + '/ca' + cleanPath" />
        <link rel="alternate" hreflang="gl" :href="baseUrl + '/gl' + cleanPath" />
        <link rel="alternate" hreflang="eu" :href="baseUrl + '/eu' + cleanPath" />
        <link rel="alternate" hreflang="x-default" :href="baseUrl + cleanPath" />

        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="jsonLdFaq" />
    </Head>

    <FrontLayout>
        <!-- Page hero -->
        <section class="bg-slate-50 border-b border-slate-100 py-16">
            <div class="container mx-auto px-4 max-w-3xl text-center">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">FAQ</span>
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">{{ t('pages.faq.title') }}</h1>
                <p class="text-lg text-slate-500">
                    {{ t('pages.faq.subtitle') }}
                    <Link :href="contactHref" class="text-teal-600 hover:text-teal-700 underline underline-offset-2">{{ t('pages.faq.contact_link') }}</Link>.
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
                    <h2 class="text-xl font-bold text-slate-900 mb-2">{{ t('pages.faq.cta_title') }}</h2>
                    <p class="text-slate-500 mb-5 text-sm">{{ t('pages.faq.cta_subtitle') }}</p>
                    <Link
                        :href="contactHref"
                        class="inline-flex items-center gap-2 px-7 py-3 rounded-full bg-teal-500 hover:bg-teal-600 text-white font-bold text-sm transition-colors"
                    >
                        {{ t('pages.faq.cta_button') }}
                    </Link>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
