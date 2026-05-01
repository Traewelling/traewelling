<script setup lang="ts">
import { trans } from 'laravel-vue-i18n';
import { Locate, Search } from 'lucide-vue-next';
import { ref } from 'vue';
import { StationResource } from '../../types/Api.gen';

function ril100(station: StationResource): string | undefined {
    return station.identifiers?.find((i) => i.type === 'de_db_ril100')?.identifier ?? undefined;
}

const query = ref('');
const suggestions = ref<StationResource[]>([]);
const recentStations = ref<StationResource[]>([]);
const open = ref(false);
const fetchingGps = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

async function loadHistory(): Promise<void> {
    try {
        const res = await fetch('/api/v1/trains/station/history');
        const json = await res.json();
        recentStations.value = json.data ?? [];
    } catch {
        // history is best-effort
    }
}

async function fetchAutocomplete(): Promise<void> {
    if (!query.value.trim()) {
        suggestions.value = [];
        return;
    }
    try {
        const res = await fetch(`/api/v1/trains/station/autocomplete/${encodeURIComponent(query.value.trim())}`);
        const json = await res.json();
        suggestions.value = json.data ?? json ?? [];
    } catch {
        suggestions.value = [];
    }
}

function onInput(): void {
    if (debounceTimer) clearTimeout(debounceTimer);
    open.value = true;
    debounceTimer = setTimeout(fetchAutocomplete, 250);
}

function onFocus(): void {
    open.value = true;
    if (!recentStations.value.length) loadHistory();
}

function onBlur(): void {
    setTimeout(() => {
        open.value = false;
    }, 150);
}

function selectStation(station: StationResource): void {
    window.location.href = `/stationboard?stationId=${station.id}&stationName=${encodeURIComponent(station.name)}`;
}

async function searchByGps(): Promise<void> {
    if (!navigator.geolocation) return;
    fetchingGps.value = true;
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            try {
                const res = await fetch(
                    `/api/v1/trains/station/nearby?latitude=${position.coords.latitude}&longitude=${position.coords.longitude}`,
                );
                const json = await res.json();
                const station: StationResource | undefined = json.data;
                if (station) selectStation(station);
            } finally {
                fetchingGps.value = false;
            }
        },
        () => {
            fetchingGps.value = false;
        },
    );
}

const displayed = (): StationResource[] => (query.value.trim() ? suggestions.value : recentStations.value);
</script>

<template>
    <div class="card bg-base-100 mb-4">
        <div class="card-body py-3 px-4">
            <div class="relative flex gap-2">
                <div class="relative flex-1">
                    <div class="input input-bordered flex items-center gap-2 w-full">
                        <Search class="w-4 h-4 text-base-content/40 shrink-0" />
                        <input
                            v-model="query"
                            type="text"
                            class="grow"
                            :placeholder="trans('stationboard.station-placeholder')"
                            autocomplete="off"
                            @input="onInput"
                            @focus="onFocus"
                            @blur="onBlur"
                            @keydown.enter.prevent="displayed()[0] && selectStation(displayed()[0])"
                        />
                    </div>

                    <!-- Dropdown -->
                    <ul
                        v-if="open && displayed().length"
                        class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-box shadow-lg max-h-64 overflow-y-auto"
                    >
                        <li v-for="station in displayed()" :key="station.id">
                            <button
                                class="w-full text-left px-4 py-2 hover:bg-base-200 text-sm"
                                @mousedown.prevent="selectStation(station)"
                            >
                                {{ station.name }}
                                <span v-if="ril100(station)" class="text-base-content/40 ml-1"
                                    >({{ ril100(station) }})</span
                                >
                            </button>
                        </li>
                    </ul>
                </div>

                <button
                    class="btn btn-square btn-outline"
                    :class="{ loading: fetchingGps }"
                    :title="trans('stationboard.search-by-location')"
                    :disabled="fetchingGps"
                    @click="searchByGps"
                >
                    <Locate v-if="!fetchingGps" class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>
