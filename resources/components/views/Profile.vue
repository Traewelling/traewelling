<template>
    <HeroLayout>
        <template v-if="!loading" v-slot:hero>
            <img :alt="i18n.get('_.settings.picture')" :src="`/profile/${user.username}/profilepicture`"
                 class="float-end img-thumbnail rounded-circle img-fluid"
                 height="20%" width="20%"/>
            <div class="text-white px-4">
                <h2 class="card-title font-bold">
                    <strong>{{ user.displayName }} <i v-if="user.privateProfile" aria-hidden="true"
                                                      class="fas fa-user-lock"/>
                    </strong> <br/>
                    <small class="font-weight-light">@{{ user.username }}</small>
                    <FollowButton :user="user" v-on:updateUser="updateUser"></FollowButton>
                    <MuteButton v-if="!user.privateProfile" :user="user" v-on:updateUser="updateUser"></MuteButton>
                </h2>
                <h2>
          <span class="font-weight-bold">
            <i aria-hidden="true" class="fa fa-route d-inline"/>&nbsp;{{ (user.trainDistance / 1000).toFixed(1) }}
          </span>
                    <span class="small font-weight-lighter">km</span>
                    <span class="font-weight-bold ps-sm-2">
            <i aria-hidden="true" class="fa fa-stopwatch d-inline"/>&nbsp;{{ duration }}
          </span>
                    <span class="font-weight-bold ps-sm-2">
            <i aria-hidden="true" class="fa fa-dice-d20 d-inline"/>&nbsp;{{ user.points }}
          </span>
                    <span class="small font-weight-lighter">{{ i18n.get("_.profile.points-abbr") }}</span>
                    <!-- ToDo: Adapt twitterUrl to ID Link (as in blade template) and remove the getTwitterUrl method afterwards! -->
                    <span v-if="user.twitterUrl" class="font-weight-bold ps-sm-2">
            <a :href="user.twitterUrl" class="text-white" rel="me" target="_blank">
              <i aria-hidden="true" class="fab fa-twitter d-inline"/>
            </a>
          </span>
                    <span v-if="user.mastodonUrl" class="font-weight-bold ps-sm-2">
            <a :href="user.mastodonUrl" class="text-white" rel="me" target="_blank">
              <i aria-hidden="true" class="fab fa-mastodon d-inline"/>
            </a>
          </span>
                </h2>
            </div>
        </template>
        <Spinner v-if="loading || statusesLoading" class="mt-5"/>

        <div v-if="!statusesLoading && !loading" class="row justify-content-center mt-5">

            <div v-if="user.muted" class="col-md-8 col-lg-7 text-center mb-5">
                <header><h3>{{ i18n.get('_.user.muted.heading') }}</h3></header>
                <h5>{{ i18n.choice("_.user.muted.text", 1, {"username": user.username}) }}</h5>
                <MuteButton :user="user" bigButton="true"
                            v-on:updateUser="updateUser"></MuteButton>
            </div>
            <div v-else-if="user.userInvisibleToMe" class="col-md-8 col-lg-7 text-center mb-5">
                <header><h3>{{ i18n.get("_.profile.private-profile-text") }}</h3></header>
                <h5>
                    {{
                        i18n.choice("_.profile.private-profile-information-text", 1, {
                            "username": user.username,
                            "request": i18n.get("_.profile.follow_req")
                        })
                    }}
                </h5>
            </div>
            <div v-else-if="statuses.length > 0" class="col-md-8 col-lg-7">
                <header><h3>{{ i18n.get("_.profile.last-journeys-of") }} {{ user.displayName }}:</h3></header>

                <div v-if="statuses">
                    <Status v-for="status in statuses" v-bind:key="status.id"
                            :show-date="showDate(status, statuses)"
                            :status="status"/>
                </div>
                <div class="mt-5">
                    <div v-if="links && links.next" class="text-center">
                        <button :aria-label="i18n.get('_.menu.show-more')"
                                class="btn btn-primary btn-lg btn-floating mt-4"
                                @click.prevent="fetchMore">
                            <i aria-hidden="true" class="fas fa-caret-down"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="col-md-8 col-lg-7">
                <h3 class="text-danger">
                    strtr({{ i18n.get("_.profile.no-statuses") }}, [':username' => $user->name])
                </h3>
            </div>
        </div>
    </HeroLayout>
</template>

<script>
import moment from "moment";
import Status from "../Status";
import {ProfileModel, StatusModel} from "../../js/APImodels";
import LayoutBasic from "../layouts/Basic";
import HeroLayout from "../layouts/HeroLayout";
import Spinner from "../Spinner";
import FollowButton from "../FollowButton";
import MuteButton from "../MuteButton";
import User from "../../js/ApiClient/User";

export default {
    name: "ProfilePage",
    data() {
        return {
            username: this.$route.params.username,
            loading: false,
            statusesLoading: false,
            user: ProfileModel,
            statuses: [StatusModel],
            description: undefined,
            robots: "noindex",
            links: null,
        };
    },
    metaInfo() {
        return {
            title: this.user.displayName,
            meta: [
                {name: "robots", content: this.robots, vmid: "robots"},
                {name: "description", content: this.description, vmid: "description"},
                {name: "DC.Description", content: this.description, vmid: "DC.Description"}
            ]
        };
    },
    components: {
        MuteButton,
        FollowButton,
        Spinner,
        HeroLayout,
        LayoutBasic,
        Status
    },
    computed: {
        duration() {
            //ToDo this needs localization, also this is code duplication...
            const duration = moment.duration(this.user.trainDuration, "minutes").asMinutes();
            let minutes    = duration % 60;
            let hours      = Math.floor(duration / 60);

            return hours + "h " + minutes + "m";
        },
    },
    created() {
        this.fetchData();
    },
    methods: {
        updateUser(user) {
            if (!user.userInvisibleToMe) {
                this.fetchStatuses();
            } else {
                this.statuses = [];
            }
            this.user = user;
        },
        showDate(item, statuses) {
            let index = statuses.indexOf(item);
            if (index === -1 || index === 0) {
                return true;
            }
            return moment(item.train.origin.departure).date() !== moment(statuses[index - 1].train.origin.departure).date();
        },
        updateMetadata() {
            this.description = this.i18n.choice("_.description.profile", 1, {
                "username": this.user.username,
                "kmAmount": this.user.trainDistance.toFixed(2),
                "hourAmount": this.duration
            });
            if (this.user.preventIndex) {
                this.robots = "noindex";
            }
        },
        fetchData() {
            this.loading = true;
            User
                .getByUsername(this.$route.params.username)
                .then((data) => {
                    this.loading = false;
                    this.user    = data;
                    this.updateMetadata();
                    if (!this.user.userInvisibleToMe) {
                        this.fetchStatuses();
                    }
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        fetchStatuses() {
            this.statusesLoading = true;
            User
                .getStatusesForUser(this.$route.params.username)
                .then((data) => {
                    this.statusesLoading = false;
                    this.statuses        = data.data;
                    this.links           = data.links;
                })
                .catch((error) => {
                    this.statusesLoading = false;
                    this.apiErrorHandler(error);
                });
        },
        fetchMore() {
            this.fetchMoreData(this.links.next)
                .then((data) => {
                    this.statuses = this.statuses.concat(data.data);
                    this.links    = data.links;
                });
        }
    }
};
</script>

<style scoped>

</style>
