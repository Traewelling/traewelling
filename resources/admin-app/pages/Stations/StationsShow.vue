<script setup lang="ts">
import { ArrowLeft } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { Api, type Station, type StationResource } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';
import StationCoordinatesMap from './partials/StationCoordinatesMap.vue';
import StationDetailsCard from './partials/StationDetailsCard.vue';
import StationIdentifiersCard from './partials/StationIdentifiersCard.vue';
import StationUsageCard from './partials/StationUsageCard.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const route = useRoute();
const stationId = computed(() => Number(route.params.id));

const station = ref<Station | null>(null);
const nearbyStations = ref<StationResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

function haversineMeters(lat1: number, lon1: number, lat2: number, lon2: number): number {
    const R = 6_371_000;
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLon = ((lon2 - lon1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos((lat1 * Math.PI) / 180) * Math.cos((lat2 * Math.PI) / 180) * Math.sin(dLon / 2) ** 2;
    return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
}

async function fetchStation(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.stations.showStation(stationId.value, { withIdentifiers: true });
        station.value = res.data.data;

        const lat = station.value.latitude!;
        const lon = station.value.longitude!;
        const delta = 0.009; // ≈ 1 km

        const nearby = await api.stations.indexStation({
            min_lat: lat - delta,
            max_lat: lat + delta,
            min_lon: lon - delta,
            max_lon: lon + delta,
            limit: 51,
            withIdentifiers: true,
        });
        nearbyStations.value = (nearby.data.data ?? [])
            .filter((s) => s.id !== stationId.value)
            .sort(
                (a, b) =>
                    haversineMeters(lat, lon, a.latitude!, a.longitude!) -
                    haversineMeters(lat, lon, b.latitude!, b.longitude!),
            )
            .slice(0, 50);
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

function onStationUpdated(updated: Station): void {
    station.value = { ...updated, identifiers: station.value?.identifiers };
}

onMounted(fetchStation);
watch(stationId, fetchStation);
</script>

<template>
    <BackendLayout>
        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="error" role="alert" class="alert alert-error">
            <span>{{ error }}</span>
        </div>

        <template v-else-if="station">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <router-link to="/admin/stations" class="btn btn-ghost btn-sm gap-1">
                    <ArrowLeft class="w-4 h-4" />
                    Stations
                </router-link>
                <h1 class="text-2xl font-bold">{{ station.name }}</h1>
                <span class="font-mono text-sm text-base-content/50">#{{ station.id }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left column -->
                <div class="space-y-4">
                    <StationDetailsCard :station="station" @updated="onStationUpdated" />

                    <StationIdentifiersCard
                        :station-id="stationId"
                        :identifiers="station.identifiers ?? []"
                        :nearby-stations="nearbyStations"
                        @changed="fetchStation"
                    />

                    <StationCoordinatesMap :station="station" :identifiers="station.identifiers ?? []" />
                </div>

                <!-- Right column -->
                <div class="space-y-4">
                    <StationUsageCard :station="station" />

                    <!-- Nearby stations -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Nearby Stations</h2>

                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Distance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="nearby in nearbyStations" :key="nearby.id" class="hover">
                                            <td class="font-mono text-xs">{{ nearby.id }}</td>
                                            <td class="text-sm">
                                                <router-link
                                                    :to="`/admin/stations/${nearby.id}`"
                                                    class="link link-hover"
                                                >
                                                    {{ nearby.name }}
                                                </router-link>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    <span
                                                        v-for="ident in nearby.identifiers.filter(
                                                            (i) => i.type === 'ifopt',
                                                        )"
                                                        :key="ident.id"
                                                        class="badge badge-ghost badge-sm font-mono"
                                                    >
                                                        {{ ident.identifier }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="text-xs text-base-content/60 tabular-nums">
                                                {{
                                                    haversineMeters(
                                                        station.latitude!,
                                                        station.longitude!,
                                                        nearby.latitude!,
                                                        nearby.longitude!,
                                                    ).toLocaleString()
                                                }}
                                                m
                                            </td>
                                        </tr>
                                        <tr v-if="!nearbyStations.length">
                                            <td colspan="3" class="text-center text-base-content/50 py-4">
                                                No nearby stations found.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </BackendLayout>
</template>
