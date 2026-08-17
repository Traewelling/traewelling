import { Menu } from '@lucide/vue';
import { i18nVue } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { createPinia } from 'pinia';
import { App, Component, createApp } from 'vue';
import { createMemoryHistory, createRouter } from 'vue-router';
import StatusCardPreview from './components/Welcome/StatusCardPreview.vue';
import WelcomeApi from './components/Welcome/WelcomeApi.vue';
import WelcomeCommunity from './components/Welcome/WelcomeCommunity.vue';
import WelcomeExport from './components/Welcome/WelcomeExport.vue';
import WelcomeSelfHosting from './components/Welcome/WelcomeSelfHosting.vue';
import WelcomeSocialLinks from './components/Welcome/WelcomeSocialLinks.vue';
import WelcomeStats from './components/Welcome/WelcomeStats.vue';
import WelcomeTags from './components/Welcome/WelcomeTags.vue';
import WelcomeVisibility from './components/Welcome/WelcomeVisibility.vue';

function mount(selector: string, component: Component, configure?: (app: App) => void) {
    const el = document.querySelector<HTMLElement>(selector);
    if (!el) {
        return;
    }

    const app = createApp(component, { ...el.dataset });
    configure?.(app);
    app.mount(el);
}

function withAppContext(app: App) {
    const blank = { render: () => null };

    app.use(createPinia());
    app.use(i18nVue, {
        fallbackLang: 'en',
        fallbackMissingTranslations: true,
        resolve: (lang: string) => import(`../../lang/${lang}.json`),
    });
    app.use(
        createRouter({
            history: createMemoryHistory(),
            routes: [
                { path: '/:pathMatch(.*)*', component: blank },
                { path: '/stationboard', name: 'stationboard', component: blank },
            ],
        }),
    );
    app.provide('notyf', new Notyf());
}

function mountWhenVisible(selector: string, load: () => Promise<Component>) {
    const el = document.querySelector<HTMLElement>(selector);
    if (!el) {
        return;
    }

    const run = () => load().then((component) => mount(selector, component));

    if (!('IntersectionObserver' in window)) {
        void run();
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                observer.disconnect();
                void run();
            }
        },
        { rootMargin: '400px' },
    );
    observer.observe(el);
}

document.addEventListener('DOMContentLoaded', () => {
    mount('#welcome-nav-toggle', Menu);
    mount('#welcome-stats', WelcomeStats);
    mount('#welcome-social-links', WelcomeSocialLinks);
    mount('#welcome-status-card', StatusCardPreview, withAppContext);
    mount('#welcome-tags', WelcomeTags);
    mount('#welcome-visibility', WelcomeVisibility);
    mount('#welcome-export', WelcomeExport);
    mount('#welcome-api', WelcomeApi);
    mount('#welcome-community', WelcomeCommunity);
    mount('#welcome-self-hosting', WelcomeSelfHosting);
    mountWhenVisible('#welcome-stats-charts', () =>
        import('./components/Welcome/WelcomeStatsCharts.vue').then((module) => module.default),
    );
});
