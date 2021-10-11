<template>
    <LayoutBasic>
            <div class="col-md-9 col-lg-9">
                <div id="leaderboard" class="card">
                    <div class="card-header">
                        <router-link :to="{ name: 'leaderboard.month', params:{month: month} }" class="float-end">
                            {{ i18n.get("_.leaderboard.month.title") }}
                        </router-link>
                        {{ i18n.get("_.menu.leaderboard") }}
                    </div>
                    <div v-if="!loading" class="card-body">
                        <ul id="myTab" class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a id="main-tab" aria-controls="home" aria-selected="true" class="nav-link active"
                                   data-toggle="tab" href="#leaderboard-main" role="tab">
                                    {{ i18n.get("_.leaderboard.top") }} {{ users.length }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a id="distance-tab" aria-controls="profile" aria-selected="false" class="nav-link"
                                   data-toggle="tab" href="#leaderboard-distance" role="tab">
                                    {{ i18n.get("_.leaderboard.distance") }}
                                </a>
                            </li>
                            <li v-if="$auth.check() && friends" class="nav-item">
                                <a id="friends-tab" aria-controls="contact" aria-selected="false" class="nav-link"
                                   data-toggle="tab" href="#leaderboard-friends" role="tab">
                                    {{ i18n.get("_.leaderboard.friends") }}
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div id="leaderboard-main" class="tab-pane fade show active table-responsive"
                                 role="tabpanel">
                                <LeaderboardTable :users="users" described-by="main-tab"></LeaderboardTable>
                            </div>
                            <div id="leaderboard-distance" class="tab-pane fade table-responsive" role="tabpanel">
                                <LeaderboardTable :users="distance" described-by="distance-tab"></LeaderboardTable>
                            </div>
                            <div v-if="$auth.check() && friends" id="leaderboard-friends"
                                 class="tab-pane fade table-responsive" role="tabpanel">
                                <LeaderboardTable :users="friends" described-by="distance-tab"></LeaderboardTable>
                            </div>
                        </div>
                    </div>
                    <div v-else class="card-body">
                        <Spinner class="mt-5"/>
                    </div>
                    <div class="card-footer text-muted">
                        <i aria-hidden="true" class="far fa-question-circle"></i>
                        {{ i18n.get("_.leaderboard.notice") }}
                    </div>
                </div>
            </div>
    </LayoutBasic>
</template>

<script>
import moment from "moment";
import LeaderboardTable from "../LeaderboardTable";
import axios from "axios";
import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";

export default {
    //ToDo format numbers correctly for languages, etc.
    name: "Leaderboard",
    data() {
        return {
            month: moment().format("YYYY-MM"),
            users: null,
            distance: null,
            friends: null,
            loading: false
        };
    },
    metaInfo() {
        return {
            title: this.i18n.get("_.menu.leaderboard"),
            meta: [
                {name: "description", content: this.i18n.get("_.description.leaderboard.main"), vmid: "description"},
                {
                    name: "DC.Description",
                    content: this.i18n.get("_.description.leaderboard.main"),
                    vmid: "DC.Description"
                }
            ]
        };
    },
    components: {
        Spinner,
        LayoutBasic,
        LeaderboardTable
    },
    methods: {
        fetchData() {
            this.error   = null;
            this.loading = true;
            axios
                .get("/leaderboard/")
                .then((response) => {
                    this.loading = false;
                    this.users   = response.data.data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.error   = error.data.message || error.message;
                });
            axios
                .get("/leaderboard/distance")
                .then((response) => {
                    this.loading  = false;
                    this.distance = response.data.data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.error   = error.data.message || error.message;
                });
            if (this.$auth.check()) {
                axios
                    .get("/leaderboard/friends")
                    .then((response) => {
                        this.loading = false;
                        this.friends = response.data.data;
                        if (!Object.keys(this.friends).length) {
                            this.friends = null;
                        }
                    })
                    .catch((error) => {
                        this.loading = false;
                        this.error   = error.data.message || error.message;
                    });
            }
        },
    },
    created() {
        this.fetchData();
    }
};
</script>

<style scoped>

</style>
