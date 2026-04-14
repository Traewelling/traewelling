<template>
    <AppLayout :legacy="true" :fullscreen="true">
        <LegacyFrame :src="`/embed/event/${route.params.slug}`" :min-height="600" />
    </AppLayout>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router';
import LegacyFrame from '../../components/LegacyFrame.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
</script>
