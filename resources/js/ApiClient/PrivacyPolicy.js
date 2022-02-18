import {getBody, getContent, postToApi} from "./Helpers";

export default class PrivacyPolicy {
    static getPolicy(slug) {
        return getContent("static/privacy");
    }

}
