<script setup lang="ts">
import { onMounted, ref } from 'vue';
import type { RouteSegmentResource } from '../../../types/Api.gen';

const props = defineProps<{
    fromStationId: number;
    toStationId: number;
    currentSegmentId: string;
}>();

const segments = ref<RouteSegmentResource[]>([]);
const error = ref<string | null>(null);

function formatDuration(seconds: number | null): string {
    if (seconds === null) return '';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return [h, m, s].map((v) => String(v).padStart(2, '0')).join(':');
}

function formatDistance(meters: number | null): string {
    if (meters === null) return '';
    return (meters / 1000).toFixed(3) + ' km';
}

function segmentUrl(id: string): string {
    return `/admin/routesegment/${id}`;
}

onMounted(async () => {
    try {
        const params = new URLSearchParams({
            from_station_id: String(props.fromStationId),
            to_station_id: String(props.toStationId),
        });
        const res = await fetch(`/api/v1/route-segments?${params}`, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) {
            error.value = `Error ${res.status}: ${res.statusText}`;
            return;
        }
        const json = await res.json();
        segments.value = json.data;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    }
});
</script>

<template>
    <div class="card">
        <div class="card-body">
            <div v-if="error" class="alert alert-danger">{{ error }}</div>

            <div v-else-if="!segments.length" class="text-center text-muted py-2 small">
                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                Loading…
            </div>

            <template v-else>
                <h6>Other segments for this station pair</h6>
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Duration</th>
                            <th>Distance</th>
                            <th>Path Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="segment in segments"
                            :key="segment.id"
                            :class="{ 'table-active fw-bold': segment.id === currentSegmentId }"
                        >
                            <td>{{ formatDuration(segment.duration) }}</td>
                            <td>{{ formatDistance(segment.distance) }}</td>
                            <td>{{ segment.pathType ?? '' }}</td>
                            <td>
                                <a
                                    v-if="segment.id !== currentSegmentId"
                                    :href="segmentUrl(segment.id)"
                                    class="btn btn-primary btn-sm"
                                    >Open</a
                                >
                                <span v-else class="badge bg-secondary">Current</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </div>
    </div>
</template>
