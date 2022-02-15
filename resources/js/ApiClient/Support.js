import {postToApi} from "./Helpers";

export default class Support {
    static createTicket(data) {
        return postToApi("/support/ticket", data);
    }
}
