<template>
    <a v-if="showButton && userData.muted" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
       title="{{ __('user.unmute-tooltip') }}" href="#">
        <i class="far fa-eye-slash" aria-hidden="true"></i>
    </a>
    <a v-else-if="showButton" class="btn btn-sm btn-primary disabled" data-mdb-toggle="tooltip"
       title="{{ __('user.mute-tooltip') }}" href="#">
        <i class="far fa-eye" aria-hidden="true"></i>
    </a>
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
    props: ['user'],
    mounted() {
        this.userData = this.$props.user;
    },
    watch: {
        user(val, oldVal) {
            console.log('test');
            this.userData = this.$props.user;
        }
    },
    computed: {
        showButton() {
            return this.userData.id !== $auth.user().id;
        }
    },
    methods: {
        follow() {
            axios
                .post('/user/createMute', {userId: this.user.id})
                .then((result) => {
                    this.userData = result.data.data;
                })
                .catch((error) => {
                    console.error(error);
                })
        },
        unfollow() {
            axios
                .delete('/user/destroyMute', {data: {userId: this.user.id}})
                .then((result) => {
                    this.userData = result.data.data;
                    if (this.userData.privateProfile) {
                        window.location.reload();
                    }
                })
                .catch((error) => {
                    console.error(error);
                })
        }
    }
};
</script>

<style scoped>

</style>
