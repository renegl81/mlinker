import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
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

function getStoredLocale(): SupportedLocale {
    if (typeof localStorage !== 'undefined') {
        const stored = localStorage.getItem('locale');
        if (stored && (SUPPORTED_LOCALES as readonly string[]).includes(stored)) {
            return stored as SupportedLocale;
        }
    }
    return 'es';
}

function createI18nInstance() {
    return createI18n({
        legacy: false,
        locale: getStoredLocale(),
        fallbackLocale: 'es',
        messages: { es, en, ca, gl, eu },
    });
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createI18nInstance())
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
