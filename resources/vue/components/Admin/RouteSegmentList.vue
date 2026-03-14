<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Api, type RouteSegmentResource } from '../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const props = defineProps<{
    fromStationId: number;
    toStationId: number;
    currentSegmentId: string;
}>();

const segments = ref<RouteSegmentResource[]>([]);
const error = ref<string | null>(null);
const deletingIds = ref<Set<string>>(new Set());

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

async function fetchSegments(): Promise<void> {
    error.value = null;
    try {
        const res = await api.routeSegments.listRouteSegments({
            from_station_id: props.fromStationId,
            to_station_id: props.toStationId,
        });
        segments.value = res.data?.data ?? [];
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    }
}

async function deleteSegment(id: string): Promise<void> {
    if (
        !confirm(
            'Delete this route segment? All stopovers using it will be reassigned to another matching segment if possible.',
        )
    ) {
        return;
    }

    deletingIds.value = new Set([...deletingIds.value, id]);
    try {
        await api.routeSegments.deleteRouteSegment(id);
        segments.value = segments.value.filter((s) => s.id !== id);
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        const next = new Set(deletingIds.value);
        next.delete(id);
        deletingIds.value = next;
    }
}

onMounted(fetchSegments);
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
                                <template v-if="segment.id !== currentSegmentId">
                                    <a :href="segmentUrl(segment.id)" class="btn btn-primary btn-sm me-1">Open</a>
                                    <button
                                        v-if="segments.length > 1"
                                        class="btn btn-danger btn-sm"
                                        :disabled="deletingIds.has(segment.id)"
                                        @click="deleteSegment(segment.id)"
                                    >
                                        <span
                                            v-if="deletingIds.has(segment.id)"
                                            class="spinner-border spinner-border-sm"
                                            role="status"
                                        ></span>
                                        <span v-else>Delete</span>
                                    </button>
                                </template>
                                <span v-else class="badge bg-secondary">Current</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </div>
    </div>
</template>
