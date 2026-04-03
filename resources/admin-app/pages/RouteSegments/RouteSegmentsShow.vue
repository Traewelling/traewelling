<script setup lang="ts">
import { useRoute } from 'vue-router';
import RouteSegmentPanel from '../../../vue/components/Admin/RouteSegmentPanel.vue';
import BackendLayout from '../../layouts/BackendLayout.vue';

const route = useRoute();
const segmentId = route.params.id as string;
</script>

<template>
    <BackendLayout>
        <RouteSegmentPanel :segment-id="segmentId" />
    </BackendLayout>
</template>
