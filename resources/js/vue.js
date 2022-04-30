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

Vue.prototype.notyf = new Notyf({
    duration: 5000,
    position: {x: "center", y: "top"},
    dismissible: true
});
// Set Vue router
Vue.router          = router;
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

new Vue({
    el: "#app",
    components: {App},
    router,
});

Vue.mixin({
    methods: {
        apiErrorHandler: (response) => {
            if (response.status === 406) {
                if (router.currentRoute.name !== "privacy") {
                    //we do not need any "privacy policy not accepted" errors on the privacy policy
                    router.push({
                        name: "privacy", query: {
                            validFrom: response.meta.validFrom,
                            acceptedAt: response.meta.acceptedAt
                        }
                    });
                }
            } else {
                if (response.errors.length > 0) {
                    response.errors.forEach((error) => {
                        Vue.prototype.notyf.error(error);
                    });
                } else {
                    Vue.prototype.notyf.error(this.i18n.get("_.messages.exception.general"));
                }
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
        },
        localizeThousands(number, fixed = 0) {
            return parseFloat(number.toFixed(fixed)).toLocaleString(Vue.prototype.i18n.getLocale());
        },
        localizeDistance(distance) {
            return this.localizeThousands(distance / 1000, 1);
        },
        hoursAndMinutes(duration) {
            const dur   = moment.duration(duration, "minutes").asMinutes();
            let minutes = dur % 60;
            let hours   = Math.floor(dur / 60);

            return "".concat(
                hours.toString(),
                this.i18n.get("_.time.hours.short"),
                " ",
                minutes.toString(),
                this.i18n.get("_.time.minutes.short")
            );
        },
        fullTime(minutes, short = false) {
            const duration = moment.duration(minutes, "minutes");
            let append     = "";
            if (short) {
                append = ".short";
            }

            let output = "";
            if (duration.years()) {
                output = output.concat(duration.years().toString(), this.i18n.get("_.time.years" + append), " ");
            }
            if (duration.months()) {
                output = output.concat(duration.months().toString(), this.i18n.get("_.time.months" + append), " ");
            }
            if (duration.days()) {
                output = output.concat(duration.days().toString(), this.i18n.get("_.time.days" + append), " ");
            }
            if (duration.hours()) {
                output = output.concat(duration.hours().toString(), this.i18n.get("_.time.hours" + append), " ");
            }
            if (duration.minutes()) {
                output = output.concat(duration.minutes().toString(), this.i18n.get("_.time.minutes" + append), " ");
            }

            return output;
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


axios.interceptors.request.use(function (config) {
    config.headers["User-Agent"] = "vue-mdb-spa";

    return config;
});
