'use strict';

export default class API {
    static request(path, method = 'GET', data = {}, customErrorHandling = false) {
        let requestBody = undefined;

        if (method !== 'GET' && data != {}) {
            requestBody = JSON.stringify(data);
        }
        let request = fetch('/api/v1' + path, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: requestBody,
        });
        if (!customErrorHandling) {
            request.catch(this.handleGenericError);
        }
        return request;
    }
    static handleGenericError(error) {
        console.error(error);
        let errorMessage = error?.message ?? error?.data?.message ?? 'An unknown error occured.';
        // eslint-disable-next-line no-undef
        notyf.error(errorMessage);
        return error;
    }
}
