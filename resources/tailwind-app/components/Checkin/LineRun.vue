<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { onMounted, ref, watch } from 'vue';
import { Api, StopoverResource, TripResource } from '../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const props = defineProps<{
    tripId: string;
    lineName?: string | null;
    startId: number | string;
    plannedWhen: string;
    fastCheckinId?: number | null;
}>();

const emit = defineEmits<{
    (e: 'select', stopover: StopoverResource): void;
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

function getPlannedTime(item: StopoverResource): string | null {
    if (item.isArrivalDelayed) return item.arrivalPlanned ?? null;
    if (item.isDepartureDelayed) return item.departurePlanned ?? null;
    return null;
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

        const givenDeparture = DateTime.fromISO(props.plannedWhen);
        stopovers.value = (trip.stopovers ?? []).filter((item) => {
            let time: DateTime | null = null;
            if (item.arrivalPlanned) time = DateTime.fromISO(item.arrivalPlanned);
            else if (item.departurePlanned) time = DateTime.fromISO(item.departurePlanned);
            if (!time) return true;
            if (time.toMillis() < givenDeparture.toMillis()) return false;
            if (time.toMillis() > givenDeparture.toMillis()) return true;
            return Number(props.startId) !== Number(item.id);
        });

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
                class="w-full flex justify-between items-center px-4 py-3 hover:bg-base-200 text-left transition-colors"
                :class="{ 'opacity-60': item.cancelled }"
                @click="emit('select', item)"
            >
                <span class="flex-1 text-sm" :class="{ 'line-through text-error': item.cancelled }">
                    {{ item.name }}
                    <span v-if="item.cancelled" class="badge badge-error badge-xs align-middle ml-1">
                        {{ trans('stationboard.stop-cancelled') }}
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
                        <span :class="{ 'text-warning': item.isArrivalDelayed || item.isDepartureDelayed }">
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
