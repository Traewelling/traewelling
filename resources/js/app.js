/**
 * Here, we include all of our external dependencies
 */
import {Notyf} from 'notyf';

require("./bootstrap");
require("awesomplete/awesomplete");
require("leaflet/dist/leaflet.js");

document.addEventListener("DOMContentLoaded", function () {
    window.notyf = new Notyf({
        duration: 5000,
        position: {x: "right", y: "top"}
    });
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("./components/alert");
    require("./components/ActiveJourneys");
    require("./components/notifications-board");
    require("./components/progressbar");
    require("./components/settings");
    require("./components/station-autocomplete");
    require("./components/stationboard");
    require("./components/stationboard-gps");
    require("./components/statusMap");
    require("./components/timepicker");
    require("./components/business-check-in");
    require("./../../node_modules/bootstrap/js/dist/modal");
    require("./appControls");
    require("bootstrap-cookie-alert/cookiealert");
});
