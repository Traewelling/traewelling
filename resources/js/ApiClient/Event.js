import {getBody, getContent, postToApi} from "./Helpers";

export default class Event {
    static fetchData(slug) {
        return getContent("/event/${slug}");
    }

    static fetchStatuses(slug) {
        return getBody("/event/${slug}/statuses");
    }

    static active() {
        return getContent("/activeEvents");
    }

    static upcoming() {
        return getBody("/events");
    }

    static suggest(data) {
        return postToApi("/event", data);
    }
}
