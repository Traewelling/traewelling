import "awesomplete/awesomplete";

window.addEventListener("load", () => {
    import("./components/station-autocomplete");
});

import * as Popper from "@popperjs/core";
window.Popper = Popper;

import "bootstrap";
import "leaflet";
