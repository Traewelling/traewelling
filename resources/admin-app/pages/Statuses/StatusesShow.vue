<script setup lang="ts">
import { ArrowLeft, Save } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { Api, type AdminStatusResource } from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const route = useRoute();

const statusId = Number(route.params.id);

const status = ref<AdminStatusResource | null>(null);
const loading = ref(true);
const saving = ref(false);
const error = ref<string | null>(null);
const successMessage = ref<string | null>(null);

const form = ref({
    origin: 0,
    destination: 0,
    body: '',
    visibility: 0,
    business: 0,
    event_id: null as number | null,
    points: null as number | null,
    moderation_notes: '',
    lock_visibility: false,
    hide_body: false,
});

async function fetchStatus(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminStatus(statusId);
        status.value = res.data.data ?? null;
        if (status.value) {
            form.value = {
                origin: status.value.checkin?.origin_station_id ?? 0,
                destination: status.value.checkin?.destination_station_id ?? 0,
                body: status.value.body ?? '',
                visibility: status.value.visibility ?? 0,
                business: status.value.business ?? 0,
                event_id: status.value.event_id ?? null,
                points: status.value.checkin?.points ?? null,
                moderation_notes: status.value.moderation_notes ?? '',
                lock_visibility: status.value.lock_visibility ?? false,
                hide_body: status.value.hide_body ?? false,
            };
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function save(): Promise<void> {
    saving.value = true;
    successMessage.value = null;
    error.value = null;
    try {
        const res = await api.admin.updateAdminStatus(statusId, {
            origin: form.value.origin,
            destination: form.value.destination,
            body: form.value.body || null,
            visibility: form.value.visibility,
            business: form.value.business,
            event_id: form.value.event_id,
            points: form.value.points,
            moderation_notes: form.value.moderation_notes || null,
            lock_visibility: form.value.lock_visibility,
            hide_body: form.value.hide_body,
        });
        status.value = res.data.data ?? null;
        successMessage.value = 'Status updated successfully.';
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        saving.value = false;
    }
}

function visibilityOptions(): { value: number; label: string }[] {
    return [
        { value: 0, label: 'Public' },
        { value: 1, label: 'Unlisted' },
        { value: 2, label: 'Followers' },
        { value: 3, label: 'Private' },
        { value: 4, label: 'Authenticated' },
        { value: 5, label: 'Trusted' },
    ];
}

function businessOptions(): { value: number; label: string }[] {
    return [
        { value: 0, label: 'Private' },
        { value: 1, label: 'Business' },
        { value: 2, label: 'Commute' },
    ];
}

function formatDate(iso: string | null | undefined): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleString();
}

onMounted(() => fetchStatus());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center gap-3 mb-6">
            <router-link to="/admin/statuses" class="btn btn-ghost btn-sm">
                <ArrowLeft class="w-4 h-4" />
                Statuses
            </router-link>
            <h1 class="text-2xl font-bold">Status #{{ statusId }}</h1>
            <a v-if="status" :href="`/status/${statusId}`" target="_blank" class="btn btn-ghost btn-sm ml-auto text-xs">
                View in frontend ↗
            </a>
        </div>

        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="!status && error" role="alert" class="alert alert-error">
            <span>{{ error }}</span>
        </div>

        <template v-else-if="status">
            <div v-if="successMessage" role="alert" class="alert alert-success mb-4">
                <span>{{ successMessage }}</span>
            </div>
            <div v-if="error" role="alert" class="alert alert-error mb-4">
                <span>{{ error }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Details card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-base">Details</h2>

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-base-content/60">User</span>
                                <span class="font-medium"
                                    >{{ status.user?.name }}
                                    <span class="text-base-content/50">@{{ status.user?.username }}</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Trip</span>
                                <span>{{ status.checkin?.linename ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Departure</span>
                                <span>{{ formatDate(status.checkin?.departure) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Arrival</span>
                                <span>{{ formatDate(status.checkin?.arrival) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Distance</span>
                                <span>{{
                                    status.checkin?.distance ? (status.checkin.distance / 1000).toFixed(1) + ' km' : '—'
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Created</span>
                                <span>{{ formatDate(status.created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-base-content/60">Updated</span>
                                <span>{{ formatDate(status.updated_at) }}</span>
                            </div>
                            <div v-if="status.checkin?.trip_id" class="flex justify-between">
                                <span class="text-base-content/60">Trip ID</span>
                                <a :href="`/admin/trips/${status.checkin.trip_id}`" class="link link-primary">
                                    #{{ status.checkin.trip_id }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit form card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body">
                        <h2 class="card-title text-base">Moderation</h2>

                        <form class="space-y-3" @submit.prevent="save">
                            <div class="grid grid-cols-2 gap-2">
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Origin</legend>
                                    <select
                                        v-model.number="form.origin"
                                        class="select select-bordered select-sm w-full"
                                        required
                                    >
                                        <option :value="0" disabled>Select origin...</option>
                                        <option
                                            v-for="s in status.stopovers ?? []"
                                            :key="s.station_id"
                                            :value="s.station_id"
                                        >
                                            {{ s.station_name }}
                                            (D:
                                            {{
                                                s.departure_planned
                                                    ? new Date(s.departure_planned).toLocaleTimeString([], {
                                                          hour: '2-digit',
                                                          minute: '2-digit',
                                                      })
                                                    : '—'
                                            }})
                                        </option>
                                    </select>
                                </fieldset>

                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Destination</legend>
                                    <select
                                        v-model.number="form.destination"
                                        class="select select-bordered select-sm w-full"
                                        required
                                    >
                                        <option :value="0" disabled>Select destination...</option>
                                        <option
                                            v-for="s in status.stopovers ?? []"
                                            :key="s.station_id"
                                            :value="s.station_id"
                                        >
                                            {{ s.station_name }}
                                            (A:
                                            {{
                                                s.arrival_planned
                                                    ? new Date(s.arrival_planned).toLocaleTimeString([], {
                                                          hour: '2-digit',
                                                          minute: '2-digit',
                                                      })
                                                    : '—'
                                            }})
                                        </option>
                                    </select>
                                </fieldset>
                            </div>

                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Body</legend>
                                <textarea
                                    v-model="form.body"
                                    class="textarea textarea-bordered w-full"
                                    rows="3"
                                    maxlength="280"
                                />
                            </fieldset>

                            <div class="grid grid-cols-2 gap-2">
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Visibility</legend>
                                    <select
                                        v-model.number="form.visibility"
                                        class="select select-bordered select-sm w-full"
                                    >
                                        <option v-for="o in visibilityOptions()" :key="o.value" :value="o.value">
                                            {{ o.label }}
                                        </option>
                                    </select>
                                </fieldset>

                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Business</legend>
                                    <select
                                        v-model.number="form.business"
                                        class="select select-bordered select-sm w-full"
                                    >
                                        <option v-for="o in businessOptions()" :key="o.value" :value="o.value">
                                            {{ o.label }}
                                        </option>
                                    </select>
                                </fieldset>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Event ID</legend>
                                    <input
                                        v-model.number="form.event_id"
                                        type="number"
                                        class="input input-bordered input-sm w-full"
                                        placeholder="empty = none"
                                    />
                                </fieldset>

                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Points</legend>
                                    <input
                                        v-model.number="form.points"
                                        type="number"
                                        min="0"
                                        class="input input-bordered input-sm w-full"
                                        placeholder="empty = recalculate"
                                    />
                                </fieldset>
                            </div>

                            <div class="border-warning border-2 border-dashed -mx-4 p-4 rounded-lg my-5">
                                <div class="text-warning text-xs mb-6">Danger Zone</div>
                                <p class="text-xs text-base-content/60">
                                    The note below is visible to the user. Explain why you changed the status.
                                </p>

                                <fieldset class="fieldset">
                                    <legend class="fieldset-legend text-xs">Moderation Notes</legend>
                                    <textarea
                                        v-model="form.moderation_notes"
                                        class="textarea textarea-bordered w-full"
                                        rows="2"
                                        maxlength="255"
                                    />
                                </fieldset>

                                <div class="grid grid-cols-2 gap-2">
                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend text-xs">Lock Visibility?</legend>
                                        <select
                                            v-model="form.lock_visibility"
                                            class="select select-bordered select-sm w-full"
                                        >
                                            <option :value="false">No</option>
                                            <option :value="true">Yes</option>
                                        </select>
                                    </fieldset>

                                    <fieldset class="fieldset">
                                        <legend class="fieldset-legend text-xs">Hide Body from Public?</legend>
                                        <select
                                            v-model="form.hide_body"
                                            class="select select-bordered select-sm w-full"
                                        >
                                            <option :value="false">No</option>
                                            <option :value="true">Yes</option>
                                        </select>
                                    </fieldset>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-full" :disabled="saving">
                                <span v-if="saving" class="loading loading-spinner loading-sm" />
                                <Save v-else class="w-4 h-4" />
                                Save
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </BackendLayout>
</template>
