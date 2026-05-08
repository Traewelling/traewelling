<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { Api, type RouteSegmentResource } from '../../../types/Api.gen';

const router = useRouter();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const props = defineProps<{ segment: RouteSegmentResource }>();

const assignStatus = ref<'idle' | 'loading' | 'success' | 'error'>('idle');

async function assignStopovers(): Promise<void> {
    assignStatus.value = 'loading';
    try {
        await api.routeSegments.assignRouteSegmentToStopovers(props.segment.id!);
        assignStatus.value = 'success';
    } catch {
        assignStatus.value = 'error';
    }
}

function formatDistance(meters: number | null | undefined): string {
    if (!meters) return '—';
    return (meters / 1000).toFixed(3) + ' km';
}

function formatDuration(seconds: number | null | undefined): string {
    if (!seconds) return '—';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return [h, m, s].map((v) => String(v).padStart(2, '0')).join(':');
}
</script>

<template>
    <div class="card bg-base-100 shadow mb-4">
        <div class="card-body gap-3">
            <h2 class="card-title text-base">Segment Details</h2>

            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                <dt class="text-base-content/50 font-medium">From</dt>
                <dd>
                    <a
                        v-if="segment.fromStation?.id"
                        class="link link-hover"
                        @click.prevent="router.push(`/admin/stations/${segment.fromStation.id}`)"
                        >{{ segment.fromStation.name }}</a
                    >
                    <span v-else>{{ segment.fromStation?.name ?? '—' }}</span>
                    <div v-if="segment.fromIdentifier" class="text-xs text-base-content/50 mt-0.5">
                        {{ segment.fromIdentifier.type }}:
                        <code class="font-mono">{{ segment.fromIdentifier.identifier }}</code>
                        <template
                            v-if="
                                segment.fromIdentifier.name && segment.fromIdentifier.name !== segment.fromStation?.name
                            "
                        >
                            · {{ segment.fromIdentifier.name }}
                        </template>
                    </div>
                    <div v-else class="text-xs text-base-content/40 mt-0.5">station-based only</div>
                </dd>

                <dt class="text-base-content/50 font-medium">To</dt>
                <dd>
                    <a
                        v-if="segment.toStation?.id"
                        class="link link-hover"
                        @click.prevent="router.push(`/admin/stations/${segment.toStation.id}`)"
                        >{{ segment.toStation.name }}</a
                    >
                    <span v-else>{{ segment.toStation?.name ?? '—' }}</span>
                    <div v-if="segment.toIdentifier" class="text-xs text-base-content/50 mt-0.5">
                        {{ segment.toIdentifier.type }}:
                        <code class="font-mono">{{ segment.toIdentifier.identifier }}</code>
                        <template
                            v-if="segment.toIdentifier.name && segment.toIdentifier.name !== segment.toStation?.name"
                        >
                            · {{ segment.toIdentifier.name }}
                        </template>
                    </div>
                    <div v-else class="text-xs text-base-content/40 mt-0.5">station-based only</div>
                </dd>

                <dt class="text-base-content/50 font-medium">Distance</dt>
                <dd class="tabular-nums">{{ formatDistance(segment.distance) }}</dd>

                <dt class="text-base-content/50 font-medium">Duration</dt>
                <dd class="font-mono tabular-nums">{{ formatDuration(segment.duration) }}</dd>

                <dt class="text-base-content/50 font-medium">Path Type</dt>
                <dd>{{ segment.pathType ?? '—' }}</dd>

                <dt class="text-base-content/50 font-medium">Polyline</dt>
                <dd>
                    <input
                        class="input input-xs input-bordered w-full font-mono text-xs"
                        :value="segment.polyline"
                        readonly
                    />
                </dd>

                <dt class="text-base-content/50 font-medium">Precision</dt>
                <dd>{{ segment.polylinePrecision }}</dd>

                <template v-if="segment.customWaypointsCount !== null && segment.customWaypointsCount !== undefined">
                    <dt class="text-base-content/50 font-medium">Waypoints</dt>
                    <dd>{{ segment.customWaypointsCount }} points</dd>
                </template>

                <dt class="text-base-content/50 font-medium">Trips</dt>
                <dd>{{ segment.tripsCount ?? '…' }}</dd>
            </dl>

            <div class="card-actions justify-start pt-1">
                <button
                    class="btn btn-sm"
                    :class="{
                        'btn-neutral': assignStatus === 'idle',
                        'btn-warning': assignStatus === 'loading',
                        'btn-success': assignStatus === 'success',
                        'btn-error': assignStatus === 'error',
                    }"
                    :disabled="assignStatus === 'loading' || assignStatus === 'success'"
                    @click="assignStopovers"
                >
                    <span v-if="assignStatus === 'loading'" class="loading loading-spinner loading-xs" />
                    <span v-if="assignStatus === 'loading'">Dispatching…</span>
                    <span v-else-if="assignStatus === 'success'">Job dispatched ✓</span>
                    <span v-else-if="assignStatus === 'error'">Error — retry?</span>
                    <span v-else>Re-Assign Stopovers</span>
                </button>
            </div>
        </div>
    </div>
</template>
