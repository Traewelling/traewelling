<script>
import { trans } from 'laravel-vue-i18n';
import Chart from 'chart.js/auto';

const transportKeyMap = {
    nationalExpress: 'nationalExpress',
    national: 'national',
    regionalExp: 'regionalExp',
    regional: 'regional',
    suburban: 'suburban',
    bus: 'bus',
    ferry: 'ferry',
    subway: 'subway',
    tram: 'tram',
    taxi: 'taxi',
    plane: 'plane',
};

export default {
    name: 'ChartCategories',
    props: {
        series: { type: Array, required: true },
        labels: { type: Array, required: true },
    },
    data() {
        return {
            chart: null,
        };
    },
    watch: {
        series: 'createChart',
        labels: 'createChart',
    },
    mounted() {
        this.createChart();
    },
    beforeUnmount() {
        if (this.chart) {
            this.chart.destroy();
        }
    },
    methods: {
        trans,
        createChart() {
            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }
            if (!this.series.length) return;

            const translatedLabels = this.labels.map(label => {
                const key = transportKeyMap[label];
                return key ? this.trans(`transport_types.${key}`) : label;
            });

            this.chart = new Chart(this.$refs.canvas, {
                type: 'doughnut',
                data: {
                    labels: translatedLabels,
                    datasets: [
                        {
                            data: this.series,
                            label: this.trans('time.minutes'),
                        },
                    ],
                },
                options: {
                    responsive: true,
                    animation: false,
                    plugins: { legend: { position: 'bottom' } },
                },
            });
        },
    },
};
</script>

<template>
    <div class="card">
        <div class="card-body">
            <h5>{{ trans('stats.categories') }}</h5>
            <canvas ref="canvas" role="img" :aria-label="trans('stats.categories') + ' ' + trans('time.minutes')" />
        </div>
    </div>
</template>
