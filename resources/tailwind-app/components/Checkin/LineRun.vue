<script setup lang="ts">
import { House } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { computed, onMounted, ref, watch } from 'vue';
import { Api, StationResource, StopoverResource, TripResource } from '../../../types/Api.gen';
import { useUserStore } from '../../../vue/stores/user';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const userStore = useUserStore();

const homeStation = computed<StationResource | null>(() => userStore.getHome);

const props = defineProps<{
    tripId: string;
    lineName?: string | null;
    startId: number | string;
    plannedWhen: string;
    fastCheckinId?: number | null;
}>();

const emit = defineEmits<{
    (e: 'select', stopover: StopoverResource): void;
    (e: 'trip', trip: TripResource): void;
}>();

const stopovers = ref<StopoverResource[]>([]);
const attribution = ref<string | null>(null);
const loading = ref(false);
const error = ref(false);

function formatTime(time: string | null | undefined): string {
    if (!time) return '';
    return DateTime.fromISO(time).toFormat('HH:mm');
}

function getDisplayTime(item: StopoverResource): string | null {
    if (item.arrivalPlanned) return item.arrivalReal ?? item.arrivalPlanned;
    return item.departureReal ?? item.departurePlanned ?? null;
}

function getDeviationInMinutes(item: StopoverResource): number | null {
    const planned = item.arrivalPlanned ? item.arrivalPlanned : item.departurePlanned;
    const real = item.arrivalPlanned ? item.arrivalReal : item.departureReal;
    if (!planned || !real) return null;
    return Math.round(DateTime.fromISO(real).diff(DateTime.fromISO(planned), 'minutes').minutes);
}

function getPlannedTime(item: StopoverResource): string | null {
    const deviation = getDeviationInMinutes(item);
    if (deviation === null || deviation === 0) return null;
    return item.arrivalPlanned ?? item.departurePlanned ?? null;
}

function isHome(item: StopoverResource): boolean {
    const home = homeStation.value;
    if (!home) return false;
    if (home.uuid && item.station?.uuid) return home.uuid === item.station.uuid;
    return home.id === item.station?.id;
}

function getPlatformPair(item: StopoverResource): { planned: string | null; real: string | null } {
    if (item.arrivalPlanned) {
        return { planned: item.arrivalPlatformPlanned, real: item.arrivalPlatformReal };
    }
    return { planned: item.departurePlatformPlanned, real: item.departurePlatformReal };
}

function getPlatform(item: StopoverResource): string | null {
    const { planned, real } = getPlatformPair(item);
    return real ?? planned;
}

function getReplacedPlatform(item: StopoverResource): string | null {
    const { planned, real } = getPlatformPair(item);
    if (!planned || !real || planned === real) return null;
    return planned;
}

function deviationClass(item: StopoverResource): string {
    const deviation = getDeviationInMinutes(item);
    if (deviation === null) return '';
    if (deviation < 0) return 'text-info';
    if (deviation > 5) return 'text-error';
    if (deviation >= 1) return 'text-warning';
    return 'text-success';
}

async function fetchLineRun(): Promise<void> {
    error.value = false;
    loading.value = true;
    try {
        const res = await api.trains.getTrainTrip({
            hafasTripId: props.tripId,
            lineName: props.lineName ?? '',
        });
        const trip: TripResource = res.data?.data as TripResource;
        emit('trip', trip);

        const givenDeparture = DateTime.fromISO(props.plannedWhen);
        const allStopovers = trip.stopovers ?? [];
        const startIndex = allStopovers.findIndex((item) => {
            if (Number(item.id) !== Number(props.startId)) return false;
            const dep = item.departurePlanned
                ? DateTime.fromISO(item.departurePlanned)
                : item.arrivalPlanned
                  ? DateTime.fromISO(item.arrivalPlanned)
                  : null;
            return dep !== null && dep.toMillis() === givenDeparture.toMillis();
        });
        stopovers.value = startIndex !== -1 ? allStopovers.slice(startIndex + 1) : allStopovers;

        attribution.value = trip.dataSource?.attribution ?? null;

        if (props.fastCheckinId) {
            const dest = stopovers.value.find((s) => Number(s.id) === Number(props.fastCheckinId));
            if (dest) emit('select', dest);
        }
    } catch {
        error.value = true;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchLineRun);
watch(() => props.tripId, fetchLineRun);
</script>

<template>
    <div v-if="error" class="p-4 text-error text-sm">
        {{ trans('messages.exception.motis.502') }}
    </div>

    <div v-else-if="loading" class="flex flex-col gap-2 p-4">
        <div v-for="n in 8" :key="n" class="skeleton h-9 w-full rounded" />
    </div>

    <ul v-else class="divide-y divide-base-200">
        <li v-for="item in stopovers" :key="item.id">
            <button
                class="w-full flex justify-between items-center px-4 py-3 hover:bg-base-200 text-left transition-colors cursor-pointer"
                :class="{ 'opacity-60': item.cancelled, 'bg-primary/10 font-medium': isHome(item) }"
                @click="emit('select', item)"
            >
                <span class="flex-1 text-sm" :class="{ 'line-through text-error': item.cancelled }">
                    {{ item.name }}
                    <House v-if="isHome(item)" class="size-4 inline-block ms-1 -mt-0.5 text-primary" />
                    <span v-if="item.cancelled" class="badge badge-error badge-xs align-middle ml-1">
                        {{ trans('stationboard.stop-cancelled') }}
                    </span>
                </span>
                <span v-if="getPlatform(item)" class="flex-shrink-0 ml-3 flex items-center gap-1 text-xs">
                    <span v-if="getReplacedPlatform(item)" class="text-base-content/40 line-through">
                        {{ getReplacedPlatform(item) }}
                    </span>
                    <span
                        class="badge badge-soft badge-sm text-xs whitespace-nowrap"
                        :class="getReplacedPlatform(item) ? 'badge-warning' : 'badge-info'"
                        :title="trans('platform')"
                    >
                        {{ getPlatform(item) }}
                    </span>
                </span>
                <span class="text-right text-sm flex-shrink-0 ml-3">
                    <template v-if="item.cancelled">
                        <span class="text-error line-through">{{ formatTime(getDisplayTime(item)) }}</span>
                    </template>
                    <template v-else>
                        <span v-if="getPlannedTime(item)" class="text-base-content/40 line-through text-xs block">
                            {{ formatTime(getPlannedTime(item)) }}
                        </span>
                        <span :class="deviationClass(item)">
                            {{ formatTime(getDisplayTime(item)) }}
                        </span>
                    </template>
                </span>
            </button>
        </li>
    </ul>

    <div v-if="attribution" class="px-4 pb-4 pt-2">
        <!-- eslint-disable-next-line vue/no-v-html -->
        <span class="text-xs text-base-content/40" v-html="attribution" />
    </div>
</template>
