/**
 * Here, we include all of our external dependencies
 */
import {Notyf} from "notyf";
import {createApp} from "vue";
import NotificationBell from "../vue/components/NotificationBell.vue";
import ActiveJourneyMap from "../vue/components/ActiveJourneyMap.vue";
import Stationboard from "../vue/components/Stationboard.vue";
import StationAutocomplete from "../vue/components/StationAutocomplete/StationAutocomplete.vue";
import "./bootstrap";
import "awesomplete/awesomplete";
import "leaflet/dist/leaflet.js";
import "./api/api";
import "./components/maps";
import CheckinSuccessHelper from "../vue/components/CheckinSuccessHelper.vue";
import {i18nVue} from "laravel-vue-i18n";
import TagHelper from "../vue/components/TagHelper.vue";
import TripCreationForm from "../vue/components/TripCreation/TripCreationForm.vue";
import {createPinia} from 'pinia'
import piniaPluginPersistedsState from 'pinia-plugin-persistedstate'
import FriendCheckinSettings from "../vue/components/Settings/FriendCheckinSettings.vue";
import WebhookSettings from "../vue/components/Settings/Webhooks.vue";

window.notyf = new Notyf({
    duration: 5000,
    position: {x: "right", y: window.innerWidth > 480 ? "top" : "bottom"},
    dismissible: true,
    ripple: true,
    types: [
        {
            type: "info",
            background: "#0dcaf0",
            icon: {
                className: "fa-solid fa-circle-info",
                color: "white",
                tagName: "i",
            },
        },
        {
            type: "warning",
            background: "#ffc107",
            icon: {
                className: "fa-solid fa-triangle-exclamation",
                tagName: "i",
                color: "white",
            },
        },
    ],
});

document.addEventListener("DOMContentLoaded", function () {
    // get language query parameter
    let fallbackLang = "en";
    const urlParams  = new URLSearchParams(window.location.search);
    const lang       = urlParams.get("language");
    const pinia      = createPinia();
    pinia.use(piniaPluginPersistedsState);

    if (lang && lang.startsWith("de_")) {
        fallbackLang = "de";
    }

    // TODO: As we add more vue components here, we should consider embedding them in a better way

    const i18nOptions = {
        fallbackLang: fallbackLang,
        fallbackMissingTranslations: true,
        resolve: (lang) => import(`../../lang/${lang}.json`)
    }

    if (document.getElementById("nav-main")) {
        const app = createApp({});
        app.component("NotificationBell", NotificationBell);
        app.use(pinia);
        app.use(i18nVue, i18nOptions);
        app.mount("#nav-main");
    }

    if (document.getElementById("activeJourneys")) {
        const app2 = createApp({});
        app2.component("ActiveJourneyMap", ActiveJourneyMap);
        app2.use(pinia);
        app2.use(i18nVue, i18nOptions);
        app2.mount("#activeJourneys");
    }

    if (document.getElementById("station-board-new")) {
        const app3 = createApp({});
        app3.component("Stationboard", Stationboard);
        app3.component("Stationautocomplete", StationAutocomplete);
        app3.use(pinia);
        app3.use(i18nVue, i18nOptions);
        app3.mount("#station-board-new");
    }

    if (document.getElementById("checkin-success-helper")) {
        const app4 = createApp({});
        app4.component("CheckinSuccessHelper", CheckinSuccessHelper);
        app4.use(i18nVue, i18nOptions);
        app4.mount("#checkin-success-helper");
    }

    if (document.getElementById("tag-helper")) {
        const app5 = createApp({});
        app5.component("TagHelper", TagHelper);
        app5.use(i18nVue, i18nOptions);
        app5.mount("#tag-helper");
    }

    if (document.getElementById("trip-creation-form")) {
        const app6 = createApp({});
        app6.component("TripCreationForm", TripCreationForm);
        app6.use(i18nVue, i18nOptions);
        app6.mount("#trip-creation-form");
    }

    if (document.getElementById("settings-friend-checkin")) {
        const app7 = createApp({});
        app7.component("FriendCheckinSettings", FriendCheckinSettings);
        app7.use(i18nVue, i18nOptions);
        app7.use(pinia);
        app7.mount("#settings-friend-checkin");
    }

    if (document.getElementById("settings-webhooks")) {
        const app8 = createApp({});
        app8.component("Webhooks", WebhookSettings);
        app8.use(i18nVue, i18nOptions);
        app8.use(pinia);
        app8.mount("#settings-webhooks");
    }
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    import("./components/DarkModeToggle");
    import("./components/Event");
    import("./components/progressbar");
    import("./components/settings");
    import("./components/station-autocomplete");
    import("./components/stationboard");
    import("./components/stationboard-gps");
    import("./components/Status");
    import("./components/timepicker");
    import("./components/export");
    import("./components/business-check-in");
    import("./appControls");
    import("bootstrap-cookie-alert/cookiealert");
});
