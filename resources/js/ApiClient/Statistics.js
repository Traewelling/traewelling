import {getBody, postReturnRequest} from "./Helpers";

export default class Statistics {
    static export(from, until, filetype) {
        return postReturnRequest("/statistics/export", {from, until, filetype}, {responseType: "blob"});
    }

    static fetchPersonalData(from, until) {
        return getBody("/statistics", {parmams: {from, until}});
    }

    static fetchGlobalData() {
        return getBody("/statistics/global");
    }
}
