<template>
    <LayoutBasic>
        <div class="col-md-8">
            <div v-for="user in users" v-bind:key="user.id" class="card status mt-3">
                <div class="card-body row">
                    <div class="col-2 image-box search-image-box d-lg-flex">
                        <router-link :to="{name: 'profile', params: {username: user.username}}">
                            <img :alt="i18n.get('_.settings.picture')"
                                 :src="`/profile/${user.username}/profilepicture`">
                        </router-link>
                    </div>

                    <div class="col ps-0">
                                <span class="float-end mt-3">
                                    <FollowButton :user="user"></FollowButton>
                                </span>

                        <router-link :to="{name: 'profile', params: {username: user.username}}"
                                     style="font-size: calc(1.26rem + .12vw)">
                            {{ user.displayName }}
                            <i v-if="user.privateProfile" aria-hidden="true" class="fas fa-user-lock"></i>
                            <small class="text-muted">@ {{ user.username }}</small>
                        </router-link>
                        <br/>
                        <span style="font-size: 0.875em;">
                                    <span class="font-weight-bold">
                                        <i aria-hidden="true" class="fa fa-route d-inline"></i>
                                        <span class="sr-only-focusable">{{ i18n.get("_.leaderboard.distance") }}</span>
                                        {{ user.trainDistance / 1000 }}
                                    </span>
                                    <span class="small font-weight-lighter">km</span>
                                    <span class="font-weight-bold ps-sm-2">
                                        <i aria-hidden="true" class="fa fa-stopwatch d-inline"></i>
                                        <span class="sr-only-focusable">{{ i18n.get("_.leaderboard.duration") }}</span>
                                        {{ user.trainDuration.toFixed(0) }}
                                        <!-- ToDo: trainDuration in hours & minutes -->
                                    </span>
                                    <span class="small font-weight-ligheer">min</span>
                                    <span class="font-weight-bold ps-sm-2">
                                        <i aria-hidden="true" class="fa fa-dice-d20 d-inline"></i>
                                        <span class="sr-only-focusable">{{ i18n.get("_.leaderboard.points") }}</span>
                                        {{ user.points }}
                                    </span>
                                    <span
                                        class="small font-weight-lighter">{{ i18n.get("_.profile.points-abbr") }}</span>
                                </span>
                    </div>
                </div>
            </div>
        </div>
    </LayoutBasic>
</template>

<script>
import LayoutBasic from "../layouts/Basic";
import axios from "axios";
import {ProfileModel} from "../../js/APImodels";
import FollowButton from "../FollowButton";

export default {
    name: "SearchView",
    data() {
        return {
            errors: null,
            loading: false,
            users: [ProfileModel]
        };
    },
    components: {FollowButton, LayoutBasic},
    methods: {
        fetchData(query = this.$route.query.query) {
            axios
                .get("/user/search/" + query)
                .then((response) => {
                    this.users = response.data.data;
                    console.log(this.users);
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                    this.error   = error.data.message || error.message;
                    console.error(this.error);
                });
        }
    },
    mounted() {
        this.fetchData();
    },
    beforeRouteUpdate(to) {
        this.fetchData(to.query.query);
    }
}
</script>

<style scoped>

</style>
