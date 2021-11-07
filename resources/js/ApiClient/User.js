import {returnDataData} from "./Helpers";

export default class User {
    static getByUsername(username) {
        return returnDataData(`/user/${username}`);
    }

    static getStatusesForUser(username) {
        return returnDataData(`/user/${username}/statuses`);
    }

    static follow(userId) {
        return returnDataData("/user/createFollow", {userId: userId});
    }

    static unfollow(userId) {
        return returnDataData("/user/destroyFollow", {data: {userId: userId}});
    }

    static mute(userId) {
        return returnDataData("/user/createMute", {userId: userId});
    }

    static unmute(userId) {
        return returnDataData("/user/destroyMute", {data: {userId: userId}});
    }
}
