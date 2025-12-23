// File: vite.config.ts
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig(({ mode }) => {
    const isDev = mode !== 'production';

    return {
        plugins: [
            laravel({
                input: ['resources/js/app.ts'],
                ssr: 'resources/js/ssr.ts',
                refresh: true,
            }),
            tailwindcss(),
            wayfinder({
                formVariants: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
        server: isDev
            ? {
                host: '0.0.0.0',
                port: 5173,
                strictPort: true,
                hmr: {
                    host: 'localhost',
                    protocol: 'ws',
                    clientPort: 5173,
                },
                watch: {
                    usePolling: true, // Importante para Docker
                },
                cors: true, // Simplificado
            }
            : undefined,
        ssr: {
            noExternal: ['@inertiajs/server'],
        },
    };
});
