<template>
    <ContributeLayout>
        <div class="w-full">
            <h1 class="font-title text-2xl md:text-3xl lg:text-4xl font-bold mb-2">
                {{ trans('contribute.suggest_event.title') }}
            </h1>
            <p class="mb-4 opacity-80">
                {{ trans('contribute.suggest_event.description') }}
            </p>
            <p class="mb-6 text-sm opacity-80">
                <i18n-t keypath="contribute.suggest_event.wiki_hint" scope="global">
                    <template #link>
                        <a
                            :href="
                                getActiveLanguage() === 'de'
                                    ? 'https://help.traewelling.de/features/events/'
                                    : 'https://help.traewelling.de/en/features/events/'
                            "
                            target="_blank"
                            class="link link-primary font-semibold"
                        >
                            {{ trans('contribute.suggest_event.wiki_link_text') }}
                        </a>
                    </template>
                </i18n-t>
            </p>

            <div v-if="successMessage" role="alert" class="alert alert-success mb-6">
                <CircleCheck class="h-6 w-6" />
                <span>{{ successMessage }}</span>
            </div>

            <div v-if="errorMessage" role="alert" class="alert alert-error mb-6">
                <CircleX class="h-6 w-6" />
                <span>{{ errorMessage }}</span>
            </div>

            <div class="card bg-base-200 shadow-sm">
                <div class="card-body">
                    <form @submit.prevent="submit">
                        <!-- Name -->
                        <fieldset class="fieldset mb-4">
                            <legend class="fieldset-legend">
                                {{ trans('contribute.suggest_event.field_name') }} *
                            </legend>
                            <input v-model="form.name" type="text" class="input input-bordered w-full" required />
                        </fieldset>

                        <!-- Host -->
                        <fieldset class="fieldset mb-4">
                            <legend class="fieldset-legend">{{ trans('contribute.suggest_event.field_host') }}</legend>
                            <input v-model="form.host" type="text" class="input input-bordered w-full" />
                        </fieldset>

                        <!-- Date row -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">
                                    {{ trans('contribute.suggest_event.field_begin') }} *
                                </legend>
                                <input
                                    v-model="form.begin"
                                    type="date"
                                    class="input input-bordered w-full"
                                    required
                                    @change="onBeginChange"
                                />
                            </fieldset>
                            <fieldset class="fieldset">
                                <legend class="fieldset-legend">
                                    {{ trans('contribute.suggest_event.field_end') }} *
                                </legend>
                                <input v-model="form.end" type="date" class="input input-bordered w-full" required />
                            </fieldset>
                        </div>

                        <!-- URL -->
                        <fieldset class="fieldset mb-4">
                            <legend class="fieldset-legend">{{ trans('contribute.suggest_event.url_label') }}</legend>
                            <p class="text-xs opacity-60 mb-1">
                                {{ trans('contribute.suggest_event.url_hint') }}
                            </p>
                            <input
                                v-model="form.url"
                                type="url"
                                class="input input-bordered w-full"
                                placeholder="https://"
                            />
                        </fieldset>

                        <!-- Hashtag -->
                        <fieldset class="fieldset mb-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input v-model="hasHashtag" type="checkbox" class="checkbox" />
                                <span class="fieldset-legend">{{
                                    trans('contribute.suggest_event.field_hashtag')
                                }}</span>
                            </label>
                            <div v-if="hasHashtag" class="mt-2 join w-full">
                                <span class="join-item btn btn-disabled no-animation">#</span>
                                <input
                                    v-model="form.hashtag"
                                    type="text"
                                    class="input input-bordered join-item w-full"
                                    :placeholder="trans('contribute.suggest_event.hashtag_placeholder')"
                                />
                            </div>
                        </fieldset>

                        <!-- Nearest Station (Autocomplete) -->
                        <fieldset class="fieldset mb-4">
                            <legend class="fieldset-legend">
                                {{ trans('contribute.suggest_event.nearest_station') }}
                            </legend>
                            <div ref="stationDropdownRef" class="relative">
                                <!-- Selected station chip -->
                                <div v-if="selectedStation" class="flex items-center gap-2 mb-1">
                                    <div class="badge badge-lg badge-primary gap-2">
                                        <MapPin class="h-3 w-3" />
                                        {{ selectedStation.name }}
                                        <button
                                            type="button"
                                            class="btn btn-ghost btn-xs btn-circle"
                                            @click="clearStation"
                                        >
                                            <X class="h-3 w-3" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Search input -->
                                <div v-else>
                                    <div class="relative">
                                        <input
                                            ref="stationInputRef"
                                            v-model="stationQuery"
                                            type="text"
                                            class="input input-bordered w-full pr-10"
                                            :placeholder="trans('contribute.suggest_event.nearest_station_placeholder')"
                                            autocomplete="off"
                                            @input="onStationInput"
                                            @focus="onStationFocus"
                                        />
                                        <span v-if="stationLoading" class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <span class="loading loading-spinner loading-sm"></span>
                                        </span>
                                        <Search
                                            v-else
                                            class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 opacity-40"
                                        />
                                    </div>

                                    <!-- Dropdown results -->
                                    <div
                                        v-if="
                                            showStationDropdown &&
                                            (stationResults.length > 0 || stationQuery.length >= 2)
                                        "
                                        class="absolute z-50 w-full mt-1 bg-base-100 border border-base-300 rounded-box shadow-lg max-h-60 overflow-y-auto"
                                    >
                                        <ul class="menu menu-sm p-1">
                                            <li v-if="stationResults.length === 0 && !stationLoading">
                                                <span class="text-base-content/50 pointer-events-none">
                                                    {{ trans('contribute.suggest_event.nearest_station_no_results') }}
                                                </span>
                                            </li>
                                            <li v-for="station in stationResults" :key="station.id">
                                                <a @click.prevent="selectStation(station)">
                                                    <MapPin class="h-4 w-4 opacity-50" />
                                                    <div class="flex flex-col">
                                                        <span>
                                                            {{ station.name }}
                                                            <span
                                                                v-if="
                                                                    station.identifiers?.find(
                                                                        (i) => i.type === 'de_db_ril100',
                                                                    )
                                                                "
                                                                class="badge badge-xs badge-ghost ml-1"
                                                            >
                                                                {{
                                                                    station.identifiers?.find(
                                                                        (i) => i.type === 'de_db_ril100',
                                                                    )?.identifier
                                                                }}
                                                            </span>
                                                        </span>
                                                        <span v-if="getStationArea(station)" class="text-xs opacity-50">
                                                            {{ getStationArea(station) }}
                                                        </span>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Warnings -->
                        <div v-if="warnings.length > 0" role="alert" class="alert alert-warning mb-4">
                            <TriangleAlert class="h-6 w-6" />
                            <div>
                                <p v-for="(warning, index) in warnings" :key="index">{{ warning }}</p>
                            </div>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary" :disabled="submitting">
                            <span v-if="submitting" class="loading loading-spinner loading-sm"></span>
                            {{ trans('contribute.suggest_event.submit') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <router-link to="/" class="btn btn-ghost btn-sm">
                    <ArrowLeft class="h-4 w-4 mr-1" />
                    {{ trans('contribute.profile.back_to_overview') }}
                </router-link>
            </div>
        </div>
    </ContributeLayout>
</template>

<script setup lang="ts">
import { ArrowLeft, CircleCheck, CircleX, MapPin, Search, TriangleAlert, X } from '@lucide/vue';
import { getActiveLanguage, trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue';
import ContributeLayout from '../../layouts/ContributeLayout.vue';

interface StationArea {
    name: string;
    default: boolean;
    adminLevel: number;
}

interface StationIdentifier {
    type: string;
    identifier: string;
}

interface Station {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    areas?: StationArea[];
    identifiers?: StationIdentifier[];
}

const form = reactive({
    name: '',
    host: '',
    begin: '',
    end: '',
    url: '',
    hashtag: '',
});

const hasHashtag = ref(false);
const warnings = ref<string[]>([]);
const submitting = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Station autocomplete state
const stationQuery = ref('');
const stationResults = ref<Station[]>([]);
const selectedStation = ref<Station | null>(null);
const stationLoading = ref(false);
const showStationDropdown = ref(false);
const stationInputRef = ref<HTMLInputElement | null>(null);
const stationDropdownRef = ref<HTMLElement | null>(null);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;

function onBeginChange() {
    if (!form.end) {
        form.end = form.begin;
    }
}

function getStationArea(station: Station): string {
    if (!station.areas || station.areas.length === 0) return '';
    const defaultArea = station.areas.find((a) => a.default);
    const country = station.areas.find((a) => a.adminLevel === 2);
    if (defaultArea) {
        return country ? `${defaultArea.name}, ${country.name}` : defaultArea.name;
    }
    return country?.name ?? '';
}

function onStationInput() {
    if (debounceTimer) clearTimeout(debounceTimer);
    if (stationQuery.value.length < 2) {
        stationResults.value = [];
        showStationDropdown.value = false;
        return;
    }
    stationLoading.value = true;
    showStationDropdown.value = true;
    debounceTimer = setTimeout(() => searchStations(), 400);
}

function onStationFocus() {
    if (stationQuery.value.length >= 2 && stationResults.value.length > 0) {
        showStationDropdown.value = true;
    }
}

async function searchStations() {
    const query = stationQuery.value.replace(/%2F/g, ' ').replace(/\//g, ' ');
    try {
        const response = await fetch(`/api/v1/stations/?query=${encodeURIComponent(query)}`);
        if (response.ok) {
            const data = await response.json();
            stationResults.value = data.data ?? [];
        }
    } catch {
        stationResults.value = [];
    } finally {
        stationLoading.value = false;
    }
}

function selectStation(station: Station) {
    selectedStation.value = station;
    stationQuery.value = '';
    stationResults.value = [];
    showStationDropdown.value = false;
}

function clearStation() {
    selectedStation.value = null;
    stationQuery.value = '';
    stationResults.value = [];
}

function handleClickOutside(event: MouseEvent) {
    if (stationDropdownRef.value && !stationDropdownRef.value.contains(event.target as Node)) {
        showStationDropdown.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    if (debounceTimer) clearTimeout(debounceTimer);
});

function validate(): boolean {
    warnings.value = [];

    const now = DateTime.now();
    const begin = DateTime.fromISO(form.begin);
    const end = DateTime.fromISO(form.end);
    const daysUntilBegin = begin.diff(now, 'days').days;
    const duration = end.diff(begin, 'days').days;

    if (daysUntilBegin < 14) {
        warnings.value.push(trans('events.request.warn.date'));
    }
    if (duration > 3) {
        warnings.value.push(trans('events.request.warn.duration'));
    }
    if (form.url.length < 5) {
        warnings.value.push(trans('events.request.warn.url'));
    }

    if (warnings.value.length > 0) {
        const message = warnings.value.join('\n\n') + '\n\n' + trans('events.request.warn.confirm');
        return confirm(message);
    }

    return true;
}

async function submit() {
    successMessage.value = '';
    errorMessage.value = '';

    if (!validate()) {
        return;
    }

    submitting.value = true;

    try {
        const body: Record<string, unknown> = {
            name: form.name,
            host: form.host || undefined,
            begin: form.begin,
            end: form.end,
            url: form.url || undefined,
            hashtag: form.hashtag || undefined,
        };

        if (selectedStation.value) {
            body.nearestStationId = selectedStation.value.id;
        }

        const response = await fetch('/api/v1/event', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });

        if (response.ok) {
            successMessage.value = trans('contribute.suggest_event.success');
            form.name = '';
            form.host = '';
            form.begin = '';
            form.end = '';
            form.url = '';
            form.hashtag = '';
            hasHashtag.value = false;
            clearStation();
            warnings.value = [];
        } else {
            const data = await response.json();
            errorMessage.value = data.message || trans('contribute.suggest_event.error');
        }
    } catch {
        errorMessage.value = trans('contribute.suggest_event.error');
    } finally {
        submitting.value = false;
    }
}
</script>
