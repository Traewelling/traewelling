<script setup lang="ts">
import { Locate } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { Api, StationResource } from '../../types/Api.gen';
import router from '../router';
import StationAutocomplete from './StationAutocomplete.vue';

defineProps({
    small: {
        type: Boolean,
        required: false,
        default: false,
    },
});

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const fetchingGps = ref(false);

function selectStation(station: StationResource): void {
    router.push({ name: 'stationboard', query: { stationId: station.id, stationName: station.name } });
}

async function searchByGps(): Promise<void> {
    if (!navigator.geolocation) return;
    fetchingGps.value = true;
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            try {
                const res = await api.trains.trainStationsNearby({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                });
                // The endpoint answers with a single station, the generated type is wider than that
                const station = res.data?.data as unknown as StationResource | undefined;
                if (station) selectStation(station);
            } finally {
                fetchingGps.value = false;
            }
        },
        () => {
            fetchingGps.value = false;
        },
    );
}
</script>

<template>
    <div class="card bg-base-100" :class="{ 'mb-2': small, 'mb-4': !small }">
        <div class="card-body py-3 px-4">
            <div class="relative flex gap-2">
                <div class="flex-1">
                    <StationAutocomplete
                        :model-value="null"
                        :placeholder="trans('stationboard.station-placeholder')"
                        :small="small"
                        with-icon
                        @update:model-value="selectStation"
                    />
                </div>

                <button
                    class="btn btn-square btn-outline"
                    :class="{ loading: fetchingGps, 'btn-sm': small }"
                    :title="trans('stationboard.search-by-location')"
                    :disabled="fetchingGps"
                    @click="searchByGps"
                >
                    <Locate v-if="!fetchingGps" class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
