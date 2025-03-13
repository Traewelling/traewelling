import API from "../api/api";

export class Follow {

    static destroy(userId) {
        return API.request(`/user/${userId}/follow`, 'delete');
    }

    static create(userId) {
        return API.request(`/user/${userId}/follow`, 'POST');
    }
}
