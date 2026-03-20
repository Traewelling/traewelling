import { RouteRecordRaw } from 'vue-router';
import AlertsForm from '../../vue/components/Admin/Alerts/AlertsForm.vue';
import AlertsIndex from '../../vue/components/Admin/Alerts/AlertsIndex.vue';
import ReportsIndex from '../../vue/components/Admin/Reports/ReportsIndex.vue';
import ReportsShow from '../../vue/components/Admin/Reports/ReportsShow.vue';
import Welcome from '../pages/Admin/Welcome.vue';

const adminRoutes: Array<RouteRecordRaw> = [
    {
        path: '/admin',
        component: Welcome,
    },
    {
        path: '/admin/alerts',
        component: AlertsIndex,
    },
    {
        path: '/admin/alerts/create',
        component: AlertsForm,
    },
    {
        path: '/admin/alerts/:id/edit',
        component: AlertsForm,
    },
    {
        path: '/admin/reports',
        component: ReportsIndex,
    },
    {
        path: '/admin/reports/:id',
        component: ReportsShow,
    },
];

export default adminRoutes;
