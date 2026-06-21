<script setup lang="ts">
import { Map, Train } from '@lucide/vue';
import { transChoice } from 'laravel-vue-i18n';
import { LngLat, LngLatBounds } from 'maplibre-gl';
import { onUnmounted, ref } from 'vue';
import { Api, EventResource, LivePointDto, MapProvider, StatusResource } from '../../../types/Api.gen';
import EventMarker from '../../../vue/components/Map/EventMarker.vue';
import GenericMap from '../../../vue/components/Map/GenericMap.vue';
import { useUserStore } from '../../../vue/stores/user';
import Loading from '../../components/Loading.vue';
import StatusCard from '../../components/Status/StatusCard.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const user = useUserStore();

const statuses = ref<StatusResource[]>([]);
const livePositions = ref<LivePointDto[]>([]);
const bounds = ref<LngLatBounds>(LngLatBounds.fromLngLat(new LngLat(9.902056, 49.843), 1000000));
const events = ref<EventResource[]>([]);
const loading = ref(true);

function fetchStatuses() {
    loading.value = true;
    api.statuses
        .getActiveStatuses()
        .then((response) => {
            response.json().then((data) => {
                statuses.value = data.data;
                loading.value = false;
            });
        })
        .catch(() => {
            loading.value = false;
        });
}

function fetchLivePositions(updateBounds = true) {
    api.positions
        .getLivePositionsForActiveStatuses()
        .then((response) => {
            livePositions.value = response.data.data || [];
            if (updateBounds) {
                const newBounds = LngLatBounds.fromLngLat(new LngLat(9.902056, 49.843), 1000000);
                for (const position of livePositions.value) {
                    position.polyline?.features?.forEach((feature) => {
                        const coord = feature.geometry?.coordinates;
                        if (coord && coord[0] && coord[1]) {
                            newBounds.extend([coord[0], coord[1]]);
                        }
                    });
                }
                bounds.value = newBounds;
            }
        })
        .catch((error) => {
            console.error('Error fetching live positions:', error);
        });
}

function fetchEvents() {
    api.events.getEvents().then((response) => {
        events.value = response.data.data || [];
    });
}

fetchStatuses();
fetchEvents();
fetchLivePositions();

const statusInterval = setInterval(() => fetchStatuses(), 10000);
const positionInterval = setInterval(() => fetchLivePositions(false), 20000);

onUnmounted(() => {
    clearInterval(statusInterval);
    clearInterval(positionInterval);
});
</script>

<template>
    <AppLayout>
        <div class="container mx-auto md:px-4 py-4 min-h-screen">
            <h1 class="font-bold text-xl mb-4 flex items-center gap-2">
                <Map class="size-6" />
                {{ $t('menu.active') }}
                <Loading v-if="loading" />
            </h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Map -->
                <div class="flex flex-col gap-2">
                    <div class="rounded-box overflow-hidden h-80 md:h-[500px] isolate">
                        <GenericMap
                            :live-positions="livePositions"
                            :bounds="bounds"
                            :map-provider="
                                user.user?.mapProvider
                                    ? MapProvider[user.user!.mapProvider as keyof typeof MapProvider]
                                    : MapProvider.Cargo
                            "
                        >
                            <EventMarker v-for="event in events" :key="event.id" :event="event" />
                        </GenericMap>
                    </div>

                    <p class="text-lg text-center flex items-center justify-center gap-2">
                        <Train class="size-5" />
                        {{ statuses.length }}
                        {{ transChoice('active-journeys', statuses.length) }}
                    </p>
                </div>

                <!-- Status list -->
                <div class="flex flex-col gap-3">
                    <div v-if="loading && statuses.length === 0" class="flex justify-center py-12">
                        <span class="loading loading-spinner loading-lg" />
                    </div>

                    <div v-else-if="!loading && statuses.length === 0" class="alert alert-warning">
                        <span class="font-semibold">{{ $t('empty-en-route') }}</span>
                    </div>

                    <StatusCard v-for="status in statuses" :key="status.id" :status="status" :show-map="false" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
