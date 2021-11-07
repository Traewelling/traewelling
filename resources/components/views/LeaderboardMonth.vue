<template>
    <LayoutBasicNoSidebar>
        <div v-if="!loading">
            <div class="row">
                <div class="col-md-12">
                    <h4>{{ i18n.get("_.leaderboard.month") }} <strong>{{ month.format("MMMM YYYY") }}</strong></h4>
                    <hr/>
                    <router-link :to="{ name: 'leaderboard.month', params:{month: lastMonth} }"
                                 class="btn btn-sm btn-primary float-left">
                        <i aria-hidden="true" class="fas fa-arrow-left"/> {{ moment(lastMonth).format("MMMM YYYY") }}
                    </router-link>

                    <router-link v-if="moment(nextMonth).isBefore()"
                                 :to="{ name: 'leaderboard.month', params:{month: nextMonth} }"
                                 class="btn btn-sm btn-primary float-end">
                        {{ moment(nextMonth).format("MMMM YYYY") }} <i aria-hidden="true" class="fas fa-arrow-right"/>
                    </router-link>
                    <div class="clearfix"></div>
                    <hr/>
                </div>


                <div v-if="users.length === 0" class="col-md-12">
                    <div class="card">
                        <div class="card-body text-center text-danger text-bold">
                            {{ i18n.get("_.leaderboard.no_data") }}
                        </div>
                    </div>
                </div>


                <div v-for="(place, index) in users.slice(0, 3)" class="col-md-4">
                    <div class="card mb-2">
                        <div class="card-header"> {{ i18n.get("_.leaderboard.rank") }} {{ index + 1 }}</div>
                        <div class="card-body text-center">
                            <div class="image-box pe-0 d-lg-flex">
                                <router-link :to="{ name: 'profile', params: {username: place.username}}">
                                    <img :alt="place.username" :src="`/profile/${place.username}/profilepicture`"
                                         style="width: 50%;">
                                </router-link>
                            </div>
                            <router-link :to="{ name: 'profile', params: {username: place.username}}"
                                         style="font-size: 1.3em;">
                                {{ place.username }}
                            </router-link>
                            <table class="table text-muted" role="presentation">
                                <tbody>
                                    <tr>
                                        <td>
                                            <i aria-hidden="true" class="fas fa-dice-d20"/>
                                            <span class="sr-only">{{ i18n.get("_.leaderboard.points") }}</span>
                                            {{ place.points.toFixed(0) }}
                                        </td>
                                        <td>
                                            <i aria-hidden="true" class="fas fa-clock"/>
                                            <span class="sr-only">{{ i18n.get("_.leaderboard.duration") }}</span>
                                            {{ place.trainDuration.toFixed(0) }}min
                                        </td>
                                        <td>
                                            <i aria-hidden="true" class="fas fa-route"/>
                                            <span class="sr-only">{{ i18n.get("_.leaderboard.distance") }}</span>
                                            {{ (place.trainDistance / 1000).toFixed(1) }}km
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
            <div v-if="users.length > 3" class="row justify-content-center">
                <div class="col-md-8 col-lg-7">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table :aria-label="i18n.get('_.menu.leaderboard')" class="table table-vertical-center">
                                <thead>
                                    <tr>
                                        <th scope="col"> {{ i18n.get("_.leaderboard.rank") }}</th>
                                        <th colspan="2" scope="col"> {{ i18n.get("_.leaderboard.user") }}</th>
                                        <th scope="col"> {{ i18n.get("_.leaderboard.duration") }}</th>
                                        <th scope="col"> {{ i18n.get("_.leaderboard.distance") }}</th>
                                        <th scope="col"> {{ i18n.get("_.leaderboard.points") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(place, index) in users.slice(3, 100)">
                                        <td>{{ index + 4 }}</td>
                                        <td>
                                            <div class="image-box pe-0 d-lg-flex" style="width: 4em; height: 4em;">
                                                <router-link
                                                    :to="{ name: 'profile', params: {username: place.username}}">
                                                    <img :alt="place.username"
                                                         :src="`/profile/${place.username}/profilepicture`"
                                                         style="width: 50%;">
                                                </router-link>
                                            </div>
                                        </td>
                                        <td>
                                            <router-link :to="{ name: 'profile', params: {username: place.username}}"
                                                         style="font-size: 1.3em;">
                                                {{ place.username }}
                                            </router-link>
                                        </td>
                                        <td>
                                            {{ place.trainDuration }}min
                                        </td>
                                        <td>
                                            {{ (place.trainDistance / 1000).toFixed(1) }}km
                                        </td>
                                        <td>
                                            {{ place.points.toFixed(0) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <hr/>
        </div>
        <div v-else>
            <Spinner class="mt-5"/>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>
import moment from "moment";
import LeaderboardTable from "../LeaderboardTable";
import {LeaderboardUserModel} from "../../js/APImodels";
import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import Statistics from "../../js/ApiClient/Statistics";

export default {
    name: "LeaderboardMonth",
    data() {
        return {
            moment: moment,
            users: [LeaderboardUserModel],
            loading: false,
            metaData: {
                description: undefined
            }
        };
    },
    metaInfo() {
        return {
            title: this.i18n.get("_.menu.leaderboard"),
            meta: [
                {name: "description", content: this.metaData.description, vmid: "description"},
                {name: "DC.Description", content: this.metaData.description, vmid: "DC.Description"}
            ]
        };
    },
    computed: {
        lastMonth() {
            return moment(this.$route.params.month).subtract(1, "months").format("YYYY-MM");
        },
        month() {
            return moment(this.$route.params.month);
        },
        nextMonth() {
            return moment(this.$route.params.month).add(1, "months").format("YYYY-MM");
        }
    },
    components: {
        LayoutBasicNoSidebar,
        Spinner,
        LayoutBasic,
        LeaderboardTable
    },
    methods: {
        fetchData() {
            this.error   = null;
            this.loading = true;
            Statistics.getLeaderBoardMonth(this.$route.params.month)
                .then((data) => {
                    this.loading = false;
                    this.users   = data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.apiErrorHandler(error);
                });
        },
        updateMetadata() {
            this.metaData.description = this.i18n.choice("_.description.leaderboard.monthly", 1, {
                "month": this.month.format("MMMM"),
                "year": this.month.format("YYYY")
            });
        }
    },
    watch: {
        month() {
            this.fetchData();
        }
    },
    created() {
        this.updateMetadata();
        this.fetchData();
    }
};
</script>

<style scoped>

</style>
