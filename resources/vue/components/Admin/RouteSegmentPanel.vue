<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Api, type RouteSegmentResource } from '../../../types/Api.gen';
import RouteSegmentDetails from './RouteSegmentDetails.vue';
import RouteSegmentEditor from './RouteSegmentEditor.vue';
import RouteSegmentList from './RouteSegmentList.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const props = defineProps<{ segmentId: string }>();

const segment = ref<RouteSegmentResource | null>(null);
const error = ref<string | null>(null);

async function load(): Promise<void> {
    try {
        const res = await api.routeSegments.getRouteSegment(props.segmentId);
        segment.value = res.data.data ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    }
}

function onSaved(polyline: string, distance: number): void {
    if (!segment.value) return;
    segment.value = { ...segment.value, polyline, distance };
}

onMounted(load);
</script>

<template>
    <div v-if="error" role="alert" class="alert alert-error">{{ error }}</div>

    <div v-else-if="!segment" class="flex justify-center py-16">
        <span class="loading loading-spinner loading-lg" />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-[1fr_2fr] gap-4">
        <!-- Left: details + other segments + trip count -->
        <div class="space-y-4 min-w-0">
            <RouteSegmentDetails :segment="segment" />
            <RouteSegmentList
                :from-station-id="segment.fromStation!.id"
                :to-station-id="segment.toStation!.id"
                :current-segment-id="segment.id!"
            />
        </div>

        <!-- Right: map editor -->
        <div class="min-w-0">
            <RouteSegmentEditor :segment="segment" @saved="onSaved" />
        </div>
    </div>
</template>
