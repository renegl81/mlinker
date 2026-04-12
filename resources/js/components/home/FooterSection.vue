<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const appName = usePage().props.name as string;

const columns = [
    {
        titleKey: 'home.footer.columns.product.title',
        links: [
            { labelKey: 'home.footer.columns.product.links.features', href: '/#features' },
            { labelKey: 'home.footer.columns.product.links.pricing', href: '/#pricing' },
            { labelKey: 'home.footer.columns.product.links.templates', href: '/#features' },
        ],
    },
    {
        titleKey: 'home.footer.columns.company.title',
        links: [
            { labelKey: 'home.footer.columns.company.links.contact', href: '/contact' },
        ],
    },
    {
        titleKey: 'home.footer.columns.legal.title',
        links: [
            { labelKey: 'home.footer.columns.legal.links.privacy', href: '/privacy' },
            { labelKey: 'home.footer.columns.legal.links.terms', href: '/terms' },
            { labelKey: 'home.footer.columns.legal.links.cookies', href: '/cookies' },
        ],
    },
    {
        titleKey: 'home.footer.columns.support.title',
        links: [
            { labelKey: 'home.footer.columns.support.links.faq', href: '/faq' },
        ],
    },
];
</script>

<template>
    <footer class="bg-slate-900 text-slate-300">
        <div class="container mx-auto px-4 py-16 max-w-7xl">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-10 mb-14">
                <!-- Brand column -->
                <div class="col-span-2 md:col-span-1">
                    <Link href="/" class="flex items-center gap-2.5 mb-4 group">
                        <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <span class="text-white font-bold text-lg tracking-tight">{{ appName }}</span>
                    </Link>
                    <p class="text-slate-400 text-sm leading-relaxed mb-6">
                        {{ t('home.footer.brand_description') }}
                    </p>
                    <!-- Social icons -->
                    <div class="flex items-center gap-3">
                        <a
                            href="https://instagram.com/menulinker"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="t('home.footer.social.instagram')"
                            class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-teal-500/20 hover:text-teal-400 flex items-center justify-center transition-colors"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a
                            href="https://twitter.com/menulinker"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="t('home.footer.social.twitter')"
                            class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-teal-500/20 hover:text-teal-400 flex items-center justify-center transition-colors"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                        <a
                            href="https://linkedin.com/company/menulinker"
                            target="_blank"
                            rel="noopener noreferrer"
                            :aria-label="t('home.footer.social.linkedin')"
                            class="w-9 h-9 rounded-lg bg-slate-800 hover:bg-teal-500/20 hover:text-teal-400 flex items-center justify-center transition-colors"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Nav columns -->
                <div
                    v-for="col in columns"
                    :key="col.titleKey"
                    class="col-span-1"
                >
                    <h3 class="text-white font-semibold text-sm mb-4 uppercase tracking-wider">{{ t(col.titleKey) }}</h3>
                    <ul class="space-y-2.5">
                        <li v-for="link in col.links" :key="link.labelKey">
                            <Link
                                :href="link.href"
                                class="text-slate-400 hover:text-teal-400 text-sm transition-colors"
                            >
                                {{ t(link.labelKey) }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="border-t border-slate-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-slate-500 text-sm">
                    &copy; {{ new Date().getFullYear() }} {{ appName }}. {{ t('home.footer.rights') }}
                </p>
                <div class="flex items-center gap-4">
                    <Link href="/privacy" class="text-slate-500 hover:text-slate-300 text-xs transition-colors">{{ t('home.footer.bottom.privacy') }}</Link>
                    <Link href="/terms" class="text-slate-500 hover:text-slate-300 text-xs transition-colors">{{ t('home.footer.bottom.terms') }}</Link>
                    <Link href="/cookies" class="text-slate-500 hover:text-slate-300 text-xs transition-colors">{{ t('home.footer.bottom.cookies') }}</Link>
                </div>
            </div>
        </div>
    </footer>
</template>
