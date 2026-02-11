import { i18nVue } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { createPinia } from 'pinia';
import piniaPluginPersistedsState from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import '../css/contribute.css';
import ContributeApp from '../vue/ContributeApp.vue';

// Initialize Notyf for notifications
window.notyf = new Notyf({
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
});

document.addEventListener('DOMContentLoaded', function () {
    // Get language fallback
    let fallbackLang = 'en';
    const urlParams = new URLSearchParams(window.location.search);
    const lang = urlParams.get('language');

    if (lang && lang.startsWith('de_')) {
        fallbackLang = 'de';
    }

    // Setup Pinia
    const pinia = createPinia();
    pinia.use(piniaPluginPersistedsState);

    // Setup i18n
    const i18nOptions = {
        fallbackLang: fallbackLang,
        fallbackMissingTranslations: true,
        resolve: (lang) => import(`../../lang/${lang}.json`),
    };

    // Setup router
    const router = createRouter({
        history: createWebHistory('/contribute'),
        routes: [
            {
                path: '/',
                name: 'index',
                component: () => import('../vue/views/Contribute/Index.vue'),
            },
            {
                path: '/profile',
                name: 'profile',
                component: () => import('../vue/views/Contribute/Profile.vue'),
            },
        ],
    });

    // Create and mount app
    if (document.getElementById('contribute-app')) {
        const app = createApp(ContributeApp);
        app.use(pinia);
        app.use(i18nVue, i18nOptions);
        app.use(router);
        app.mount('#contribute-app');
    }
});
