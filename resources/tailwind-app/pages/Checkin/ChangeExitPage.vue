<script setup lang="ts">
import { ArrowLeft } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { Notyf } from 'notyf';
import { computed, inject, onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Api, StatusResource, StopoverResource } from '../../../types/Api.gen';
import LineIndicator from '../../../vue/components/LineIndicator.vue';
import LineRun from '../../components/Checkin/LineRun.vue';
import TransportIcon from '../../components/TransportIcon.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const route = useRoute();
const router = useRouter();
const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;

const statusId = computed(() => Number(route.params.id));

const status = ref<StatusResource | null>(null);
const loading = ref(true);
const saving = ref(false);

async function loadStatus(): Promise<void> {
    loading.value = true;
    try {
        const res = await api.status.getSingleStatus(statusId.value);
        status.value = res.data.data as StatusResource;
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        loading.value = false;
    }
}

onMounted(loadStatus);

function handleBack(): void {
    router.back();
}

async function selectExit(stopover: StopoverResource): Promise<void> {
    if (saving.value) return;
    saving.value = true;
    try {
        await api.status.updateSingleStatus(
            {
                destinationId: stopover.id.toString(),
                destinationArrivalPlanned: stopover.arrivalPlanned ?? undefined,
            } as never,
            statusId.value,
        );
        await router.push({ name: 'single-status', params: { id: statusId.value } });
    } catch {
        notyf?.error(trans('generic.error'));
        saving.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <div class="max-w-2xl mx-auto">
            <h2 class="font-semibold text-lg mb-2">{{ trans('checkin.select-exit') }}</h2>

            <div class="card bg-base-100">
                <!-- Header -->
                <div v-if="status" class="flex items-center gap-3 px-4 py-3 border-b border-base-300">
                    <button class="btn btn-ghost btn-sm btn-square" @click="handleBack">
                        <ArrowLeft class="w-4 h-4" />
                    </button>

                    <div class="w-5 h-5 flex items-center justify-center text-base-content/60 flex-shrink-0">
                        <TransportIcon :mode="status.checkin.mode" :product="status.checkin.category" />
                    </div>
                    <LineIndicator
                        :mode="status.checkin.mode"
                        :product-name="status.checkin.category"
                        :number="status.checkin.lineName"
                        :background-color="status.checkin.routeColor || undefined"
                        :color="status.checkin.routeTextColor || undefined"
                    />
                    <span class="font-medium text-sm truncate">{{ status.checkin.origin.name }}</span>
                </div>

                <!-- Stop list -->
                <div v-if="loading || saving" class="flex flex-col gap-2 p-4">
                    <div v-for="n in 8" :key="n" class="skeleton h-9 w-full rounded" />
                </div>
                <LineRun
                    v-else-if="status"
                    :trip-id="status.checkin.trip.toString()"
                    :line-name="status.checkin.lineName"
                    :start-id="status.checkin.origin.id"
                    :planned-when="status.checkin.origin.departurePlanned ?? status.checkin.origin.departureReal"
                    :selected-id="status.checkin.destination.id"
                    :selected-arrival="status.checkin.destination.arrivalPlanned"
                    @select="selectExit"
                />
            </div>
        </div>
    </AppLayout>
</template>
