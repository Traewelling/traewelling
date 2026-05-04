import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import i18n from 'laravel-vue-i18n/vite';
import { resolve } from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/js/app.js',
                'resources/sass/app.scss',
                'resources/sass/app-dark.scss',
                'resources/js/admin.js',
                'resources/sass/admin.scss',
                'resources/css/welcome.css',
                'resources/js/year-in-review.js',
                'resources/tailwind-app/app.ts',
                'resources/admin-app/app.ts',
                'resources/css/trwl-theme.css',
                'resources/css/tailwind-app.css',
            ],
            refresh: true,
        }),
        // Required Vue config for Laravel
        // See https://laravel.com/docs/10.x/vite#vue
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        i18n(),
    ],
    resolve: {
        alias: [
            // we need the runtime template compiler for the notification bell
            // we might want to migrate that, so we don't need to bundle the compiler at runtime
            { find: 'vue', replacement: 'vue/dist/vue.esm-bundler.js' },
            { find: /^daisyui$/, replacement: resolve('./node_modules/daisyui/index.js') },
        ],
    },
    build: {
        sourcemap: true,
    },
});
