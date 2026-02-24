import { RouteRecordRaw } from 'vue-router';
import Index from '../pages/Contribute/Index.vue';
import Profile from '../pages/Contribute/Profile.vue';
import SuggestEvent from '../pages/Contribute/SuggestEvent.vue';
import ProfileSettings from '../pages/Settings/Profile.vue';

const routes: Array<RouteRecordRaw> = [
    {
        path: '/contribute',
        name: 'index',
        component: Index,
    },
    {
        path: '/contribute/profile',
        name: 'profile',
        component: Profile,
    },
    {
        path: '/contribute/event-proposal',
        name: 'events-suggest',
        component: SuggestEvent,
    },
    {
        path: '/settings/profile',
        name: 'settings-profile',
        component: ProfileSettings,
    },
];

export default routes;
