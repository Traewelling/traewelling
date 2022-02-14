import {getBody, getContent, postToApi, putToApi} from "./Helpers";

export default class Checkin {
    static getHistory() {
        return getContent("/trains/station/history");
    }

    static getNearbyStations(latitude, longitude) {
        return getContent("/trains/station/nearby", {params: {latitude, longitude}});
    }

    static checkIn(data) {
        return postToApi("/trains/checkin", data);
    }

    static editCheckin(statusId, data) {
        return putToApi(`/statuses/${statusId}`, data);
    }

    static saveHome(stationName) {
        return putToApi(`/trains/station/${stationName.replace("/", " ")}/home`);
    }

    static getDepartures(stationName, when = null, travelType = null) {
        const encodedStation = stationName.replace("/", " ");
        let query            = {
            when: when ?? "",
            travelType: travelType ?? ""
        };
        return getBody(`/trains/station/${encodedStation}/departures`, {params: query});
    }

    static getTrip(tripId, lineName, start) {
        return getContent("/trains/trip", {params: {tripId, lineName, start}});
    }

}
