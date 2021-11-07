import {returnDataData} from "./Helpers";

export default class User {
    static getHistory() {
        return returnDataData("/trains/station/history");
    }

    static getNearbyStations(latitude, longitude) {
        return returnDataData("/trains/station/nearby", {params: {latitude, longitude}});
    }
}
