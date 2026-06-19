<script setup lang="ts">
import { ChartNoAxesCombined } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, onMounted, ref, watch } from 'vue';
import { Api, type StatisticsGlobalData, type StatusResource } from '../../../types/Api.gen';
import AdvancedStats from '../../components/Stats/AdvancedStats.vue';
import ChartDoughnut from '../../components/Stats/ChartDoughnut.vue';
import ChartHorizontalBar from '../../components/Stats/ChartHorizontalBar.vue';
import ChartTimeline from '../../components/Stats/ChartTimeline.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const notyf = inject('notyf') as Notyf;

type StatsData = {
    purpose: { name: string; duration: number; count: number }[];
    categories: { name: string; duration: number; count: number }[];
    operators: { name: string; duration: number; count: number }[];
    time: { date: string; duration: number; count: number }[];
};

type PeriodData = { period: string; period_type: string; checkin_count: number; distance_km: number };

type AdvancedData = {
    summary: object | null;
    by_period: { yearly: PeriodData[]; monthly: PeriodData[]; weekly: PeriodData[] };
    favorites: object | null;
};

type PeriodSummary = {
    labelKey: string;
    from: string;
    until: string;
    total_checkins: number;
    total_distance_km: number;
    mean_distance_km: number;
    longest_ride: StatusResource | null;
    shortest_ride: StatusResource | null;
};

const loading = ref(true);
const data = ref<StatsData>({ purpose: [], categories: [], operators: [], time: [] });
const advancedData = ref<AdvancedData>({
    summary: null,
    by_period: { yearly: [], monthly: [], weekly: [] },
    favorites: null,
});
const periods = ref<PeriodSummary[] | null>(null);
const globalStats = ref<StatisticsGlobalData | null>(null);
const globalFrom = ref<Date | null>(null);
const globalUntil = ref<Date | null>(null);

function toDateString(d: Date): string {
    return d.toISOString().split('T')[0];
}

const until = ref(new Date());
const from = ref(new Date(new Date().setMonth(new Date().getMonth() - 3)));

const fromStr = computed(() => toDateString(from.value));
const untilStr = computed(() => toDateString(until.value));

const businessKeyMap: Record<string, string> = {
    '0': 'stationboard.business.private',
    '1': 'stationboard.business.business',
    '2': 'stationboard.business.commute',
};

const transportKeyMap: Record<string, string> = {
    nationalExpress: 'transport_types.nationalExpress',
    national: 'transport_types.national',
    regionalExp: 'transport_types.regionalExp',
    regional: 'transport_types.regional',
    suburban: 'transport_types.suburban',
    bus: 'transport_types.bus',
    ferry: 'transport_types.ferry',
    subway: 'transport_types.subway',
    tram: 'transport_types.tram',
    taxi: 'transport_types.taxi',
    plane: 'transport_types.plane',
    freightTrain: 'transport_types.freightTrain',
};

const purposeLabels = computed(() =>
    data.value.purpose.map((p) => trans(businessKeyMap[String(p.name)] ?? String(p.name))),
);
const categoryLabels = computed(() =>
    data.value.categories.map((c) => trans(transportKeyMap[c.name] ?? c.name, {}, c.name)),
);

const presets: { label: string; months?: number; days?: number }[] = [
    { label: '7d', days: 7 },
    { label: '30d', days: 30 },
    { label: '90d', days: 90 },
    { label: '1y', days: 365 },
];

function applyPreset(days: number): void {
    until.value = new Date();
    from.value = new Date(Date.now() - days * 86400000);
}

function isPresetActive(days: number): boolean {
    const diff = Math.round((until.value.getTime() - from.value.getTime()) / 86400000);
    return diff === days;
}

