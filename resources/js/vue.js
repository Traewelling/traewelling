/**
 * Here, we include all of our external dependencies
 */

import Vue from "vue";
import VueRouter from "vue-router";
import {router} from "./routes";
import App from "../components/App";
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
import {Notyf} from "notyf";
import "notyf/notyf.min.css";

Vue.config.productionTip = false;

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
Vue.prototype.$appName = process.env.MIX_APP_NAME;
// Set Vue router
Vue.router             = router;
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
        notFoundRedirect: {name: "dashboard"},
        forbiddenRedirect: {name: "dashboard"}
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
    tagIDKeyName: "vmid",
    refreshOnceOnNavigation: true
});

Vue.mixin({
    methods: {
        apiErrorHandler: (response) => {
            if (response.errors.length > 0) {
                response.errors.forEach((error) => {
                    this.notyf.error(error);
                });
            } else {
                this.notyf.error(this.i18n.get("_.messages.exception.general"));
            }
        },
        fetchMoreData(next) {
            return new Promise(function (resolve) {
                let returnObject = {};
                axios.get(next)
                    .then((response) => {
                        returnObject.data  = response.data.data;
                        returnObject.links = response.data.links;
                        resolve(returnObject);
                    })
                    .catch((error) => {
                        this.apiErrorHandler(error);
                    });
            });
        }
    },
});

new Vue({
    provide: () => {
        return {
            notyf: new Notyf({
                duration: 5000,
                position: {x: "center", y: "top"},
                dismissible: true
            })
        };
    },
    el: "#app",
    components: {App},
    router,
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("./components/alert");
    require("bootstrap-cookie-alert/cookiealert");
});
