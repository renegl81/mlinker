import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createI18n } from 'vue-i18n';
import { initializeTheme } from './composables/useAppearance';
import ca from './locales/ca.json';
import en from './locales/en.json';
import es from './locales/es.json';
import eu from './locales/eu.json';
import gl from './locales/gl.json';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const SUPPORTED_LOCALES = ['es', 'en', 'ca', 'gl', 'eu'] as const;
type SupportedLocale = (typeof SUPPORTED_LOCALES)[number];

function resolveInitialLocale(serverLocale?: string): SupportedLocale {
    // 1. Server locale (from URL prefix or user.locale) has priority
    if (serverLocale && (SUPPORTED_LOCALES as readonly string[]).includes(serverLocale)) {
        localStorage.setItem('locale', serverLocale);
        return serverLocale as SupportedLocale;
    }
    // 2. Fallback to localStorage
    if (typeof localStorage !== 'undefined') {
        const stored = localStorage.getItem('locale');
        if (stored && (SUPPORTED_LOCALES as readonly string[]).includes(stored)) {
            return stored as SupportedLocale;
        }
    }
    return 'es';
}

const i18n = createI18n({
    legacy: false,
    locale: 'es',
    fallbackLocale: 'es',
    messages: { es, en, ca, gl, eu },
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const serverLocale = (props.initialPage.props as Record<string, unknown>).locale as string | undefined;
        const resolved = resolveInitialLocale(serverLocale);
        (i18n.global.locale as unknown as { value: string }).value = resolved;

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// Sync Vue i18n locale with server locale on every Inertia navigation
router.on('navigate', (event) => {
    const pageLocale = (event.detail.page.props as Record<string, unknown>).locale as string | undefined;
    if (pageLocale && (SUPPORTED_LOCALES as readonly string[]).includes(pageLocale)) {
        (i18n.global.locale as unknown as { value: string }).value = pageLocale;
        localStorage.setItem('locale', pageLocale);
    }
});

// This will set light / dark mode on page load...
initializeTheme();
