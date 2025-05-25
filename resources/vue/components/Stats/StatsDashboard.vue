<template>
  <div class="stats-dashboard">
    <h4>{{ trans('stats.personal', {fromDate: formatDate(from), toDate: formatDate(to)}) }}</h4>
    <hr/>
    <div class="row mb-3">
      <div class="col-md-3">
        <label for="fromDate" class="sr-only">{{ trans('stats.from') }}</label>
        <DatePicker
            id="fromDate"
            v-model="from"
            format="yyyy-MM-dd"
            :input-props="{ class: 'form-control', 'aria-label': trans('stats.from') }"
        />
      </div>
      <div class="col-md-3">
        <label for="toDate" class="sr-only">{{ trans('stats.to') }}</label>
        <DatePicker
            id="toDate"
            v-model="to"
            format="yyyy-MM-dd"
            :input-props="{ class: 'form-control', 'aria-label': trans('stats.to') }"
        />
      </div>
    </div>
    <div class="row">
      <div class="col-md-4 mb-4">
        <ChartPurpose
            v-if="data.purpose.length"
            :key="'purpose-' + fromStr + '-' + toStr"
            :series="data.purpose.map(p => p.duration)"
            :labels="data.purpose.map(p => p.name)"
        />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
      <div class="col-md-4 mb-4">
        <ChartCategories
            v-if="data.categories.length"
            :key="'categories-' + fromStr + '-' + toStr"
            :series="data.categories.map(c => c.duration)"
            :labels="data.categories.map(c => c.name)"
        />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
      <div class="col-md-4 mb-4">
        <ChartCompanies
            v-if="data.operators.length"
            :key="'companies-' + fromStr + '-' + toStr"
            :series="data.operators.map(o => o.duration)"
            :labels="data.operators.map(o => o.name || trans('other'))"
        />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
      <div class="col-12 mb-4">
        <ChartVolume
            v-if="data.time.length"
            :key="'volume-' + fromStr + '-' + toStr"
            :data="data.time"
        />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
    </div>
    <div class="row mb-4">
      <GlobalCards
          v-if="globalStats.distance !== undefined"
          :stats="globalStats"
          :from="fromStr"
          :to="toStr"
      />
    </div>
  </div>
</template>

<script setup>
import {computed, onMounted, ref, watch} from 'vue';
import {trans} from 'laravel-vue-i18n';
import DatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

import {Notyf} from 'notyf';
import 'notyf/notyf.min.css';

import ChartPurpose from './ChartPurpose.vue';
import ChartCategories from './ChartCategories.vue';
import ChartCompanies from './ChartCompanies.vue';
import ChartVolume from './ChartVolume.vue';
import GlobalCards from './GlobalCards.vue';

const notyf = new Notyf();

// Default date range: last 3 months to today (as Date objects)
const today = new Date();
const to = ref(today);
const threeMonthsAgo = new Date();
threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
const from = ref(threeMonthsAgo);

const fromStr = computed(() => from.value.toISOString().split('T')[0]);
const toStr = computed(() => to.value.toISOString().split('T')[0]);

const data = ref({purpose: [], categories: [], operators: [], time: []});
const globalStats = ref({});

async function fetchStats() {
  const url = new URL('/api/v1/statistics', window.location.origin);
  url.searchParams.set('from', fromStr.value);
  url.searchParams.set('until', toStr.value);
  const res = await fetch(url.toString(), {headers: {Accept: 'application/json'}});
  if (!res.ok) throw new Error('Failed to fetch statistics');
  const json = await res.json();
  data.value = json.data;
}

async function fetchGlobalStats() {
  const url = new URL('/api/v1/statistics/global', window.location.origin);
  url.searchParams.set('from', fromStr.value);
  url.searchParams.set('until', toStr.value);
  const res = await fetch(url.toString(), {headers: {Accept: 'application/json'}});
  if (!res.ok) throw new Error('Failed to fetch global statistics');
  const json = await res.json();
  globalStats.value = {
    distance: Number(json.data.distance),
    duration: Number(json.data.duration),
    user_count: json.data.activeUsers
  };
}

async function reloadStats() {
  try {
    await Promise.all([fetchStats(), fetchGlobalStats()]);
  } catch (error) {
    notyf.error(error.message);
  }
}

function formatDate(d) {
  return d.toLocaleDateString();
}

watch([from, to], reloadStats);

onMounted(() => {
  reloadStats();
});
</script>
