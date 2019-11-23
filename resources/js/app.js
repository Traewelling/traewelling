/**
 * Here, we include all of our external dependencies
 */
require("jquery");
window.bsCustomFileInput = require("mdbootstrap/js/modules/bs-custom-file-input");
require("./bootstrap");
require("./appControls");
require("awesomplete/awesomplete");
require("leaflet/dist/leaflet.js");

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("bootstrap-cookie-alert/cookiealert");
    require("./components/progressbar");
    require("./components/stationboard");
    require("./components/statusMap");
    require("./components/timepicker");
    require("./components/settings");
    require("./components/alert");
    require("./components/pwa_fix");
});
