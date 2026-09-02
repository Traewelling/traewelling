<script setup lang="ts">
import { ArrowLeft } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { HafasTravelType, StopoverResource, TripResource } from '../../../types/Api.gen';
import LineIndicator from '../../../vue/components/LineIndicator.vue';
import LineRun from '../../components/Checkin/LineRun.vue';
import TransportIcon from '../../components/TransportIcon.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();

const tripId = computed(() => route.query.tripId as string);
const lineName = computed(() => (route.query.lineName as string | undefined) ?? undefined);
const startId = computed(() => (route.query.startId as string | undefined) ?? undefined);
const plannedWhen = computed(() => (route.query.plannedWhen as string | undefined) ?? '');
const direction = computed(() => (route.query.direction as string | undefined) ?? '');
const originName = computed(() => (route.query.originName as string | undefined) ?? undefined);
const product = computed(() => (route.query.product as string | undefined) ?? null);
const mode = computed(() => (route.query.mode as string | undefined) ?? null);
const color = computed(() => (route.query.color as string | undefined) ?? null);
const textColor = computed(() => (route.query.textColor as string | undefined) ?? null);
const initialCategory = computed(() => (route.query.category as HafasTravelType | undefined) ?? null);

// No startId yet means the trip was entered manually and the exact departure
// stop within it still needs to be picked before a destination can be chosen.
const isSelectingStart = computed(() => !startId.value);

// TransportIcon falls back to its HafasTravelType lookup via the `product` prop,
// so a manually created trip (which only carries `category`) still gets an icon.
const iconProduct = computed(() => product.value ?? initialCategory.value ?? undefined);

const displayLineName = computed(() => (lineName.value ?? '').replaceAll(/\(.*?\)/g, '').trim());

const tripCategory = ref<HafasTravelType | null>(initialCategory.value);
const tripUuid = ref<string | null>(null);

function handleBack(): void {
    router.back();
}

function onTrip(trip: TripResource): void {
    tripCategory.value = trip.category ?? initialCategory.value;
    tripUuid.value = trip.uuid ?? tripUuid.value;
}

function selectStart(stopover: StopoverResource): void {
    router.push({
        name: 'line-run',
        query: {
            ...route.query,
            startId: stopover.id,
            plannedWhen: stopover.departurePlanned ?? stopover.arrivalPlanned ?? undefined,
            originName: stopover.name,
        },
    });
}

function selectStopover(stopover: StopoverResource): void {
    if (isSelectingStart.value) selectStart(stopover);
    else selectDestination(stopover);
}

function selectDestination(stopover: StopoverResource): void {
    router.push({
        name: 'checkin',
        query: {
            tripId: tripId.value,
            lineName: lineName.value,
            start: startId.value,
            destination: stopover.id,
            departure: plannedWhen.value,
            originName: originName.value,
            destinationName: stopover.name,
            destinationArrival: stopover.arrivalPlanned ?? undefined,
            destinationDeparture: stopover.departurePlanned ?? undefined,
            category: tripCategory.value ?? undefined,
            tripUuid: tripUuid.value ?? undefined,
        },
    });
}
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <!-- Step heading -->
            <h2 class="font-semibold text-lg mb-2">
                {{ isSelectingStart ? trans('checkin.select-departure') : trans('checkin.select-exit') }}
            </h2>

            <div class="card bg-base-100">
                <!-- Header -->
                <div class="flex items-center gap-3 px-4 py-3 border-b border-base-300">
                    <button class="btn btn-ghost btn-sm btn-square" @click="handleBack">
                        <ArrowLeft class="w-4 h-4" />
                    </button>

                    <div class="w-5 h-5 flex items-center justify-center text-base-content/60 flex-shrink-0">
                        <TransportIcon :product="iconProduct" :mode="mode" />
                    </div>
                    <LineIndicator
                        :mode="mode"
                        :product-name="product ?? ''"
                        :number="displayLineName"
                        :background-color="color"
                        :color="textColor"
                    />
                    <span class="font-medium text-sm truncate">{{ direction }}</span>
                </div>

                <!-- Stop list: no startId yet lists every stop but the last for
                     picking a departure, otherwise it lists the exits after it -->
                <LineRun
                    v-if="tripId"
                    :trip-id="tripId"
                    :line-name="lineName"
                    :start-id="startId"
                    :planned-when="plannedWhen"
                    @select="selectStopover"
                    @trip="onTrip"
                />
            </div>
        </div>
    </AppLayout>
</template>
