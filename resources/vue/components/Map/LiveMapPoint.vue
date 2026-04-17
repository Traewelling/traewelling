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
            <img class="live-map-avatar" :src="point.status.user.profilePictureUrl" :alt="point.status.user.username" />
        </template>
        <mgl-popup ref="popup">
            <div class="live-popup">
                <a :href="`/@${point.status.user.username}`" class="live-popup-link">@{{ point.status.user.username }}</a>
                <a :href="`/status/${point.status.id}`" class="live-popup-link">Status →</a>
            </div>
        </mgl-popup>
    </mgl-marker>
</template>

<style scoped>
.live-map-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
    object-fit: cover;
}

.live-popup {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.live-popup-link {
    font-size: 0.85rem;
    color: #000 !important;
    text-decoration: none;
    font-weight: 500;
}

.live-popup-link:hover {
    text-decoration: underline;
}
</style>
