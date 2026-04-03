import { createPinia } from 'pinia';
import piniaPluginPersistedState from 'pinia-plugin-persistedstate';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import '../css/tailwind-app.css';
import '../js/components/maps';
import AdminApp from './AdminApp.vue';
import routes from './router/routes';

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
});

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('vue-admin-app');
    if (el) {
        const pinia = createPinia();
        pinia.use(piniaPluginPersistedState);

        const app = createApp(AdminApp);
        app.use(pinia);
        app.use(router);
        app.mount(el);
    }
});
