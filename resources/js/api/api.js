'use strict';

require('./UserReport');

window.API = class API {

    static request(path, method = 'GET', data = []) {
        return fetch('/api/v1' + path, {
            method: method,
            headers: {
                "Content-Type": "application/json",
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data),
        });
    }
}
