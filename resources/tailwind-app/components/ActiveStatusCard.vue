<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { StopoverResource } from '../../types/Api.gen';
import LineIndicator from '../../vue/components/LineIndicator.vue';
import { NextStation } from '../../vue/helpers/NextStation';
import { useActiveCheckin } from '../../vue/stores/activeCheckin';

const router = useRouter();
const state = useActiveCheckin();

const progress = ref(0);
const nextStation = ref<StopoverResource | null>(null);
let fetchInterval: ReturnType<typeof setInterval> | null = null;
let nextStationInterval: ReturnType<typeof setInterval> | null = null;

const departure = computed(() => {
    const manual = state.status?.checkin?.manualDeparture ?? null;
    if (manual) return DateTime.fromISO(manual);
    const dep = state.status?.checkin?.origin?.departure ?? state.status?.checkin?.origin?.arrival ?? null;
    return dep ? DateTime.fromISO(dep) : null;
});

const arrival = computed(() => {
    const manual = state.status?.checkin?.manualArrival ?? null;
    if (manual) return DateTime.fromISO(manual);
    const arr = state.status?.checkin?.destination?.arrival ?? state.status?.checkin?.destination?.departure ?? null;
    return arr ? DateTime.fromISO(arr) : null;
});

const showCard = computed(() => {
    return state.status != null && progress.value >= 0 && progress.value <= 100;
});

function getProgress() {
    if (departure.value && arrival.value) {
        const now = DateTime.now();
        const total = arrival.value.diff(departure.value).milliseconds;
        const current = now.diff(departure.value).milliseconds;
        progress.value = Math.round((current / total) * 100);
    }
}

function getNextStation() {
    getProgress();
    if (state.stopovers && progress.value < 100) {
        nextStation.value = NextStation.getNextStation(state.stopovers);
    }
}

function format(dateTime: DateTime | null): string {
    return dateTime ? dateTime.toFormat('HH:mm') : '';
}

function goToStatus() {
    if (state.status?.id) {
        router.push({ name: 'single-status', params: { id: state.status.id } });
    }
}

onMounted(() => {
    state.fetchActiveStatus();
    setTimeout(getNextStation, 500);
    fetchInterval = setInterval(() => state.fetchActiveStatus(), 30000);
    nextStationInterval = setInterval(getNextStation, 10000);
});

onUnmounted(() => {
    if (fetchInterval) clearInterval(fetchInterval);
    if (nextStationInterval) clearInterval(nextStationInterval);
});
</script>

<template>
    <div v-show="showCard" class="fixed bottom-8 left-0 right-0 z-50 flex justify-center px-4 md:hidden">
        <div class="card bg-base-300 shadow-lg w-full cursor-pointer" @click="goToStatus">
            <div class="card-body py-2 px-3">
                <p class="flex justify-between items-baseline mb-0">
                    <span>{{ state.status?.checkin?.origin?.name }}</span>
                    <small class="text-base-content/60">{{ format(departure) }}</small>
                </p>

                <p v-show="state.status?.checkin?.lineName" class="flex items-center gap-1 my-0 ml-2">
                    <LineIndicator
                        :product-name="state.status?.checkin?.category ?? ''"
                        :mode="state.status?.checkin?.mode ?? null"
                        :number="state.status?.checkin?.lineName ?? ''"
                        :color="state.status?.checkin?.routeTextColor || undefined"
                        :background-color="state.status?.checkin?.routeColor || undefined"
                    />
                    <span v-show="nextStation">{{ trans('stationboard.next-stop') }} {{ nextStation?.name }}</span>
                </p>

                <p class="flex justify-between items-baseline mb-0">
                    <span>{{ state.status?.checkin?.destination?.name }}</span>
                    <small class="text-base-content/60">{{ format(arrival) }}</small>
                </p>

                <progress class="progress progress-primary h-1" :value="progress" max="100"></progress>
            </div>
        </div>
    </div>
</template>
