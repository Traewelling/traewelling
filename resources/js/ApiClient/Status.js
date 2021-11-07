import {deleteFromApi, postToApi, returnDataAsArray, returnDataData} from "./Helpers";

export default class Status {
    static get() {
        return returnDataData(`/statuses`);
    }

    static getById(id) {
        return returnDataData(`/statuses/${id}`);
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
        return returnDataData(`/stopovers/${tripIds}`);
    }

    static fetchLikes(statusId) {
        return returnDataData(`/statuses/${statusId}/likedby`);
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
