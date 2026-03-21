<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import BackendLayout from '../../../../tailwind-app/layouts/BackendLayout.vue';
import { Api, type Station } from '../../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const route = useRoute();
const stationId = Number(route.params.id);

const station = ref<Station | null>(null);
const nearbyStations = ref<Station[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const resetting = ref(false);
const resetSuccess = ref(false);

function transitousLink(identifier: string): string {
    const params = new URLSearchParams({
        stopId: identifier,
        n: '50',
        time: new Date().toISOString(),
    });
    return `https://api.transitous.org/api/v1/stoptimes?${params}`;
}

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
        const res = await api.stations.showStation(stationId, { withIdentifiers: true });
        station.value = res.data.data;

        const lat = station.value.latitude!;
        const lon = station.value.longitude!;
        const delta = 0.009; // ≈ 1 km

        const nearby = await api.stations.indexStation({
            min_lat: lat - delta,
            max_lat: lat + delta,
            min_lon: lon - delta,
            max_lon: lon + delta,
            limit: 16,
        });
        nearbyStations.value = (nearby.data.data ?? [])
            .filter((s) => s.id !== stationId)
            .sort(
                (a, b) =>
                    haversineMeters(lat, lon, a.latitude!, a.longitude!) -
                    haversineMeters(lat, lon, b.latitude!, b.longitude!),
            )
            .slice(0, 15);
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function resetTimeOffset(): Promise<void> {
    if (!station.value) return;
    resetting.value = true;
    resetSuccess.value = false;
    try {
        await api.stations.updateStation(stationId, { time_offset: null });
        station.value = { ...station.value, time_offset: null };
        resetSuccess.value = true;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Reset failed';
    } finally {
        resetting.value = false;
    }
}

onMounted(fetchStation);
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
                <router-link to="/admin/stations" class="btn btn-ghost btn-sm">← Stations</router-link>
                <h1 class="text-2xl font-bold">{{ station.name }}</h1>
                <span class="font-mono text-sm text-base-content/50">#{{ station.id }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left column -->
                <div class="space-y-4">
                    <!-- Station details -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-3">
                            <h2 class="card-title text-base">Details</h2>

                            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                <dt class="text-base-content/50 font-medium">ID</dt>
                                <dd class="font-mono">{{ station.id }}</dd>

                                <dt class="text-base-content/50 font-medium">Name</dt>
                                <dd>{{ station.name }}</dd>

                                <dt class="text-base-content/50 font-medium">Lat / Lon</dt>
                                <dd class="font-mono text-xs">
                                    {{ station.latitude?.toFixed(6) }}, {{ station.longitude?.toFixed(6) }}
                                </dd>

                                <dt class="text-base-content/50 font-medium">Time offset</dt>
                                <dd class="flex items-center gap-2">
                                    <span class="font-mono">{{ station.time_offset ?? '—' }}</span>
                                    <button
                                        class="btn btn-xs btn-outline"
                                        :disabled="resetting"
                                        @click="resetTimeOffset"
                                    >
                                        <span v-if="resetting" class="loading loading-spinner loading-xs" />
                                        Reset
                                    </button>
                                    <span v-if="resetSuccess" class="text-xs text-success">Done</span>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Created</dt>
                                <dd class="text-xs text-base-content/70">
                                    {{ station.created_at ? new Date(station.created_at).toLocaleString() : '—' }}
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Identifiers -->
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Identifiers</h2>

                            <div class="overflow-x-auto">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Identifier</th>
                                            <th>Name</th>
                                            <th>Origin</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="ident in station.identifiers"
                                            :key="`${ident.type}-${ident.identifier}`"
                                            class="hover"
                                        >
                                            <td class="font-mono text-xs">{{ ident.type }}</td>
                                            <td>
                                                <a
                                                    :href="transitousLink(ident.identifier)"
                                                    target="_blank"
                                                    class="link link-hover font-mono text-xs"
                                                >
                                                    {{ ident.identifier }}
                                                </a>
                                            </td>
                                            <td class="text-sm">{{ ident.name ?? '—' }}</td>
                                            <td class="text-xs text-base-content/60">{{ ident.origin ?? '—' }}</td>
                                        </tr>
                                        <tr v-if="!station.identifiers?.length">
                                            <td colspan="4" class="text-center text-base-content/50 py-4">
                                                No identifiers.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right column -->
                <div class="space-y-4">
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
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="nearby in nearbyStations" :key="nearby.id" class="hover">
                                            <td class="font-mono text-xs">{{ nearby.id }}</td>
                                            <td class="text-sm">{{ nearby.name }}</td>
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
                                            <td class="text-right">
                                                <router-link
                                                    :to="`/admin/stations/${nearby.id}`"
                                                    class="btn btn-xs btn-ghost"
                                                >
                                                    →
                                                </router-link>
                                            </td>
                                        </tr>
                                        <tr v-if="!nearbyStations.length">
                                            <td colspan="4" class="text-center text-base-content/50 py-4">
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
