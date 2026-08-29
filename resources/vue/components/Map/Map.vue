<script setup lang="ts">
import { DateTime } from 'luxon';
import { GeoJSONFeature } from 'maplibre-gl';
import { computed, onMounted, PropType, ref } from 'vue';
import { Api, EventResource, LivePointDto, MapProvider, StatusResource } from '../../../types/Api.gen';
import { useUserStore } from '../../stores/user';
import EventMarker from './EventMarker.vue';
import GenericMap from './GenericMap.vue';

const props = defineProps({
    statuses: {
        type: Array as PropType<StatusResource[]>,
        required: false,
        default: () => [],
    },
});

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const userStore = useUserStore();

const lineColor = ref<string>('#c72730');
const polylines = ref<GeoJSONFeature[]>([]);
const livePositions = ref<LivePointDto[]>([]);
const events = ref<EventResource[]>([]);
const mapProvider = computed<MapProvider>(() => userStore.user?.mapProvider ?? MapProvider.OpenFreeMap);

if (props.statuses.length === 1) {
    lineColor.value = props.statuses[0].checkin.routeColor ? '#' + props.statuses[0].checkin.routeColor : '#c72730';
    api.polyline
        .getPolylines(props.statuses.map((s) => s.id.toString()).join(','))
        .then((response) => {
            for (const feature of response.data.data?.features || []) {
                polylines.value.push(feature as GeoJSONFeature);
            }
        })
        .catch((error) => {
            console.error('Error fetching polylines:', error);
        });
}

function filterActiveStatuses() {
    return props.statuses.filter((s) => {
        const arrival =
            s.checkin.manualArrival ??
            s.checkin.destination.arrivalReal ??
            s.checkin.destination.arrivalPlanned ??
            s.checkin.destination.departureReal ??
            s.checkin.destination.departurePlanned;
        if (arrival) {
            return DateTime.fromISO(arrival) > DateTime.now();
        }
    });
}

function fetchPositions() {
    api.positions
        .getLivePositionsForStatuses(
            filterActiveStatuses()
                .map((s) => s.id.toString())
                .join(','),
        )
        .then((response) => {
            livePositions.value = response.data.data || [];
        })
        .catch((error) => {
            console.error('Error fetching live positions:', error);
        });
}

function fetchEvents() {
    api.events
        .getEvents()
        .then((response) => {
            events.value = response.data.data || [];
        })
        .catch((error) => {
            console.error('Error fetching events:', error);
        });
}

onMounted(() => {
    fetchPositions();
    fetchEvents();
    const interval = setInterval(() => {
        fetchPositions();
    }, 30000); // Refresh every 30 seconds

    // Clear the interval when the component is unmounted
    return () => clearInterval(interval);
});
</script>

<template>
    <GenericMap
        v-if="polylines.length || livePositions.length"
        :poly-lines="polylines"
        :line-color="lineColor"
        :live-positions="livePositions"
        :map-provider="mapProvider"
        :cooperative-gestures="false"
    >
        <EventMarker v-for="trwlEvent in events" :key="trwlEvent.id" :event="trwlEvent" />
    </GenericMap>
</template>
