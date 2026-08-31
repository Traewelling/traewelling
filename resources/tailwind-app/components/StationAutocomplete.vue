<script setup lang="ts">
import { House, Search } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { computed, ref, watch } from 'vue';
import { Api, StationResource } from '../../types/Api.gen';
import { useUserStore } from '../../vue/stores/user';

const props = withDefaults(
    defineProps<{
        modelValue: StationResource | null;
        placeholder?: string;
        /** Shows the stations the user recently visited while the input is still empty. */
        showHistory?: boolean;
        /** Puts a magnifier in front of the input, for places where the field stands on its own. */
        withIcon?: boolean;
        small?: boolean;
    }>(),
    {
        placeholder: '',
        showHistory: true,
        withIcon: false,
        small: true,
    },
);

const emit = defineEmits<{
    'update:modelValue': [station: StationResource];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });
const userStore = useUserStore();

const homeStation = computed<StationResource | null>(() => userStore.getHome);

const query = ref(props.modelValue?.name ?? '');
const suggestions = ref<StationResource[]>([]);
const recentStations = ref<StationResource[]>([]);
const dropdownOpen = ref(false);
const loading = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(
    () => props.modelValue,
    (station) => {
        query.value = station?.name ?? '';
    },
);

function ril100(station: StationResource): string | undefined {
    return station.identifiers?.find((i) => i.type === 'de_db_ril100')?.identifier ?? undefined;
}

/** The area a station sits in, so that identically named stations stay distinguishable. */
function area(station: StationResource): string {
    if (!station.areas || station.areas.length === 0) return '';
    const defaultArea = station.areas.find((a) => a.default);
    const country = station.areas.find((a) => a.adminLevel === 2);
    if (defaultArea) {
        return country ? `${defaultArea.name}, ${country.name}` : defaultArea.name;
    }
    return country?.name ?? '';
}

async function loadHistory(): Promise<void> {
    try {
        const res = await api.trains.trainStationHistory();
        recentStations.value = res.data?.data ?? [];
    } catch {
        // The history is a convenience, searching works without it
    }
}

async function fetchSuggestions(): Promise<void> {
    // Slashes separate the parts of many station names and would be read as a path segment
    const q = query.value.trim().replace(/\//, ' ');
    if (q.length < 2) {
        suggestions.value = [];
        loading.value = false;
        return;
    }
    loading.value = true;
    try {
        const res = await api.trains.trainStationAutocomplete(q);
        suggestions.value = res.data?.data ?? [];
    } catch {
        suggestions.value = [];
    } finally {
        loading.value = false;
    }
}

function onInput(): void {
    dropdownOpen.value = true;
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchSuggestions, 300);
}

function onFocus(): void {
    dropdownOpen.value = true;
    if (props.showHistory && !recentStations.value.length) loadHistory();
}

function onBlur(): void {
    setTimeout(() => {
        dropdownOpen.value = false;
        // Restore the selected station name, so a half-typed query never looks like a selection
        query.value = props.modelValue?.name ?? '';
    }, 150);
}

function pick(station: StationResource): void {
    query.value = station.name;
    suggestions.value = [];
    dropdownOpen.value = false;
    emit('update:modelValue', station);
}

/**
 * While nothing is typed the home station leads the list, followed by the recently visited ones.
 */
const displayed = (): StationResource[] => {
    if (query.value.trim()) {
        return suggestions.value;
    }
    if (!props.showHistory) {
        return [];
    }
    const home = homeStation.value;
    if (!home) {
        return recentStations.value;
    }
    return [home, ...recentStations.value.filter((station) => station.id !== home.id)];
};

function isHome(station: StationResource): boolean {
    return !query.value.trim() && homeStation.value?.id === station.id;
}

function pickFirst(): void {
    const first = displayed()[0];
    if (first) pick(first);
}
</script>

<template>
    <div class="relative">
        <div v-if="withIcon" class="input input-bordered flex items-center gap-2 w-full" :class="{ 'input-sm': small }">
            <Search class="size-4 text-base-content/40 shrink-0" />
            <input
                v-model="query"
                type="text"
                class="grow"
                :placeholder="placeholder || trans('trip_creation.form.stopover')"
                autocomplete="off"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur"
                @keydown.enter.prevent="pickFirst"
            />
        </div>
        <input
            v-else
            v-model="query"
            type="text"
            class="input input-bordered w-full pr-8"
            :class="{ 'input-sm': small }"
            :placeholder="placeholder || trans('trip_creation.form.stopover')"
            autocomplete="off"
            @input="onInput"
            @focus="onFocus"
            @blur="onBlur"
            @keydown.enter.prevent="pickFirst"
        />
        <span
            v-if="loading"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 loading loading-spinner loading-xs opacity-50"
        />
        <ul
            v-if="dropdownOpen && displayed().length > 0"
            class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-box shadow-lg max-h-64 overflow-y-auto"
        >
            <li v-for="station in displayed()" :key="station.id">
                <button
                    class="w-full text-left px-3 py-2 hover:bg-base-200 text-sm"
                    :class="{ 'bg-primary/10 font-medium': isHome(station) }"
                    @mousedown.prevent="pick(station)"
                >
                    <House v-if="isHome(station)" class="size-4 inline-block me-1 -mt-0.5 text-primary" />
                    {{ station.name }}
                    <span v-if="ril100(station)" class="badge badge-soft badge-accent badge-sm ml-1">
                        {{ ril100(station) }}
                    </span>
                    <span v-if="area(station)" class="opacity-65 text-xs">
                        <br />
                        {{ area(station) }}
                    </span>
                </button>
            </li>
        </ul>
    </div>
</template>
