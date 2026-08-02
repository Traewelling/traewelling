<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { ref, watch } from 'vue';
import { StationResource } from '../../../types/Api.gen';
import StationAutocomplete from '../StationAutocomplete.vue';

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

const station = ref<StationResource | null>(null);

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

function onStationPicked(picked: StationResource): void {
    station.value = picked;
    emit('selectStation', picked);
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

/**
 * Fills the row with a station the parent already knows about, for example from the route preview.
 * Deliberately silent: emitting selectStation here would bounce that state straight back at the parent.
 */
function setStation(picked: StationResource): void {
    station.value = picked;
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
        <StationAutocomplete :model-value="station" :placeholder="placeholder" @update:model-value="onStationPicked" />

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
