<script>
import { trans } from 'laravel-vue-i18n';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';
import { nextTick } from 'vue';

Chart.defaults.animation = false;

export default {
    name: 'ChartVolume',
    props: {
        data: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            chart: null,
        };
    },
    watch: {
        data: 'createChart',
    },
    mounted() {
        this.createChart();
    },
    beforeUnmount() {
        if (this.chart) this.chart.destroy();
    },
    methods: {
        trans,
        createChart() {
            nextTick(() => {
                if (this.chart) {
                    this.chart.destroy();
                    this.chart = null;
                }
                if (!this.data.length) return;
                this.chart = new Chart(this.$refs.canvas, {
                    type: 'line',
                    data: {
                        labels: this.data.map(d => new Date(d.date)),
                        datasets: [
                            {
                                data: this.data.map(d => d.duration),
                                label: this.trans('time.minutes'),
                                fill: false,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        animation: false,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                type: 'time',
                                time: { unit: 'day' },
                            },
                        },
                    },
                });
            });
        },
    },
};
</script>

<template>
    <div class="card">
        <div class="card-body">
            <h5>{{ trans('stats.time') }}</h5>
            <div style="height: 250px">
                <canvas ref="canvas" role="img" :aria-label="trans('stats.time') + ' ' + trans('time.minutes')" />
            </div>
        </div>
    </div>
</template>
