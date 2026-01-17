<script setup lang="ts">
import { MglMarker, MglPopup } from '@indoorequal/vue-maplibre-gl';
import { trans, transChoice } from 'laravel-vue-i18n';
import { LngLat, LngLatBounds } from 'maplibre-gl';
import { Notyf } from 'notyf';
import { ref } from 'vue';
import { Api, EventResource, LivePointDto, StatusResource } from '../../types/Api.gen';
import ActiveJourneyMap from '../components/ActiveJourneyMap.vue';
import GenericMap from '../components/Map/GenericMap.vue';
import StatusCard from '../components/Status/StatusCard.vue';
import { DtmRange } from '../helpers/DateRange';
import { useUserStore } from '../stores/user';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const statuses = ref<StatusResource[]>([]);
const livePositions = ref([] as LivePointDto[]);
const bounds = ref(LngLatBounds.fromLngLat(new LngLat(9.902056, 49.843), 1000000) as LngLatBounds);
const events = ref([] as EventResource[]);
const loading = ref(true);
const user = useUserStore();
const notyf = new Notyf({ position: { x: 'right', y: 'bottom' } });

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
        .catch((error) => {
            loading.value = false;
            notyf.error('Error fetching statuses: ' + error.message);
        });
}

function fetchStatusPositions(initialize: boolean = true) {
    if (!user.hasBeta) return;

    api.positions
        .getLivePositionsForActiveStatuses()
        .then((response) => {
            livePositions.value = response.data.data || [];
            const newBounds = LngLatBounds.fromLngLat(new LngLat(9.902056, 49.843), 1000000);
            for (const position of livePositions.value) {
                position.polyline?.features?.forEach((feature) => {
                    const coord = feature.geometry?.coordinates;
                    if (coord && coord[0] && coord[1]) {
                        newBounds.extend([coord[0], coord[1]]);
                    }
                });
            }
            if (initialize) {
                bounds.value = newBounds;
            }
        })
        .catch((error) => {
            console.error('Error fetching live positions: ' + error.message);
        });
}

function fetchEvents() {
    if (!user.hasBeta) return;
    api.events.getEvents().then((response) => {
        events.value = response.data.data || [];
    });
}

fetchStatuses();
fetchEvents();
fetchStatusPositions();
fetchStatusPositions();

setInterval(() => {
    fetchStatusPositions(false);
}, 20000); // Refresh live positions every 20 seconds

setInterval(() => {
    fetchStatuses();
}, 10000); // Refresh the map every 10 seconds
</script>

<template>
    <div class="row">
        <div class="col-12">
            <h1 class="fs-4">
                {{ trans('menu.active') }}
            </h1>
        </div>
        <div id="activeJourneys" class="col-md-6 mb-4">
            <GenericMap v-if="user.hasBeta" :live-positions="livePositions" :bounds="bounds">
                <template v-for="trwlEvent in events">
                    <mgl-marker
                        v-if="trwlEvent.station?.latitude && trwlEvent.station?.longitude"
                        :key="trwlEvent.id"
                        :coordinates="[trwlEvent.station.longitude, trwlEvent.station.latitude]"
                    >
                        <mgl-popup>
                            <strong
                                ><a target="_blank" :href="trwlEvent.url">{{ trwlEvent.name }}</a></strong
                            ><br />
                            <i class="fa fa-user-clock" /> {{ trwlEvent.host }}<br />
                            <i class="fa fa-calendar-day" />{{
                                DtmRange.fromISO(trwlEvent.begin, trwlEvent.end).toLocaleDateString()
                            }}<br />
                            <a :href="`/event/${trwlEvent.slug}`">{{ trans('events.show-all-for-event') }}</a>
                        </mgl-popup>
                    </mgl-marker>
                </template>
            </GenericMap>
            <ActiveJourneyMap v-else ref="map" :map-provider="user.user?.mapProvider || 'default'" />

            <div class="row text-center fs-5 mt-3">
                <div class="col mb-3">
                    <i class="fa-solid fa-train" />
                    {{ statuses.length }}
                    {{ transChoice('active-journeys', statuses.length) }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div v-if="statuses.length === 0 && loading" class="text-center my-4">
                <i class="fa-solid fa-spinner fa-spin fa-2x" aria-hidden="true" />
            </div>
            <div v-else-if="statuses.length === 0" class="alert alert-danger text-center">
                <strong class="fs-4">{{ trans('empty-en-route') }}</strong>
            </div>

            <StatusCard
                v-for="status in statuses"
                :key="status.id"
                :status="status"
                :authenticated-user="user.user"
                :show-map="false"
            />
        </div>
    </div>
</template>
