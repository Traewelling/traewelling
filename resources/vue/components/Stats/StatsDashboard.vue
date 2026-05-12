<script setup lang="ts">
import '@vuepic/vue-datepicker/dist/main.css';
import { trans } from 'laravel-vue-i18n';
import { computed, onMounted, ref, watch } from 'vue';

import { Notyf } from 'notyf';
import 'notyf/notyf.min.css';

import Chart from 'chart.js/auto';
import AdvancedStats from './AdvancedStats.vue';
import ChartCategories from './ChartCategories.vue';
import ChartCompanies from './ChartCompanies.vue';
import ChartPurpose from './ChartPurpose.vue';
import ChartVolume from './ChartVolume.vue';
import GlobalCards from './GlobalCards.vue';

const notyf = new Notyf();

// Default date range: last 3 months to today (as Date objects)
const until = ref(new Date() as Date);
const from = ref(new Date(new Date().setMonth(new Date().getMonth() - 3)) as Date);

const globalFrom = ref(null as Date | null);
const globalUntil = ref(null as Date | null);

function getDateString(date: Date) {
    return date.toISOString().split('T')[0];
}

const fromStr = computed(() => getDateString(from.value));
const untilStr = computed(() => getDateString(until.value));

function getQueryParameters() {
    const urlParams = new URLSearchParams(window.location.search);
    const fromUrlString = urlParams.get('from');
    if (fromUrlString) {
        const fromDate = new Date(fromUrlString);
        if (!isNaN(fromDate.getTime())) {
            from.value = fromDate;
        }
    }

    const untilUrlString = urlParams.get('until');
    if (untilUrlString) {
        const untilDate = new Date(untilUrlString);
        if (!isNaN(untilDate.getTime())) {
            until.value = untilDate;
        }
    }
}

getQueryParameters();

const data = ref({ purpose: [], categories: [], operators: [], time: [] });
const globalStats = ref({});

async function fetchStats() {
    const url = new URL('/api/v1/statistics', window.location.origin);
    url.searchParams.set('from', fromStr.value);
    url.searchParams.set('until', untilStr.value);
    const res = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('Failed to fetch statistics');
    const json = await res.json();
    data.value = json.data;
}

async function fetchGlobalStats() {
    const res = await fetch('/api/v1/statistics/global', { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error('Failed to fetch global statistics');
    const json = await res.json();
    globalStats.value = {
        distance: Number(json.data.distance),
        duration: Number(json.data.duration),
        user_count: json.data.activeUsers,
    };
    globalFrom.value = new Date(json.meta.from);
    globalUntil.value = new Date(json.meta.until);
}

async function reloadStats() {
    // push the current date range to the URL
    const url = new URL(window.location.href);
    url.searchParams.set('from', fromStr.value);
    url.searchParams.set('until', untilStr.value);
    window.history.pushState({}, '', url.toString());

    try {
        await Promise.all([fetchStats()]);
    } catch (error) {
        notyf.error(error.message);
    }
}

function formatDate(d: Date) {
    return d.toLocaleDateString();
}

function getDarkMode() {
    const dark = localStorage.getItem('darkMode');
    if (dark && dark !== 'auto') {
        return dark === 'dark';
    }

    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function setColors() {
    if (getDarkMode()) {
        Chart.defaults.color = 'rgb(185, 188, 198)';
    } else {
        Chart.defaults.color = 'rgb(0, 0, 0)';
    }
}

watch([from, until], reloadStats);
window.addEventListener('popstate', () => {
    getQueryParameters();
});

onMounted(() => {
    reloadStats();
    fetchGlobalStats();
    setColors();
});
</script>

<template>
    <div class="stats-dashboard">
        <h4>{{ trans('stats.personal', { fromDate: formatDate(from), toDate: formatDate(until) }) }}</h4>
        <hr />
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="fromDate" class="sr-only">{{ trans('stats.from') }}</label>
                <input
                    id="fromDate"
                    type="date"
                    class="form-control"
                    :value="from.toISOString().split('T')[0]"
                    @change="from = new Date($event.target.value)"
                />
            </div>
            <div class="col-md-3">
                <label for="untilDate" class="sr-only">{{ trans('stats.to') }}</label>
                <input
                    id="untilDate"
                    class="form-control"
                    :value="until.toISOString().split('T')[0]"
                    type="date"
                    @change="until = new Date($event.target.value)"
                />
            </div>
        </div>
        <!-- Advanced Statistics Section -->
        <AdvancedStats :data="data.summary ? data : null" />

        <hr />

        <div class="row">
            <div class="col-md-4 mb-4">
                <ChartPurpose
                    v-if="data.purpose.length"
                    :key="'purpose-' + fromStr + '-' + untilStr"
                    :series="data.purpose.map((p) => p.duration)"
                    :labels="data.purpose.map((p) => p.name)"
                />
                <p v-else class="text-danger font-weight-bold mt-2">
                    {{ trans('stats.no-data') }}
                </p>
            </div>
            <div class="col-md-4 mb-4">
                <ChartCategories
                    v-if="data.categories.length"
                    :key="'categories-' + fromStr + '-' + untilStr"
                    :series="data.categories.map((c) => c.duration)"
                    :labels="data.categories.map((c) => c.name)"
                />
                <p v-else class="text-danger font-weight-bold mt-2">
                    {{ trans('stats.no-data') }}
                </p>
            </div>
            <div class="col-md-4 mb-4">
                <ChartCompanies
                    v-if="data.operators.length"
                    :key="'companies-' + fromStr + '-' + untilStr"
                    :series="data.operators.map((o) => o.duration)"
                    :labels="data.operators.map((o) => o.name || trans('other'))"
                />
                <p v-else class="text-danger font-weight-bold mt-2">
                    {{ trans('stats.no-data') }}
                </p>
            </div>
            <div class="col-12 mb-4">
                <ChartVolume v-if="data.time.length" :key="'volume-' + fromStr + '-' + untilStr" :data="data.time" />
                <p v-else class="text-danger font-weight-bold mt-2">
                    {{ trans('stats.no-data') }}
                </p>
            </div>
        </div>
        <div class="row mb-4">
            <GlobalCards
                v-if="globalStats.distance !== undefined"
                :stats="globalStats"
                :from="globalFrom"
                :until="globalUntil"
            />
        </div>
    </div>
</template>
