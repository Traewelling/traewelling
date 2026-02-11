import { i18nVue } from 'laravel-vue-i18n';
import { Notyf, INotyfOptions } from 'notyf';
import { createPinia } from 'pinia';
import piniaPluginPersistedState from 'pinia-plugin-persistedstate';
import {createApp} from 'vue';
import router from './router';
import '../css/contribute.css';
import App from './App.vue';

// Notyf can be used like this in the options api:
//
// const notyf = inject('notyf') as Notyf
// notyf.success('hi there!')
//
const notyf = new Notyf({
    duration: 5000,
    position: { x: 'right', y: window.innerWidth > 480 ? 'top' : 'bottom' },
    dismissible: true,
    ripple: true,
    types: [
        {
            type: 'info',
            background: '#0dcaf0',
            icon: {
                className: 'fa-solid fa-circle-info',
                color: 'white',
                tagName: 'i',
            },
        },
        {
            type: 'warning',
            background: '#ffc107',
            icon: {
                className: 'fa-solid fa-triangle-exclamation',
                tagName: 'i',
                color: 'white',
            },
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
    if (document.getElementById('contribute-app')) {
        const app = createApp(App);
        app.use(pinia);
        app.use(i18nVue, i18nOptions);
        app.use(router);
        app.provide('notyf', notyf);
        app.mount('#contribute-app');
    }
});
