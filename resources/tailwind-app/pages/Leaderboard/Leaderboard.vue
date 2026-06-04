<script setup lang="ts">
import { ChartLine, Medal } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { onMounted, ref } from 'vue';
import { Api, LeaderboardUserResource } from '../../../types/Api.gen';
import { useUserStore } from '../../../vue/stores/user';
import Loading from '../../components/Loading.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import LeaderboardEntry from './partials/LeaderboardEntry.vue';

const usersLeaderboard = ref<LeaderboardUserResource[]>([]);
const distanceLeaderboard = ref<LeaderboardUserResource[]>([]);
const friendsLeaderboard = ref<LeaderboardUserResource[]>([]);
const selectedData = ref<LeaderboardUserResource[]>([]);
const selected = ref('users');
const focusData = ref<'points' | 'distance' | 'duration' | 'speed'>('points');
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
            select(selected.value);
        })
        .catch(() => {
            loading.value--;
        });

    api.leaderboard
        .getLeaderboardByDistance()
        .then((data) => {
            data.json().then((data) => {
                distanceLeaderboard.value = data.data;
                select('users');
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

function select(type: string) {
    selected.value = type;
    switch (type) {
        case 'users':
            selectedData.value = usersLeaderboard.value;
            focusData.value = 'points';
            break;
        case 'distance':
            selectedData.value = distanceLeaderboard.value;
            focusData.value = 'distance';
            break;
        case 'friends':
            selectedData.value = friendsLeaderboard.value;
            focusData.value = 'points';
            break;
    }
}

onMounted(fetchData);
</script>

<template>
    <AppLayout>
        <div class="container mx-auto md:px-4 py-4 min-h-screen">
            <div class="flex justify-between">
                <h1 class="font-bold text-xl mb-1">
                    <Medal class="size-8 inline-block" />
                    {{ $t('menu.leaderboard') }}
                    <Loading v-if="loading" />
                </h1>

                <RouterLink
                    :to="{ name: 'leaderboard-monthly', params: { month: DateTime.now().toFormat('yyyy-MM') } }"
                    class="link text-xs"
                >
                    <ChartLine class="inline-block size-3" />
                    {{ $t('leaderboard.month.title') }}
                </RouterLink>
            </div>
            <p class="mb-4">
                {{ $t('leaderboard.notice') }}
            </p>

            <div class="flex justify-between mb-2">
                <div role="tablist" class="tabs tabs-box">
                    <a role="tab" class="tab" :class="{ 'tab-active': selected === 'users' }" @click="select('users')">
                        {{ $t('leaderboard.top') }} {{ usersLeaderboard.length || '' }}
                    </a>
                    <a
                        role="tab"
                        class="tab"
                        :class="{ 'tab-active': selected === 'distance' }"
                        @click="select('distance')"
                    >
                        {{ $t('leaderboard.distance') }}
                    </a>
                    <a
                        v-if="friendsLeaderboard.length > 1"
                        role="tab"
                        class="tab"
                        :class="{ 'tab-active': selected === 'friends' }"
                        @click="select('friends')"
                    >
                        {{ $t('leaderboard.friends') }}
                    </a>
                </div>
                <div class="max-w-xs">
                    <select v-model="focusData" class="select flex md:hidden">
                        <option :value="'duration'">{{ $t('leaderboard.duration') }}</option>
                        <option :value="'distance'">{{ $t('leaderboard.distance') }}</option>
                        <option :value="'speed'">{{ $t('leaderboard.averagespeed') }}</option>
                        <option :value="'points'">{{ $t('leaderboard.points') }}</option>
                    </select>
                </div>
            </div>

            <ul class="list bg-base-100 rounded-box shadow-md">
                <LeaderboardEntry
                    v-for="(entry, number) in selectedData"
                    :key="entry.user.id"
                    :number
                    :entry
                    :show-distance="focusData === 'distance'"
                    :show-duration="focusData === 'duration'"
                    :show-points="focusData === 'points'"
                    :show-speed="focusData === 'speed'"
                />
            </ul>
        </div>
    </AppLayout>
</template>
