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

    static follow(userId) {
        return new Promise(function (resolve, reject) {
            axios
                .post("/user/createFollow", {userId: userId})
                .then((result) => {
                    resolve(result.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static unfollow(userId) {
        return new Promise(function (resolve, reject) {
            axios
                .delete("/user/destroyFollow", {data: {userId: userId}})
                .then((result) => {
                    resolve(result.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static mute(userId) {
        return new Promise(function (resolve, reject) {
            axios
                .post("/user/createMute", {userId: userId})
                .then((result) => {
                    resolve(result.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }

    static unmute(userId) {
        return new Promise(function (resolve, reject) {
            axios
                .delete("/user/destroyMute", {data: {userId: userId}})
                .then((result) => {
                    resolve(result.data.data);
                })
                .catch((error) => {
                    reject(catchError(error));
                });
        });
    }
}
