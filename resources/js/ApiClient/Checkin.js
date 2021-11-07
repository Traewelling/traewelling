import {postToApi, putToApi, returnDataData} from "./Helpers";

export default class User {
    static getHistory() {
        return returnDataData("/trains/station/history");
    }

    static getNearbyStations(latitude, longitude) {
        return returnDataData("/trains/station/nearby", {params: {latitude, longitude}});
    }

    static checkIn(data) {
        return postToApi("/trains/checkin", data);
    }

    static editCheckin(statusId, data) {
        return putToApi(`/statuses/${statusId}`, data);
    }
}
