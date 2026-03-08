<script setup lang="ts">
import RouteSegmentDetails from './RouteSegmentDetails.vue';
import RouteSegmentList from './RouteSegmentList.vue';

defineProps<{
    segmentId: string;
    fromStationId: number;
    toStationId: number;
}>();
</script>

<template>
    <RouteSegmentDetails :segment-id="segmentId" />
    <RouteSegmentList :from-station-id="fromStationId" :to-station-id="toStationId" :current-segment-id="segmentId" />
</template>
