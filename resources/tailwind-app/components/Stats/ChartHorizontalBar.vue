<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { chartColors } from './chartColors';

const props = defineProps<{
    title: string;
    series: number[];
    labels: string[];
}>();

function isDark(): boolean {
    return document.documentElement.classList.contains('dark');
}

const apexSeries = computed(() => [{ name: props.title, data: props.series }]);

const options = computed(
    (): ApexCharts.ApexOptions => ({
        chart: {
            type: 'bar',
            background: 'transparent',
            animations: { enabled: false },
            toolbar: { show: false },
        },
        colors: [chartColors()[0]],
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        xaxis: { categories: props.labels },
        theme: { mode: isDark() ? 'dark' : 'light' },
        legend: { show: false },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: (val: number) => `${val} min` } },
    }),
);
</script>

<template>
    <div class="card bg-base-100 h-full">
        <div class="card-body">
            <h2 class="card-title text-sm">{{ title }}</h2>
            <VueApexCharts type="bar" :series="apexSeries" :options="options" />
        </div>
    </div>
</template>
