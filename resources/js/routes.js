import Vue from "vue";
import VueRouter from "vue-router";
import ActiveStatuses from "../components/views/ActiveStatuses";
import SingleStatus from "../components/views/SingleStatus";
import Profile from "../components/views/Profile";
import Event from "../components/views/Event";
import Leaderboard from "../components/views/Leaderboard";
import LeaderboardMonth from "../components/views/LeaderboardMonth";
import Login from "../components/Login";
import Dashboard from "../components/views/Dashboard";
import Stationboard from "../components/views/Stationboard";
import Trip from "../components/views/Trip";
import Index from "../components/views/Index";
import About from "../components/views/About";
import Charts from "../components/views/Statistics";
import SearchView from "../components/views/SearchView";
import SettingsView from "../components/views/Settings";

Vue.use(VueRouter);

export const router = new VueRouter({
    mode: "history",
    linkActiveClass: "active",
    routes:
        [
            {
                path: "/",
                component: Index,
                name: "index",
                meta: {
                    auth: false
                }
            },
            {
                path: "/about",
                component: About,
                name: "about"
            },
            {
                path: "/statuses/active",
                component: ActiveStatuses,
                name: "statuses.active"
            },
            {
                path: "/status/:id",
                component: SingleStatus,
                props: true,
                name: "singleStatus"
            },
            {
                path: "/@:username",
                component: Profile,
                name: "profile"
            },
            {
                path: "/profile/:username",
                redirect: "/@:username"
            },
            {
                path: "/event/:slug",
                component: Event,
                name: "event"
            },
            {
                path: "/leaderboard/",
                component: Leaderboard,
                name: "leaderboard"
            },
            {
                path: "/leaderboard/:month",
                component: LeaderboardMonth,
                name: "leaderboard.month",
            },
            {
                path: "/login",
                component: Login,
                name: "auth.login",
                meta: {
                    auth: false
                }
            },
            {
                path: "/dashboard",
                component: Dashboard,
                name: "dashboard",
                meta: {
                    auth: true
                }
            },
            {
                path: "/dashboard/global",
                component: Dashboard,
                name: "dashboard.global",
                meta: {
                    auth: true
                }
            },
            {
                path: "/trains/stationboard",
                component:Stationboard,
                name: "trains.stationboard",
                meta: {
                    auth: true
                }
            },
            {
                path: "/trains/trip",
                component: Trip,
                name: "trains.trip",
                meta: {
                    auth: true
                }
            },
            {
                path: "/statistics",
                component: Charts,
                name: "statistics",
                meta: {
                    auth: true
                }
            },
            {
                path: "/search",
                component: SearchView,
                name: "search",
                meta: {
                    auth: true
                }
            },
            {
                path: "/settings",
                component: SettingsView,
                name: "settings",
                meta: {
                    auth: true
                }
            }
        ],
    base: "/",
});
