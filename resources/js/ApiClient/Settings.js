import {deleteFromApi, getBody, getContent, postToApi, putToApi} from "./Helpers";

export default class Settings {
    static getProfileSettings() {
        return getContent("/settings/profile");
    }

    static deleteAccount(confirmation) {
        return deleteFromApi("/settings/account", {confirmation});
    }

    static updatePassword(currentPassword, password, password_confirmation) {
        return putToApi("/settings/password", {currentPassword, password, password_confirmation});
    }

    static resendMail() {
        return postToApi("/settings/email/resend");
    }

    static updateMail(email, password) {
        return putToApi("/settings/email", {email, password});
    }

    static createIcsToken(name) {
        return postToApi("/settings/ics-token", {name});
    }

    static fetchIcsTokens() {
        return getContent("/settings/ics-tokens");
    }

    static deleteIcsToken(tokenId) {
        return deleteFromApi("/settings/ics-token", {tokenId});
    }

    static fetchSessions() {
        return getContent("/settings/sessions");
    }

    static deleteSessions() {
        return deleteFromApi("/settings/sessions");
    }

    static fetchApiTokens() {
        return getContent("/settings/tokens");
    }

    static revokeApiToken(tokenId) {
        return deleteFromApi("/settings/token", {tokenId});
    }

    static revokeAllApiTokens() {
        return deleteFromApi("/settings/tokens");
    }

    static updateProfilePicture(image) {
        return postToApi("/settings/profilePicture", {image});
    }

    static updateProfileSettings(data) {
        return putToApi("/settings/profile", data);
    }

    static deleteProfilePicture() {
        return deleteFromApi("/settings/profilePicture");
    }

    static getFollowers() {
        return getBody("/settings/followers");
    }

    static getFollowings() {
        return getBody("/settings/followings");
    }

    static getFollowRequests() {
        return getBody("/settings/follow-requests");
    }
}
