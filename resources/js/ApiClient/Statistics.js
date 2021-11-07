import {getBody, getContent, postReturnRequest} from "./Helpers";

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

    static getLeaderBoard() {
        return getContent("/leaderboard");
    }

    static getLeaderBoardDistance() {
        return getContent("/leaderboard/distance");
    }

    static getLeaderBoardFriends() {
        return getContent("/leaderboard/friends");
    }

    static getLeaderBoardMonth(month) {
        return getContent(`/leaderboard/${month}`);
    }
}
