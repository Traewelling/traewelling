/**
 * Here, we include all of our external dependencies
 */
import { Notyf } from 'notyf';
import { createApp } from 'vue';
import NotificationBell from "../vue/components/NotificationBell.vue";
import ActiveJourneyMap from "../vue/components/ActiveJourneyMap.vue";
import Stationboard from "../vue/components/Stationboard.vue";
import StationAutocomplete from "../vue/components/StationAutocomplete.vue";
import "./bootstrap";
import "awesomplete/awesomplete";
import "leaflet/dist/leaflet.js";
import "./api/api";
import "./components/maps";
import CheckinSuccessHelper from "../vue/components/CheckinSuccessHelper.vue";

window.notyf = new Notyf({
    duration: 5000,
    position: { x: "right", y: window.innerWidth > 480 ? "top" : "bottom" },
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

document.addEventListener("DOMContentLoaded", function() {

    const app = createApp({});
    app.component('NotificationBell', NotificationBell);
    app.config.devtools = true;
    app.mount('#nav-main');

    const app2 = createApp({});
    app2.component('ActiveJourneyMap', ActiveJourneyMap);
    app2.mount('#activeJourneys');

    const app3 = createApp({});
    app3.component('Stationboard', Stationboard);
    app3.component('Stationautocomplete', StationAutocomplete);
    app3.mount('#station-board-new');

    const app4 = createApp({});
    app4.component('CheckinSuccessHelper', CheckinSuccessHelper);
    app4.mount('#checkin-success-helper');

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
    import("./appControls");
    import("bootstrap-cookie-alert/cookiealert");
});
