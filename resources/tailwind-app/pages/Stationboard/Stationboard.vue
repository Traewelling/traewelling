<template>
    <AppLayout :legacy="true">
        <LegacyFrame :src="embedUrl" :min-height="600" />
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import LegacyFrame from '../../components/LegacyFrame.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();

const embedUrl = computed(() => {
    const params = new URLSearchParams(route.query as Record<string, string>);
    const queryString = params.toString();
    return `/embed/stationboard${queryString ? '?' + queryString : ''}`;
});
</script>
