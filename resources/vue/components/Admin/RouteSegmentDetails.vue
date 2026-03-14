<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { RouteSegmentResource } from '../../../types/Api.gen';

const props = defineProps<{ segmentId: string }>();

const segment = ref<RouteSegmentResource | null>(null);
const error = ref<string | null>(null);
const assignStatus = ref<'idle' | 'loading' | 'success' | 'error'>('idle');

async function assignStopovers() {
    assignStatus.value = 'loading';
    try {
        const res = await fetch(`/api/v1/route-segments/${props.segmentId}/assign-stopovers`, {
            method: 'POST',
            headers: { Accept: 'application/json' },
        });
        assignStatus.value = res.status === 202 ? 'success' : 'error';
    } catch {
        assignStatus.value = 'error';
    }
}

function formatDistance(meters: number | null): string {
    if (meters === null) return '';
    return (meters / 1000).toFixed(3) + ' km';
}

function formatDuration(seconds: number | null): string {
    if (seconds === null) return '';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return [h, m, s].map((v) => String(v).padStart(2, '0')).join(':');
}

onMounted(async () => {
    try {
        const res = await fetch(`/api/v1/route-segments/${props.segmentId}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            error.value = `Error ${res.status}: ${res.statusText}`;
            return;
        }
        const json = await res.json();
        segment.value = json.data;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    }
});
</script>

<template>
    <div class="card mb-3">
        <div class="card-body">
            <div v-if="error" class="alert alert-danger">{{ error }}</div>

            <div v-else-if="!segment" class="text-center text-muted py-3">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Loading…
            </div>

            <table v-else class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th colspan="2">Segment Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>From Station</td>
                        <td>{{ segment.fromStation?.name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>To Station</td>
                        <td>{{ segment.toStation?.name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Distance</td>
                        <td>{{ formatDistance(segment.distance) }}</td>
                    </tr>
                    <tr>
                        <td>Duration</td>
                        <td>{{ formatDuration(segment.duration) }}</td>
                    </tr>
                    <tr>
                        <td>Polyline</td>
                        <td><input class="input w-100" :value="segment.polyline" disabled /></td>
                    </tr>
                    <tr>
                        <td>Polyline Precision</td>
                        <td>{{ segment.polylinePrecision }}</td>
                    </tr>
                    <tr>
                        <td>Path Type</td>
                        <td>{{ segment.pathType ?? '' }}</td>
                    </tr>
                    <tr v-if="segment.customWaypointsCount !== null">
                        <td>Custom Waypoints</td>
                        <td>{{ segment.customWaypointsCount }} points</td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-2">
                <button
                    class="btn btn-sm"
                    :class="{
                        'btn-secondary': assignStatus === 'idle',
                        'btn-warning': assignStatus === 'loading',
                        'btn-success': assignStatus === 'success',
                        'btn-danger': assignStatus === 'error',
                    }"
                    :disabled="assignStatus === 'loading' || assignStatus === 'success'"
                    @click="assignStopovers"
                >
                    <span v-if="assignStatus === 'loading'">Dispatching...</span>
                    <span v-else-if="assignStatus === 'success'">Job dispatched ✓</span>
                    <span v-else-if="assignStatus === 'error'">Error | retry?</span>
                    <span v-else>Re-Assign Stopovers</span>
                </button>
            </div>
        </div>
    </div>
</template>
