window.$ = window.jQuery = require("jquery");
require("admin-lte");
const Chart = require("chart.js");

window.addEventListener("load", () => {
    require("./components/usageBoard");
});

// For the event tabs:
require("bootstrap/js/dist/tab");