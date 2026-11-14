import { i18nVue } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { createPinia } from 'pinia';
import { App, Component, createApp } from 'vue';
import { createMemoryHistory, createRouter, START_LOCATION } from 'vue-router';
import LoginForm from './components/Auth/LoginForm.vue';
import StatusCardPreview from './components/Welcome/StatusCardPreview.vue';
import WelcomeApi from './components/Welcome/WelcomeApi.vue';
import WelcomeCommunity from './components/Welcome/WelcomeCommunity.vue';
import WelcomeExport from './components/Welcome/WelcomeExport.vue';
import WelcomeLinks from './components/Welcome/WelcomeLinks.vue';
import WelcomeSelfHosting from './components/Welcome/WelcomeSelfHosting.vue';
import WelcomeStats from './components/Welcome/WelcomeStats.vue';
import WelcomeTags from './components/Welcome/WelcomeTags.vue';
import WelcomeVisibility from './components/Welcome/WelcomeVisibility.vue';
import Footer from './layouts/partials/Footer.vue';
import Navbar from './layouts/partials/Navbar.vue';

function mount(selector: string, component: Component, configure?: (app: App) => void) {
    const el = document.querySelector<HTMLElement>(selector);
    if (!el) {
        return;
    }

    const app = createApp(component, { ...el.dataset });
    configure?.(app);
    app.mount(el);
}

function withAppContext(app: App, fullPageNavigation = false) {
    const blank = { render: () => null };
    const router = createRouter({
        history: createMemoryHistory(),
        routes: [
            { path: '/:pathMatch(.*)*', component: blank },
            { path: '/stationboard', name: 'stationboard', component: blank },
            { path: '/@:username', name: 'user-profile', component: blank },
            { path: '/status/:id', name: 'single-status', component: blank },
            { path: '/event/:slug', name: 'event-view', component: blank },
            { path: '/events', name: 'event-list', component: blank },
            { path: '/changelog', name: 'changelog', component: blank },
            { path: '/legal', name: 'legal-notice', component: blank },
            { path: '/legal/privacy-policy', name: 'privacy-policy', component: blank },
            { path: '/dashboard', name: 'dashboard', component: blank },
            { path: '/leaderboard', name: 'leaderboard', component: blank },
            { path: '/statuses/active', name: 'active-journeys', component: blank },
            { path: '/statistics', name: 'statistics', component: blank },
            { path: '/login', name: 'login', component: blank },
            { path: '/register', name: 'register', component: blank },
            { path: '/password/reset', name: 'password-request', component: blank },
        ],
    });

    if (fullPageNavigation) {
        router.beforeEach((to, from) => {
            if (from === START_LOCATION) {
                return true;
            }
            window.location.href = to.fullPath;
            return false;
        });
    }

    app.use(createPinia());
    app.use(i18nVue, {
        fallbackLang: 'en',
        fallbackMissingTranslations: true,
        resolve: (lang: string) => import(`../../lang/${lang}.json`),
    });
    app.use(router);
    app.provide('notyf', new Notyf());
}

function withPageNavigation(app: App) {
    withAppContext(app, true);
    app.provide('reloadOnLanguageChange', true);
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
    mount('#welcome-navbar', Navbar, withPageNavigation);
    mount('#welcome-login', LoginForm, withPageNavigation);
    mount('#welcome-links', WelcomeLinks);
    mount('#welcome-stats', WelcomeStats);
    mount('#welcome-footer', Footer, withPageNavigation);
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
