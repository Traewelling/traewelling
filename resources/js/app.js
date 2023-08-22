/**
 * Here, we include all of our external dependencies
 */
import { Notyf } from 'notyf';
import { createApp } from 'vue';
import NotificationBell from "../vue/components/NotificationBell.vue";
import ActiveJourneyMap from "../vue/components/ActiveJourneyMap.vue";
import "./bootstrap";
import "awesomplete/awesomplete";
import "leaflet/dist/leaflet.js";
import "./api/api";
import "./components/maps";

document.addEventListener("DOMContentLoaded", function () {

    const app = createApp({});
    app.component('NotificationBell', NotificationBell);
    app.config.devtools = true;
    app.mount('#nav-main');

    const app2 = createApp({});
    app2.component('ActiveJourneyMap', ActiveJourneyMap);
    app2.mount('#activeJourneys');

    window.notyf = new Notyf({
        duration: 5000,
        position: { x: "right", y: "top" },
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
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    import("./components/DarkModeToggle");
    import("./components/alert");
    import("./components/Event");
    import("./components/progressbar");
    import("./components/settings");
    import("./components/station-autocomplete");
    import("./components/stationboard");
    import("./components/stationboard-gps");
    import("./components/Status");
    import("./components/timepicker");
    import("./components/business-check-in");
    import("bootstrap/js/dist/modal");
    import("./appControls");
    import("bootstrap-cookie-alert/cookiealert");
});
