<template>
    <LayoutBasicNoSidebar>
        <div class="col-md-9 col-lg-9">
            <div id="leaderboard" class="card">
                <div class="card-header">
                    <router-link :to="{name: 'settings'}" class="text-black-50">
                        <i class="fas fa-arrow-left" aria-hidden="true"></i> &nbsp;
                        {{ i18n.get("_.menu.settings") }}
                    </router-link>
                </div>
                <div class="card-body p-0">
                    <ul id="myTab" class="nav nav-tabs nav-fill" role="tablist">
                        <li class="nav-item">
                            <a id="followers-tab" aria-controls="home" aria-selected="true" class="nav-link active px-4"
                               data-mdb-toggle="tab" href="#followers" role="tab">
                                {{ i18n.get("_.menu.settings.myFollower") }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a id="requests-tab" aria-controls="profile" aria-selected="false" class="nav-link px-4"
                               data-mdb-toggle="tab" href="#requests" role="tab">
                                {{ i18n.get("_.menu.settings.follower-requests") }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a id="followings-tab" aria-controls="contact" aria-selected="false" class="nav-link px-4"
                               data-mdb-toggle="tab" href="#followings" role="tab">
                                {{ i18n.get("_.menu.settings.followings") }}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="followers" class="tab-pane fade show active table-responsive"
                             role="tabpanel">
                            <FollowTable v-if="followers" :users="followers"></FollowTable>
                            <spinner v-if="followersLoading"></spinner>
                        </div>
                        <div id="requests" class="tab-pane fade table-responsive" role="tabpanel">
                            <FollowTable v-if="followRequests" :users="followRequests"></FollowTable>
                            <spinner v-if="followRequestsLoading"></spinner>
                        </div>
                        <div v-if="" id="followings"
                             class="tab-pane fade table-responsive" role="tabpanel">
                            <FollowTable v-if="followRequests" :users="followings"></FollowTable>
                            <spinner v-if="followRequestsLoading"></spinner>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-muted">
                </div>
            </div>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>

import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";
import Settings from "../../js/ApiClient/Settings";
import FollowTable from "../FollowTable";

export default {
    name: "Followers",
    data() {
        return {
            followers: [null],
            followRequests: [null],
            followings: [null],
            followersLoading: true,
            followRequestsLoading: true,
            followingsLoading: true,
            followersLinks: null,
            followRequestsLinks: null,
            followingsLinks: null
        };
    },
    metaInfo() {
        return {
            title: this.i18n.get("_.menu.settings.myFollower")
        };
    },
    components: {
        FollowTable,
        LayoutBasicNoSidebar,
        Spinner,
        LayoutBasic
    },
    methods: {
        fetchData() {
            Settings
                .getFollowers()
                .then((data) => {
                    this.followersLoading = false;
                    this.followers        = data.data;
                    this.followersLinks   = data.links;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                    this.followersLoading = false;
                });

            Settings
                .getFollowings()
                .then((data) => {
                    this.followingsLoading = false;
                    this.followings        = data.data;
                    this.followingsLinks   = data.links;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                    this.followingsLoading = false;
                });


            Settings
                .getFollowRequests()
                .then((data) => {
                    this.followRequestsLoading = false;
                    this.followRequests        = data.data;
                    this.followRequestsLinks   = data.links;
                })
                .catch((error) => {
                    this.apiErrorHandler(error);
                    this.followRequestsLoading = false;
                });
        },
    },
    created() {
        this.fetchData();
    }
};
</script>

<style scoped>

</style>
