<script setup lang="ts">
import { ArrowRightLeft } from 'lucide-vue-next';
import { ref } from 'vue';
import { Api, type Station, type StationIdentifier } from '../../../../types/Api.gen';

const props = defineProps<{
    stationId: number;
    identifiers: StationIdentifier[];
    nearbyStations: Station[];
}>();

const emit = defineEmits<{ moved: [] }>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const moveModalOpen = ref(false);
const movingIdentifier = ref<StationIdentifier | null>(null);
const moveTargetId = ref('');
const moveSearchQuery = ref('');
const moveSearchResults = ref<Station[]>([]);
const moveSearching = ref(false);
const moving = ref(false);
const moveError = ref<string | null>(null);

function transitousLink(identifier: string): string {
    const params = new URLSearchParams({
        stopId: identifier,
        n: '50',
        time: new Date().toISOString(),
    });
    return `https://api.transitous.org/api/v5/stoptimes?${params}`;
}

function openMoveModal(ident: StationIdentifier): void {
    movingIdentifier.value = ident;
    moveTargetId.value = '';
    moveSearchQuery.value = '';
    moveSearchResults.value = [...props.nearbyStations];
    moveError.value = null;
    moveModalOpen.value = true;
}

function closeMoveModal(): void {
    moveModalOpen.value = false;
    movingIdentifier.value = null;
}

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

async function searchStations(): Promise<void> {
    if (searchDebounceTimer) clearTimeout(searchDebounceTimer);

    const q = moveSearchQuery.value.trim();
    if (!q) {
        moveSearchResults.value = [...props.nearbyStations];
        return;
    }

    searchDebounceTimer = setTimeout(async () => {
        moveSearching.value = true;
        try {
            const res = await api.stations.indexStation({ query: q, limit: 10 });
            moveSearchResults.value = (res.data.data ?? []).filter((s) => s.id !== props.stationId);
        } catch {
            // keep previous results on error
        } finally {
            moveSearching.value = false;
        }
    }, 300);
}

async function confirmMove(): Promise<void> {
    if (!movingIdentifier.value || !moveTargetId.value) return;

    moving.value = true;
    moveError.value = null;
    try {
        const res = await fetch(
            `${window.location.origin}/api/v1/stations/${props.stationId}/identifiers/${movingIdentifier.value.id}/move`,
            {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ target_station_id: Number(moveTargetId.value) }),
            },
        );

        if (!res.ok) {
            const body = await res.json().catch(() => ({}));
            throw new Error(body?.message ?? `HTTP ${res.status}`);
        }

        closeMoveModal();
        emit('moved');
    } catch (e) {
        moveError.value = e instanceof Error ? e.message : 'Move failed';
    } finally {
        moving.value = false;
    }
}
</script>

<template>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ident in identifiers" :key="`${ident.type}-${ident.identifier}`" class="hover">
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
                            <td class="text-right">
                                <button
                                    class="btn btn-xs btn-ghost"
                                    title="Move to another station"
                                    @click="openMoveModal(ident)"
                                >
                                    <ArrowRightLeft class="w-3 h-3" />
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!identifiers.length">
                            <td colspan="5" class="text-center text-base-content/50 py-4">No identifiers.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Move modal -->
    <div v-if="moveModalOpen" class="modal modal-open" role="dialog">
        <div class="modal-box max-w-lg">
            <h3 class="font-bold text-lg mb-1">Move Identifier</h3>
            <p class="text-sm text-base-content/60 mb-4">
                Move
                <span class="font-mono">{{ movingIdentifier?.identifier }}</span>
                (<span class="font-mono">{{ movingIdentifier?.type }}</span
                >) to another station.
            </p>

            <div class="form-control mb-3">
                <label class="label pb-1">
                    <span class="label-text">Search target station</span>
                </label>
                <div class="flex gap-2">
                    <input
                        v-model="moveSearchQuery"
                        type="text"
                        placeholder="Name or ID..."
                        class="input input-sm input-bordered flex-1"
                        @input="searchStations"
                    />
                    <span v-if="moveSearching" class="loading loading-spinner loading-sm self-center" />
                </div>
            </div>

            <div class="overflow-y-auto max-h-64 border border-base-300 rounded-lg mb-4">
                <table class="table table-xs w-full">
                    <thead class="sticky top-0 bg-base-200 z-10">
                        <tr>
                            <th class="w-4"></th>
                            <th>ID</th>
                            <th>Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="s in moveSearchResults"
                            :key="s.id"
                            class="hover cursor-pointer"
                            :class="{ 'bg-primary/10': String(s.id) === String(moveTargetId) }"
                            @click="moveTargetId = String(s.id)"
                        >
                            <td>
                                <input
                                    type="radio"
                                    class="radio radio-xs radio-primary"
                                    :value="String(s.id)"
                                    :checked="String(s.id) === String(moveTargetId)"
                                    @change="moveTargetId = String(s.id)"
                                />
                            </td>
                            <td class="font-mono text-xs">{{ s.id }}</td>
                            <td class="text-sm">{{ s.name }}</td>
                        </tr>
                        <tr v-if="!moveSearchResults.length">
                            <td colspan="3" class="text-center text-base-content/50 py-3">No stations found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="moveError" role="alert" class="alert alert-error alert-sm mb-3 py-2 text-sm">
                {{ moveError }}
            </div>

            <div class="modal-action mt-0">
                <button class="btn btn-ghost btn-sm" :disabled="moving" @click="closeMoveModal">Cancel</button>
                <button class="btn btn-primary btn-sm" :disabled="!moveTargetId || moving" @click="confirmMove">
                    <span v-if="moving" class="loading loading-spinner loading-xs" />
                    Move
                </button>
            </div>
        </div>
        <div class="modal-backdrop" @click="closeMoveModal" />
    </div>
</template>
