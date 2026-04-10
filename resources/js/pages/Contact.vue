<script setup lang="ts">
import FrontLayout from '@/layouts/app/FrontLayout.vue';
import { Head } from '@inertiajs/vue3';
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

const baseUrl = computed(() => props.seo.url.replace(/\/contact$/, '').replace(/\/$/, ''));
const cleanPath = '/contact';

const form = ref({
    name: '',
    email: '',
    subject: '',
    message: '',
});

const subjects = computed(() => [
    t('pages.contact.subject_support'),
    t('pages.contact.subject_sales'),
    t('pages.contact.subject_billing'),
    t('pages.contact.subject_demo'),
    t('pages.contact.subject_other'),
]);

const faqHref = computed(() => {
    const locale = props.locale;
    return locale === 'es' ? '/faq' : `/${locale}/faq`;
});

function handleSubmit() {
    const mailto = `mailto:hello@menulinker.com?subject=${encodeURIComponent(form.value.subject || 'Consulta desde web')}&body=${encodeURIComponent(`Nombre: ${form.value.name}\nEmail: ${form.value.email}\n\n${form.value.message}`)}`;
    window.location.href = mailto;
}
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
    </Head>

    <FrontLayout>
        <!-- Page hero -->
        <section class="bg-slate-50 border-b border-slate-100 py-16">
            <div class="container mx-auto px-4 max-w-5xl text-center">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">{{ t('pages.contact.badge') }}</span>
                <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">{{ t('pages.contact.title') }}</h1>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto">
                    {{ t('pages.contact.subtitle') }}
                </p>
            </div>
        </section>

        <section class="py-16">
            <div class="container mx-auto px-4 max-w-5xl">
                <div class="grid lg:grid-cols-5 gap-12 lg:gap-16">

                    <!-- Form -->
                    <div class="lg:col-span-3">
                        <h2 class="text-xl font-bold text-slate-900 mb-6">{{ t('pages.contact.form_title') }}</h2>

                        <form class="space-y-5" @submit.prevent="handleSubmit">
                            <div class="grid sm:grid-cols-2 gap-5">
                                <div>
                                    <label for="contact-name" class="block text-sm font-medium text-slate-700 mb-1.5">{{ t('pages.contact.name_label') }}</label>
                                    <input
                                        id="contact-name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        autocomplete="name"
                                        :placeholder="t('pages.contact.name_placeholder')"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 placeholder-slate-400 text-sm transition-shadow"
                                    />
                                </div>
                                <div>
                                    <label for="contact-email" class="block text-sm font-medium text-slate-700 mb-1.5">{{ t('pages.contact.email_label') }}</label>
                                    <input
                                        id="contact-email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        autocomplete="email"
                                        :placeholder="t('pages.contact.email_placeholder')"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 placeholder-slate-400 text-sm transition-shadow"
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="contact-subject" class="block text-sm font-medium text-slate-700 mb-1.5">{{ t('pages.contact.subject_label') }}</label>
                                <select
                                    id="contact-subject"
                                    v-model="form.subject"
                                    required
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 text-sm bg-white transition-shadow"
                                >
                                    <option value="" disabled>{{ t('pages.contact.subject_placeholder') }}</option>
                                    <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
                                </select>
                            </div>

                            <div>
                                <label for="contact-message" class="block text-sm font-medium text-slate-700 mb-1.5">{{ t('pages.contact.message_label') }}</label>
                                <textarea
                                    id="contact-message"
                                    v-model="form.message"
                                    required
                                    rows="5"
                                    :placeholder="t('pages.contact.message_placeholder')"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-slate-900 placeholder-slate-400 text-sm transition-shadow resize-none"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-full bg-teal-500 hover:bg-teal-600 text-white font-bold text-sm transition-colors w-full sm:w-auto"
                            >
                                {{ t('pages.contact.submit') }}
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                            </button>

                            <p class="text-xs text-slate-400">
                                {{ t('pages.contact.privacy_notice') }}
                                <a href="/privacy" class="text-teal-600 hover:underline">{{ t('pages.contact.privacy_link') }}</a>.
                            </p>
                        </form>
                    </div>

                    <!-- Info -->
                    <div class="lg:col-span-2 space-y-8">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-3">{{ t('pages.contact.info_email_title') }}</h3>
                            <a href="mailto:hello@menulinker.com" class="text-teal-600 hover:text-teal-700 font-medium text-sm">
                                hello@menulinker.com
                            </a>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-3">{{ t('pages.contact.info_hours_title') }}</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">
                                {{ t('pages.contact.info_hours_body') }}
                            </p>
                        </div>

                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-3">{{ t('pages.contact.info_links_title') }}</h3>
                            <ul class="space-y-2.5 text-sm">
                                <li>
                                    <a :href="faqHref" class="flex items-center gap-2 text-slate-600 hover:text-teal-600 transition-colors">
                                        <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                                        </svg>
                                        {{ t('pages.contact.link_faq') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="/doc" class="flex items-center gap-2 text-slate-600 hover:text-teal-600 transition-colors">
                                        <svg class="w-4 h-4 text-teal-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        {{ t('pages.contact.link_docs') }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Note on response time -->
                        <div class="bg-teal-50 border border-teal-100 rounded-xl p-5">
                            <p class="text-teal-800 text-sm font-medium mb-1">{{ t('pages.contact.response_title') }}</p>
                            <p class="text-teal-600 text-xs leading-relaxed">
                                {{ t('pages.contact.response_body') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </FrontLayout>
</template>
