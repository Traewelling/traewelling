<template>
  <div class="stats-dashboard">
    <div class="row mb-3">
      <div class="col-md-3">
        <label for="fromDate" class="sr-only">{{ trans('stats.from') }}</label>
        <input
            id="fromDate"
            type="date"
            v-model="from"
            class="form-control"
            :aria-label="trans('stats.from')"
        />
      </div>
      <div class="col-md-3">
        <label for="toDate" class="sr-only">{{ trans('stats.to') }}</label>
        <input
            id="toDate"
            type="date"
            v-model="to"
            class="form-control"
            :aria-label="trans('stats.to')"
        />
      </div>
    </div>

    <h4>{{ trans('stats.personal', { fromDate: formatDate(from), toDate: formatDate(to) }) }}</h4>
    <hr />
    <div class="row">
      <div class="col-md-4 mb-4">
        <ChartPurpose
            v-if="data.purpose.length"
            :series="data.purpose.map(p => p.duration)"
            :labels="data.purpose.map(p => p.name)"
        />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
      <div class="col-md-4 mb-4">
        <ChartCategories
            v-if="data.categories.length"
            :series="data.categories.map(c => c.duration)"
            :labels="data.categories.map(c => c.name)"
        />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
      <div class="col-md-4 mb-4">
        <ChartCompanies
            v-if="data.operators.length"
            :series="data.operators.map(o => o.duration)"
            :labels="data.operators.map(o => o.name || trans('other'))"
        />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
      <div class="col-12 mb-4">
        <ChartVolume v-if="data.time.length" :data="data.time" />
        <p v-else class="text-danger font-weight-bold mt-2">{{ trans('stats.no-data') }}</p>
      </div>
    </div>
    <div class="row mb-4">
      <GlobalCards
          v-if="globalStats.distance !== undefined"
          :stats="globalStats"
          :from="from"
          :to="to"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { trans } from 'laravel-vue-i18n';
import ChartPurpose from './ChartPurpose.vue';
import ChartCategories from './ChartCategories.vue';
import ChartCompanies from './ChartCompanies.vue';
import ChartVolume from './ChartVolume.vue';
import GlobalCards from './GlobalCards.vue';

// Default date range: last 3 months to today
const today = new Date();
const to = ref(today.toISOString().split('T')[0]);
const threeMonthsAgo = new Date();
threeMonthsAgo.setMonth(threeMonthsAgo.getMonth() - 3);
const from = ref(threeMonthsAgo.toISOString().split('T')[0]);

const data = ref({ purpose: [], categories: [], operators: [], time: [] });
const globalStats = ref({});

async function fetchStats() {
  const url = new URL('/api/v1/statistics', window.location.origin);
  url.searchParams.set('from', from.value);
  url.searchParams.set('until', to.value);
  const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
  if (!res.ok) throw new Error('Failed to fetch statistics');
  const json = await res.json();
  data.value = json.data;
}

async function fetchGlobalStats() {
  const url = new URL('/api/v1/statistics/global', window.location.origin);
  url.searchParams.set('from', from.value);
  url.searchParams.set('until', to.value);
  const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
  if (!res.ok) throw new Error('Failed to fetch global statistics');
  const json = await res.json();
  globalStats.value = {
    distance: Number(json.data.distance),
    duration: Number(json.data.duration),
    user_count: json.data.activeUsers
  };
}

async function reloadStats() {
  await Promise.all([fetchStats(), fetchGlobalStats()]);
}

function formatDate(d) {
  return new Date(d).toLocaleDateString();
}

watch([from, to], reloadStats);

onMounted(() => {
  reloadStats();
});
</script>
