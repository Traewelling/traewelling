<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    Api,
    type AdminEventRequest,
    type EventSuggestionResource,
    type StationResource,
} from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

interface ParallelEvent {
    id: number;
    name: string;
    slug: string;
    checkin_start: string;
    checkin_end: string;
    similarity: number;
}

const api = new Api({ baseUrl: window.location.origin + '/api' });
const route = useRoute();
const router = useRouter();

const suggestionId = Number(route.params.id);
const loading = ref(true);
const saving = ref(false);
const error = ref<string | null>(null);

const suggestion = ref<EventSuggestionResource | null>(null);
const parallelEvents = ref<ParallelEvent[]>([]);

const form = ref({
    name: '',
    hashtag: '',
    host: '',
    url: '',
    checkin_start: '',
    checkin_end: '',
    event_start: '',
    event_end: '',
});

const stationSearch = ref('');
const stationResults = ref<StationResource[]>([]);
const stationSearching = ref(false);
const selectedStation = ref<{ id: number; name: string } | null>(null);
let stationSearchTimeout: ReturnType<typeof setTimeout> | null = null;

async function fetchSuggestion(): Promise<void> {
    try {
        const res = await api.admin.getAdminEventSuggestion(suggestionId);
        const responseData = res.data?.data;
        if (responseData) {
            suggestion.value = responseData.suggestion ?? null;
            parallelEvents.value = (responseData.parallelEvents as ParallelEvent[]) ?? [];
            if (responseData.suggestion) prefillForm(responseData.suggestion);
        }
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

function prefillForm(s: EventSuggestionResource): void {
    form.value = {
        name: s.name ?? '',
        hashtag: s.hashtag ?? '',
        host: s.host ?? '',
        url: s.url ?? '',
        checkin_start: s.begin ?? '',
        checkin_end: s.end ?? '',
        event_start: s.begin ?? '',
        event_end: s.end ?? '',
    };
    if (s.station) {
        selectedStation.value = { id: s.station.id!, name: s.station.name! };
        stationSearch.value = s.station.name!;
    }
}

async function searchStations(): Promise<void> {
    if (!stationSearch.value.trim()) {
        stationResults.value = [];
        return;
    }
    stationSearching.value = true;
    try {
        const res = await api.stations.indexStation({ query: stationSearch.value });
        stationResults.value = res.data?.data ?? [];
    } catch {
        stationResults.value = [];
    } finally {
        stationSearching.value = false;
    }
}

function onStationInput(): void {
    selectedStation.value = null;
    if (stationSearchTimeout) clearTimeout(stationSearchTimeout);
    stationSearchTimeout = setTimeout(() => searchStations(), 300);
}

function selectStation(station: StationResource): void {
    selectedStation.value = { id: station.id!, name: station.name! };
    stationSearch.value = station.name!;
    stationResults.value = [];
}

function clearStation(): void {
    selectedStation.value = null;
    stationSearch.value = '';
    stationResults.value = [];
}

async function submit(): Promise<void> {
    saving.value = true;
    error.value = null;

    const payload: AdminEventRequest = {
        name: form.value.name,
        hashtag: form.value.hashtag || null,
        host: form.value.host || null,
        url: form.value.url || null,
        station_id: selectedStation.value?.id ?? null,
        checkin_start: form.value.checkin_start,
        checkin_end: form.value.checkin_end,
        event_start: form.value.event_start || null,
        event_end: form.value.event_end || null,
    };

    try {
        await api.admin.acceptAdminEventSuggestion(suggestionId, payload);
        await router.push('/admin/event-suggestions');
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Something went wrong.';
    } finally {
        saving.value = false;
    }
}

function similarityBadgeClass(similarity: number): string {
    if (similarity >= 80) return 'badge-error';
    if (similarity >= 50) return 'badge-warning';
    return 'badge-ghost';
}

onMounted(() => fetchSuggestion());
</script>

<template>
    <BackendLayout>
        <div class="flex items-center gap-3 mb-6">
            <router-link to="/admin/event-suggestions" class="btn btn-ghost btn-sm"> ← Suggestions </router-link>
            <h1 class="text-2xl font-bold">Accept Suggestion</h1>
        </div>

        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Left: form -->
            <form class="xl:col-span-2 grid grid-cols-1 gap-6" @submit.prevent="submit">
                <div v-if="error" role="alert" class="alert alert-error">
                    <span>{{ error }}</span>
                </div>

                <!-- Original suggestion info -->
                <div v-if="suggestion" class="card bg-base-200">
                    <div class="card-body py-3">
                        <p
                            v-if="suggestion.user"
                            class="text-xs text-base-content/60 font-semibold uppercase tracking-wide"
                        >
                            Original suggestion by {{ suggestion.user.username }}
                        </p>
                        <p class="text-sm">
                            <span class="font-medium">{{ suggestion.name }}</span>
                            <span class="text-base-content/50 ml-2">
                                {{ suggestion.begin }} – {{ suggestion.end }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Event data card -->
                <div class="card bg-base-100 shadow">
                    <div class="card-body gap-4">
                        <h2 class="card-title text-base">Event Data</h2>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Name *</legend>
                            <input
                                v-model="form.name"
                                type="text"
                                class="input input-sm w-full"
                                maxlength="255"
                                required
                            />
                        </fieldset>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Hashtag</legend>
                                <input
                                    v-model="form.hashtag"
                                    type="text"
                                    class="input input-sm w-full"
                                    maxlength="30"
                                    placeholder="WithoutHash"
                                />
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Host / Organizer</legend>
                                <input v-model="form.host" type="text" class="input input-sm w-full" maxlength="255" />
                            </fieldset>
                        </div>

                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">URL</legend>
                            <input
                                v-model="form.url"
                                type="url"
                                class="input input-sm w-full"
                                maxlength="255"
                                placeholder="https://…"
                            />
                        </fieldset>

                        <!-- Station autocomplete -->
                        <fieldset class="fieldset">
                            <legend class="fieldset-legend text-xs">Station</legend>
                            <div class="relative">
                                <div class="flex gap-2">
                                    <input
                                        v-model="stationSearch"
                                        type="text"
                                        class="input input-sm flex-1"
                                        placeholder="Search station..."
                                        :disabled="selectedStation !== null"
                                        @input="onStationInput"
                                    />
                                    <button
                                        v-if="selectedStation"
                                        type="button"
                                        class="btn btn-sm btn-ghost"
                                        @click="clearStation"
                                    >
                                        ✕ Clear
                                    </button>
                                </div>
                                <div
                                    v-if="stationResults.length"
                                    class="absolute z-10 w-full bg-base-100 border border-base-300 rounded-box shadow-lg mt-1 max-h-48 overflow-y-auto"
                                >
                                    <button
                                        v-for="station in stationResults"
                                        :key="station.id"
                                        type="button"
                                        class="w-full text-left px-3 py-2 hover:bg-base-200 text-sm"
                                        @click="selectStation(station)"
                                    >
                                        <span class="font-medium">{{ station.name }}</span>
                                        <span class="text-xs text-base-content/50 ml-2">{{ station.id }}</span>
                                    </button>
                                </div>
                                <div v-if="stationSearching" class="absolute right-2 top-2">
                                    <span class="loading loading-spinner loading-xs" />
                                </div>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-4">
                            <h2 class="card-title text-base">Check-in Period *</h2>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Start</legend>
                                <input
                                    v-model="form.checkin_start"
                                    type="date"
                                    class="input input-sm w-full"
                                    required
                                />
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">End</legend>
                                <input
                                    v-model="form.checkin_end"
                                    type="date"
                                    class="input input-sm w-full"
                                    :min="form.checkin_start"
                                    required
                                />
                            </fieldset>
                        </div>
                    </div>

                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-4">
                            <h2 class="card-title text-base">Event Period (optional)</h2>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">Start</legend>
                                <input v-model="form.event_start" type="date" class="input input-sm w-full" />
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend text-xs">End</legend>
                                <input v-model="form.event_end" type="date" class="input input-sm w-full" />
                            </fieldset>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <router-link to="/admin/event-suggestions" class="btn btn-ghost">Cancel</router-link>
                    <button type="submit" class="btn btn-success" :disabled="saving">
                        <span v-if="saving" class="loading loading-spinner loading-sm" />
                        Accept & Create Event
                    </button>
                </div>
            </form>

            <!-- Right: parallel events -->
            <div class="xl:col-span-1">
                <div class="card bg-base-100 shadow sticky top-6">
                    <div class="card-body">
                        <h2 class="card-title text-base">Parallel Events</h2>
                        <p class="text-xs text-base-content/50 mb-3">
                            Events overlapping the suggested period, sorted by name similarity.
                        </p>

                        <div v-if="!parallelEvents.length" class="text-sm text-base-content/40 py-4 text-center">
                            No overlapping events.
                        </div>

                        <div v-else class="flex flex-col gap-2">
                            <div
                                v-for="event in parallelEvents"
                                :key="event.id"
                                class="border border-base-300 rounded-lg p-3"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-medium leading-tight">{{ event.name }}</p>
                                    <span
                                        class="badge badge-sm shrink-0"
                                        :class="similarityBadgeClass(event.similarity)"
                                    >
                                        {{ event.similarity }}%
                                    </span>
                                </div>
                                <p class="text-xs text-base-content/50 mt-1">
                                    {{ event.checkin_start }} – {{ event.checkin_end }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackendLayout>
</template>
