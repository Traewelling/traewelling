'use strict';

export default class API {

    static request(path, method = 'GET', data = {}, customErrorHandling = false) {
        let requestBody = undefined;

        if (method !== 'GET' && data !== {}) {
            requestBody = JSON.stringify(data);
        }
        let request = fetch('/api/v1' + path, {
            method: method,
            headers: {
                "Content-Type": "application/json"
            },
            body: requestBody,
        });
        if (!customErrorHandling) {
            request.catch(this.handleGenericError);
        }
        return request;
    }

    static handleDefaultResponse(response) {
        if (!response.ok) {
            return response.json().then(this.handleGenericError);
        }

        return response.json().then(data => {
            notyf.success(data.data.message);
        });
    }

    static handleGenericError(error) {
        console.error(error);
        let errorMessage = error?.message ?? error?.data?.message ?? 'An unknown error occured.';
        notyf.error(errorMessage);
        return error;
    }
}
