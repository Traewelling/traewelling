'use strict';

window.API = class API {

    static request(path, method = 'GET', data = []) {
        return fetch('/api/v1' + path, {
            method: method,
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data),
        });
    }
}
