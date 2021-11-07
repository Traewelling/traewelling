import {getBody, getContent} from "./Helpers";

export default class Event {
    static fetchData(slug) {
        return getContent(`/event/${slug}`);
    }

    static fetchStatuses(slug) {
        return getBody(`/event/${slug}/statuses`);
    }
}
