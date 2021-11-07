import {deleteFromApi, getContent, postToApi, returnDataAsArray} from "./Helpers";

export default class Status {
    static get() {
        return getContent("/statuses");
    }

    static getById(id) {
        return getContent(`/statuses/${id}`);
    }

    static fetchPolyLine(id) {
        return returnDataAsArray(`/polyline/${id}`);
    }

    /**
     * for multiple tripIds separate the Ids with ,
     * @param tripIds
     * @returns {Promise<unknown>}
     */
    static fetchStopovers(tripIds) {
        return getContent(`/stopovers/${tripIds}`);
    }

    static fetchLikes(statusId) {
        return getContent(`/statuses/${statusId}/likedby`);
    }

    static like(statusId) {
        return postToApi(`/like/${statusId}`);
    }

    static dislike(statusId) {
        return deleteFromApi(`/like/${statusId}`);
    }

    static delete(statusId) {
        return deleteFromApi(`/statuses/${statusId}`);
    }
}
