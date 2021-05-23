import Vue from "vue";
import VueRouter from "vue-router";
import Statuses from "./views/Statuses";
import SingleStatus from "./views/SingleStatus";
import Profile from "./views/Profile";

Vue.use(VueRouter);

export const router = new VueRouter({
    mode: "history",
    routes:
        [
            {
                path: "/",
                component: Statuses,
                name: "statuses.active",
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
            }

        ],
    base: "/",
});
