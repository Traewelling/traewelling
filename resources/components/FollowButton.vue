<template>
    <a v-if="userData.id === $auth.user().id" :class="{'btn btn-sm btn-primary': !dropdown, 'dropdown-item': dropdown}"
       href="#">
        {{ i18n.get("_.profile.settings") }}
    </a>
    <a v-else-if="userData.privateProfile && userData.followPending" aria-disabled="true"
       :class="{'btn btn-sm btn-primary disabled': !dropdown, 'dropdown-item': dropdown}" href="#">
        <i v-if="dropdown" aria-hidden="true" class="fas fa-user-clock"></i>
        {{ i18n.get("_.profile.follow_req.pending") }}
    </a>
    <a v-else-if="userData.privateProfile && !userData.following"
       :class="{'btn btn-sm btn-primary': !dropdown, 'dropdown-item': dropdown}"
       href="#" @click.prevent="follow">
        <i v-if="dropdown" aria-hidden="true" class="fas fa-user-plus"></i>
        {{ i18n.get("_.profile.follow_req") }}
    </a>
    <a v-else-if="!userData.following" :class="{'btn btn-sm btn-primary': !dropdown, 'dropdown-item': dropdown}"
       href="#" @click.prevent="follow">
        <i v-if="dropdown" aria-hidden="true" class="fas fa-user-plus"></i>
        {{ i18n.get("_.profile.follow") }}
    </a>
    <a v-else :class="{'btn btn-sm btn-danger': !dropdown, 'dropdown-item': dropdown}" href="#"
       @click.prevent="unfollow">
        <i v-if="dropdown" aria-hidden="true" class="fas fa-user-minus"></i>
        {{ i18n.get("_.profile.unfollow") }}
    </a>
</template>

<script>

import {ProfileModel} from "../js/APImodels";
import User from "../js/ApiClient/User";

export default {
    name: "FollowButton",
    inject: ["notyf"],
    data() {
        return {
            userData: ProfileModel
        };
    },
    props: ["user", "dropdown"],
    mounted() {
        this.userData = this.$props.user;
    },
    watch: {
        user(val, oldVal) {
            this.userData = this.$props.user;
        }
    },
    methods: {
        follow() {
            User
                .follow(this.user.id)
                .then((data) => {
                    this.userData = data;
                    this.$emit("updateUser", this.userData);
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                });
        },
        unfollow() {
            User
                .unfollow(this.user.id)
                .then((data) => {
                    this.userData = data;
                    this.$emit("updateUser", this.userData);
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
