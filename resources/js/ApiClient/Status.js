import axios from "axios";
import {catchError} from "./Helpers";

export default class Status {
    static get() {
        return new Promise(function (resolve, reject) {
            axios
                .get(`/statuses`)
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((errors) => {
                    reject(catchError(errors));
                });
        });
    }

    static getById(id) {
        return new Promise(function (resolve, reject) {
            axios
                .get(`/statuses/${id}`)
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((errors) => {
                    reject(catchError(errors));
                });
        });
    }

    static fetchPolyLine(id) {
        return new Promise(function (resolve, reject) {
            axios
                .get(`/polyline/${id}`)
                .then((response) => {
                    resolve([response.data.data]);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    /**
     * for multiple tripIds separate the Ids with ,
     * @param tripIds
     * @returns {Promise<unknown>}
     */
    static fetchStopovers(tripIds) {
        return new Promise(function (resolve, reject) {
            axios
                .get(`/stopovers/${tripIds}`)
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static fetchLikes(statusId) {
        return new Promise(function (resolve, reject) {
            axios
                .get(`/statuses/${statusId}/likedby`)
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static like(statusId) {
        return new Promise(function (resolve, reject) {
            axios
                .post(`/like/${statusId}`)
                .then(() => {
                    resolve();
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static dislike(statusId) {
        return new Promise(function (resolve, reject) {
            axios
                .delete(`/like/${statusId}`)
                .then(() => {
                    resolve();
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static delete(statusId) {
        return new Promise(function (resolve, reject) {
            axios
                .delete(`/statuses/${statusId}`)
                .then(() => {
                    resolve()
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static dummy(statusId) {
        return new Promise(function (resolve, reject) {
            axios
                .get(`/statuses/${statusId}/likedby`)
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }
}
