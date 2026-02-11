import { RouteRecordRaw } from 'vue-router';
import Index from '../pages/Index.vue';
import Profile from '../pages/Profile.vue';

const routes: Array<RouteRecordRaw> = [
    {
        path: '/',
        name: 'index',
        component: () => Index,
    },
    {
        path: '/profile',
        name: 'profile',
        component: () => Profile,
    },
];

export default routes;
