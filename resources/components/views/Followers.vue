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
                <div v-if="!loading" class="card-body p-0">
                    <ul id="myTab" class="nav nav-tabs nav-fill" role="tablist">
                        <li class="nav-item">
                            <a id="main-tab" aria-controls="home" aria-selected="true" class="nav-link active px-4"
                               data-mdb-toggle="tab" href="#followers" role="tab">
                                {{ i18n.get("_.menu.settings.myFollower") }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a id="distance-tab" aria-controls="profile" aria-selected="false" class="nav-link px-4"
                               data-mdb-toggle="tab" href="#requests" role="tab">
                                {{ i18n.get("_.menu.settings.follower-requests") }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a id="friends-tab" aria-controls="contact" aria-selected="false" class="nav-link px-4"
                               data-mdb-toggle="tab" href="#followings" role="tab">
                                {{ i18n.get("_.menu.settings.followings") }}
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="followers" class="tab-pane fade show active table-responsive"
                             role="tabpanel">
                            1
                        </div>
                        <div id="requests" class="tab-pane fade table-responsive" role="tabpanel">
                            2
                        </div>
                        <div v-if="" id="followings"
                             class="tab-pane fade table-responsive" role="tabpanel">
                            3
                        </div>
                    </div>
                </div>
                <div v-else class="card-body">
                    <Spinner class="mt-5"/>
                </div>
                <div class="card-footer text-muted">
<!--                    Pagination -->
                </div>
            </div>
        </div>
    </LayoutBasicNoSidebar>
</template>

<script>

import LayoutBasic from "../layouts/Basic";
import Spinner from "../Spinner";
import LayoutBasicNoSidebar from "../layouts/BasicNoSidebar";

export default {
    name: "Followers",
    data() {
        return {
            followers: [null],
            followRequests: [null],
            followings: [null],
            loading: false
        };
    },
    metaInfo() {
        return {
            title: this.i18n.get("_.menu.settings.myFollower")
        };
    },
    components: {
        LayoutBasicNoSidebar,
        Spinner,
        LayoutBasic
    },
    methods: {
        fetchData() {
            //
        },
    },
    created() {
        this.fetchData();
    }
};
</script>

<style scoped>

</style>
