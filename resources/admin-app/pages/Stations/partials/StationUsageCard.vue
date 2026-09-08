<script setup lang="ts">
import { ArrowRightLeft, Trash2 } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { Api, type Station, type StationUsageDto } from '../../../../types/Api.gen';
import StationMoveTargetModal from './StationMoveTargetModal.vue';

const props = defineProps<{ station: Station; nearbyStations: Station[] }>();
const emit = defineEmits<{ changed: [] }>();

const api = new Api({ baseUrl: window.location.origin + '/api' });
const router = useRouter();

const usages = ref<StationUsageDto | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
const deleting = ref(false);

type MovableType = 'stopovers' | 'trips' | 'events' | 'eventSuggestions' | 'routeSegments' | 'homeUsers';

const MOVABLE_TYPES: MovableType[] = ['stopovers', 'trips', 'events', 'eventSuggestions', 'routeSegments', 'homeUsers'];

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

const movableCount = computed(() =>
    usages.value === null ? 0 : MOVABLE_TYPES.reduce((sum, type) => sum + (usages.value![type] ?? 0), 0),
);

const identifiers = computed(() => props.station.identifiers ?? []);

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

// move modal
const moveModalOpen = ref(false);
const moveTypes = ref<MovableType[]>([]);
const moveIdentifiersToo = ref(false);
const moving = ref(false);
const moveError = ref<string | null>(null);
const moveProgress = ref<string | null>(null);

const moveSummaryLabel = computed(() => {
    const parts = moveIdentifiersToo.value ? [usageLabels.identifiers] : [];
    parts.push(...moveTypes.value.map((t) => usageLabels[t] ?? t));
    return parts.join(', ');
});

function openMoveModal(types: MovableType[], includeIdentifiers = false): void {
    moveTypes.value = types;
    moveIdentifiersToo.value = includeIdentifiers;
    moveError.value = null;
    moveProgress.value = null;
    moveModalOpen.value = true;
}

function moveAll(): void {
    // only move types that actually have references, plus identifiers if present
    const types = MOVABLE_TYPES.filter((type) => (usages.value?.[type] ?? 0) > 0);
    openMoveModal(types, identifiers.value.length > 0);
}

async function confirmMove(targetId: number): Promise<void> {
    moving.value = true;
    moveError.value = null;
    moveProgress.value = null;
    try {
        // move identifiers first, one after another, so their linked stopovers/trips/route
        // segments are reassigned before the remaining references get swept over by station id
        if (moveIdentifiersToo.value) {
            const list = identifiers.value;
            for (let i = 0; i < list.length; i++) {
                moveProgress.value = `Moving identifier ${i + 1}/${list.length}...`;
                await api.stations.moveStationIdentifier(props.station.id!, list[i].id!, {
                    target_station_id: targetId,
                });
            }
        }

        if (moveTypes.value.length > 0) {
            moveProgress.value = 'Moving remaining references...';
            await api.stations.moveStationUsages(props.station.id!, {
                target_station_id: targetId,
                types: moveTypes.value,
            });
        }

        moveModalOpen.value = false;
        await fetchUsages();
        emit('changed');
    } catch (e) {
        moveError.value = e instanceof Error ? e.message : 'Move failed';
    } finally {
        moving.value = false;
        moveProgress.value = null;
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
                            <td class="w-10 text-right">
                                <button
                                    v-if="count > 0 && MOVABLE_TYPES.includes(key as MovableType)"
                                    class="btn btn-xs btn-ghost"
                                    title="Move to another station"
                                    @click="openMoveModal([key as MovableType])"
                                >
                                    <ArrowRightLeft class="w-3 h-3" />
                                </button>
                                <span
                                    v-else-if="key === 'identifiers' && count > 0"
                                    class="text-xs text-base-content/40"
                                    title="Move identifiers via the identifiers card"
                                >
                                    ↑
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex items-center gap-2 mt-2 flex-wrap">
                    <button
                        v-if="movableCount > 0 || identifiers.length > 0"
                        class="btn btn-sm"
                        :disabled="moving"
                        @click="moveAll"
                    >
                        <ArrowRightLeft class="w-4 h-4" />
                        Move all to another station
                    </button>
                    <button class="btn btn-sm btn-error" :disabled="!deletable || deleting" @click="deleteStation">
                        <span v-if="deleting" class="loading loading-spinner loading-xs" />
                        <Trash2 v-else class="w-4 h-4" />
                        Delete station
                    </button>
                    <span v-if="!deletable" class="text-xs text-base-content/60">
                        Deleting is only possible when all counts are zero.
                    </span>
                </div>
            </template>
        </div>
    </div>

    <!-- Move references modal -->
    <StationMoveTargetModal
        :open="moveModalOpen"
        title="Move references to another station"
        :station-id="station.id!"
        :nearby-stations="nearbyStations"
        :moving="moving"
        :error="moveError"
        @close="moveModalOpen = false"
        @confirm="confirmMove"
    >
        <template v-if="moveProgress">{{ moveProgress }}</template>
        <template v-else>
            Moving: <span class="font-medium">{{ moveSummaryLabel }}</span>
        </template>
    </StationMoveTargetModal>
</template>
