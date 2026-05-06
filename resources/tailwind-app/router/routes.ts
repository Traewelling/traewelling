import { RouteRecordRaw } from 'vue-router';
import ErrorPage from '../components/ErrorPage.vue';
import ActiveJourneys from '../pages/ActiveJourneys/ActiveJourneys.vue';
import Changelog from '../pages/Changelog/Changelog.vue';
import CheckinPage from '../pages/Checkin/CheckinPage.vue';
import Index from '../pages/Contribute/Index.vue';
import Profile from '../pages/Contribute/Profile.vue';
import SuggestEvent from '../pages/Contribute/SuggestEvent.vue';
import Dashboard from '../pages/Dashboard/Dashboard.vue';
import EventList from '../pages/Events/EventList.vue';
import EventView from '../pages/Events/EventView.vue';
import Export from '../pages/Export/Export.vue';
import Leaderboard from '../pages/Leaderboard/Leaderboard.vue';
import MonthlyLeaderboard from '../pages/Leaderboard/MonthlyLeaderboard.vue';
import Notifications from '../pages/Notifications/Notifications.vue';
import UserProfile from '../pages/Profile/UserProfile.vue';
import Search from '../pages/Search/Search.vue';
import Account from '../pages/Settings/Account/Account.vue';
import Applications from '../pages/Settings/Applications/Applications.vue';
import WebhookStats from '../pages/Settings/Applications/WebhookStats.vue';
import Blocks from '../pages/Settings/Blocks/Blocks.vue';
import Followers from '../pages/Settings/Followers/Followers.vue';
import Followings from '../pages/Settings/Followers/Followings.vue';
import FollowRequests from '../pages/Settings/Followers/FollowRequests.vue';
import Mutes from '../pages/Settings/Mutes/Mutes.vue';
import Privacy from '../pages/Settings/Privacy/Privacy.vue';
import ProfileSettings from '../pages/Settings/Profile/Profile.vue';
import Security from '../pages/Settings/Services/Security.vue';
import Wellbeing from '../pages/Settings/Wellbeing/Wellbeing.vue';
import Stationboard from '../pages/Stationboard/Stationboard.vue';
import DailyStats from '../pages/Statistics/DailyStats.vue';
import Statistics from '../pages/Statistics/Statistics.vue';
import SingleStatus from '../pages/Status/SingleStatus.vue';
import TicketDetail from '../pages/Tickets/TicketDetail.vue';
import Tickets from '../pages/Tickets/Tickets.vue';
import TripCreation from '../pages/Trip/TripCreation.vue';

const routes: Array<RouteRecordRaw> = [
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'menu.dashboard' },
    },
    {
        path: '/contribute',
        name: 'index',
        component: Index,
        meta: { title: 'contribute' },
    },
    {
        path: '/contribute/profile',
        name: 'profile',
        component: Profile,
        meta: { title: 'profile.settings' },
    },
    {
        path: '/contribute/event-proposal',
        name: 'events-suggest',
        component: SuggestEvent,
        meta: { title: 'contribute.suggest_event.title' },
    },
    {
        path: '/settings/blocks',
        name: 'settings-blocks',
        component: Blocks,
        meta: { title: 'user.blocked.heading2' },
    },
    {
        path: '/settings/mutes',
        name: 'settings-mutes',
        component: Mutes,
        meta: { title: 'user.muted.heading2' },
    },
    {
        path: '/settings/applications',
        name: 'settings-applications',
        component: Applications,
        meta: { title: 'your-apps' },
    },
    {
        path: '/settings/applications/:clientId/webhook-stats',
        name: 'settings-application-webhook-stats',
        component: WebhookStats,
        meta: { title: 'webhook-stats.title' },
    },
    {
        path: '/settings/profile',
        name: 'settings-profile',
        component: ProfileSettings,
        meta: { title: 'profile.settings' },
    },
    {
        path: '/settings/followers',
        name: 'settings-followers',
        component: Followers,
        meta: { title: 'menu.settings.myFollower' },
    },
    {
        path: '/settings/followings',
        name: 'settings-followings',
        component: Followings,
        meta: { title: 'menu.settings.followings' },
    },
    {
        path: '/settings/follow-requests',
        name: 'settings-follow-requests',
        component: FollowRequests,
        meta: { title: 'menu.settings.follower-requests' },
    },
    {
        path: '/settings/account',
        name: 'settings-account',
        component: Account,
        meta: { title: 'menu.settings' },
    },
    {
        path: '/settings/security',
        name: 'settings-security',
        component: Security,
        meta: { title: 'menu.settings' },
    },
    {
        path: '/settings/privacy',
        name: 'settings-privacy',
        component: Privacy,
        meta: { title: 'menu.privacy' },
    },
    {
        path: '/settings/wellbeing',
        name: 'settings-wellbeing',
        component: Wellbeing,
        meta: { title: 'settings.tab.wellbeing' },
    },
    {
        path: '/events',
        name: 'event-list',
        component: EventList,
        meta: { title: 'events' },
    },
    {
        path: '/changelog',
        name: 'changelog',
        component: Changelog,
        meta: { title: 'changelog' },
    },
    {
        path: '/leaderboard',
        name: 'leaderboard',
        component: Leaderboard,
        meta: { title: 'leaderboard' },
    },
    {
        path: '/leaderboard/monthly/:month',
        name: 'leaderboard-monthly',
        component: MonthlyLeaderboard,
    },
    {
        path: '/statuses/active',
        name: 'active-journeys',
        component: ActiveJourneys,
        meta: { title: 'menu.active' },
    },
    {
        path: '/statistics',
        name: 'statistics',
        component: Statistics,
        meta: { title: 'stats' },
    },
    {
        path: '/statistics/daily/:dateString',
        name: 'stats-daily',
        component: DailyStats,
    },
    {
        path: '/stationboard',
        name: 'stationboard',
        component: Stationboard,
        meta: { title: 'stationboard' },
    },
    {
        path: '/checkin',
        name: 'checkin',
        component: CheckinPage,
        meta: { title: 'stationboard.btn-checkin' },
    },
    {
        path: '/tickets',
        name: 'tickets',
        component: Tickets,
        meta: { title: 'tickets.title', requiresClosedBeta: true },
    },
    {
        path: '/tickets/:id',
        name: 'ticket-detail',
        component: TicketDetail,
        meta: { title: 'tickets.title', requiresClosedBeta: true },
    },
    {
        path: '/trip/create',
        name: 'trip-create',
        component: TripCreation,
    },
    {
        path: '/export',
        name: 'export',
        component: Export,
        meta: { title: 'export.title' },
    },
    {
        path: '/@:username',
        name: 'user-profile',
        component: UserProfile,
    },
    {
        path: '/status/:id',
        name: 'single-status',
        component: SingleStatus,
    },
    {
        path: '/event/:slug',
        name: 'event-view',
        component: EventView,
    },
    {
        path: '/notifications',
        name: 'notifications',
        component: Notifications,
        meta: { title: 'notifications.title' },
    },
    {
        path: '/search',
        name: 'search',
        component: Search,
        meta: { title: 'search-results' },
    },
    {
        path: '/settings',
        redirect: '/settings/profile',
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: ErrorPage,
        props: { code: 404, standalone: true },
        meta: { title: 'error.404' },
    },
];

export default routes;
