<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref, useTemplateRef, watch } from 'vue';
import { Api, StationResource } from '../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const props = withDefaults(
    defineProps<{
        placeholder: string;
        showArrival?: boolean;
        showDeparture?: boolean;
        canDelete?: boolean;
        minArrival?: string;
        minDeparture?: string;
        maxArrival?: string;
        maxDeparture?: string;
        arrivalValue?: string;
        departureValue?: string;
    }>(),
    {
        showArrival: true,
        showDeparture: true,
        canDelete: false,
        minArrival: '',
        minDeparture: '',
        maxArrival: '',
        maxDeparture: '',
        arrivalValue: '',
        departureValue: '',
    },
);

const emit = defineEmits<{
    selectStation: [station: StationResource];
    updateArrival: [localStr: string];
    updateDeparture: [localStr: string];
    delete: [];
}>();

const inputEl = useTemplateRef<HTMLInputElement>('inputEl');

const query = ref('');
const suggestions = ref<StationResource[]>([]);
const recentStations = ref<StationResource[]>([]);
const dropdownOpen = ref(false);
const loading = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const arrivalLocal = ref(props.arrivalValue ?? '');
const departureLocal = ref(props.departureValue ?? '');

watch(
    () => props.arrivalValue,
    (v) => {
        arrivalLocal.value = v ?? '';
    },
);
watch(
    () => props.departureValue,
    (v) => {
        departureLocal.value = v ?? '';
    },
);

async function loadHistory(): Promise<void> {
    try {
        const res = await api.trains.trainStationHistory();
        recentStations.value = res.data?.data ?? [];
    } catch {
        // ignore
    }
}

async function fetchSuggestions(): Promise<void> {
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
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(fetchSuggestions, 300);
}

function onFocus(): void {
    dropdownOpen.value = true;
    if (!recentStations.value.length) loadHistory();
}

function onBlur(): void {
    setTimeout(() => {
        dropdownOpen.value = false;
    }, 150);
}

function pickStation(station: StationResource): void {
    query.value = station.name;
    suggestions.value = [];
    dropdownOpen.value = false;
    emit('selectStation', station);
}

function onArrivalChange(e: Event): void {
    const val = (e.target as HTMLInputElement).value;
    arrivalLocal.value = val;
    if (val) emit('updateArrival', val);
}

function onDepartureChange(e: Event): void {
    const val = (e.target as HTMLInputElement).value;
    departureLocal.value = val;
    if (val) emit('updateDeparture', val);
}

const displayed = (): StationResource[] => (query.value.trim() ? suggestions.value : recentStations.value);

function setStation(station: StationResource): void {
    query.value = station.name;
}

function setArrivalDatetime(localStr: string): void {
    arrivalLocal.value = localStr;
}

function setDepartureDatetime(localStr: string): void {
    departureLocal.value = localStr;
}

defineExpose({ setStation, setArrivalDatetime, setDepartureDatetime });
</script>

<template>
    <div class="space-y-2">
        <div class="relative">
            <input
                ref="inputEl"
                v-model="query"
                type="text"
                class="input input-bordered input-sm w-full pr-8"
                :placeholder="placeholder"
                autocomplete="off"
                @input="onInput"
                @focus="onFocus"
                @blur="onBlur"
            />
            <span
                v-if="loading"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 loading loading-spinner loading-xs opacity-50"
            />
            <ul
                v-if="dropdownOpen && displayed().length > 0"
                class="absolute z-50 mt-1 w-full bg-base-100 border border-base-300 rounded-box shadow-lg max-h-52 overflow-y-auto"
            >
                <li v-for="station in displayed()" :key="station.id">
                    <button
                        class="w-full text-left px-3 py-2 hover:bg-base-200 text-sm"
                        @mousedown.prevent="pickStation(station)"
                    >
                        {{ station.name }}
                    </button>
                </li>
            </ul>
        </div>

        <div class="flex gap-2 items-center">
            <div v-if="showArrival" class="form-control flex-1">
                <label class="label py-0.5">
                    <span class="label-text text-xs">
                        {{ trans('trip_creation.form.arrival') }}
                        <span v-if="showDeparture" class="opacity-50">({{ trans('optional') }})</span>
                    </span>
                </label>
                <input
                    type="datetime-local"
                    class="input input-bordered input-xs w-full"
                    :value="arrivalLocal"
                    :min="minArrival || undefined"
                    :max="maxArrival || undefined"
                    @change="onArrivalChange"
                />
            </div>

            <div v-if="showDeparture" class="form-control flex-1">
                <label class="label py-0.5">
                    <span class="label-text text-xs">{{ trans('trip_creation.form.departure') }}</span>
                </label>
                <input
                    type="datetime-local"
                    class="input input-bordered input-xs w-full"
                    :value="departureLocal"
                    :min="minDeparture || undefined"
                    :max="maxDeparture || undefined"
                    @change="onDepartureChange"
                />
            </div>

            <button
                v-if="canDelete"
                type="button"
                class="btn btn-ghost btn-xs btn-circle text-error self-end mb-0.5"
                @click="emit('delete')"
            >
                <Trash2 class="w-3.5 h-3.5" />
            </button>
        </div>
    </div>
</template>
