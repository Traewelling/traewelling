import axios from "axios";
import {catchError} from "./Helpers";

export class Status {
    static getById(id) {
        return new Promise(function (resolve, reject) {
            axios
                .get("/statuses/" + id)
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((errors) => {
                    reject(catchError(errors));
                });
        });
    }
}
