'use strict';

window.API = class API {

    static request(path, method = 'GET', data = [], notifyErrors = true) {
        let requestBody = undefined;

        if (method !== 'GET' && data.length > 0) {
            requestBody = JSON.stringify(data);
        }

        return fetch('/api/v1' + path, {
            method: method,
            headers: {
                "Content-Type": "application/json"
            },
            body: requestBody,
        })
            .then(response => {
                if (notifyErrors && !response.ok) {
                    return response.json().then(API.handleGenericError);
                }
                return response;
            })
            .catch(API.handleGenericError);
    }

    static handleGenericError(error) {
        console.error(error);
        let errorMessage = error?.message ?? error?.data?.message ?? 'An unknown error occured.';
        notyf.error(errorMessage);
        return error;
    }
}
