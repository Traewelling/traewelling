import "awesomplete/awesomplete";

import "leaflet/dist/leaflet.js";
import "./components/maps";
import * as Popper from "@popperjs/core";
import "bootstrap";
import "leaflet";

window.addEventListener("load", () => {
    import("./components/station-autocomplete");
});

window.Popper = Popper;
