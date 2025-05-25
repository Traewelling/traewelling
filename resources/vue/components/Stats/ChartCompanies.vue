<template>
  <div class="card">
    <div class="card-body">
      <h5>{{ trans('stats.companies') }}</h5>
      <canvas
          ref="canvas"
          role="img"
          :aria-label="trans('stats.companies') + ' ' + trans('time.minutes')"
      ></canvas>
    </div>
  </div>
</template>

<script>
import { trans } from 'laravel-vue-i18n'
import Chart from 'chart.js/auto'

export default {
  name: 'ChartCompanies',
  props: {
    series: { type: Array, required: true },
    labels: { type: Array, required: true }
  },
  data() {
    return {
      chart: null
    }
  },
  methods: {
    trans,
    createChart() {
      if (this.chart) {
        this.chart.destroy()
        this.chart = null
      }
      if (!this.series.length) return
      this.chart = new Chart(this.$refs.canvas, {
        type: 'bar',
        data: {
          labels: this.labels,
          datasets: [{ data: this.series, label: this.trans('time.minutes') }]
        },
        options: {
          responsive: true,
          animation: false,
          indexAxis: 'y',
          plugins: { legend: { display: false } }
        }
      })
    }
  },
  mounted() {
    this.createChart()
  },
  watch: {
    series: 'createChart',
    labels: 'createChart'
  },
  beforeUnmount() {
    if (this.chart) this.chart.destroy()
  }
}
</script>
