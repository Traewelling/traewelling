<script setup lang="ts">
import { ArrowLeft, ArrowRight } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { HafasTravelType, StopoverResource } from '../../../types/Api.gen';
import CheckinForm from '../../components/Checkin/CheckinForm.vue';
import TransportIcon from '../../components/TransportIcon.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();

const tripId = computed(() => route.query.tripId as string);
const lineName = computed(() => (route.query.lineName as string | undefined) ?? undefined);
const startId = computed(() => route.query.start as string);
const destinationId = computed(() => route.query.destination as string);
const departureTime = computed(() => route.query.departure as string);
const originName = computed(() => (route.query.originName as string | undefined) ?? undefined);
const destinationName = computed(() => (route.query.destinationName as string | undefined) ?? undefined);
const destinationArrival = computed(() => (route.query.destinationArrival as string | undefined) ?? null);
const destinationDeparture = computed(() => (route.query.destinationDeparture as string | undefined) ?? null);
const category = computed(() => (route.query.category as HafasTravelType | undefined) ?? undefined);
const tripUuid = computed(() => (route.query.tripUuid as string | undefined) ?? null);

const destination = computed(
    (): StopoverResource =>
        ({
            id: Number(destinationId.value),
            name: destinationName.value ?? '',
            arrivalPlanned: destinationArrival.value,
            departurePlanned: destinationDeparture.value,
        }) as StopoverResource,
);

function handleBack(): void {
    router.back();
}
</script>

<template>
    <AppLayout>
        <div class="max-w-lg mx-auto">
            <div class="card bg-base-100">
                <!-- Back + title -->
                <div class="flex items-center gap-2 p-4 border-b border-base-300">
                    <button class="btn btn-ghost btn-sm btn-square" @click="handleBack">
                        <ArrowLeft class="w-4 h-4" />
                    </button>
                    <span class="font-semibold text-sm">{{ trans('stationboard.btn-checkin') }}</span>
                </div>

                <!-- Trip summary -->
                <div v-if="originName || lineName" class="flex items-center gap-3 px-4 py-3 bg-base-200 text-sm">
                    <TransportIcon :product="category" class="flex-shrink-0 opacity-70" />
                    <span v-if="lineName" class="badge badge-neutral badge-sm font-mono flex-shrink-0">
                        {{ lineName }}
                    </span>
                    <span class="truncate text-base-content/70">{{ originName }}</span>
                    <ArrowRight class="w-3 h-3 flex-shrink-0 text-base-content/40" />
                    <span class="truncate text-base-content/70">{{ destinationName }}</span>
                </div>

                <!-- Checkin form -->
                <CheckinForm
                    :planned-when="departureTime"
                    :line-name="lineName ?? ''"
                    :stop-id="Number(startId)"
                    :trip-id="tripId"
                    :destination="destination"
                    :trip-uuid="tripUuid"
                />
            </div>
        </div>
    </AppLayout>
</template>
