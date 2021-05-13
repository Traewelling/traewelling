import Vue from 'vue';
import VueRouter from 'vue-router';
import Welcome from './components/Welcome';
import Statuses from './components/Statuses';

Vue.use(VueRouter);

export const router = new VueRouter({
    mode: 'history',
    routes:
        [
            {
                path: '/',
                component: Welcome,
                name: 'welcome',
            },
            {
                path: '/statuses',
                component: Statuses,
                name: 'statuses.active'
            }

        ],
    base: '/',
});
