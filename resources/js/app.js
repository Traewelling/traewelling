/**
 * Here, we include all of our external dependencies
 */
import {Notyf} from 'notyf';
import {createApp} from 'vue';
import NotificationBell from "../vue/components/NotificationBell.vue";

require("./bootstrap");
require("awesomplete/awesomplete");
require("leaflet/dist/leaflet.js");
require("./api/api");
require("./components/maps");


document.addEventListener("DOMContentLoaded", function () {

    const app = createApp({
        data() {
            return{
                count: 0
            }
        }
    });

    app.component('NotificationBell', NotificationBell);
    app.config.devtools = true;
    app.mount('#nav-main');

    window.notyf = new Notyf({
        duration: 5000,
        position: {x: "right", y: "top"},
        dismissible: true,
        ripple: true,
        types: [
            {
                type: 'info',
                background: '#0dcaf0',
                icon: {
                    className: 'fa-solid fa-circle-info',
                    color: 'white',
                    tagName: 'i',
                }
            },
            {
                type: 'warning',
                background: '#ffc107',
                icon: {
                    className: 'fa-solid fa-triangle-exclamation',
                    tagName: 'i',
                    color: 'white',
                }
            },
        ]
    });
});

/**
 * Once the page is loaded, we can load our frontend components.
 */
window.addEventListener("load", () => {
    require("./components/alert");
    require("./components/ActiveJourneys");
    require("./components/Event");
    require("./components/progressbar");
    require("./components/settings");
    require("./components/station-autocomplete");
    require("./components/stationboard");
    require("./components/stationboard-gps");
    require("./components/Status");
    require("./components/statusMap");
    require("./components/timepicker");
    require("./components/business-check-in");
    require("./../../node_modules/bootstrap/js/dist/modal");
    require("./appControls");
    require("bootstrap-cookie-alert/cookiealert");
});
