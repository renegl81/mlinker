import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createSSRApp, DefineComponent, h } from 'vue';
import { createI18n } from 'vue-i18n';
import { renderToString } from 'vue/server-renderer';
import en from './locales/en.json';
import es from './locales/es.json';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

function createI18nInstance(locale: string = 'es') {
    return createI18n({
        legacy: false,
        locale: locale === 'en' ? 'en' : 'es',
        fallbackLocale: 'es',
        messages: { es, en },
    });
}

createServer(
    (page) =>
        createInertiaApp({
            page,
            render: renderToString,
            title: (title) => (title ? `${title} - ${appName}` : appName),
            resolve: (name) =>
                resolvePageComponent(
                    `./pages/${name}.vue`,
                    import.meta.glob<DefineComponent>('./pages/**/*.vue'),
                ),
            setup: ({ App, props, plugin }) =>
                createSSRApp({ render: () => h(App, props) })
                    .use(plugin)
                    .use(createI18nInstance()),
        }),
    { cluster: true },
);
