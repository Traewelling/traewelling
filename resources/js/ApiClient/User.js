import axios from "axios";
import {catchError} from "./Helpers";

export default class User {
    static getByUsername(username) {
        return new Promise(function (resolve, reject) {
            axios
                .get(`/user/${username}`)
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }
}
