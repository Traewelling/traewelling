import {getContent, postToApi, putToApi} from "./Helpers";

export default class Notifications {
    static fetchNotifications() {
        return getContent("/notifications");
    }

    static readAll() {
        return postToApi("/notifications/readAll");
    }

    static toggleRead(notificationId) {
        return putToApi(`/notifications/${notificationId}`);
    }

    static getCount() {
        return getContent("/notifications/count");
    }
}
