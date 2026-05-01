<script setup lang="ts">
import { GeoJSONFeature } from 'maplibre-gl';
import { computed, PropType, ref } from 'vue';
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
const mapProvider = computed<MapProvider>(() => userStore.user?.mapProvider ?? MapProvider.Cargo);

if (props.statuses.length === 1) {
    lineColor.value = props.statuses[0].train.routeColor ? '#' + props.statuses[0].train.routeColor : '#c72730';
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

api.positions
    .getLivePositionsForStatuses(props.statuses.map((s) => s.id.toString()).join(','))
    .then((response) => {
        livePositions.value = response.data.data || [];
    })
    .catch((error) => {
        console.error('Error fetching live positions:', error);
    });

api.events
    .getEvents()
    .then((response) => {
        events.value = response.data.data || [];
    })
    .catch((error) => {
        console.error('Error fetching events:', error);
    });
</script>

<template>
    <GenericMap
        v-if="polylines.length || livePositions.length"
        :poly-lines="polylines"
        :line-color="lineColor"
        :live-positions="livePositions"
        :map-provider="mapProvider"
    >
        <EventMarker v-for="trwlEvent in events" :key="trwlEvent.id" :event="trwlEvent" />
    </GenericMap>
</template>
