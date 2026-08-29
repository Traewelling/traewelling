<script setup lang="ts">
import { ChevronDown, ChevronUp, Train } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { inject, onMounted, ref } from 'vue';
import { Api, StatusResource, StopoverResource } from '../../../types/Api.gen';
import { getDepartureForStatus } from '../../../vue/helpers/DateTimeHelper';
import AlertBanner from '../../components/AlertBanner.vue';
import StationSearch from '../../components/StationSearch.vue';
import StatusCard from '../../components/Status/StatusCard.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;

const statuses = ref<StatusResource[]>([]);
const futureStatuses = ref<StatusResource[]>([]);
const stopovers = ref<Record<string, StopoverResource[]>>({});
const loading = ref(true);
const loadingMore = ref(false);
const showMore = ref(false);
const currentPage = ref(1);
const futureOpen = ref(false);

async function fetchStopovers(items: StatusResource[]): Promise<void> {
    if (!items.length) return;
    const tripIds = items.map((s) => s.checkin.trip.toString());
    try {
        const res = await api.stopovers.getStopOvers(tripIds.join(','));
        const data = res.data?.data;
        for (const tripId in data) {
            stopovers.value[tripId] = data[tripId];
        }
    } catch {
        // stopovers are best-effort
    }
}

async function fetchStatuses(page = 1, append = false): Promise<void> {
    if (append) {
        loadingMore.value = true;
    } else {
        loading.value = true;
    }
    try {
        const res = await api.dashboard.getDashboard({ page });
        const items: StatusResource[] = res.data?.data ?? [];
        if (append) {
            statuses.value.push(...items);
        } else {
            statuses.value = items;
        }
        showMore.value = !!res.data?.links?.next;
        currentPage.value = res.data?.meta?.current_page ?? page;
        await fetchStopovers(items);
    } catch (e: unknown) {
        notyf.error(e instanceof Error ? e.message : trans('generic.error'));
    } finally {
        loading.value = false;
        loadingMore.value = false;
    }
}

async function fetchFutureStatuses(): Promise<void> {
    try {
        const res = await api.dashboard.getFutureDashboard();
        futureStatuses.value = res.data?.data ?? [];
    } catch {
        // future statuses are best-effort
    }
}

function isNewDay(index: number): boolean {
    if (index === 0) return true;
    return !getDepartureForStatus(statuses.value[index]).dateTime.hasSame(
        getDepartureForStatus(statuses.value[index - 1]).dateTime,
        'day',
    );
}

function dayLabel(status: StatusResource): string {
    return getDepartureForStatus(status).dateTime.toLocaleString(DateTime.DATE_HUGE);
}

function getStopoverForTrip(tripId: string): StopoverResource[] | undefined {
    return stopovers.value[tripId];
}

onMounted(() => {
    fetchStatuses();
    fetchFutureStatuses();
});
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <AlertBanner />
            <StationSearch />

            <!-- Future check-ins -->
            <div v-if="futureStatuses.length" class="mb-4">
                <button class="btn btn-ghost btn-sm w-full justify-between" @click="futureOpen = !futureOpen">
                    <span>{{ trans('dashboard.future') }}</span>
                    <ChevronUp v-if="futureOpen" class="w-4 h-4" />
                    <ChevronDown v-else class="w-4 h-4" />
                </button>
                <div v-if="futureOpen" class="flex flex-col gap-3 mt-2">
                    <StatusCard v-for="status in futureStatuses" :key="status.id" :status="status" />
                </div>
            </div>

            <!-- Loading skeleton -->
            <template v-if="loading">
                <div v-for="n in 3" :key="n" class="card bg-base-100 mb-3">
                    <div class="card-body gap-3">
                        <div class="skeleton h-4 w-32 rounded" />
                        <div class="skeleton h-24 w-full rounded" />
                    </div>
                </div>
            </template>

            <template v-else>
                <!-- Empty state -->
                <div v-if="!statuses.length" class="card bg-base-100">
                    <div class="card-body items-center text-center gap-3 py-12">
                        <Train class="w-12 h-12 text-base-content/20" />
                        <h3 class="font-semibold text-lg">{{ trans('dashboard.empty') }}</h3>
                        <p class="text-base-content/60 text-sm">{{ trans('dashboard.empty.teaser') }}</p>
                        <p class="text-base-content/60 text-sm">
                            {{ trans('dashboard.empty.discover1') }}
                            <a href="/statuses/active" class="link link-primary">{{ trans('menu.active') }}</a>
                            {{ trans('dashboard.empty.discover3') }}.
                        </p>
                    </div>
                </div>

                <!-- Status feed -->
                <template v-for="(status, index) in statuses" :key="status.id">
                    <p v-if="isNewDay(index)" class="text-sm font-medium text-base-content/50 mt-4 mb-2 first:mt-0">
                        {{ dayLabel(status) }}
                    </p>
                    <div class="mb-3">
                        <StatusCard
                            :status="status"
                            :stopovers="getStopoverForTrip(status.checkin.trip.toString())"
                            @status-deleted="statuses = statuses.filter((s) => s.id !== $event)"
                            @status-updated="statuses = statuses.map((s) => (s.id === $event.id ? $event : s))"
                        />
                    </div>
                </template>

                <!-- Load more -->
                <div v-if="showMore" class="text-center my-4">
                    <button
                        class="btn btn-primary btn-sm"
                        :class="{ loading: loadingMore }"
                        @click="fetchStatuses(currentPage + 1, true)"
                    >
                        <ChevronDown v-if="!loadingMore" class="w-4 h-4" />
                    </button>
                </div>

                <!-- End of feed -->
                <div v-if="!showMore && statuses.length" class="text-center py-8 text-base-content/30 text-sm">
                    <p>Final stop. All change, please!</p>
                    <p>{{ trans('dashboard-end-seven-days') }}</p>
                </div>
            </template>

            <!-- Loading more skeleton -->
            <template v-if="loadingMore">
                <div v-for="n in 2" :key="n" class="card bg-base-100 mb-3">
                    <div class="card-body gap-3">
                        <div class="skeleton h-4 w-32 rounded" />
                        <div class="skeleton h-24 w-full rounded" />
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
