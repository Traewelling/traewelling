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
const loading = ref(true);
const error = ref<string | null>(null);
const deletingIds = ref<Set<string>>(new Set());

function formatDuration(seconds: number | null): string {
    if (seconds === null) return '—';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return [h, m, s].map((v) => String(v).padStart(2, '0')).join(':');
}

function formatDistance(meters: number | null): string {
    if (meters === null) return '—';
    return (meters / 1000).toFixed(3) + ' km';
}

function segmentUrl(id: string): string {
    return `/admin/routesegment/${id}`;
}

async function fetchSegments(): Promise<void> {
    error.value = null;
    loading.value = true;
    try {
        const res = await api.routeSegments.listRouteSegments({
            from_station_id: props.fromStationId,
            to_station_id: props.toStationId,
        });
        segments.value = res.data?.data ?? [];
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
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
    <div class="card bg-base-100 shadow mb-4">
        <div class="card-body gap-3">
            <h2 class="card-title text-base">Other Segments for this Pair</h2>

            <div v-if="error" role="alert" class="alert alert-error text-sm">{{ error }}</div>

            <div v-else-if="loading" class="flex justify-center py-4">
                <span class="loading loading-spinner loading-sm" />
            </div>

            <div v-else-if="!segments.length" class="text-sm text-base-content/50 text-center py-2">
                No segments found.
            </div>

            <div v-else class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Duration</th>
                            <th>Distance</th>
                            <th>Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="segment in segments"
                            :key="segment.id"
                            :class="{ 'bg-base-200 font-semibold': segment.id === currentSegmentId }"
                        >
                            <td class="font-mono tabular-nums text-xs">{{ formatDuration(segment.duration) }}</td>
                            <td class="tabular-nums text-xs">{{ formatDistance(segment.distance) }}</td>
                            <td class="text-xs">{{ segment.pathType ?? '—' }}</td>
                            <td class="text-right">
                                <template v-if="segment.id !== currentSegmentId">
                                    <a :href="segmentUrl(segment.id!)" class="btn btn-xs btn-ghost">Open</a>
                                    <button
                                        v-if="segments.length > 1"
                                        class="btn btn-xs btn-error btn-outline"
                                        :disabled="deletingIds.has(segment.id!)"
                                        @click="deleteSegment(segment.id!)"
                                    >
                                        <span
                                            v-if="deletingIds.has(segment.id!)"
                                            class="loading loading-spinner loading-xs"
                                        />
                                        <span v-else>Delete</span>
                                    </button>
                                </template>
                                <span v-else class="badge badge-neutral badge-sm">Current</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
