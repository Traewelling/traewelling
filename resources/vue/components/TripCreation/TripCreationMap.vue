<script setup lang="ts">
import { LngLat, LngLatBounds } from 'maplibre-gl';
import { computed, ref } from 'vue';
import GenericMap from '../Map/GenericMap.vue';

type MapStation = { id?: string | number; name: string; latitude: number; longitude: number };
type MapPoint = { lat: number; lng: number; title: string };

const MARKER_COLOR = '#c30b82';

const origin = ref<MapPoint | null>(null);
const destination = ref<MapPoint | null>(null);
const stopovers = ref<MapPoint[]>([]);

const orderedPoints = computed<MapPoint[]>(() =>
    [origin.value, ...stopovers.value, destination.value].filter((point): point is MapPoint => point !== null),
);

const markers = computed(() =>
    orderedPoints.value.map((point, index) => ({
        id: `${index}-${point.lat}-${point.lng}`,
        lat: point.lat,
        lng: point.lng,
        color: MARKER_COLOR,
        title: point.title,
    })),
);

const bounds = computed<LngLatBounds>(() => {
    const points = orderedPoints.value;
    if (!points.length) {
        return LngLatBounds.fromLngLat(new LngLat(10.47, 50.3), 500000);
    }
    const result = LngLatBounds.fromLngLat(new LngLat(points[0].lng, points[0].lat), 2000);
    for (const point of points) {
        result.extend([point.lng, point.lat]);
    }
    return result;
});

function toPoint(station: MapStation): MapPoint {
    return { lat: station.latitude, lng: station.longitude, title: station.name };
}

/**
 * Places the marker for the origin, the destination or the stopover at the given index.
 * A stopover marker replaces the existing one at that index once every stopover has a marker.
 */
function addMarker(station: MapStation, index: 'origin' | 'destination' | number, length: number): void {
    const point = toPoint(station);

    if (index === 'origin') {
        origin.value = point;
        return;
    }
    if (index === 'destination') {
        destination.value = point;
        return;
    }

    if (length === stopovers.value.length) {
        removeMarker(index);
    }
    if (index === 0 || index === stopovers.value.length) {
        stopovers.value.push(point);
    } else {
        stopovers.value.splice(index, 0, point);
    }
}

function removeMarker(index: number): void {
    stopovers.value.splice(index, 1);
}

defineExpose({ addMarker, removeMarker });
</script>

<template>
    <GenericMap :bounds="bounds" :preview-markers="markers" :cooperative-gestures="false" height="100%" />
</template>
