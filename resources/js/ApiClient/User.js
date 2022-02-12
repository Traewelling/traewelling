import {deleteFromApi, getBody, getContent, postToApi, putToApi} from "./Helpers";

export default class User {
    static getByUsername(username) {
        return getContent(`/user/${username}`);
    }

    static getStatusesForUser(username) {
        return getBody(`/user/${username}/statuses`);
    }

    static follow(userId) {
        return postToApi("/user/createFollow", {userId});
    }

    static unfollow(userId) {
        return deleteFromApi("/user/destroyFollow", {userId});
    }

    static removeFollower(userId) {
        return deleteFromApi("/user/removeFollower", {userId});
    }

    static rejectFollowRequest(userId) {
        return deleteFromApi("/user/rejectFollowRequest", {userId});
    }

    static approveFollowRequest(userId) {
        return putToApi("/user/approveFollowRequest", {userId});
    }

    static mute(userId) {
        return postToApi("/user/createMute", {userId});
    }

    static unmute(userId) {
        return deleteFromApi("/user/destroyMute", {userId});
    }

    static search(query) {
        return getBody(`/user/search/${query}`);
    }
}
