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

Vue.use(VueRouter);

export const router = new VueRouter({
    mode: "history",
    linkActiveClass: "active",
    routes:
        [
            {
                path: "/",
                redirect: { name: "statuses.active" },
                meta: {
                    auth: false
                }
            },
            {
                path: "/statuses/active",
                component: ActiveStatuses,
                name: "statuses.active",
                meta: {
                    auth: false
                }
            },
            {
                path: "/status/:id",
                component: SingleStatus,
                props: true,
                name: "singleStatus",
                meta: {
                    auth: false
                }
            },
            {
                path: "/profile/:username",
                component: Profile,
                name: "profile",
                meta: {
                    auth: false
                }
            },
            {
                path: "/event/:slug",
                component: Event,
                name: "event",
                meta: {
                    auth: false
                }
            },
            {
                path: "/leaderboard/",
                component: Leaderboard,
                name: "leaderboard",
                meta: {
                    auth: false
                }
            },
            {
                path: "/leaderboard/:month",
                component: LeaderboardMonth,
                name: "leaderboard.month",
                meta: {
                    auth: false
                }
            },
            {
                path: "/login",
                component: Login,
                name: "auth.login"
            },
            {
                path: "/dashboard",
                component: Dashboard,
                name: "dashboard",
                meta: {
                    auth: true
                }
            }

        ],
    base: "/",
});
