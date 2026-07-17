<script setup lang="ts">
import { ArrowRightLeft, Pencil, Plus } from '@lucide/vue';
import { ref } from 'vue';
import { Api, type Station, type StationIdentifier, StationIdentifierType } from '../../../../types/Api.gen';
import { IDENTIFIER_TYPES, type IdentifierType } from '../../../../types/StationIdentifier';
import StationMoveTargetModal from './StationMoveTargetModal.vue';

const props = defineProps<{
    stationId: number;
    identifiers: StationIdentifier[];
    nearbyStations: Station[];
}>();

const emit = defineEmits<{ changed: [] }>();

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const moveModalOpen = ref(false);
const movingIdentifier = ref<StationIdentifier | null>(null);
const moving = ref(false);
const moveError = ref<string | null>(null);

function openMoveModal(ident: StationIdentifier): void {
    movingIdentifier.value = ident;
    moveError.value = null;
    moveModalOpen.value = true;
}

function closeMoveModal(): void {
    moveModalOpen.value = false;
    movingIdentifier.value = null;
}

async function confirmMove(targetId: number): Promise<void> {
    if (!movingIdentifier.value) return;
    moving.value = true;
    moveError.value = null;
    try {
        await api.stations.moveStationIdentifier(props.stationId, movingIdentifier.value.id!, {
            target_station_id: targetId,
        });
        closeMoveModal();
        emit('changed');
    } catch (e) {
        moveError.value = e instanceof Error ? e.message : 'Move failed';
    } finally {
        moving.value = false;
    }
}

type FormMode = 'create' | 'edit';

const formModalOpen = ref(false);
const formMode = ref<FormMode>('create');
const editingIdentifier = ref<StationIdentifier | null>(null);
const formType = ref<IdentifierType>('motis');
const formValue = ref('');
const formSaving = ref(false);
const formError = ref<string | null>(null);

function openCreateModal(): void {
    formMode.value = 'create';
    editingIdentifier.value = null;
    formType.value = 'motis';
    formValue.value = '';
    formError.value = null;
    formModalOpen.value = true;
}

function openEditModal(ident: StationIdentifier): void {
    formMode.value = 'edit';
    editingIdentifier.value = ident;
    formType.value = (ident.type ?? 'motis') as IdentifierType;
    formValue.value = ident.identifier ?? '';
    formError.value = null;
    formModalOpen.value = true;
}

function closeFormModal(): void {
    formModalOpen.value = false;
    editingIdentifier.value = null;
}

async function submitForm(): Promise<void> {
    if (!formValue.value.trim()) return;
    formSaving.value = true;
    formError.value = null;
    try {
        const payload = { type: formType.value as StationIdentifierType, identifier: formValue.value.trim() };
        if (formMode.value === 'edit' && editingIdentifier.value) {
            await api.stations.updateStationIdentifier(props.stationId, editingIdentifier.value.id!, payload);
        } else {
            await api.stations.storeStationIdentifier(props.stationId, payload);
        }
        closeFormModal();
        emit('changed');
    } catch (e) {
        formError.value = e instanceof Error ? e.message : 'Save failed';
    } finally {
        formSaving.value = false;
    }
}

function identifierLink(type: string | undefined, value: string | undefined): string | null {
    if (!type || !value) return null;
    switch (type) {
        case 'motis': {
            const params = new URLSearchParams({ stopId: value, n: '50', time: new Date().toISOString() });
            return `https://api.transitous.org/api/v1/stoptimes?${params}`;
        }
        case 'wikidata_id':
            return `https://www.wikidata.org/wiki/${value}`;
        case 'de_db_ril100':
            return `https://iris.noncd.db.de/wbt/js/index.html?bhf=${encodeURIComponent(value)}&zeilen=50&seclang=en`;
        case 'ifopt':
            return `https://transmodel-ids.toolforge.org/ifopt/${encodeURIComponent(value)}`;
        default:
            return null;
    }
}
</script>

<template>
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <div class="flex items-center justify-between mb-1">
                <h2 class="card-title text-base">Identifiers</h2>
                <button class="btn btn-xs btn-ghost gap-1" @click="openCreateModal">
                    <Plus class="w-3 h-3" />
                    Add
                </button>
            </div>

            <table class="table table-sm w-full">
                <colgroup>
                    <col class="w-32" />
                    <col />
                    <col class="w-32" />
                    <col class="w-20" />
                    <col class="w-16" />
                </colgroup>
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
                        <td class="font-mono text-xs align-top">{{ ident.type }}</td>
                        <td class="align-top min-w-0">
                            <a
                                v-if="identifierLink(ident.type, ident.identifier)"
                                :href="identifierLink(ident.type, ident.identifier)!"
                                target="_blank"
                                class="link link-hover font-mono text-xs break-all"
                            >
                                {{ ident.identifier }}
                            </a>
                            <span v-else class="font-mono text-xs break-all">{{ ident.identifier }}</span>
                        </td>
                        <td class="text-sm align-top">{{ ident.name ?? '—' }}</td>
                        <td class="text-xs text-base-content/60 align-top">{{ ident.origin ?? '—' }}</td>
                        <td class="text-right align-top whitespace-nowrap">
                            <button class="btn btn-xs btn-ghost" title="Edit identifier" @click="openEditModal(ident)">
                                <Pencil class="w-3 h-3" />
                            </button>
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

    <!-- Create / edit modal -->
    <div v-if="formModalOpen" class="modal modal-open" role="dialog">
        <div class="modal-box max-w-sm">
            <h3 class="font-bold text-lg mb-4">
                {{ formMode === 'create' ? 'Add Identifier' : 'Edit Identifier' }}
            </h3>

            <div class="form-control mb-3">
                <label class="label pb-1"><span class="label-text">Type</span></label>
                <select v-model="formType" class="select select-sm select-bordered w-full font-mono">
                    <option v-for="t in IDENTIFIER_TYPES" :key="t" :value="t">{{ t }}</option>
                </select>
            </div>

            <div class="form-control mb-4">
                <label class="label pb-1"><span class="label-text">Value</span></label>
                <input
                    v-model="formValue"
                    type="text"
                    placeholder="e.g. de:08212:1"
                    class="input input-sm input-bordered font-mono"
                    @keydown.enter="submitForm"
                />
            </div>

            <div v-if="formError" role="alert" class="alert alert-error alert-sm mb-3 py-2 text-sm">
                {{ formError }}
            </div>

            <div class="modal-action mt-0">
                <button class="btn btn-ghost btn-sm" :disabled="formSaving" @click="closeFormModal">Cancel</button>
                <button class="btn btn-primary btn-sm" :disabled="!formValue.trim() || formSaving" @click="submitForm">
                    <span v-if="formSaving" class="loading loading-spinner loading-xs" />
                    {{ formMode === 'create' ? 'Add' : 'Save' }}
                </button>
            </div>
        </div>
        <div class="modal-backdrop" @click="closeFormModal" />
    </div>

    <!-- Move modal -->
    <StationMoveTargetModal
        :open="moveModalOpen"
        title="Move Identifier"
        :station-id="stationId"
        :nearby-stations="nearbyStations"
        :moving="moving"
        :error="moveError"
        @close="closeMoveModal"
        @confirm="confirmMove"
    >
        Move
        <span class="font-mono">{{ movingIdentifier?.identifier }}</span>
        (<span class="font-mono">{{ movingIdentifier?.type }}</span
        >) to another station.
    </StationMoveTargetModal>
</template>
