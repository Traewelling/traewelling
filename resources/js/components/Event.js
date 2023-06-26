'use strict';

document.querySelector('form#event-suggest')?.addEventListener('submit', function (event) {
    event.preventDefault();

    TrwlEvent.suggest(
        document.querySelector('form#event-suggest input[name="name"]').value,
        document.querySelector('form#event-suggest input[name="host"]').value,
        document.querySelector('form#event-suggest input[name="begin"]').value,
        document.querySelector('form#event-suggest input[name="end"]').value,
        document.querySelector('form#event-suggest input[name="url"]').value,
    );

    // clear form
    document.querySelector('form#event-suggest input[name="name"]').value  = '';
    document.querySelector('form#event-suggest input[name="host"]').value  = '';
    document.querySelector('form#event-suggest input[name="begin"]').value = '';
    document.querySelector('form#event-suggest input[name="end"]').value   = '';
    document.querySelector('form#event-suggest input[name="url"]').value   = '';
});
window.TrwlEvent = class TrwlEvent {

    static suggest(name, host, begin, end, url) {
        API.request('/event', 'POST', {
            name, host, begin, end, url,
        }).then(API.handleDefaultResponse);
    }

    static deleteCategory(id, categoryId) {
        API.request(`/event/${id}/category`, "DELETE", {category_id: categoryId})
            .then(API.handleDefaultResponse);
    }

    static addCategory(id, categoryId) {
        API.request(`/event/${id}/category`, "POST", {category_id: categoryId})
            .then(API.handleDefaultResponse);
    }
}
