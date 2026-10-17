<script setup lang="ts">
import { ArrowLeft, ArrowRight } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { HafasTravelType, MotisCategory, StopoverResource } from '../../../types/Api.gen';
import LineIndicator from '../../../vue/components/LineIndicator.vue';
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
const product = computed(() => (route.query.product as string | undefined) ?? null);
const mode = computed(() => (route.query.mode as MotisCategory | undefined) ?? null);
const color = computed(() => route.query.color as string | undefined);
const textColor = computed(() => route.query.textColor as string | undefined);

function isNumericId(value: string | undefined): boolean {
    return !!value && !Number.isNaN(Number(value));
}

const hasRequiredQuery = computed(
    () => !!tripId.value && !!departureTime.value && isNumericId(startId.value) && isNumericId(destinationId.value),
);

const iconProduct = computed(() => product.value ?? category.value ?? undefined);

const displayLineName = computed(() => (lineName.value ?? '').replaceAll(/\(.*?\)/g, '').trim());

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

onMounted(() => {
    if (!hasRequiredQuery.value) {
        router.replace({ name: 'dashboard' });
    }
});
</script>

<template>
    <AppLayout>
        <div v-if="hasRequiredQuery" class="max-w-2xl mx-auto">
            <h2 class="font-semibold text-lg mb-2">{{ trans('stationboard.btn-checkin') }}</h2>

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
                        :product-name="iconProduct ?? ''"
                        :number="displayLineName"
                        :background-color="color"
                        :color="textColor"
                    />
                    <span class="font-medium text-sm truncate">{{ originName }}</span>
                    <ArrowRight class="w-3 h-3 flex-shrink-0 text-base-content/40" />
                    <span class="font-medium text-sm truncate">{{ destinationName }}</span>
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
