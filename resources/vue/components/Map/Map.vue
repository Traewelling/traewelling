<script setup lang="ts">
import { GeoJSONFeature, LngLat, LngLatBounds } from 'maplibre-gl';
import { PropType, ref } from 'vue';
import { Api, LivePointDto, StatusResource } from '../../../types/Api.gen';
import GenericMap from './GenericMap.vue';

const props = defineProps({
    statuses: {
        type: Array as PropType<StatusResource[]>,
        required: false,
        default: () => [],
    },
});

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const center = ref(new LngLat(9.902056, 49.843));
const bounds = ref<LngLatBounds>(LngLatBounds.fromLngLat(center.value, 100000));
const lineColor = ref<string>('#c72730');
const polylines = ref<GeoJSONFeature[]>([]);
const livePositions = ref<LivePointDto[]>([]);

if (props.statuses.length === 1) {
    lineColor.value = props.statuses[0].train.routeColor ? '#' + props.statuses[0].train.routeColor : '#c72730';
    api.polyline
        .getPolylines(props.statuses.map((s) => s.id.toString()).join(','))
        .then((response) => {
            const newBounds = new LngLatBounds();

            for (const feature of response.data.data?.features || []) {
                polylines.value.push(feature as GeoJSONFeature);
                for (const point of feature.geometry?.coordinates || []) {
                    newBounds.extend([point[0], point[1]]);
                }
            }
            setNewBounds(newBounds);
        })
        .catch((error) => {
            console.error('Error fetching polylines:', error);
        });
}

function setNewBounds(newBounds: LngLatBounds) {
    const sw = newBounds.getSouthWest();
    const ne = newBounds.getNorthEast();
    if (!sw || !ne) return;
    const latDiff = ne.lat - sw.lat;
    const lngDiff = ne.lng - sw.lng;
    newBounds = new LngLatBounds(
        [sw.lng - lngDiff * 0.1, sw.lat - latDiff * 0.1],
        [ne.lng + lngDiff * 0.1, ne.lat + latDiff * 0.1],
    );
    bounds.value = newBounds;
}

api.positions
    .getLivePositionsForStatuses(props.statuses.map((s) => s.id.toString()).join(','))
    .then((response) => {
        livePositions.value = response.data.data || [];

        console.log(livePositions.value);
    })
    .catch((error) => {
        console.error('Error fetching live positions:', error);
    });
</script>

<template>
    <GenericMap
        v-if="polylines.length || livePositions.length"
        :bounds="bounds"
        :poly-lines="polylines"
        :line-color="lineColor"
        :live-positions="livePositions"
    />
</template>
