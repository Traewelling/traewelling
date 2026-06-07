<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto min-h-screen">
            <div class="flex items-center gap-4 mb-4">
                <h1 class="text-2xl font-bold">
                    {{ $t('stationboard.submit-search') }}
                    <Loading v-if="loading" />
                </h1>
            </div>

            <div class="join w-full mb-2">
                <input
                    v-model="query"
                    autofocus
                    class="input join-item w-full"
                    :placeholder="$t('stationboard.submit-search')"
                    @keyup.enter="getResults()"
                />
                <button class="btn join-item" @click="getResults()">
                    <Search class="w-4 h-4">
                        <title>
                            {{ $t('search') }}
                        </title>
                    </Search>
                </button>
            </div>

            <template v-for="entry in results" :key="entry.id">
                <div
                    class="card card-side bg-base-100 shadow-sm my-2 cursor-pointer"
                    @click="router.push({ name: 'user-profile', params: { username: entry.username } })"
                >
                    <figure>
                        <div class="avatar">
                            <div class="min-w-24 max-w-24 rounded">
                                <img :src="entry.profilePicture" :alt="entry.username" />
                            </div>
                        </div>
                    </figure>
                    <div class="card-body">
                        <h2 class="card-title">
                            {{ entry.displayName }} <small class="opacity-65">@{{ entry.username }}</small>
                        </h2>
                        <div class="flex flex-wrap items-center gap-1.5 mt-1 text-sm text-base-content/60">
                            <div class="md:flex items-center">
                                <div>
                                    <Timer class="inline-block size-4 me-1">
                                        <title>{{ $t('leaderboard.duration') }}</title>
                                    </Timer>
                                    <DurationSpan :duration="60 * entry.totalDuration" />
                                </div>
                            </div>

                            <div class="col md:flex items-center">
                                <div>
                                    <Route class="inline-block size-4 me-1">
                                        <title>{{ $t('leaderboard.distance') }}</title>
                                    </Route>
                                    {{ Math.round(entry.totalDistance / 1000) }} <small class="text-muted">km</small>
                                </div>
                            </div>

                            <div class="items-center md:flex">
                                <div>
                                    <Gauge class="inline-block size-4 me-1">
                                        <title>{{ $t('leaderboard.averagespeed') }}</title>
                                    </Gauge>
                                    {{ calculateAverageSpeed(entry.totalDistance, entry.totalDuration) }}
                                    <small class="text-muted">km/h</small>
                                </div>
                            </div>

                            <div v-if="entry.points" class="md:flex items-center">
                                <div>
                                    <Gem class="inline-block size-4 me-1">
                                        <title>{{ $t('leaderboard.points') }}</title>
                                    </Gem>
                                    {{ entry.points || 0 }}
                                </div>
                            </div>
                        </div>
                        <p v-if="entry.bio" class="italic">
                            {{ entry.bio }}
                        </p>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Gauge, Gem, Route, Search, Timer } from '@lucide/vue';
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { Api, UserResource } from '../../../types/Api.gen';
import DurationSpan from '../../../vue/components/Status/Partials/DurationSpan.vue';
import Loading from '../../components/Loading.vue';
import AppLayout from '../../layouts/AppLayout.vue';
import router from '../../router';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const route = useRoute();
const query = ref('');
const loading = ref(false);
const results = ref<UserResource[]>([]);

function getFromSearchBar() {
    const params = new URLSearchParams(route.query as Record<string, string>);
    const query = params.get('searchQuery');

    return query || null;
}

function getResults() {
    loading.value = true;
    results.value = [];
    router.push({ name: 'search', query: { searchQuery: query.value } });

    api.user
        .searchUsers(query.value)
        .then((response) => {
            results.value = response.data.data || [];

            if (results.value.length == 1) {
                router.push({ name: 'user-profile', params: { username: results.value[0].username } });
            }
        })
        .catch((error) => {
            console.error(error);
        })
        .finally(() => {
            loading.value = false;
        });
}

const calculateAverageSpeed = (distance: number, duration: number): number => {
    if (duration === 0) {
        return 0;
    }
    // distance in meters, duration in minutes
    const hours = duration / 60;
    const km = distance / 1000;
    return Math.round(km / hours);
};

onMounted(() => {
    const initialQuery = getFromSearchBar();
    if (initialQuery) {
        query.value = initialQuery;
        getResults();
    }
});
</script>
