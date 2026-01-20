import * as Popper from '@popperjs/core';
import 'lodash';

document.addEventListener('DOMContentLoaded', function () {
    try {
        window.Popper = Popper;

        import('bootstrap/js/dist/collapse');
        import('bootstrap/js/dist/alert');
        import('bootstrap/js/dist/button');
        import('bootstrap/js/dist/tab');
        import('bootstrap/js/dist/dropdown');
    } catch (e) {
        throw new Error(e);
    }
});
