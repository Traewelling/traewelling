import {Event} from "../api/Event";

document.querySelector('form#event-suggest')?.addEventListener('submit', function (event) {
    event.preventDefault();

    Event.suggest(
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
