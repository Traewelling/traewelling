<template>
    <AppLayout :legacy="true">
        <LegacyFrame src="/embed/export" :min-height="600" />
    </AppLayout>
</template>

<script setup lang="ts">
import LegacyFrame from '../../components/LegacyFrame.vue';
import AppLayout from '../../layouts/AppLayout.vue';
</script>
