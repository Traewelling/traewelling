<script setup lang="ts">
import { Pencil, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { Api, type Station } from '../../../../types/Api.gen';

const props = defineProps<{ station: Station }>();
const emit = defineEmits<{ updated: [station: Station] }>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const editing = ref(false);
const saving = ref(false);
const resetting = ref(false);
const resetSuccess = ref(false);
const saveError = ref<string | null>(null);

const editName = ref('');
const editLat = ref('');
const editLon = ref('');

function openEdit(): void {
    editName.value = props.station.name ?? '';
    editLat.value = String(props.station.latitude ?? '');
    editLon.value = String(props.station.longitude ?? '');
    saveError.value = null;
    editing.value = true;
}

function cancelEdit(): void {
    editing.value = false;
}

async function saveEdit(): Promise<void> {
    saving.value = true;
    saveError.value = null;
    try {
        const res = await api.stations.updateStation(props.station.id!, {
            name: editName.value.trim() || undefined,
            latitude: editLat.value !== '' ? Number(editLat.value) : undefined,
            longitude: editLon.value !== '' ? Number(editLon.value) : undefined,
        });
        emit('updated', res.data.data!);
        editing.value = false;
    } catch (e) {
        saveError.value = e instanceof Error ? e.message : 'Save failed';
    } finally {
        saving.value = false;
    }
}

async function resetTimeOffset(): Promise<void> {
    resetting.value = true;
    resetSuccess.value = false;
    try {
        await api.stations.updateStation(props.station.id!, { time_offset: null });
        emit('updated', { ...props.station, time_offset: null });
        resetSuccess.value = true;
    } catch {
        // ignore
    } finally {
        resetting.value = false;
    }
}
</script>

<template>
    <div class="card bg-base-100 shadow">
        <div class="card-body gap-3">
            <div class="flex items-center justify-between">
                <h2 class="card-title text-base">Details</h2>
                <div class="flex gap-1">
                    <button v-if="!editing" class="btn btn-xs btn-ghost gap-1" @click="openEdit">
                        <Pencil class="w-3 h-3" />
                        Edit
                    </button>
                    <template v-else>
                        <button class="btn btn-xs btn-ghost gap-1" :disabled="saving" @click="cancelEdit">
                            <X class="w-3 h-3" />
                            Cancel
                        </button>
                        <button class="btn btn-xs btn-primary gap-1" :disabled="saving" @click="saveEdit">
                            <span v-if="saving" class="loading loading-spinner loading-xs" />
                            Save
                        </button>
                    </template>
                </div>
            </div>

            <div v-if="saveError" role="alert" class="alert alert-error py-2 text-sm">{{ saveError }}</div>

            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                <dt class="text-base-content/50 font-medium">ID</dt>
                <dd class="font-mono">{{ station.id }}</dd>

                <dt class="text-base-content/50 font-medium">Name</dt>
                <dd>
                    <input
                        v-if="editing"
                        v-model="editName"
                        type="text"
                        class="input input-xs input-bordered w-full"
                        @keydown.enter="saveEdit"
                        @keydown.esc="cancelEdit"
                    />
                    <span v-else>{{ station.name }}</span>
                </dd>

                <dt class="text-base-content/50 font-medium">Lat / Lon</dt>
                <dd>
                    <div v-if="editing" class="flex gap-2">
                        <input
                            v-model="editLat"
                            type="number"
                            step="any"
                            placeholder="Latitude"
                            class="input input-xs input-bordered w-full font-mono"
                            @keydown.esc="cancelEdit"
                        />
                        <input
                            v-model="editLon"
                            type="number"
                            step="any"
                            placeholder="Longitude"
                            class="input input-xs input-bordered w-full font-mono"
                            @keydown.esc="cancelEdit"
                        />
                    </div>
                    <span v-else class="font-mono text-xs">
                        {{ station.latitude?.toFixed(6) }}, {{ station.longitude?.toFixed(6) }}
                    </span>
                </dd>

                <dt class="text-base-content/50 font-medium">Time offset</dt>
                <dd class="flex items-center gap-2">
                    <span class="font-mono">{{ station.time_offset ?? '—' }}</span>
                    <button class="btn btn-xs btn-outline" :disabled="resetting" @click="resetTimeOffset">
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
</template>