async function fetchStats(): Promise<void> {
    loading.value = true;
    try {
        const dateParams = { from: fromStr.value, until: untilStr.value };
        const [mainRes, summaryRes, favoritesRes] = await Promise.all([
            api.statistics.getStatistics(dateParams),
            api.statistics.getStatisticsOverview(dateParams),
            api.statistics.getStatisticsFavorites(dateParams),
        ]);

        const d = mainRes.data.data;
        data.value = {
            purpose: (d?.purpose ?? []).map((p) => ({
                name: String(p.name ?? ''),
                duration: p.duration ?? 0,
                count: p.count ?? 0,
            })),
            categories: (d?.categories ?? []).map((c) => ({
                name: String(c.name ?? ''),
                duration: c.duration ?? 0,
                count: c.count ?? 0,
            })),
            operators: (d?.operators ?? []).map((o) => ({
                name: String(o.name ?? ''),
                duration: o.duration ?? 0,
                count: o.count ?? 0,
            })),
            time: (d?.time ?? []).map((t) => ({
                date: String(t.date ?? ''),
                duration: t.duration ?? 0,
                count: t.count ?? 0,
            })),
        };

        const s = summaryRes.data.data;
        advancedData.value.summary = s?.summary ?? null;

        advancedData.value.favorites = favoritesRes.data.data ?? null;
    } catch (e: unknown) {
        notyf.error(e instanceof Error ? e.message : trans('generic.error'));
    } finally {
        loading.value = false;
    }
}

async function fetchPeriodStats(): Promise<void> {
    try {
        const res = await api.statistics.getStatisticsHistory();
        advancedData.value.by_period = res.data.data ?? { yearly: [], monthly: [], weekly: [] };
    } catch {
        // non-critical, fail silently
    }
}

async function fetchPeriods(): Promise<void> {
    const now = new Date();
    const toStr = (d: Date) => d.toISOString().split('T')[0];
    const configs: { labelKey: string; from: string; until: string }[] = [
        { labelKey: 'stats.last-week', from: toStr(new Date(now.getTime() - 7 * 86400000)), until: toStr(now) },
        { labelKey: 'stats.last-month', from: toStr(new Date(now.getTime() - 30 * 86400000)), until: toStr(now) },
        { labelKey: 'stats.last-year', from: toStr(new Date(now.getTime() - 365 * 86400000)), until: toStr(now) },
    ];
    try {
        const results = await Promise.all(
            configs.map(({ from, until }) => api.statistics.getStatisticsOverview({ from, until })),
        );
        periods.value = results.map((res, i) => {
            const s = (res.data.data as { summary: PeriodSummary }).summary;
            return { ...s, labelKey: configs[i].labelKey, from: configs[i].from, until: configs[i].until };
        });
    } catch {
        // non-critical
    }
}

async function fetchGlobalStats(): Promise<void> {
    try {
        const res = await api.statistics.getGlobalStatistics();
        const d = res.data.data;
        const meta = res.data.meta;
        if (!d) return;
        globalStats.value = {
            distance: Number(d.distance),
            duration: Number(d.duration),
            activeUsers: d.activeUsers,
        };
        if (meta?.from) globalFrom.value = new Date(meta.from);
        if (meta?.until) globalUntil.value = new Date(meta.until);
    } catch {
        // global stats are best-effort
    }
}

const PAGE_SIZE = 5;
const yearlyPage = ref(0);
const monthlyPage = ref(0);

const yearlyRows = computed(() =>
    [...(advancedData.value.by_period.yearly ?? [])].sort((a, b) => b.period.localeCompare(a.period)),
);
const monthlyRows = computed(() =>
    [...(advancedData.value.by_period.monthly ?? [])].sort((a, b) => b.period.localeCompare(a.period)),
);
const yearlyPaged = computed(() =>
    yearlyRows.value.slice(yearlyPage.value * PAGE_SIZE, (yearlyPage.value + 1) * PAGE_SIZE),
);
const monthlyPaged = computed(() =>
    monthlyRows.value.slice(monthlyPage.value * PAGE_SIZE, (monthlyPage.value + 1) * PAGE_SIZE),
);
const yearlyTotalPages = computed(() => Math.ceil(yearlyRows.value.length / PAGE_SIZE));
const monthlyTotalPages = computed(() => Math.ceil(monthlyRows.value.length / PAGE_SIZE));

