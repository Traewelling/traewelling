<script setup lang="ts">
import { ref, watch } from 'vue';
import { Api, type Station } from '../../../../types/Api.gen';

const props = defineProps<{
    open: boolean;
    title: string;
    stationId: number;
    nearbyStations: Station[];
    moving: boolean;
    error: string | null;
}>();

const emit = defineEmits<{ close: []; confirm: [targetId: number] }>();

const api = new Api({ baseUrl: window.location.origin + '/api' });

const targetId = ref('');
const searchQuery = ref('');
const searchResults = ref<Station[]>([]);
const searching = ref(false);

watch(
    () => props.open,
    (open) => {
        if (open) {
            targetId.value = '';
            searchQuery.value = '';
            searchResults.value = [...props.nearbyStations];
        }
    },
);

let searchTimer: ReturnType<typeof setTimeout> | null = null;

async function searchStations(): Promise<void> {
    if (searchTimer) clearTimeout(searchTimer);
    const q = searchQuery.value.trim();
    if (!q) {
        searchResults.value = [...props.nearbyStations];
        return;
    }
    searchTimer = setTimeout(async () => {
        searching.value = true;
        try {
            const res = await api.stations.indexStation({ query: q, limit: 10 });
            searchResults.value = (res.data.data ?? []).filter((s) => s.id !== props.stationId);
        } catch {
            // keep previous results on error
        } finally {
            searching.value = false;
        }
    }, 300);
}
</script>

<template>
    <div v-if="open" class="modal modal-open" role="dialog">
        <div class="modal-box max-w-lg">
            <h3 class="font-bold text-lg mb-1">{{ title }}</h3>
            <p class="text-sm text-base-content/60 mb-4"><slot /></p>

            <div class="form-control mb-3">
                <label class="label pb-1"><span class="label-text">Search target station</span></label>
                <div class="flex gap-2">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Name or ID..."
                        class="input input-sm input-bordered flex-1"
                        @input="searchStations"
                    />
                    <span v-if="searching" class="loading loading-spinner loading-sm self-center" />
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
                            v-for="s in searchResults"
                            :key="s.id"
                            class="hover cursor-pointer"
                            :class="{ 'bg-primary/10': String(s.id) === String(targetId) }"
                            @click="targetId = String(s.id)"
                        >
                            <td>
                                <input
                                    type="radio"
                                    class="radio radio-xs radio-primary"
                                    :value="String(s.id)"
                                    :checked="String(s.id) === String(targetId)"
                                    @change="targetId = String(s.id)"
                                />
                            </td>
                            <td class="font-mono text-xs">{{ s.id }}</td>
                            <td class="text-sm">{{ s.name }}</td>
                        </tr>
                        <tr v-if="!searchResults.length">
                            <td colspan="3" class="text-center text-base-content/50 py-3">No stations found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="error" role="alert" class="alert alert-error alert-sm mb-3 py-2 text-sm">
                {{ error }}
            </div>

            <div class="modal-action mt-0">
                <button class="btn btn-ghost btn-sm" :disabled="moving" @click="emit('close')">Cancel</button>
                <button
                    class="btn btn-primary btn-sm"
                    :disabled="!targetId || moving"
                    @click="emit('confirm', Number(targetId))"
                >
                    <span v-if="moving" class="loading loading-spinner loading-xs" />
                    Move
                </button>
            </div>
        </div>
        <div class="modal-backdrop" @click="emit('close')" />
    </div>
</template>
