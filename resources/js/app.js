/**
 * Here, we include all of our external dependencies
 */
require("jquery");

require("./bootstrap");
require("./appControls");
require("awesomplete/awesomplete");
require("leaflet/dist/leaflet.js");

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("./components/alert");
    require("./components/notifications-board");
    require("./components/progressbar");
    require("./components/pwa_fix");
    require("./components/settings");
    require("./components/station-autocomplete");
    require("./components/stationboard");
    require("./components/statusMap");
    require("./components/timepicker");
    require("bootstrap-cookie-alert/cookiealert");
});