function getPeriodLabel(period: string, periodType: string): string {
    if (periodType === 'month') {
        return new Date(period + '-01').toLocaleDateString(undefined, { year: 'numeric', month: 'long' });
    }
    if (periodType === 'week') {
        return `${trans('stats.week-short')} ${period.split('-W')[1]}`;
    }
    return period;
}

function avgDistance(distanceKm: number, checkins: number): string {
    return checkins > 0 ? (distanceKm / checkins).toFixed(2) : '0.00';
}

function syncUrlParams(): void {
    const url = new URL(window.location.href);
    url.searchParams.set('from', fromStr.value);
    url.searchParams.set('until', untilStr.value);
    window.history.pushState({}, '', url.toString());
}

function readUrlParams(): void {
    const urlParams = new URLSearchParams(window.location.search);
    const f = urlParams.get('from');
    const u = urlParams.get('until');
    if (f) {
        const d = new Date(f);
        if (!isNaN(d.getTime())) from.value = d;
    }
    if (u) {
        const d = new Date(u);
        if (!isNaN(d.getTime())) until.value = d;
    }
}

watch([from, until], () => {
    syncUrlParams();
    fetchStats();
});

onMounted(() => {
    readUrlParams();
    fetchStats();
    fetchPeriodStats();
    fetchPeriods();
    fetchGlobalStats();
    window.addEventListener('popstate', readUrlParams);
});
</script>

