<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { chartColors } from './chartColors';

const props = defineProps<{
    title: string;
    series: number[];
    labels: string[];
    type?: 'donut' | 'pie';
}>();

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

const options = computed((): ApexCharts.ApexOptions => ({
    chart: {
        type: props.type ?? 'donut',
        background: 'transparent',
        animations: { enabled: false },
    },
    colors: chartColors(),
    labels: props.labels,
    theme: { mode: isDark() ? 'dark' : 'light' },
    legend: { position: 'bottom' },
    dataLabels: { enabled: false },
    tooltip: { y: { formatter: (val: number) => `${val} min` } },
}));
</script>

<template>
    <div class="card bg-base-100 h-full">
        <div class="card-body">
            <h2 class="card-title text-sm">{{ title }}</h2>
            <VueApexCharts :type="type ?? 'donut'" :series="series" :options="options" />
        </div>
    </div>
</template>
