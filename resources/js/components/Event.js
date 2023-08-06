'use strict';
import API from "../api/api";

document.querySelector('form#event-suggest')?.addEventListener('submit', function (event) {
    event.preventDefault();

    TrwlEvent.suggest(
        document.querySelector('form#event-suggest input[name="name"]').value,
        document.querySelector('form#event-suggest input[name="host"]').value,
        document.querySelector('form#event-suggest input[name="begin"]').value,
        document.querySelector('form#event-suggest input[name="end"]').value,
        document.querySelector('form#event-suggest input[name="url"]').value,
        document.querySelector('form#event-suggest input[name="hashtag"]').value,
        document.querySelector('form#event-suggest input[name="nearestStation"]').value,
    );

    // clear form
    document.querySelector('form#event-suggest input[name="name"]').value           = '';
    document.querySelector('form#event-suggest input[name="host"]').value           = '';
    document.querySelector('form#event-suggest input[name="begin"]').value          = '';
    document.querySelector('form#event-suggest input[name="end"]').value            = '';
    document.querySelector('form#event-suggest input[name="url"]').value            = '';
    document.querySelector('form#event-suggest input[name="hashtag"]').value        = '';
    document.querySelector('form#event-suggest input[name="nearestStation"]').value = '';
});
window.TrwlEvent = class TrwlEvent {

    static suggest(name, host, begin, end, url, hashtag, nearestStation) {
        API.request('/event', 'POST', {
            name, host, begin, end, url, hashtag, nearestStation
        }).then(API.handleDefaultResponse);
    }
}
