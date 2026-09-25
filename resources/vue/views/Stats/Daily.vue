<script setup lang="ts">
import { trans, transChoice as trans_choice } from 'laravel-vue-i18n';
import type { GeoJSONFeature } from 'maplibre-gl';
import { computed, onMounted, ref, shallowRef, watch } from 'vue';
import { Api, type StatusResource } from '../../../types/Api.gen';
import LoadingSkeletonRows from '../../components/Loader/LoadingSkeletonRows.vue';
import GenericMap from '../../components/Map/GenericMap.vue';
import StatusCard from '../../components/Status/StatusCard.vue';

const props = defineProps<{
    date: string;
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });

const loading = ref(false);
const errorMsg = ref<string | null>(null);

const statuses = ref<StatusResource[]>([]);
const totalDistance = ref(0); // meter
const totalDurationMin = ref(0);
const totalPoints = ref(0);
const polylines = shallowRef<GeoJSONFeature[]>([]);

async function fetchDaily() {
    loading.value = true;
    errorMsg.value = null;

    try {
        const res = await api.statistics.getDailyStatistics(props.date, { withPolylines: true });
        const data = res.data.data;

        statuses.value = data.statuses ?? [];
        totalDistance.value = data.totalDistance ?? 0;
        totalDurationMin.value = data.totalDuration ?? 0;
        totalPoints.value = data.totalPoints ?? 0;
        const collection = data.polylines as unknown as { features?: GeoJSONFeature[] } | null;
        polylines.value = collection?.features ?? [];
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
    } catch (e: any) {
        errorMsg.value = e?.message || 'Unbekannter Fehler.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchDaily);
watch(() => props.date, fetchDaily);

const tripsText = computed(() => trans_choice('stats.trips', statuses.value.length));

const kmRounded = computed(() => Math.round((totalDistance.value || 0) / 1000));

const durationParts = computed(() => {
    const m = Math.max(0, totalDurationMin.value || 0);
    const days = Math.floor(m / (60 * 24));
    const hrs = Math.floor((m % (60 * 24)) / 60);
    const mins = Math.floor(m % 60);
    return { days, hrs, mins };
});
</script>

<template>
    <div class="row">
        <div class="col-md-6 mb-4">
            <GenericMap :poly-lines="polylines" height="600px" />
        </div>
        <div class="col-md-6">
            <div v-if="errorMsg" class="alert alert-danger my-3">
                {{ errorMsg }}
            </div>

            <div v-if="loading">
                <LoadingSkeletonRows :row-height="30" :columns="4" :rows="1" />
                <LoadingSkeletonRows :row-height="220" />
            </div>

            <div v-else-if="!statuses.length" class="alert alert-warning text-center fs-4">
                {{ trans('no-journeys-day') }}
            </div>

            <div v-else>
                <div id="daily-stats-statsbar" class="row text-center fs-5">
                    <div class="col-6 mb-3 col-lg-3">
                        <i class="fa-solid fa-train" />
                        {{ tripsText }}
                    </div>
                    <div class="col-6 mb-3 col-lg-3">
                        <i class="fa-solid fa-route" />
                        {{ kmRounded }} km
                    </div>
                    <div class="col-6 mb-3 col-lg-3">
                        <i class="fa-regular fa-clock" />
                        {{ durationParts.hrs }}h {{ durationParts.mins }}m
                    </div>
                    <div class="col-6 mb-3 col-lg-3">
                        <i class="fa fa-dice-d20" />
                        {{ totalPoints }} {{ trans('profile.points-abbr') }}
                    </div>
                </div>

                <template v-for="s in statuses" :key="s.id">
                    <StatusCard :status="s" />
                </template>
            </div>
        </div>
    </div>
</template>
