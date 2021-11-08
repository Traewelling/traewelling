<template>
    <LayoutBasic>
        <div class="col-md-8">
            <div v-for="user in users" v-if="users" v-bind:key="user.id" class="card status mt-3">
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
            <Spinner v-if="loading" class="mt-5"/>
            <div v-else-if="users.length === 0">
                <div class="alert my-3 alert-danger" role="alert">
                    {{ i18n.get("_.user.no-user") }}
                </div>
            </div>
            <div v-if="links && links.next" class="text-center">
                <button aria-label="i18n.get('_.menu.show-more')"
                        class="btn btn-primary btn-lg btn-floating mt-4"
                        @click.prevent="fetchMore">
                    <i aria-hidden="true" class="fas fa-caret-down"></i>
                </button>
            </div>
        </div>
    </LayoutBasic>
</template>

<script>
import LayoutBasic from "../layouts/Basic";
import FollowButton from "../FollowButton";
import Spinner from "../Spinner";
import User from "../../js/ApiClient/User";

export default {
    name: "SearchView",
    data() {
        return {
            errors: null,
            loading: false,
            links: null,
            users: null
        };
    },
    components: {Spinner, FollowButton, LayoutBasic},
    methods: {
        fetchData(query = this.$route.query.query) {
            this.loading = true;
            User.search(query)
                .then((data) => {
                    this.users   = data.data;
                    this.links   = data.links;
                    this.loading = false;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        fetchMore() {
            this.loading = true;
            this.error   = null;
            this.fetchMoreData(this.links.next)
                .then((data) => {
                    this.loading = false;
                    this.users   = this.users.concat(data.data);
                    this.links   = data.links;
                });
        },
    },
    mounted() {
        this.fetchData();
    },
    beforeRouteUpdate(to, from, next) {
        this.fetchData(to.query.query);
        next();
    }
};
</script>

<style scoped>

</style>
