<script setup lang="ts">
import { MglMarker, MglPopup } from '@indoorequal/vue-maplibre-gl';
import { onMounted, onUnmounted, PropType, ref, watch } from 'vue';
import { Coordinate, LivePointDto } from '../../../types/Api.gen';

type LngLat = [number, number];

const props = defineProps({
    point: {
        type: Object as PropType<LivePointDto>,
        required: true,
    },
});

const currentMarkerCoordinates = ref<LngLat | null>(null);
let animationFrameId: number | null = null;

function toLngLat(feature: Coordinate | null | undefined): LngLat | null {
    const coordinates = feature?.geometry?.coordinates;
    if (!coordinates || typeof coordinates[0] !== 'number' || typeof coordinates[1] !== 'number') {
        return null;
    }
    return [coordinates[0], coordinates[1]];
}

function segmentLength(a: LngLat, b: LngLat): number {
    const latitudeScale = Math.cos((((a[1] + b[1]) / 2) * Math.PI) / 180);
    return Math.hypot((b[0] - a[0]) * latitudeScale, b[1] - a[1]);
}

function positionAlong(line: LngLat[], lengths: number[], totalLength: number, fraction: number): LngLat {
    let remaining = totalLength * fraction;
    for (let i = 0; i < lengths.length; i++) {
        if (remaining <= lengths[i] && lengths[i] > 0) {
            const ratio = remaining / lengths[i];
            return [
                line[i][0] + (line[i + 1][0] - line[i][0]) * ratio,
                line[i][1] + (line[i + 1][1] - line[i][1]) * ratio,
            ];
        }
        remaining -= lengths[i];
    }
    return line[line.length - 1];
}

function stopAnimation(): void {
    if (animationFrameId !== null) {
        cancelAnimationFrame(animationFrameId);
        animationFrameId = null;
    }
}

function start(): void {
    stopAnimation();
    currentMarkerCoordinates.value = null;

    const startTime = Date.now();
    const stationPoint = toLngLat(props.point.point);
    if (stationPoint) {
        // Stopping at a station: the marker stays there until the departure.
        if (startTime < props.point.departure * 1000) {
            currentMarkerCoordinates.value = stationPoint;
        }
        return;
    }

    const line = (props.point.polyline?.features ?? []).map(toLngLat).filter((c): c is LngLat => c !== null);
    const arrivalTime = props.point.arrival * 1000;
    if (line.length === 0 || startTime >= arrivalTime) {
        return;
    }

    const lengths = line.slice(1).map((coordinate, i) => segmentLength(line[i], coordinate));
    const totalLength = lengths.reduce((sum, length) => sum + length, 0);
    if (totalLength === 0) {
        currentMarkerCoordinates.value = line[0];
        return;
    }

    const animate = () => {
        const now = Date.now();
        if (now >= arrivalTime) {
            currentMarkerCoordinates.value = null;
            animationFrameId = null;
            return;
        }
        const fraction = (now - startTime) / (arrivalTime - startTime);
        currentMarkerCoordinates.value = positionAlong(line, lengths, totalLength, fraction);
        animationFrameId = requestAnimationFrame(animate);
    };
    animate();
}

onMounted(start);
watch(() => props.point, start);
onUnmounted(stopAnimation);
</script>

<template>
    <mgl-marker v-if="currentMarkerCoordinates" :coordinates="currentMarkerCoordinates">
        <template #marker>
            <img class="live-map-avatar" :src="point.status.user.profilePictureUrl" :alt="point.status.user.username" />
        </template>
        <mgl-popup ref="popup">
            <div class="live-popup">
                <a :href="`/@${point.status.user.username}`" class="live-popup-link"
                    >@{{ point.status.user.username }}</a
                >
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
