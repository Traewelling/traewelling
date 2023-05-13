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
        window.mdb = require("./../../node_modules/mdb-ui-kit/js/mdb.min");
    } catch (e) {
        throw new Error(e);
    }
});
