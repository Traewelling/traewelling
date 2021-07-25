import Vue from "vue";
import VueRouter from "vue-router";
import ActiveStatuses from "./views/ActiveStatuses";
import SingleStatus from "./views/SingleStatus";
import Profile from "./views/Profile";
import Event from "./views/Event";
import Leaderboard from "./views/Leaderboard";
import LeaderboardMonth from "./views/LeaderboardMonth";
import Login from "./components/Login";
import Dashboard from "./components/Dashboard";
import Stationboard from "./views/Stationboard";
import Trip from "./views/Trip";

Vue.use(VueRouter);

export const router = new VueRouter({
    mode: "history",
    linkActiveClass: "active",
    routes:
        [
            {
                path: "/",
                name: "base",
                redirect: {name: "statuses.active"},
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
                path: "/profile/:username",
                component: Profile,
                name: "profile"
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
                component:Trip,
                name: "trains.trip",
                meta: {
                    auth: true
                }
            }
        ],
    base: "/",
});
