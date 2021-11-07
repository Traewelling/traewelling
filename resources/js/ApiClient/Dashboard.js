import {getBody, getContent} from "./Helpers";

export default class Dashboard {
    static getFuture() {
        return getContent("/dashboard/future");
    }

    static get(global) {
        return global ? this.getGlobalDashboard() : this.getDashboard();
    }

    static getDashboard() {
        return getBody("/dashboard");
    }

    static getGlobalDashboard() {
        return getContent("/dashboard/global");
    }
}
