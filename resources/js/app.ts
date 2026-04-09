import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { createI18n } from 'vue-i18n';
import { initializeTheme } from './composables/useAppearance';
import en from './locales/en.json';
import es from './locales/es.json';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function getStoredLocale(): string {
    if (typeof localStorage !== 'undefined') {
        const stored = localStorage.getItem('locale');
        if (stored === 'en' || stored === 'es') {
            return stored;
        }
    }
    return 'es';
}

function createI18nInstance() {
    return createI18n({
        legacy: false,
        locale: getStoredLocale(),
        fallbackLocale: 'es',
        messages: { es, en },
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
