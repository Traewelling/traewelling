import { RouteRecordRaw } from 'vue-router';
import Index from '../pages/Contribute/Index.vue';
import Profile from '../pages/Contribute/Profile.vue';
import SuggestEvent from '../pages/Contribute/SuggestEvent.vue';
import Account from '../pages/Settings/Account/Account.vue';
import Followers from '../pages/Settings/Followers/Followers.vue';
import Followings from '../pages/Settings/Followers/Followings.vue';
import FollowRequests from '../pages/Settings/Followers/FollowRequests.vue';
import Privacy from '../pages/Settings/Privacy/Privacy.vue';
import ProfileSettings from '../pages/Settings/Profile/Profile.vue';
import Security from '../pages/Settings/Services/Security.vue';

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
    {
        path: '/settings/followers',
        name: 'settings-followers',
        component: Followers,
    },
    {
        path: '/settings/followings',
        name: 'settings-followings',
        component: Followings,
    },
    {
        path: '/settings/follow-requests',
        name: 'settings-follow-requests',
        component: FollowRequests,
    },
    {
        path: '/settings/account',
        name: 'settings-account',
        component: Account,
    },
    {
        path: '/settings/security',
        name: 'settings-security',
        component: Security,
    },
    {
        path: '/settings/privacy',
        name: 'settings-privacy',
        component: Privacy,
    },
];

export default routes;
