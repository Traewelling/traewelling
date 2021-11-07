import {deleteFromApi, postToApi, returnData, returnDataData} from "./Helpers";

export default class User {
    static getByUsername(username) {
        return returnDataData(`/user/${username}`);
    }

    static getStatusesForUser(username) {
        return returnData(`/user/${username}/statuses`);
    }

    static follow(userId) {
        return postToApi("/user/createFollow", {userId: userId});
    }

    static unfollow(userId) {
        return deleteFromApi("/user/destroyFollow", {userId: userId});
    }

    static mute(userId) {
        return postToApi("/user/createMute", {userId: userId});
    }

    static unmute(userId) {
        return deleteFromApi("/user/destroyMute", {userId: userId});
    }
}
