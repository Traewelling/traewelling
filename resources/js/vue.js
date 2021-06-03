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
import { i18nStrings } from "./languages";

window.Vue = require("vue");

Vue.prototype.i18n = new Lang({
    messages: i18nStrings,
    locale: 'de',
    fallback: 'en'
});

Vue.prototype.moment = moment;
Vue.prototype.moment.locale(Vue.prototype.i18n.getLocale());

Vue.use(VueRouter);

const layoutOne = new Vue({
    el: "#app",
    components: { App },
    router,
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("./components/alert");
    require("./components/notifications-board");
    require("./components/progressbar");
    require("./components/settings");
    require("./components/station-autocomplete");
    require("./components/stationboard");
    require("./components/statusMap");
    require("./components/timepicker");
    require("./components/business-check-in");
    require("./../../node_modules/bootstrap/js/dist/modal");
    require("./appControls");
    require("bootstrap-cookie-alert/cookiealert");
});
