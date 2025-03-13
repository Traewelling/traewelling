import "lodash";
import * as Popper from '@popperjs/core';

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */
document.addEventListener("DOMContentLoaded", function () {
    try {
        window.Popper = Popper;

        import("bootstrap/js/dist/collapse");
        import("bootstrap/js/dist/alert");
        import("bootstrap/js/dist/button");
        import("bootstrap/js/dist/tab");
        import("bootstrap/js/dist/dropdown");
    } catch (e) {
        throw new Error(e);
    }
});
