import { RouteRecordRaw } from 'vue-router';
import Index from '../pages/Index.vue';
import Profile from '../pages/Profile.vue';
import SuggestEvent from '../pages/SuggestEvent.vue';

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
    {
        path: '/events/suggest',
        name: 'events-suggest',
        component: () => SuggestEvent,
    },
];

export default routes;
