const Chart = require("chart.js");
require("awesomplete/awesomplete");

window.addEventListener("load", () => {
    require("./components/usageBoard");
    require("./components/station-autocomplete");
});

window.Popper = require("@popperjs/core");

require("bootstrap");
require("leaflet");
