<template>
    <a v-if="bigButton" class="btn btn-sm btn-primary" @click.prevent="unmute">
        <i aria-hidden="true" class="far fa-eye"></i> {{ i18n.get("_.user.unmute-tooltip") }}
    </a>
    <a v-else-if="showButton && userData.muted" class="btn btn-sm btn-primary" data-mdb-toggle="tooltip"
       :title="i18n.get('_.user.unmute-tooltip')" href="#" @click.prevent="unmute">
        <i aria-hidden="true" class="far fa-eye"></i>
    </a>
    <a v-else-if="showButton" :title="i18n.get('_.user.mute-tooltip')" class="btn btn-sm btn-primary"
       data-mdb-toggle="tooltip" href="#" @click.prevent="mute">
        <i aria-hidden="true" class="far fa-eye-slash"></i>
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
    props: ["user", "bigButton"],
    mounted() {
        this.userData = this.$props.user;
        console.log(this.userData);
    },
    watch: {
        user(val, oldVal) {
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
                .post("/user/createMute", {userId: this.user.id})
                .then((result) => {
                    this.userData = result.data.data;
                    this.$emit("updateUser", this.userData);
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        unmute() {
            axios
                .delete("/user/destroyMute", {data: {userId: this.user.id}})
                .then((result) => {
                    this.userData = result.data.data;
                    this.$emit("updateUser", this.userData);
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
