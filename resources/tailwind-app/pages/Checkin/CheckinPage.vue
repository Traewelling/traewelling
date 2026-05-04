<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
import { DateTime } from 'luxon';
import { computed, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Api, DepartureResource, StopoverResource, TripResource } from '../../../types/Api.gen';
import CheckinForm from '../../components/Checkin/CheckinForm.vue';
import LineRun from '../../components/Checkin/LineRun.vue';
import TransportIcon from '../../components/TransportIcon.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const route = useRoute();
const router = useRouter();

const tripId = computed(() => route.query.tripId as string);
const lineName = computed(() => route.query.lineName as string | undefined);
const startId = computed(() => route.query.start as string | undefined);
const destinationId = computed(() => route.query.destination as string | undefined);
const departureTime = computed(() => route.query.departure as string);
const originName = computed(() => route.query.originName as string | undefined);
const category = computed(() => route.query.category as string | undefined);

const hasStartFromUrl = computed(() => !!startId.value);

const selectedStart = ref<StopoverResource | null>(null);
const selectedDestination = ref<StopoverResource | null>(null);
const fastCheckinUsed = ref(false);

const allStopovers = ref<StopoverResource[]>([]);
const loadingStops = ref(false);

async function loadAllStops(): Promise<void> {
    loadingStops.value = true;
    try {
        const res = await api.trains.getTrainTrip({
            hafasTripId: tripId.value,
            lineName: lineName.value ?? '',
        });
        const trip = res.data?.data as TripResource;
        allStopovers.value = (trip.stopovers ?? []).slice(0, -1);
    } catch {
        // ignore
    } finally {
        loadingStops.value = false;
    }
}

const effectiveStartId = computed<string>(() => {
    if (selectedStart.value) return String(selectedStart.value.id);
    return startId.value ?? '';
});

const effectiveDeparture = computed<string>(() => {
    if (selectedStart.value) {
        return selectedStart.value.departurePlanned ?? selectedStart.value.arrivalPlanned ?? departureTime.value;
    }
    return departureTime.value;
});

const effectiveOriginName = computed<string | undefined>(() => {
    return selectedStart.value?.name ?? originName.value;
});

const departure = computed(
    (): DepartureResource => ({
        tripId: tripId.value,
        plannedWhen: effectiveDeparture.value,
        when: null,
        delay: null,
        platform: null,
        plannedPlatform: null,
        direction: '',
        stop: { id: Number(effectiveStartId.value) },
        line: { name: lineName.value },
    }),
);

const showStartSelection = computed(() => !hasStartFromUrl.value && !selectedStart.value);
const showDestinationSelection = computed(() => !showStartSelection.value && !selectedDestination.value);

const stepTitle = computed<string>(() => {
    if (selectedDestination.value) return trans('stationboard.btn-checkin');
    if (showStartSelection.value) return trans('checkin.select-departure');
    return trans('stationboard.destination');
});

function handleBack(): void {
    if (selectedDestination.value) {
        selectedDestination.value = null;
        fastCheckinUsed.value = true;
    } else if (!hasStartFromUrl.value && selectedStart.value) {
        selectedStart.value = null;
    } else {
        router.back();
    }
}

onMounted(() => {
    if (!hasStartFromUrl.value) loadAllStops();
});
</script>

<template>
    <AppLayout>
        <div class="max-w-lg mx-auto">
            <!-- Back + step title -->
            <div class="flex items-center gap-2 p-4 border-b border-base-300">
                <button class="btn btn-ghost btn-sm btn-square" @click="handleBack">
                    <ArrowLeft class="w-4 h-4" />
                </button>
                <span class="font-semibold text-sm">{{ stepTitle }}</span>
            </div>

            <!-- Trip summary -->
            <div v-if="effectiveOriginName || lineName" class="flex items-center gap-3 px-4 py-3 bg-base-200 text-sm">
                <TransportIcon :product="category" class="flex-shrink-0 opacity-70" />
                <span v-if="lineName" class="badge badge-neutral badge-sm font-mono flex-shrink-0">
                    {{ lineName }}
                </span>
                <span class="truncate text-base-content/70">{{ effectiveOriginName }}</span>
                <template v-if="selectedDestination">
                    <ArrowRight class="w-3 h-3 flex-shrink-0 text-base-content/40" />
                    <span class="truncate text-base-content/70">{{ selectedDestination.name }}</span>
                </template>
            </div>

            <!-- Step 1: Select start stop -->
            <template v-if="showStartSelection">
                <div v-if="loadingStops" class="flex flex-col gap-2 p-4">
                    <div v-for="n in 6" :key="n" class="skeleton h-9 w-full rounded" />
                </div>
                <ul v-else class="divide-y divide-base-200">
                    <li v-for="item in allStopovers" :key="item.id">
                        <button
                            class="w-full flex justify-between items-center px-4 py-3 hover:bg-base-200 text-left transition-colors"
                            @click="selectedStart = item"
                        >
                            <span class="flex-1 text-sm">{{ item.name }}</span>
                            <span class="text-right text-sm flex-shrink-0 ml-3 text-base-content/60">
                                {{
                                    item.departurePlanned
                                        ? DateTime.fromISO(item.departurePlanned).toFormat('HH:mm')
                                        : ''
                                }}
                            </span>
                        </button>
                    </li>
                </ul>
            </template>

            <!-- Step 2: Select destination -->
            <LineRun
                v-else-if="showDestinationSelection"
                :trip-id="tripId"
                :line-name="lineName"
                :start-id="effectiveStartId"
                :planned-when="effectiveDeparture"
                :fast-checkin-id="!fastCheckinUsed && destinationId ? Number(destinationId) : null"
                @select="selectedDestination = $event"
            />

            <!-- Step 3: Checkin form -->
            <CheckinForm
                v-else
                :key="selectedDestination?.id"
                :departure="departure"
                :destination="selectedDestination!"
            />
        </div>
    </AppLayout>
</template>
