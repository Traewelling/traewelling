<template>
    <a v-if="showButton && userData.muted" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
       :title="i18n.get('_.user.unmute-tooltip')" href="#" @click.prevent="unmute">
        <i class="far fa-eye-slash" aria-hidden="true"></i>
    </a>
    <a v-else-if="showButton" :title="i18n.get('_.user.mute-tooltip')" class="btn btn-sm btn-primary"
       data-mdb-toggle="tooltip" href="#" @click.prevent="mute">
        <i class="far fa-eye" aria-hidden="true"></i>
    </a>
</template>

<script>

import {ProfileModel} from "../js/APImodels";

export default {
    name: "MuteButton",
    data() {
        return {
            userData: ProfileModel
        };
    },
    props: ['user'],
    mounted() {
        this.userData = this.$props.user;
        console.log(this.userData);
    },
    watch: {
        user(val, oldVal) {
            console.log('test');
            this.userData = this.$props.user;
        }
    },
    computed: {
        showButton() {
            return this.userData.id !== this.$auth.user().id;
        }
    },
    methods: {
        mute() {
            axios
                .post('/user/createMute', {userId: this.user.id})
                .then((result) => {
                    this.userData = result.data.data;
                })
                .catch((error) => {
                    console.error(error);
                })
        },
        unmute() {
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
