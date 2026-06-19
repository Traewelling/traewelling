<script setup lang="ts">
import { ExternalLink } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Api, type Station } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const stations = ref<Station[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const query = ref('');
const searched = ref(false);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

async function fetchStations(): Promise<void> {
    if (!query.value.trim()) {
        searched.value = false;
        stations.value = [];
        return;
    }

    loading.value = true;
    error.value = null;
    searched.value = true;
    try {
        const res = await api.stations.indexStation({ query: query.value });
        stations.value = res.data.data ?? [];
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

watch(query, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchStations(), 300);
});
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Stations</h1>
        </div>

        <div class="mb-4">
            <input
                v-model="query"
                type="text"
                placeholder="Search by name or ID..."
                class="input input-bordered input-sm w-full"
            />
        </div>

        <div v-if="error" role="alert" class="alert alert-error mb-4">
            <span>{{ error }}</span>
        </div>

        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="!searched" class="text-center text-base-content/40 py-16 text-sm">
            Enter a station name or ID to search.
        </div>

        <div v-else class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Lat / Lon</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!stations.length">
                            <td colspan="4" class="text-center text-base-content/50 py-8">No stations found.</td>
                        </tr>
                        <tr v-for="station in stations" :key="station.id" class="hover">
                            <td class="font-mono text-xs">{{ station.id }}</td>
                            <td class="font-medium text-sm">{{ station.name }}</td>
                            <td class="text-xs text-base-content/60">
                                {{ station.latitude?.toFixed(4) }}, {{ station.longitude?.toFixed(4) }}
                            </td>
                            <td class="text-right">
                                <a :href="`/admin/stations/${station.id}`" class="btn btn-xs btn-ghost gap-1">
                                    <ExternalLink class="w-3 h-3" />
                                    Details
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </BackendLayout>
</template>
