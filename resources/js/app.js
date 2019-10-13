/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("jquery");
require("./bootstrap");
require("./appControls");
require("awesomplete/awesomplete");
require("leaflet/dist/leaflet.js");
window.Vue = require("vue");

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component(
    "example-component",
    require("./components/ExampleComponent.vue").default
);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: "#app"
});

window.addEventListener("load", function() {
    require("bootstrap-cookie-alert/cookiealert");
    require("./components/statusMap.js");
    require("./components/progressbar.js");
});

require("./components/timepicker.js");

window.onload = function() {
    let delays = document.getElementsByClassName("traindelay");
    for (let i = 0; i < delays.length; i++) {
        let delay = delays[i].innerText;
        delay.slice(1);
        if (delay <= 3) {
            delays[i].classList.add("text-success");
        }
        if (delay > 3 && delay < 10) {
            delays[i].classList.add("text-warning");
        }
        if (delay >= 10) {
            delays[i].classList.add("text-danger");
        }
    }
};
