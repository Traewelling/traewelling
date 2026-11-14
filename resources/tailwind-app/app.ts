import { Info, TriangleAlert } from '@lucide/vue';
import { i18nVue } from 'laravel-vue-i18n';
import { INotyfOptions, Notyf } from 'notyf';
import { createPinia } from 'pinia';
import piniaPluginPersistedState from 'pinia-plugin-persistedstate';
import { Component, createApp, h, render } from 'vue';
import '../css/tailwind-app.css';
import '../js/maplibre';
import App from './App.vue';
import router from './router';

// Notyf can be used like this in the options api:
//
// const notyf = inject('notyf') as Notyf
// notyf.success('hi there!')
//
function iconHtml(icon: Component): string {
    const container = document.createElement('div');
    render(h(icon, { size: 20, color: 'white' }), container);
    return container.innerHTML;
}

const notyf = new Notyf({
    duration: 5000,
    position: { x: 'right', y: window.innerWidth > 480 ? 'top' : 'bottom' },
    dismissible: true,
    ripple: true,
    types: [
        {
            type: 'info',
            background: '#0dcaf0',
            icon: iconHtml(Info),
        },
        {
            type: 'warning',
            background: '#ffc107',
            icon: iconHtml(TriangleAlert),
        },
    ],
} as INotyfOptions);

document.addEventListener('DOMContentLoaded', function () {
    // Setup Pinia
    const pinia = createPinia();
    pinia.use(piniaPluginPersistedState);

    // Setup i18n
    const i18nOptions = {
        fallbackLang: 'en',
        fallbackMissingTranslations: true,
        resolve: (lang: string) => import(`../../lang/${lang}.json`),
    };

    // Create and mount app
    if (document.getElementById('tailwind-app')) {
        const app = createApp(App);
        app.use(pinia);
        app.use(i18nVue, i18nOptions);
        app.use(router);
        app.provide('notyf', notyf);
        app.mount('#tailwind-app');
    }
});
