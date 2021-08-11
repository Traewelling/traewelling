<template>
    <div>
        <a v-if="userData.id == $auth.user().id" class="btn btn-sm btn-primary" href="#">{{
                i18n.get('_.profile.settings')
            }}</a>
        <a v-else-if="userData.privateProfile && userData.followPending" aria-disabled="true"
           class="btn btn-sm btn-primary disabled"
           href="#">{{ i18n.get('_.profile.follow_req.pending') }}</a>
        <a v-else-if="userData.privateProfile && !userData.following" class="btn btn-sm btn-primary follow"
           href="#" @click.prevent="requestFollow">{{
                i18n.get('_.profile.follow_req')
            }}</a>
        <a v-else-if="!userData.following" class="btn btn-sm btn-primary follow"
           href="#" @click.prevent="follow">{{ i18n.get('_.profile.follow') }}</a>
        <a v-else class="btn btn-sm btn-danger follow" href="#"
           @click.prevent="unfollow">{{ i18n.get('_.profile.unfollow') }}</a>
    </div>
</template>

<script>

import {ProfileModel} from "../js/APImodels";

export default {
    name: "FollowButton",
    data() {
        return {
            userData: ProfileModel
        };
    },
    props: {
        user: null
    },
    watch: {
        user() {
            this.userData = this.$props.user;
        }
    },
    methods: {
        follow() {
            axios
                .
        },
        unfollow() {

        },
        requestFollow() {

        }
    }
};
</script>

<style scoped>

</style>
