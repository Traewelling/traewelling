import "awesomplete/awesomplete";

import "leaflet/dist/leaflet.js";
import "./components/maps";
import * as Popper from "@popperjs/core";
import "bootstrap";
import "leaflet";
import {createApp} from "vue";
import TripCreationForm from "../vue/components/TripCreation/TripCreationForm.vue";

window.addEventListener("load", () => {
    import("./components/station-autocomplete");
});

window.Popper = Popper;

document.addEventListener("DOMContentLoaded", function () {
    const admin = createApp({});
    admin.component("TripCreationForm", TripCreationForm);
    admin.mount("#trip-creation-form");
});
