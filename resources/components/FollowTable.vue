<template>
    <table class="table table-striped table-hover table-sm mb-0" aria-describedby="describedBy">
        <tbody>
            <tr v-for="row in users" v-bind:key="row.id" style="vertical-align: middle">
                <td>
                    <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                        <router-link :to="{name: 'profile', params: {username: row.username}}">
                            <img :src="row.profilePicture" style="height: 4em;"
                                 :alt="i18n.choice('_.user.profile-picture', 1, {username: row.username})">
                        </router-link>
                    </div>
                </td>
                <td>
                    <router-link :to="{name: 'profile', params: {username: row.username}}">
                        {{ row.displayName }}<br>
                        <small>@{{ row.username }}</small>
                    </router-link>
                </td>
                <td class="px-1">
                    <span class="font-weight-bold">
                                        <i aria-hidden="true" class="fa fa-route d-inline"></i>
                                        <span class="sr-only-focusable">{{ i18n.get("_.leaderboard.distance") }}</span>
                                        {{ row.trainDistance / 1000 }}
                                    </span>
                    <span class="small font-weight-lighter">km</span>
                </td>
                <td class="px-1">
                    <span class="font-weight-bold ps-sm-2">
                                        <i aria-hidden="true" class="fa fa-stopwatch d-inline"></i>
                                        <span class="sr-only-focusable">{{ i18n.get("_.leaderboard.duration") }}</span>
                                        {{ row.trainDuration.toFixed(0) }}
                        <!-- ToDo: trainDuration in hours & minutes -->
                                    </span>
                    <span class="small font-weight-ligheer">min</span>
                </td>
                <td class="px-1">
                    <span class="font-weight-bold ps-sm-2">
                                        <i aria-hidden="true" class="fa fa-dice-d20 d-inline"></i>
                                        <span class="sr-only-focusable">{{ i18n.get("_.leaderboard.points") }}</span>
                                        {{ row.points }}
                                    </span>
                    <span
                        class="small font-weight-lighter">{{ i18n.get("_.profile.points-abbr") }}</span>
                </td>
                <td class="pe-0 text-end" v-if="followers">
                    <button type="submit" class="btn btn-sm btn-danger" data-mdb-toggle="tooltip"
                            :title="i18n.get('_.settings.follower.delete')" @click="removeFollower(row)">
                        <i class="fas fa-user-minus" aria-hidden="true"></i>

                    </button>
                </td>
                <td class="pe-0" v-if="request">
                        <button type="submit" class="btn btn-danger btn-sm"
                                data-mdb-toggle="tooltip"
                                data-mdb-placement="top"
                                :title="i18n.get('_.settings.request.delete')"
                                @click="rejectFollowRequest(row)">
                            <i class="fas fa-user-times" aria-hidden="true"></i>
                        </button>
                </td>
                <td class="ps-0" v-if="request">
                        <button type="submit" class="btn btn-success btn-sm"
                                data-mdb-toggle="tooltip"
                                data-mdb-placement="top"
                                :title="i18n.get('_.settings.request.accept')"
                                @click="approveFollowRequest(row)">
                            <i class="fas fa-user-check" aria-hidden="true"></i>
                        </button>
                </td>
                <td class="px-0 text-end">
                    <MuteButton :user="row"></MuteButton>
                </td>
                <td>
                    <FollowButton :user="row"></FollowButton>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>

import MuteButton from "./MuteButton";
import FollowButton from "./FollowButton";
import User from "../js/ApiClient/User";

export default {
    name: "FollowTable",
    inject: ["notyf"],
    components: {FollowButton, MuteButton},
    props: {
        describedBy: null,
        users: null,
        followers: false,
        request: false
    },
    methods: {
        removeFollower(user) {
            User.removeFollower(user.id)
                .then(() => {
                    const index = this.users.indexOf(user);
                    this.users.splice(index, 1);
                    this.notyf.success(this.i18n.get('_.settings.follower.delete-success'));
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        rejectFollowRequest(user) {
            User.rejectFollowRequest(user.id)
                .then(() => {
                    const index = this.users.indexOf(user);
                    this.users.splice(index, 1);
                    this.notyf.success(this.i18n.get('_.settings.request.reject-success'));
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        approveFollowRequest(user) {
            User.approveFollowRequest(user.id)
                .then(() => {
                    const index = this.users.indexOf(user);
                    this.users.splice(index, 1);
                    this.notyf.success(this.i18n.get('_.settings.request.accept-success'));
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        }
    }
};
</script>

<style scoped>

</style>
