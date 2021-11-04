import axios from "axios";
import {catchError} from "./Helpers";

export default class Dashboard {
    static getFuture() {
        return new Promise(function (resolve, reject) {
            axios
                .get('/dashboard/future')
                .then((response) => {
                    resolve(response.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static get(global) {
        return global ? this.getGlobalDashboard() : this.getDashboard();
    }

    static getDashboard() {
        return new Promise(function (resolve, reject) {
            axios
                .get('/dashboard')
                .then((response) => {
                    resolve(response.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static getGlobalDashboard() {
        return new Promise(function (resolve, reject) {
            axios
                .get('/dashboard/global')
                .then((response) => {
                    resolve(response.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }
}
