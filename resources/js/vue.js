/**
 * Here, we include all of our external dependencies
 */

import Vue from "vue";

require("jquery");

require("./bootstrap");
require("awesomplete/awesomplete");
require("leaflet/dist/leaflet.js");

import VueRouter from "vue-router";
import {router} from "../routes";
import App from "../views/App";
import moment from "moment";
import Lang from "lang.js";
import {i18nStrings} from "./languages";
import axios from "axios";
import VueAxios from "vue-axios";
import auth from "@websanova/vue-auth/dist/v2/vue-auth.esm.js";
import driverAuthBearer from "@websanova/vue-auth/dist/drivers/auth/bearer.esm.js";
import driverHttpAxios from "@websanova/vue-auth/dist/drivers/http/axios.1.x.esm.js";
import driverRouterVueRouter from "@websanova/vue-auth/dist/drivers/router/vue-router.2.x.esm.js";

window.Vue = require("vue");

Vue.prototype.i18n = new Lang({
    messages: i18nStrings,
    locale: "de",
    fallback: "en"
});

Vue.prototype.moment = moment;
Vue.prototype.moment.locale(Vue.prototype.i18n.getLocale());
// Set Vue router
Vue.router = router;
Vue.use(VueRouter);

axios.defaults.baseURL = "/api/v1";
Vue.use(VueAxios, axios);

Vue.use(auth, {
    plugins: {
        http: Vue.axios, // Axios
        // http: Vue.http, // Vue Resource
        router: Vue.router,
    },
    drivers: {
        auth: driverAuthBearer,
        http: driverHttpAxios,
        router: driverRouterVueRouter
    },
    options: {
        rolesKey: "type",
        notFoundRedirect: {name: "statuses.active"},
    },
    tokenDefaultName: "laravel-vue-spa",
    tokenStore: ["localStorage"],
    // rolesVar: "role",
    registerData: {url: "auth/register", method: "POST", redirect: "/login"},
    loginData: {url: "auth/login", method: "POST", redirect: "/dashboard", fetchUser: false},
    logoutData: {url: "auth/logout", method: "POST", redirect: "/", makeRequest: true},
    fetchData: {url: "auth/user", method: "GET", enabled: true},
    // refreshData: {url: "auth/refresh", method: "GET", enabled: true, interval: 30}
});

new Vue({
    el: "#app",
    components: {App},
    router,
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("./components/alert");
    require("./components/station-autocomplete");
    require("./components/stationboard");
    require("bootstrap-cookie-alert/cookiealert");
});
