<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { ArrowLeft, ArrowRight } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { DepartureResource, StopoverResource } from '../../../types/Api.gen';
import CheckinForm from '../../components/Checkin/CheckinForm.vue';
import LineRun from '../../components/Checkin/LineRun.vue';
import TransportIcon from '../../components/TransportIcon.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();

const tripId = computed(() => route.query.tripId as string);
const lineName = computed(() => route.query.lineName as string | undefined);
const startId = computed(() => route.query.start as string);
const destinationId = computed(() => route.query.destination as string | undefined);
const departureTime = computed(() => route.query.departure as string);
const originName = computed(() => route.query.originName as string | undefined);
const category = computed(() => route.query.category as string | undefined);

const departure = computed(
    (): DepartureResource => ({
        tripId: tripId.value,
        plannedWhen: departureTime.value,
        when: null,
        delay: null,
        platform: null,
        plannedPlatform: null,
        direction: '',
        stop: { id: Number(startId.value) },
        line: { name: lineName.value },
    }),
);

const selectedDestination = ref<StopoverResource | null>(null);
const fastCheckinUsed = ref(false);

function handleBack(): void {
    if (selectedDestination.value) {
        selectedDestination.value = null;
        fastCheckinUsed.value = true;
    } else {
        router.back();
    }
}
</script>

<template>
    <AppLayout>
        <div class="max-w-lg mx-auto">
            <!-- Back + step title -->
            <div class="flex items-center gap-2 p-4 border-b border-base-300">
                <button class="btn btn-ghost btn-sm btn-square" @click="handleBack">
                    <ArrowLeft class="w-4 h-4" />
                </button>
                <span class="font-semibold text-sm">
                    {{ selectedDestination ? trans('stationboard.btn-checkin') : trans('stationboard.destination') }}
                </span>
            </div>

            <!-- Trip summary -->
            <div v-if="originName || lineName" class="flex items-center gap-3 px-4 py-3 bg-base-200 text-sm">
                <TransportIcon :product="category" class="flex-shrink-0 opacity-70" />
                <span v-if="lineName" class="badge badge-neutral badge-sm font-mono flex-shrink-0">
                    {{ lineName }}
                </span>
                <span class="truncate text-base-content/70">{{ originName }}</span>
                <template v-if="selectedDestination">
                    <ArrowRight class="w-3 h-3 flex-shrink-0 text-base-content/40" />
                    <span class="truncate text-base-content/70">{{ selectedDestination.name }}</span>
                </template>
            </div>

            <LineRun
                v-if="!selectedDestination"
                :trip-id="tripId"
                :line-name="lineName"
                :start-id="startId"
                :planned-when="departureTime"
                :fast-checkin-id="!fastCheckinUsed && destinationId ? Number(destinationId) : null"
                @select="selectedDestination = $event"
            />

            <CheckinForm
                v-else
                :key="selectedDestination?.id"
                :departure="departure"
                :destination="selectedDestination"
            />
        </div>
    </AppLayout>
</template>