<template>
    <AppLayout>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold flex items-center gap-2">
                <ChartNoAxesCombined class="w-6 h-6" />
                {{ trans('stats') }}
            </h1>
        </div>
        <!-- Date range controls -->
        <div class="card bg-base-100 mb-6">
            <div class="card-body py-3">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <!-- Presets -->
                    <div class="join shrink-0">
                        <button
                            v-for="preset in presets"
                            :key="preset.label"
                            class="join-item btn btn-sm"
                            :class="preset.days && isPresetActive(preset.days) ? 'btn-primary' : 'btn-ghost'"
                            @click="preset.days && applyPreset(preset.days)"
                        >
                            {{ preset.label }}
                        </button>
                    </div>

                    <div class="divider divider-horizontal hidden sm:flex my-0 h-8" />

                    <!-- Custom range: always one row -->
                    <div class="join">
                        <label class="join-item btn btn-sm btn-ghost cursor-default px-2 text-base-content/60">
                            {{ trans('stats.from') }}
                        </label>
                        <input
                            type="date"
                            class="join-item input input-sm input-bordered w-36"
                            :value="fromStr"
                            :max="untilStr"
                            @change="from = new Date(($event.target as HTMLInputElement).value)"
                        />
                        <label class="join-item btn btn-sm btn-ghost cursor-default px-2 text-base-content/60">
                            {{ trans('stats.to') }}
                        </label>
                        <input
                            type="date"
                            class="join-item input input-sm input-bordered w-36"
                            :value="untilStr"
                            :min="fromStr"
                            :max="toDateString(new Date())"
                            @change="until = new Date(($event.target as HTMLInputElement).value)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center py-16">
            <span class="loading loading-spinner loading-lg"></span>
        </div>

        <template v-else>
            <AdvancedStats :data="advancedData.summary ? advancedData : null" />

            <!-- No data at all -->
            <div
                v-if="!data.purpose.length && !data.categories.length && !data.operators.length && !data.time.length"
                class="text-center py-16 text-base-content/50"
            >
                {{ trans('stats.no-data') }}
            </div>

            <template v-else>
                <!-- Charts row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <template v-if="data.purpose.length">
                        <ChartDoughnut
                            type="pie"
                            :title="trans('stats.purpose')"
                            :series="data.purpose.map((p) => p.duration)"
                            :labels="purposeLabels"
                        />
                    </template>
                    <div v-else class="card bg-base-100">
                        <div class="card-body items-center justify-center text-base-content/40 text-sm">
                            {{ trans('stats.purpose') }}: {{ trans('stats.no-data') }}
                        </div>
                    </div>

                    <template v-if="data.categories.length">
                        <ChartDoughnut
                            :title="trans('stats.categories')"
                            :series="data.categories.map((c) => c.duration)"
                            :labels="categoryLabels"
                        />
                    </template>
                    <div v-else class="card bg-base-100">
                        <div class="card-body items-center justify-center text-base-content/40 text-sm">
                            {{ trans('stats.categories') }}: {{ trans('stats.no-data') }}
                        </div>
                    </div>

                    <template v-if="data.operators.length">
                        <ChartHorizontalBar
                            :title="trans('stats.companies')"
                            :series="data.operators.map((o) => o.duration)"
                            :labels="data.operators.map((o) => o.name || trans('other'))"
                        />
                    </template>
                    <div v-else class="card bg-base-100">
                        <div class="card-body items-center justify-center text-base-content/40 text-sm">
                            {{ trans('stats.companies') }}: {{ trans('stats.no-data') }}
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div v-if="data.time.length" class="mb-6">
                    <ChartTimeline :title="trans('stats.time')" :data="data.time" />
                </div>
            </template>

            <!-- History breakdown (all-time, static) -->
            <template v-if="advancedData.by_period.yearly?.length || advancedData.by_period.monthly?.length">
                <div class="divider text-base-content/40 text-xs uppercase tracking-wide mt-6">
                    {{ trans('stats.breakdown') }}
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-if="yearlyRows.length" class="card bg-base-100">
                        <div class="card-body p-0 flex flex-col">
                            <div class="px-4 py-3 border-b border-base-300">
                                <h2 class="font-semibold">{{ trans('stats.yearly-breakdown') }}</h2>
                            </div>
                            <div class="overflow-x-auto flex-1">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('stats.year') }}</th>
                                            <th class="text-right">{{ trans('stats.checkins') }}</th>
                                            <th class="text-right">{{ trans('stats.distance') }}</th>
                                            <th class="text-right">{{ trans('stats.avg') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in yearlyPaged" :key="row.period">
                                            <td>{{ getPeriodLabel(row.period, row.period_type) }}</td>
                                            <td class="text-right">{{ row.checkin_count }}</td>
                                            <td class="text-right">{{ row.distance_km }} km</td>
                                            <td class="text-right">
                                                {{ avgDistance(row.distance_km, row.checkin_count) }} km
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                v-if="yearlyTotalPages > 1"
                                class="flex items-center justify-between px-4 py-2 border-t border-base-300"
                            >
                                <button class="btn btn-xs btn-ghost" :disabled="yearlyPage === 0" @click="yearlyPage--">
                                    ‹
                                </button>
                                <span class="text-xs text-base-content/50">
                                    {{ yearlyPage + 1 }} / {{ yearlyTotalPages }}
                                </span>
                                <button
                                    class="btn btn-xs btn-ghost"
                                    :disabled="yearlyPage >= yearlyTotalPages - 1"
                                    @click="yearlyPage++"
                                >
                                    ›
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-if="monthlyRows.length" class="card bg-base-100">
                        <div class="card-body p-0 flex flex-col">
                            <div class="px-4 py-3 border-b border-base-300">
                                <h2 class="font-semibold">{{ trans('stats.monthly-breakdown') }}</h2>
                            </div>
                            <div class="overflow-x-auto flex-1">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('stats.month') }}</th>
                                            <th class="text-right">{{ trans('stats.checkins') }}</th>
                                            <th class="text-right">{{ trans('stats.distance') }}</th>
                                            <th class="text-right">{{ trans('stats.avg') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="row in monthlyPaged" :key="row.period">
                                            <td>{{ getPeriodLabel(row.period, row.period_type) }}</td>
                                            <td class="text-right">{{ row.checkin_count }}</td>
                                            <td class="text-right">{{ row.distance_km }} km</td>
                                            <td class="text-right">
                                                {{ avgDistance(row.distance_km, row.checkin_count) }} km
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div
                                v-if="monthlyTotalPages > 1"
                                class="flex items-center justify-between px-4 py-2 border-t border-base-300"
                            >
                                <button
                                    class="btn btn-xs btn-ghost"
                                    :disabled="monthlyPage === 0"
                                    @click="monthlyPage--"
                                >
                                    ‹
                                </button>
                                <span class="text-xs text-base-content/50">
                                    {{ monthlyPage + 1 }} / {{ monthlyTotalPages }}
                                </span>
                                <button
                                    class="btn btn-xs btn-ghost"
                                    :disabled="monthlyPage >= monthlyTotalPages - 1"
                                    @click="monthlyPage++"
                                >
                                    ›
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Period comparison (Rückblick, static) -->
            <div v-if="periods" class="mt-4">
                <div class="divider text-base-content/40 text-xs uppercase tracking-wide">
                    {{ trans('stats.time-comparison') }}
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="period in periods" :key="period.label" class="card bg-base-200">
                        <div class="card-body py-4 gap-3">
                            <div>
                                <p class="text-xs text-base-content/50 uppercase tracking-wide font-medium">
                                    {{ trans(period.labelKey) }}
                                </p>
                                <p class="text-xs text-base-content/40 mt-0.5">
                                    {{ new Date(period.from).toLocaleDateString() }}
                                    {{ trans('stats.to').toLowerCase() }}
                                    {{ trans('stats.today') }}
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="text-center">
                                    <p class="text-2xl font-bold leading-none">{{ period.total_checkins }}</p>
                                    <p class="text-xs text-base-content/50 mt-1">{{ trans('stats.checkins') }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xl font-bold leading-none">{{ period.total_distance_km }}</p>
                                    <p class="text-xs text-base-content/50 mt-1">km</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global stats (always last) -->
            <div v-if="globalStats" class="mt-4">
                <div class="divider text-base-content/40 text-xs uppercase tracking-wide">
                    {{ trans('stats.global') }}
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="card bg-base-100">
                        <div class="card-body items-center text-center py-4">
                            <span class="text-3xl font-bold text-primary">
                                {{ Math.round(globalStats.distance / 1000).toLocaleString() }}
                            </span>
                            <span class="text-sm text-base-content/60">km, {{ trans('stats.global.distance') }}</span>
                        </div>
                    </div>
                    <div class="card bg-base-100">
                        <div class="card-body items-center text-center py-4">
                            <span class="text-3xl font-bold text-primary">
                                {{ Math.round(globalStats.duration / 60).toLocaleString() }}
                            </span>
                            <span class="text-sm text-base-content/60">h, {{ trans('stats.global.duration') }}</span>
                        </div>
                    </div>
                    <div class="card bg-base-100">
                        <div class="card-body items-center text-center py-4">
                            <span class="text-3xl font-bold text-primary">
                                {{ Number(globalStats.activeUsers).toLocaleString() }}x
                            </span>
                            <span class="text-sm text-base-content/60">{{ trans('stats.global.active') }}</span>
                        </div>
                    </div>
                </div>
                <p v-if="globalFrom && globalUntil" class="text-center text-xs text-base-content/40 mt-2">
                    *{{
                        trans('stats.global.explain', {
                            fromDate: globalFrom.toLocaleDateString(),
                            toDate: globalUntil.toLocaleDateString(),
                        })
                    }}
                </p>
            </div>
        </template>
    </AppLayout>
</template>
