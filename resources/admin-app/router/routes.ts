import { RouteRecordRaw } from 'vue-router';
import AlertsIndex from '../pages/Alerts/AlertsIndex.vue';
import AlertsForm from '../pages/Alerts/partials/AlertsForm.vue';
import EventsIndex from '../pages/Events/EventsIndex.vue';
import EventsForm from '../pages/Events/partials/EventsForm.vue';
import SuggestionAccept from '../pages/Events/SuggestionAccept.vue';
import SuggestionsIndex from '../pages/Events/SuggestionsIndex.vue';
import ReportsIndex from '../pages/Reports/ReportsIndex.vue';
import ReportsShow from '../pages/Reports/ReportsShow.vue';
import RouteSegmentsShow from '../pages/RouteSegments/RouteSegmentsShow.vue';
import StationsIndex from '../pages/Stations/StationsIndex.vue';
import StationsShow from '../pages/Stations/StationsShow.vue';
import StatusesIndex from '../pages/Statuses/StatusesIndex.vue';
import StatusesShow from '../pages/Statuses/StatusesShow.vue';
import Welcome from '../pages/Welcome.vue';

const routes: Array<RouteRecordRaw> = [
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
        path: '/admin/routesegment/:id',
        component: RouteSegmentsShow,
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

export default routes;
