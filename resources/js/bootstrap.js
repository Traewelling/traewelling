import "lodash";
import jQuery from "jquery";
import * as Popper from '@popperjs/core';
import { mdb } from "mdb-ui-kit/js/mdb.min";
/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */
document.addEventListener("DOMContentLoaded", function () {
    try {
        window.Popper = Popper;
        window.$ = window.jQuery = jQuery;

        import("bootstrap/js/dist/collapse");
        import("bootstrap/js/dist/alert");
        import("bootstrap/js/dist/button");
        import("bootstrap/js/dist/modal");
        import("bootstrap/js/dist/tab");
        import("bootstrap/js/dist/dropdown");
        window.mdb = mdb;
    } catch (e) {
        throw new Error(e);
    }
});
