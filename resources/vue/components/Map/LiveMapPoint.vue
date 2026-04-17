<script setup lang="ts">
import { MglMarker, MglPopup } from '@indoorequal/vue-maplibre-gl';
import { onMounted, onUnmounted, PropType, ref } from 'vue';
import { LivePointDto } from '../../../types/Api.gen';

const props = defineProps({
    point: {
        type: Object as PropType<LivePointDto>,
        required: true,
    },
});

const currentMarkerCoordinates = ref<[number, number] | null>(null);
const startTime = Date.now();
const arrivalTime = props.point.arrival ? props.point.arrival * 1000 : Date.now();
let animationFrameId: number | null = null;

function getLivePointData(percentage: number = 1): boolean {
    const features = props.point?.polyline?.features;
    const featuresCopy = features ? [...features] : [];
    const featureA = featuresCopy?.pop();
    const featureB = featuresCopy?.pop();
    const coordsB = featureA?.geometry?.coordinates;
    const coordsA = featureB?.geometry?.coordinates;
    if (!coordsA || !coordsB || coordsA.length === 0 || coordsB.length === 0) {
        console.error('No coordinates found for the provided features.');
        return false;
    }
    // interpolate between featureA and featureB based on percentage
    const latitude = coordsA[1] + (coordsB[1] - coordsA[1]) * percentage;
    const longitude = coordsA[0] + (coordsB[0] - coordsA[0]) * percentage;
    currentMarkerCoordinates.value = [longitude, latitude];
    return true;
}

function animateMarker() {
    const now = Date.now();

    if (now >= arrivalTime) {
        currentMarkerCoordinates.value = null;
        return;
    }

    const totalDuration = arrivalTime - startTime;
    if (totalDuration <= 0) {
        currentMarkerCoordinates.value = null;
        return;
    }

    const elapsed = now - startTime;
    const percentage = Math.min(elapsed / totalDuration, 1);
    if (!getLivePointData(percentage)) {
        return;
    }
    if (percentage < 1) {
        animationFrameId = requestAnimationFrame(animateMarker);
    }
}

onMounted(() => {
    // Only animate if the journey has not yet ended
    if (Date.now() < arrivalTime) {
        animateMarker();
    }
});

onUnmounted(() => {
    if (animationFrameId !== null) {
        cancelAnimationFrame(animationFrameId);
    }
});
</script>

<template>
    <mgl-marker v-if="currentMarkerCoordinates" :coordinates="currentMarkerCoordinates">
        <template #marker>
            <img
                class="img-thumbnail rounded-circle img-fluid"
                style="width: 20px; padding: 1px"
                :src="point.status.user.profilePictureUrl"
                :alt="point.status.user.username"
            />
        </template>
        <mgl-popup ref="popup">
            <div>
                <strong>{{ point.lineName }}</strong
                ><br />
            </div>
        </mgl-popup>
    </mgl-marker>
</template>

<style scoped>
.img-thumbnail {
    border: none;
}

.rounded-circle {
    border-radius: 50%;
}
</style>
