<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { ref } from 'vue';
import { Api, LeaderboardUserResource } from '../../../types/Api.gen';
import { useUserStore } from '../../stores/user';
import LeaderboardTable from './LeaderboardTable.vue';

const usersLeaderboard = ref<LeaderboardUserResource[]>([]);
const distanceLeaderboard = ref<LeaderboardUserResource[]>([]);
const friendsLeaderboard = ref<LeaderboardUserResource[]>([]);
const loading = ref(0);

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const user = useUserStore();

function fetchData() {
    loading.value = user.isAuthenticated ? 3 : 2;
    api.leaderboard
        .getLeaderboard()
        .then((data) => {
            data.json().then((data) => {
                usersLeaderboard.value = data.data;
                loading.value--;
            });
        })
        .catch(() => {
            loading.value--;
        });

    api.leaderboard
        .getLeaderboardByDistance()
        .then((data) => {
            data.json().then((data) => {
                distanceLeaderboard.value = data.data;
                loading.value--;
            });
        })
        .catch(() => {
            loading.value--;
        });

    if (!user.authenticated) {
        return;
    }
    api.leaderboard
        .getLeaderboardByFriends()
        .then((data) => {
            data.json().then((data) => {
                friendsLeaderboard.value = data.data;
                loading.value--;
            });
        })
        .catch(() => {
            loading.value--;
        });
}
fetchData();
</script>

<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-9">
                <div id="leaderboard" class="card">
                    <div class="card-header">
                        <a :href="`/leaderboard/${DateTime.now().toFormat('yyyy-MM')}`" class="float-end">
                            <i class="fas fa-chart-line"></i>
                            {{ trans('leaderboard.month.title') }}
                        </a>
                        {{ trans('menu.leaderboard') }}
                    </div>
                    <div class="card-body">
                        <ul id="myTab" class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a
                                    id="main-tab"
                                    class="nav-link active"
                                    data-bs-toggle="tab"
                                    href="#leaderboard-main"
                                    role="tab"
                                    aria-controls="home"
                                    aria-selected="true"
                                >
                                    {{ trans('leaderboard.top') }} {{ usersLeaderboard?.length || '' }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    id="distance-tab"
                                    class="nav-link"
                                    data-bs-toggle="tab"
                                    href="#leaderboard-distance"
                                    role="tab"
                                    aria-controls="profile"
                                    aria-selected="false"
                                >
                                    {{ trans('leaderboard.distance') }}
                                </a>
                            </li>
                            <li v-if="friendsLeaderboard?.length > 1" class="nav-item">
                                <a
                                    id="friends-tab"
                                    class="nav-link"
                                    data-bs-toggle="tab"
                                    href="#leaderboard-friends"
                                    role="tab"
                                    aria-controls="contact"
                                    aria-selected="false"
                                >
                                    {{ trans('leaderboard.friends') }}
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div
                                id="leaderboard-main"
                                class="tab-pane fade show active table-responsive"
                                role="tabpanel"
                            >
                                <LeaderboardTable :data="usersLeaderboard" described-by="main-tab" />
                            </div>
                            <div id="leaderboard-distance" class="tab-pane fade table-responsive" role="tabpanel">
                                <LeaderboardTable :data="distanceLeaderboard" described-by="distance-tab" />
                            </div>
                            <div id="leaderboard-friends" class="tab-pane fade table-responsive" role="tabpanel">
                                <LeaderboardTable :data="friendsLeaderboard" described-by="friends-tab" />
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <i class="far fa-question-circle"></i>
                        {{ trans('leaderboard.notice') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
