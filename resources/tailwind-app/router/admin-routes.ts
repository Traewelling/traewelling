import { RouteRecordRaw } from 'vue-router';
import AlertsForm from '../../vue/components/Admin/Alerts/AlertsForm.vue';
import AlertsIndex from '../../vue/components/Admin/Alerts/AlertsIndex.vue';
import EventsForm from '../../vue/components/Admin/Events/EventsForm.vue';
import EventsIndex from '../../vue/components/Admin/Events/EventsIndex.vue';
import SuggestionAccept from '../../vue/components/Admin/Events/SuggestionAccept.vue';
import SuggestionsIndex from '../../vue/components/Admin/Events/SuggestionsIndex.vue';
import ReportsIndex from '../../vue/components/Admin/Reports/ReportsIndex.vue';
import ReportsShow from '../../vue/components/Admin/Reports/ReportsShow.vue';
import StationsIndex from '../../vue/components/Admin/Stations/StationsIndex.vue';
import StationsShow from '../../vue/components/Admin/Stations/StationsShow.vue';
import StatusesIndex from '../../vue/components/Admin/Statuses/StatusesIndex.vue';
import StatusesShow from '../../vue/components/Admin/Statuses/StatusesShow.vue';
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
        path: '/admin/events',
        component: EventsIndex,
    },
    {
        path: '/admin/events/create',
        component: EventsForm,
    },
    {
        path: '/admin/events/:id/edit',
        component: EventsForm,
    },
    {
        path: '/admin/event-suggestions',
        component: SuggestionsIndex,
    },
    {
        path: '/admin/event-suggestions/:id/accept',
        component: SuggestionAccept,
    },
    {
        path: '/admin/reports',
        component: ReportsIndex,
    },
    {
        path: '/admin/reports/:id',
        component: ReportsShow,
    },
    {
        path: '/admin/statuses',
        component: StatusesIndex,
    },
    {
        path: '/admin/statuses/:id',
        component: StatusesShow,
    },
    {
        path: '/admin/stations',
        component: StationsIndex,
    },
    {
        path: '/admin/stations/:id',
        component: StationsShow,
    },
];

export default adminRoutes;
