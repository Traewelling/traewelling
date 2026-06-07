<script setup lang="ts">
import { ChartNoAxesCombined } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, onMounted, ref, watch } from 'vue';
import { Api, type StatisticsGlobalData } from '../../../types/Api.gen';
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

const loading = ref(true);
const data = ref<StatsData>({ purpose: [], categories: [], operators: [], time: [] });
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
        const res = await api.statistics.getStatistics({ from: fromStr.value, until: untilStr.value });
        const d = res.data.data;
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
    } catch (e: unknown) {
        notyf.error(e instanceof Error ? e.message : trans('generic.error'));
    } finally {
        loading.value = false;
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

            <!-- Global stats -->
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
