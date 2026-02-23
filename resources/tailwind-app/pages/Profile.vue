<template>
    <div class="w-full space-y-6">
        <ContributionStats />
        <ContributionHistory />
    </div>
</template>

<script setup lang="ts">
import ContributionHistory from '../components/ContributionHistory.vue';
import ContributionStats from '../components/ContributionStats.vue';
</script>
