<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { chartColors } from './chartColors';

const props = defineProps<{
    title: string;
    data: { date: string; duration: number }[];
}>();

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

const apexSeries = computed(() => [
    {
        name: props.title,
        data: props.data.map((d) => ({ x: new Date(d.date).getTime(), y: d.duration })),
    },
]);

const options = computed((): ApexCharts.ApexOptions => ({
    chart: {
        type: 'area',
        background: 'transparent',
        animations: { enabled: false },
        toolbar: { show: false },
        zoom: { enabled: false },
    },
    colors: [chartColors()[0]],
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
    xaxis: { type: 'datetime' },
    yaxis: { labels: { formatter: (val: number) => `${Math.round(val)} min` } },
    theme: { mode: isDark() ? 'dark' : 'light' },
    legend: { show: false },
    dataLabels: { enabled: false },
    tooltip: { x: { format: 'dd.MM.yyyy' } },
}));
</script>

<template>
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="card-title text-sm">{{ title }}</h2>
            <VueApexCharts type="area" height="200" :series="apexSeries" :options="options" />
        </div>
    </div>
</template>
