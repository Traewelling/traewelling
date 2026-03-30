import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import '../css/tailwind-app.css';
import AdminApp from './AdminApp.vue';
import routes from './router/routes';

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
});

document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('vue-admin-app');
    if (el) {
        const app = createApp(AdminApp);
        app.use(router);
        app.mount(el);
    }
});
