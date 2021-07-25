/**
 * Here, we include all of our external dependencies
 */

import Vue from "vue";
import VueRouter from "vue-router";
import {router} from "../routes";
import App from "../views/App";
import moment from "moment";
import Lang from "lang.js";
import {i18nStrings} from "./translations";
import axios from "axios";
import VueAxios from "vue-axios";
import VueLocalStorage from "vue-localstorage";
import auth from "@websanova/vue-auth/dist/v2/vue-auth.esm.js";
import driverAuthBearer from "@websanova/vue-auth/dist/drivers/auth/bearer.esm.js";
import driverHttpAxios from "@websanova/vue-auth/dist/drivers/http/axios.1.x.esm.js";
import driverRouterVueRouter from "@websanova/vue-auth/dist/drivers/router/vue-router.2.x.esm.js";
import VueMeta from "vue-meta";

require("jquery");

require("./bootstrap");
require("awesomplete/awesomplete");
require("leaflet/dist/leaflet.js");

window.Vue = require("vue");

Vue.use(VueLocalStorage);
let currentLocale = Vue.localStorage.get("language");
if (Vue.localStorage.get("language") == null && navigator.language) {
    currentLocale = navigator.language.substr(0, 2);
}
Vue.prototype.i18n   = new Lang({
    messages: i18nStrings,
    locale: currentLocale,
    fallback: "en"
});
Vue.prototype.moment = moment;
Vue.prototype.moment.locale(Vue.prototype.i18n.getLocale().substr(0, 2));
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

Vue.use(VueMeta, {
    tagIDKeyName: 'vmid',
    refreshOnceOnNavigation: true
})

new Vue({
    el: "#app",
    components: {App},
    router,
    metaInfo() {
        return {
            title: "Träwelling",
            titleTemplate: "%s - Träwelling",//ToDo get name from .env
            htmlAttrs: {
                lang: this.i18n.getLocale()
            },
            meta: [
                {name: "charset", "content": "utf-8"},
                {name: "viewport", content: "width=device-width, initial-scale=1"},
                {name: "apple-mobile-web-app-capable", content: "yes"},
                {name: "apple-mobile-web-app-status-bar-style", content: "#c72730"},
                {name: "mobile-web-app-capable", content: "yes"},
                {name: "theme-color", content: "#c72730"},
                {name: "name", content: "Träwelling"}, //ToDo get name from .env

                {name: "copyright", content: "Träwelling Team"},
                {name: "description", content: this.i18n.get("_.about.block1"), vmid: "description"},
                {
                    name: "keywords",
                    content: "Träwelling, Twitter, Deutsche, Bahn, Travel, Check-In, Zug, Bus, Tram, Mastodon"
                },
                {name: "audience", conent: "Travellers"},
                {name: "DC.Rights", content: "Träwelling Team"},
                {name: "DC.Description", content: this.i18n.get('_.about.block1'), vmid: "DC.Description"},
                {name: "DC.Language", content: this.i18n.getLocale()},
                {property: "og:title", content: "Träwelling", vmid: "og:title"}, //ToDo get name from .env
                {property: "og:site_name", content: "Träwelling"}, //ToDo get name from .env
                {property: "og:type", content: "website"},
                {name: "robots", content: "index,follow", vmid: "robots"}
            ]
        }
    },
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("./components/alert");
    require("bootstrap-cookie-alert/cookiealert");
});
