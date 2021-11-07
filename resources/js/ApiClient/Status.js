import {returnDataAsArray, returnDataData, returnVoid} from "./Helpers";

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
        return returnVoid(`/like/${statusId}`);
    }

    static dislike(statusId) {
        return returnVoid(`/like/${statusId}`);
    }

    static delete(statusId) {
        return returnVoid(`/statuses/${statusId}`);
    }
}
