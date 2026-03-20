import { RouteRecordRaw } from 'vue-router';
import ReportsIndex from '../../vue/components/Admin/Reports/ReportsIndex.vue';
import ReportsShow from '../../vue/components/Admin/Reports/ReportsShow.vue';
import Welcome from '../pages/Admin/Welcome.vue';

const adminRoutes: Array<RouteRecordRaw> = [
    {
        path: '/admin',
        name: 'admin-welcome',
        component: Welcome,
    },
    {
        path: '/admin/reports',
        name: 'admin-reports-index',
        component: ReportsIndex,
    },
    {
        path: '/admin/reports/:id',
        name: 'admin-reports-show',
        component: ReportsShow,
    },
];

export default adminRoutes;
