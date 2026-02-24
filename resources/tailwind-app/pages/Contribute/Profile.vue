<template>
    <ContributeLayout>
        <div class="w-full space-y-6">
            <ContributionStats />
            <ContributionHistory />
        </div>
    </ContributeLayout>
</template>

<script setup lang="ts">
import ContributionHistory from '../../components/ContributionHistory.vue';
import ContributionStats from '../../components/ContributionStats.vue';
import ContributeLayout from '../../layouts/ContributeLayout.vue';
</script>
