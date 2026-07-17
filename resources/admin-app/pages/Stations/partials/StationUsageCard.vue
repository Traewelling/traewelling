<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { Api, type Station, type StationUsageDto } from '../../../../types/Api.gen';

const props = defineProps<{ station: Station }>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const router = useRouter();

const usages = ref<StationUsageDto | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
const deleting = ref(false);

const usageLabels: Record<string, string> = {
    stopovers: 'Stopovers',
    trips: 'Trips (origin/destination)',
    events: 'Events',
    eventSuggestions: 'Event suggestions',
    identifiers: 'Identifiers',
    routeSegments: 'Route segments',
    homeUsers: 'Users with home station',
};

const deletable = computed(() => usages.value !== null && Object.values(usages.value).every((count) => count === 0));

async function fetchUsages(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.stations.getStationUsages(props.station.id!);
        usages.value = res.data.data ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to load usages';
    } finally {
        loading.value = false;
    }
}

async function deleteStation(): Promise<void> {
    if (!confirm(`Delete station "${props.station.name}"?`)) return;
    deleting.value = true;
    error.value = null;
    try {
        await api.stations.deleteStation(props.station.id!);
        await router.push('/admin/stations');
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to delete station';
        deleting.value = false;
        await fetchUsages();
    }
}

onMounted(fetchUsages);
watch(() => props.station, fetchUsages);
</script>

<template>
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title text-base">Usage</h2>

            <div v-if="loading" class="flex justify-center py-4">
                <span class="loading loading-spinner loading-md" />
            </div>

            <div v-else-if="error" role="alert" class="alert alert-error py-2 text-sm">
                {{ error }}
            </div>

            <template v-else-if="usages">
                <table class="table table-sm">
                    <tbody>
                        <tr v-for="(count, key) in usages" :key="key">
                            <td class="text-sm">{{ usageLabels[key] ?? key }}</td>
                            <td class="text-right">
                                <span
                                    class="badge badge-sm tabular-nums"
                                    :class="count === 0 ? 'badge-success' : 'badge-warning'"
                                >
                                    {{ count.toLocaleString() }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex items-center gap-3 mt-2">
                    <button class="btn btn-sm btn-error" :disabled="!deletable || deleting" @click="deleteStation">
                        <span v-if="deleting" class="loading loading-spinner loading-xs" />
                        <Trash2 v-else class="w-4 h-4" />
                        Delete station
                    </button>
                    <span v-if="!deletable" class="text-xs text-base-content/60">
                        Only possible when all counts are zero.
                    </span>
                </div>
            </template>
        </div>
    </div>
</template>
