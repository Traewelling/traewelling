<template>
    <AppLayout :legacy="true">
        <LegacyFrame :src="`/embed/status/${route.params.id}`" :min-height="600" />
    </AppLayout>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router';
import LegacyFrame from '../../components/LegacyFrame.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
</script>
