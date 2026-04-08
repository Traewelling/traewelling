<script setup lang="ts">
import { ArrowRightLeft, ShieldX, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { Api, type OperatorResource } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const operators = ref<OperatorResource[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const query = ref('');
const nextCursor = ref<string | null>(null);

// Merge state
const mergeSource = ref<OperatorResource | null>(null); // will be deleted
const mergeTarget = ref<OperatorResource | null>(null); // will survive
const merging = ref(false);
const mergeError = ref<string | null>(null);
const mergeSuccess = ref(false);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

async function fetchOperators(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.operators.getOperators({ query: query.value || undefined, cursor });
        const meta = (res.data as unknown as { meta?: { next_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;

        if (cursor) {
            operators.value = [...operators.value, ...(res.data.data ?? [])];
        } else {
            operators.value = res.data.data ?? [];
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

watch(query, () => {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchOperators(), 300);
});

fetchOperators();

function copyMotisToClipboard(identifier: string, name: string | null | undefined): void {
    const text = identifier + ',"' + (name ?? '') + '",';
    navigator.clipboard.writeText(text);
}

function setMergeSource(operator: OperatorResource): void {
    if (mergeTarget.value?.uuid === operator.uuid) {
        mergeTarget.value = null;
    }
    mergeSource.value = operator;
    mergeSuccess.value = false;
    mergeError.value = null;
}

function setMergeTarget(operator: OperatorResource): void {
    if (mergeSource.value?.uuid === operator.uuid) {
        mergeSource.value = null;
    }
    mergeTarget.value = operator;
    mergeSuccess.value = false;
    mergeError.value = null;
}

function clearMerge(): void {
    mergeSource.value = null;
    mergeTarget.value = null;
    mergeError.value = null;
    mergeSuccess.value = false;
}

async function submitMerge(): Promise<void> {
    if (!mergeSource.value || !mergeTarget.value) return;

    merging.value = true;
    mergeError.value = null;
    mergeSuccess.value = false;

    try {
        await api.operators.mergeOperators(mergeSource.value.uuid, mergeTarget.value.uuid);
        mergeSuccess.value = true;
        operators.value = operators.value.filter((o) => o.uuid !== mergeSource.value!.uuid);
        mergeSource.value = null;
        mergeTarget.value = null;
    } catch (e) {
        mergeError.value = e instanceof Error ? e.message : 'Merge failed.';
    } finally {
        merging.value = false;
    }
}

function rowClass(operator: OperatorResource): string {
    if (operator.uuid === mergeSource.value?.uuid) return 'bg-error/10';
    if (operator.uuid === mergeTarget.value?.uuid) return 'bg-success/10';
    return 'hover';
}
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Operators</h1>
        </div>

        <!-- Merge panel -->
        <div v-if="mergeSource || mergeTarget" class="card bg-base-100 shadow mb-4 border border-warning/30">
            <div class="card-body gap-3 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold flex items-center gap-2">
                        <ArrowRightLeft class="w-4 h-4" />
                        Merge operators
                    </h2>
                    <button class="btn btn-xs btn-ghost" @click="clearMerge">Clear</button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] gap-3 items-center">
                    <!-- Source (will be deleted) -->
                    <div
                        class="rounded-lg border-2 border-dashed p-3 min-h-16 flex flex-col gap-1"
                        :class="mergeSource ? 'border-error bg-error/5' : 'border-base-300'"
                    >
                        <div class="text-xs font-medium text-error uppercase tracking-wide flex items-center gap-1">
                            <Trash2 class="w-3 h-3" />
                            Remove (will be deleted)
                        </div>
                        <template v-if="mergeSource">
                            <div class="font-medium text-sm">{{ mergeSource.name }}</div>
                            <div class="font-mono text-xs text-base-content/50">{{ mergeSource.uuid }}</div>
                        </template>
                        <div v-else class="text-xs text-base-content/40 mt-1">
                            Click <ShieldX class="w-3 h-3 inline" /> on a row to select
                        </div>
                    </div>

                    <div class="text-center text-base-content/40">
                        <ArrowRightLeft class="w-5 h-5 mx-auto" />
                    </div>

                    <!-- Target (will survive) -->
                    <div
                        class="rounded-lg border-2 border-dashed p-3 min-h-16 flex flex-col gap-1"
                        :class="mergeTarget ? 'border-success bg-success/5' : 'border-base-300'"
                    >
                        <div class="text-xs font-medium text-success uppercase tracking-wide flex items-center gap-1">
                            <ArrowRightLeft class="w-3 h-3" />
                            Keep (will survive)
                        </div>
                        <template v-if="mergeTarget">
                            <div class="font-medium text-sm">{{ mergeTarget.name }}</div>
                            <div class="font-mono text-xs text-base-content/50">{{ mergeTarget.uuid }}</div>
                        </template>
                        <div v-else class="text-xs text-base-content/40 mt-1">
                            Click <ArrowRightLeft class="w-3 h-3 inline" /> on a row to select
                        </div>
                    </div>
                </div>

                <div v-if="mergeError" role="alert" class="alert alert-error py-2 text-sm">
                    {{ mergeError }}
                </div>
                <div v-if="mergeSuccess" role="alert" class="alert alert-success py-2 text-sm">
                    Operators merged successfully.
                </div>

                <div class="flex justify-end">
                    <button
                        class="btn btn-warning btn-sm gap-2"
                        :disabled="!mergeSource || !mergeTarget || merging"
                        @click="submitMerge"
                    >
                        <span v-if="merging" class="loading loading-spinner loading-xs" />
                        <ArrowRightLeft v-else class="w-4 h-4" />
                        Merge
                    </button>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="mb-4">
            <input
                v-model="query"
                type="text"
                placeholder="Search operators by name..."
                class="input input-bordered input-sm w-full"
            />
        </div>

        <div v-if="error" role="alert" class="alert alert-error mb-4">
            <span>{{ error }}</span>
        </div>

        <div v-if="loading && !operators.length" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>UUID</th>
                            <th>Identifiers</th>
                            <th class="text-right">Merge</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!operators.length && !loading">
                            <td colspan="5" class="text-center text-base-content/50 py-8">No operators found.</td>
                        </tr>
                        <tr v-for="operator in operators" :key="operator.uuid" :class="rowClass(operator)">
                            <td class="font-medium text-sm">{{ operator.name }}</td>
                            <td class="font-mono text-xs text-base-content/50">{{ operator.uuid }}</td>
                            <td class="max-w-xs">
                                <div class="flex flex-wrap gap-1">
                                    <template v-for="id in operator.identifiers ?? []" :key="id.identifier">
                                        <button
                                            v-if="id.type === 'motis'"
                                            class="badge badge-info badge-sm font-mono cursor-pointer hover:opacity-80"
                                            :title="`Copy to clipboard: ${id.identifier}`"
                                            @click="copyMotisToClipboard(id.identifier!, id.name)"
                                        >
                                            {{ id.identifier }}{{ id.name ? ` (${id.name})` : '' }}
                                        </button>
                                        <span
                                            v-else-if="id.type === 'hafas'"
                                            class="badge badge-error badge-sm font-mono"
                                        >
                                            {{ id.identifier }}
                                        </span>
                                        <a
                                            v-else-if="id.type === 'wikidata'"
                                            :href="`https://www.wikidata.org/wiki/${id.identifier}`"
                                            target="_blank"
                                            class="badge badge-ghost badge-sm font-mono hover:opacity-80"
                                        >
                                            {{ id.identifier }}
                                        </a>
                                        <span v-else class="badge badge-ghost badge-sm font-mono">
                                            {{ id.type }}: {{ id.identifier }}
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        class="btn btn-xs gap-1"
                                        :class="mergeSource?.uuid === operator.uuid ? 'btn-error' : 'btn-ghost'"
                                        title="Set as source (will be deleted)"
                                        @click="setMergeSource(operator)"
                                    >
                                        <Trash2 class="w-3 h-3" />
                                    </button>
                                    <button
                                        class="btn btn-xs gap-1"
                                        :class="mergeTarget?.uuid === operator.uuid ? 'btn-success' : 'btn-ghost'"
                                        title="Set as merge target (will survive)"
                                        @click="setMergeTarget(operator)"
                                    >
                                        <ArrowRightLeft class="w-3 h-3" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Load more -->
            <div v-if="nextCursor || loading" class="flex justify-center p-4">
                <button
                    v-if="nextCursor"
                    class="btn btn-sm btn-ghost"
                    :disabled="loading"
                    @click="fetchOperators(nextCursor)"
                >
                    <span v-if="loading" class="loading loading-spinner loading-xs" />
                    Load more
                </button>
                <span v-else-if="loading" class="loading loading-spinner loading-sm" />
            </div>
        </div>
    </BackendLayout>
</template>
