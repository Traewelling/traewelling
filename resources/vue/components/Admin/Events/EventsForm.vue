<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import BackendLayout from '../../../../tailwind-app/layouts/BackendLayout.vue';
import { Api, type AdminEventRequest, type EventAdminResource, type StationResource } from '../../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const route = useRoute();
const router = useRouter();

const eventId = route.params.id ? Number(route.params.id) : null;
const isEdit = eventId !== null;

const loading = ref(isEdit);
const saving = ref(false);
const error = ref<string | null>(null);

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

function fillForm(event: EventAdminResource): void {
    form.value = {
        name: event.name ?? '',
        hashtag: event.hashtag ?? '',
        host: event.host ?? '',
        url: event.url ?? '',
        checkin_start: event.checkin_start ?? '',
        checkin_end: event.checkin_end ?? '',
        event_start: event.event_start ?? '',
        event_end: event.event_end ?? '',
    };
    if (event.station) {
        selectedStation.value = { id: event.station.id!, name: event.station.name! };
        stationSearch.value = event.station.name!;
    }
}

async function fetchEvent(): Promise<void> {
    try {
        const res = await api.admin.getAdminEvent(eventId!);
        if (res.data?.data) fillForm(res.data.data);
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
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
        if (isEdit) {
            await api.admin.updateAdminEvent(eventId!, payload);
        } else {
            await api.admin.createAdminEvent(payload);
        }
        await router.push('/admin/events');
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Something went wrong.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    if (isEdit) fetchEvent();
});
</script>

<template>
    <BackendLayout>
        <div class="flex items-center gap-3 mb-6">
            <router-link to="/admin/events" class="btn btn-ghost btn-sm">← Events</router-link>
            <h1 class="text-2xl font-bold">{{ isEdit ? 'Edit Event' : 'New Event' }}</h1>
        </div>

        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <form v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6" @submit.prevent="submit">
            <div v-if="error" role="alert" class="alert alert-error lg:col-span-2">
                <span>{{ error }}</span>
            </div>

            <!-- Basic Info -->
            <div class="card bg-base-100 shadow lg:col-span-2">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Event Info</h2>

                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Name *</legend>
                        <input v-model="form.name" type="text" class="input input-sm w-full" maxlength="255" required />
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
            <div class="card bg-base-100 shadow">
                <div class="card-body gap-4">
                    <h2 class="card-title text-base">Check-in Period *</h2>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Check-in Start</legend>
                        <input v-model="form.checkin_start" type="date" class="input input-sm w-full" required />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Check-in End</legend>
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
                        <legend class="fieldset-legend text-xs">Event Start</legend>
                        <input v-model="form.event_start" type="date" class="input input-sm w-full" />
                    </fieldset>
                    <fieldset class="fieldset">
                        <legend class="fieldset-legend text-xs">Event End</legend>
                        <input v-model="form.event_end" type="date" class="input input-sm w-full" />
                    </fieldset>
                </div>
            </div>

            <!-- Submit -->
            <div class="lg:col-span-2 flex justify-end gap-2">
                <router-link to="/admin/events" class="btn btn-ghost">Cancel</router-link>
                <button type="submit" class="btn btn-primary" :disabled="saving">
                    <span v-if="saving" class="loading loading-spinner loading-sm" />
                    {{ isEdit ? 'Save Changes' : 'Create Event' }}
                </button>
            </div>
        </form>
    </BackendLayout>
</template>
