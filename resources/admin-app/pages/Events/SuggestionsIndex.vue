<script setup lang="ts">
import { Check, X } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { Api, type EventSuggestionResource } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const suggestions = ref<EventSuggestionResource[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const nextCursor = ref<string | null>(null);
const prevCursor = ref<string | null>(null);

const denyModal = ref(false);
const denyTarget = ref<EventSuggestionResource | null>(null);
const denyReason = ref<'denied' | 'too-late' | 'duplicate' | 'not-applicable' | 'missing-information'>('denied');
const denyingId = ref<number | null>(null);

async function fetchSuggestions(cursor?: string): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminEventSuggestions({ cursor });
        suggestions.value = res.data?.data ?? [];
        const meta = (res.data as unknown as { meta?: { next_cursor?: string; prev_cursor?: string } }).meta;
        nextCursor.value = meta?.next_cursor ?? null;
        prevCursor.value = meta?.prev_cursor ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

function openDenyModal(suggestion: EventSuggestionResource): void {
    denyTarget.value = suggestion;
    denyReason.value = 'denied';
    denyModal.value = true;
}

function closeDenyModal(): void {
    denyModal.value = false;
    denyTarget.value = null;
}

async function confirmDeny(): Promise<void> {
    if (!denyTarget.value?.id) return;

    denyingId.value = denyTarget.value.id;
    try {
        await api.admin.denyAdminEventSuggestion(denyTarget.value.id, { reason: denyReason.value });
        closeDenyModal();
        await fetchSuggestions();
    } catch (e) {
        window.alert(`Deny failed: ${e instanceof Error ? e.message : 'Unknown error'}`);
    } finally {
        denyingId.value = null;
    }
}

onMounted(() => fetchSuggestions());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Event Suggestions</h1>
            <router-link to="/admin/events" class="btn btn-ghost btn-sm">← Events</router-link>
        </div>

        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="error" role="alert" class="alert alert-error">
            <span>{{ error }}</span>
        </div>

        <div v-else class="card bg-base-100 shadow">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Suggested by</th>
                            <th>Station</th>
                            <th>From</th>
                            <th>Until</th>
                            <th>Submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!suggestions.length">
                            <td colspan="7" class="text-center text-base-content/50 py-8">No open suggestions.</td>
                        </tr>
                        <tr v-for="suggestion in suggestions" :key="suggestion.id" class="hover">
                            <td class="font-medium text-sm">{{ suggestion.name }}</td>
                            <td class="text-xs text-base-content/60">
                                {{ suggestion.user?.username ?? '' }}
                            </td>
                            <td class="text-xs text-base-content/60">
                                {{ suggestion.station?.name ?? '—' }}
                            </td>
                            <td class="text-xs whitespace-nowrap">{{ suggestion.begin }}</td>
                            <td class="text-xs whitespace-nowrap">{{ suggestion.end }}</td>
                            <td class="text-xs text-base-content/60 whitespace-nowrap">
                                {{ suggestion.created_at ? new Date(suggestion.created_at).toLocaleDateString() : '—' }}
                            </td>
                            <td class="text-right">
                                <div class="flex gap-1 justify-end">
                                    <router-link
                                        :to="`/admin/event-suggestions/${suggestion.id}/accept`"
                                        class="btn btn-xs btn-success gap-1"
                                    >
                                        <Check class="w-3 h-3" />
                                        Accept
                                    </router-link>
                                    <button
                                        class="btn btn-xs btn-outline btn-error gap-1"
                                        :disabled="denyingId === suggestion.id"
                                        @click="openDenyModal(suggestion)"
                                    >
                                        <span
                                            v-if="denyingId === suggestion.id"
                                            class="loading loading-spinner loading-xs"
                                        />
                                        <X v-else class="w-3 h-3" />
                                        Deny
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="prevCursor || nextCursor" class="flex justify-center gap-2 mt-4">
            <button
                class="btn btn-sm btn-ghost"
                :disabled="!prevCursor"
                @click="fetchSuggestions(prevCursor ?? undefined)"
            >
                ← Previous
            </button>
            <button
                class="btn btn-sm btn-ghost"
                :disabled="!nextCursor"
                @click="fetchSuggestions(nextCursor ?? undefined)"
            >
                Next →
            </button>
        </div>

        <!-- Deny Modal -->
        <dialog class="modal" :class="{ 'modal-open': denyModal }">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Deny Suggestion</h3>
                <p class="text-sm mb-4">
                    Deny <span class="font-semibold">{{ denyTarget?.name }}</span
                    >?
                </p>
                <fieldset class="fieldset mb-4">
                    <legend class="fieldset-legend text-xs">Reason</legend>
                    <select v-model="denyReason" class="select select-sm w-full">
                        <option value="denied">Denied (general)</option>
                        <option value="too-late">Too late</option>
                        <option value="duplicate">Duplicate</option>
                        <option value="not-applicable">Not applicable</option>
                        <option value="missing-information">Missing information</option>
                    </select>
                </fieldset>
                <div class="modal-action">
                    <button class="btn btn-ghost" @click="closeDenyModal">Cancel</button>
                    <button class="btn btn-error" :disabled="denyingId !== null" @click="confirmDeny">
                        <span v-if="denyingId !== null" class="loading loading-spinner loading-sm" />
                        Deny
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="closeDenyModal" />
        </dialog>
    </BackendLayout>
</template>
