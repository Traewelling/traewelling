import axios from "axios";

export function catchError(error) {
    let errors   = [];
    let response = {
        status: 0,
        errors: [],
        meta: []
    };
    if (error.response.data.errors) {
        Object.entries(error.response.data.errors).forEach((err) => {
            errors.push(err[1][0]);
        });
    } else {
        errors.push(error.response.data.message);
    }

    response["status"] = error.response.status;
    response["errors"] = errors;
    if (error.response.data.meta) {
        response["meta"] = error.response.data.meta;
    }

    return response;
}

export function getContent(url, config = null) {
    return new Promise(function (resolve, reject) {
        axios
            .get(url, config)
            .then((response) => {
                resolve(response.data.data);
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}

export function getBody(url, config = null) {
    return new Promise(function (resolve, reject) {
        axios
            .get(url, config)
            .then((response) => {
                resolve(response.data);
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}

export function returnDataAsArray(url) {
    return new Promise(function (resolve, reject) {
        axios
            .get(url)
            .then((response) => {
                resolve([response.data.data]);
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}

export function returnVoid(url) {
    return new Promise(function (resolve, reject) {
        axios
            .post(url)
            .then(() => {
                resolve();
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}

export function postToApi(url, data) {
    return new Promise(function (resolve, reject) {
        axios
            .post(url, data)
            .then((result) => {
                resolve(result.data.data);
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}

export function postReturnRequest(url, data, config = null) {
    return new Promise(function (resolve, reject) {
        axios
            .post(url, data, config)
            .then((result) => {
                resolve(result);
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}

export function putToApi(url, data) {
    return new Promise(function (resolve, reject) {
        axios
            .put(url, data)
            .then((result) => {
                resolve(result.data.data);
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}

export function deleteFromApi(url, data = null) {
    return new Promise(function (resolve, reject) {
        axios
            .delete(url, {data})
            .then((result) => {
                resolve(result.data.data);
            })
            .catch((error) => {
                reject(catchError(error));
            });
    });
}
