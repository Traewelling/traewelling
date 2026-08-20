<script setup lang="ts">
import ChartDoughnut from '../Stats/ChartDoughnut.vue';

defineProps<{
    label: string;
    categoriesLabel: string;
    operatorsLabel: string;
}>();

const categories = {
    labels: ['Regional', 'S-Bahn', 'ICE', 'Tram', 'Bus', 'Ferry'],
    series: [482, 314, 265, 98, 61, 21],
};

const operators = {
    labels: ['DB Regio', 'DB Fernverkehr', 'ÖBB', 'SNCF', 'VVS'],
    series: [396, 265, 142, 87, 54],
};
</script>

<template>
    <div inert role="img" :aria-label="label" class="grid grid-cols-1 sm:grid-cols-2 gap-4 select-none">
        <ChartDoughnut :title="categoriesLabel" :series="categories.series" :labels="categories.labels" />
        <ChartDoughnut type="pie" :title="operatorsLabel" :series="operators.series" :labels="operators.labels" />
    </div>
</template>
