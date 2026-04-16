<script setup lang="ts">
import { trans, transChoice } from 'laravel-vue-i18n';
import { ChevronLeft, ChevronRight, Clock, Dices, Map, Route, Train } from 'lucide-vue-next';
import { Notyf } from 'notyf';
import { computed, inject, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Api, type StatusResource } from '../../../types/Api.gen';
import GenericMap from '../../../vue/components/Map/GenericMap.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const route = useRoute();
const router = useRouter();
const notyf = inject('notyf') as Notyf;

type DailyData = {
    statuses: StatusResource[];
    polylines: { type: string; features: unknown[] } | null;
    totalDistance: number;
    totalDuration: number;
    totalPoints: number;
    prevDate: string | null | undefined;
    nextDate: string | null | undefined;
};

const loading = ref(true);
const dayData = ref<DailyData | null>(null);

const dateString = computed(() => route.params.dateString as string);

const formattedDate = computed(() => {
    if (!dateString.value) return '';
    return new Date(dateString.value + 'T12:00:00').toLocaleDateString(undefined, {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
});

const kmRounded = computed(() => Math.round((dayData.value?.totalDistance ?? 0) / 1000));
const durationParts = computed(() => {
    const m = dayData.value?.totalDuration ?? 0;
    return {
        h: Math.floor(m / 60),
        min: m % 60,
    };
});

const polylineFeatures = computed(() => {
    if (!dayData.value?.polylines) return [];
    return (dayData.value.polylines.features ?? []) as never[];
});

async function fetchDay(date: string): Promise<void> {
    loading.value = true;
    dayData.value = null;
    try {
        const res = await api.statistics.getDailyStatistics(date, { withPolylines: true });
        const d = res.data.data;
        dayData.value = {
            statuses: d?.statuses ?? [],
            polylines: (d?.polylines as unknown as { type: string; features: unknown[] }) ?? null,
            totalDistance: d?.totalDistance ?? 0,
            totalDuration: d?.totalDuration ?? 0,
            totalPoints: d?.totalPoints ?? 0,
            prevDate: d?.prevDate,
            nextDate: d?.nextDate,
        };
    } catch (e: unknown) {
        notyf.error(e instanceof Error ? e.message : trans('generic.error'));
    } finally {
        loading.value = false;
    }
}

function navigate(date: string | null): void {
    if (!date) return;
    router.push({ name: 'stats-daily', params: { dateString: date } });
}

function onKeyDown(e: KeyboardEvent): void {
    if (e.key === 'ArrowLeft') navigate(dayData.value?.prevDate ?? null);
    if (e.key === 'ArrowRight') navigate(dayData.value?.nextDate ?? null);
}

watch(dateString, (d) => fetchDay(d));

onMounted(() => {
    fetchDay(dateString.value);
    window.addEventListener('keydown', onKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', onKeyDown);
});

function formatTime(iso: string | null | undefined): string {
    if (!iso) return '?';
    return new Date(iso).toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
}

function formatKm(meters: number): string {
    return (meters / 1000).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 1 });
}
</script>

<template>
    <AppLayout>
        <!-- Navigation header -->
        <div class="flex items-center justify-between mb-6">
            <button
                class="btn btn-ghost btn-sm gap-1"
                :disabled="!dayData?.prevDate && !loading"
                :title="trans('prev')"
                @click="navigate(dayData?.prevDate ?? null)"
            >
                <ChevronLeft class="w-4 h-4" />
                <span class="hidden sm:inline">{{ dayData?.prevDate ?? '' }}</span>
            </button>

            <div class="text-center">
                <h1 class="text-xl font-bold">{{ formattedDate }}</h1>
                <p class="text-xs text-base-content/40 mt-0.5">{{ trans('keyboard-hint', {}, '') || '← →' }}</p>
            </div>

            <button
                class="btn btn-ghost btn-sm gap-1"
                :disabled="!dayData?.nextDate && !loading"
                :title="trans('next')"
                @click="navigate(dayData?.nextDate ?? null)"
            >
                <span class="hidden sm:inline">{{ dayData?.nextDate ?? '' }}</span>
                <ChevronRight class="w-4 h-4" />
            </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex justify-center items-center py-16">
            <span class="loading loading-spinner loading-lg"></span>
        </div>

        <!-- No statuses -->
        <div v-else-if="!dayData?.statuses?.length" class="text-center py-16">
            <Train class="w-12 h-12 mx-auto text-base-content/20 mb-4" />
            <p class="text-base-content/50">{{ trans('no-journeys-day') }}</p>
        </div>

        <template v-else>
            <!-- Stats summary bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-3">
                        <Train class="w-5 h-5 text-primary mb-1" />
                        <span class="text-2xl font-bold">{{ dayData.statuses.length }}</span>
                        <span class="text-xs text-base-content/60">
                            {{ transChoice('stats.trips', dayData.statuses.length) }}
                        </span>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-3">
                        <Route class="w-5 h-5 text-primary mb-1" />
                        <span class="text-2xl font-bold">{{ kmRounded }}</span>
                        <span class="text-xs text-base-content/60">km</span>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-3">
                        <Clock class="w-5 h-5 text-primary mb-1" />
                        <span class="text-2xl font-bold">{{ durationParts.h }}h {{ durationParts.min }}m</span>
                        <span class="text-xs text-base-content/60">{{ trans('time.duration') }}</span>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body items-center text-center py-3">
                        <Dices class="w-5 h-5 text-primary mb-1" />
                        <span class="text-2xl font-bold">{{ dayData.totalPoints }}</span>
                        <span class="text-xs text-base-content/60">{{ trans('profile.points-abbr') }}</span>
                    </div>
                </div>
            </div>

            <!-- Map + statuses -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Map -->
                <div v-if="polylineFeatures.length" class="card bg-base-100 overflow-hidden">
                    <div class="h-96 lg:h-full min-h-80">
                        <GenericMap :poly-lines="polylineFeatures" class="h-full w-full" />
                    </div>
                </div>
                <div v-else class="card bg-base-100">
                    <div class="card-body items-center justify-center text-base-content/30 gap-2">
                        <Map class="w-10 h-10" />
                        <span class="text-sm">{{ trans('no-map-data') }}</span>
                    </div>
                </div>

                <!-- Status list -->
                <div class="flex flex-col gap-3">
                    <div
                        v-for="status in dayData.statuses"
                        :key="status.id"
                        class="card bg-base-100 hover:bg-base-200 transition-colors"
                    >
                        <div class="card-body py-3 px-4">
                            <div class="flex items-start justify-between gap-2">
                                <!-- Line badge + name -->
                                <div class="flex items-center gap-2 min-w-0">
                                    <span
                                        class="badge badge-sm font-mono shrink-0"
                                        :style="
                                            status.checkin.routeColor
                                                ? {
                                                      backgroundColor: '#' + status.checkin.routeColor,
                                                      color: status.checkin.routeTextColor
                                                          ? '#' + status.checkin.routeTextColor
                                                          : '#fff',
                                                  }
                                                : {}
                                        "
                                    >
                                        {{ status.checkin.lineName }}
                                    </span>
                                    <span class="text-sm font-medium truncate">
                                        {{ status.checkin.origin.name }}
                                        <ChevronRight class="inline w-3 h-3 text-base-content/40" />
                                        {{ status.checkin.destination.name }}
                                    </span>
                                </div>

                                <!-- Time -->
                                <span class="text-xs text-base-content/50 shrink-0">
                                    {{
                                        formatTime(
                                            status.checkin.manualDeparture ??
                                                status.checkin.origin.departure ??
                                                status.checkin.origin.arrival,
                                        )
                                    }}
                                </span>
                            </div>

                            <!-- Stats row -->
                            <div class="flex gap-4 text-xs text-base-content/50 mt-1">
                                <span>{{ formatKm(status.checkin.distance) }} km</span>
                                <span>{{ status.checkin.duration }} min</span>
                                <span>{{ status.checkin.points }} {{ trans('profile.points-abbr') }}</span>
                            </div>

                            <!-- Body -->
                            <p v-if="status.body" class="text-sm text-base-content/70 mt-1 truncate">
                                {{ status.body }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
