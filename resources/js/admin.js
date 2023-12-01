import "awesomplete/awesomplete";

window.addEventListener("load", () => {
    import("./components/station-autocomplete");
});

import * as Popper from "@popperjs/core";
window.Popper = Popper;

import "bootstrap";
import "leaflet";
import {createApp} from "vue";
import TripCreationForm from "../vue/components/TripCreation/TripCreationForm.vue";

document.addEventListener("DOMContentLoaded", function() {
    const admin = createApp({});
    admin.component('TripCreationForm', TripCreationForm);
    admin.mount('#trip-creation-form');

});
