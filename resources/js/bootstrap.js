window._ = require("lodash");

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */
document.addEventListener("DOMContentLoaded", function () {
    try {
        window.Popper = require('@popperjs/core');
        window.$      = window.jQuery = require("jquery");

        require("./../../node_modules/bootstrap/js/dist/collapse");
        require("./../../node_modules/bootstrap/js/dist/alert");
        require("./../../node_modules/bootstrap/js/dist/button");
        require("./../../node_modules/bootstrap/js/dist/modal");
        require("./../../node_modules/bootstrap/js/dist/tab");
        require("./../../node_modules/bootstrap/js/dist/dropdown");
        require("./../../node_modules/mdb-ui-kit/js/mdb.min");
    } catch (e) {
        throw new Error(e);
    }
});
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require("axios");

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    throw new Error("CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token");
}
