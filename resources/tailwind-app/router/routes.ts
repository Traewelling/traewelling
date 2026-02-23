import { RouteRecordRaw } from 'vue-router';

const routes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: 'index',
        component: () => import('../pages/Index.vue'),
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => import('../pages/Profile.vue'),
    },
    {
        path: '/events/suggest',
        name: 'events-suggest',
        component: () => import('../pages/SuggestEvent.vue'),
    },
];

export default routes;
