<script setup lang="ts">
import { ChevronDown, Save, Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, ref, watch } from 'vue';
import { Api, StationResource, StopoverResource } from '../../../types/Api.gen';
import StationAutocomplete from '../StationAutocomplete.vue';

const props = defineProps<{
    tripUuid: string;
    stopover: StopoverResource;
}>();

const emit = defineEmits<{
    changed: [];
}>();

const api = new Api({ baseUrl: window.location.origin + '/api' });
const notyf = inject('notyf') as Notyf;

const expanded = ref(false);
const saving = ref(false);
const deleting = ref(false);

const station = ref<StationResource | null>(null);
const arrivalPlanned = ref('');
const departurePlanned = ref('');
const arrivalReal = ref('');
const departureReal = ref('');
const arrivalPlatformPlanned = ref('');
const departurePlatformPlanned = ref('');
const arrivalPlatformReal = ref('');
const departurePlatformReal = ref('');
const cancelled = ref(false);

function toLocalInput(iso: string | null | undefined): string {
    if (!iso) return '';
    const dt = DateTime.fromISO(iso);
    return dt.isValid ? dt.toFormat("yyyy-MM-dd'T'HH:mm") : '';
}

function toIso(local: string): string | null {
    if (!local) return null;
    const dt = DateTime.fromISO(local);
    return dt.isValid ? dt.toISO() : null;
}

function resetForm(): void {
    station.value = props.stopover.station ?? null;
    arrivalPlanned.value = toLocalInput(props.stopover.arrivalPlanned);
    departurePlanned.value = toLocalInput(props.stopover.departurePlanned);
    arrivalReal.value = toLocalInput(props.stopover.arrivalReal);
    departureReal.value = toLocalInput(props.stopover.departureReal);
    arrivalPlatformPlanned.value = props.stopover.arrivalPlatformPlanned ?? '';
    departurePlatformPlanned.value = props.stopover.departurePlatformPlanned ?? '';
    arrivalPlatformReal.value = props.stopover.arrivalPlatformReal ?? '';
    departurePlatformReal.value = props.stopover.departurePlatformReal ?? '';
    cancelled.value = props.stopover.cancelled ?? false;
}

watch(() => props.stopover, resetForm, { immediate: true });

type TimeParts = { date: string; time: string };

function splitDateTime(iso: string | null | undefined): TimeParts | null {
    if (!iso) return null;
    const dt = DateTime.fromISO(iso);
    return dt.isValid ? { date: dt.toFormat('dd.MM.yyyy'), time: dt.toFormat('HH:mm') } : null;
}

const arrival = computed(() => splitDateTime(props.stopover.arrivalPlanned));
const departure = computed(() => splitDateTime(props.stopover.departurePlanned));

/**
 * The date is shown once in front of the times as long as they fall on the same day, which is the
 * normal case. Null means arrival and departure straddle midnight, so each carries its own date.
 */
const sharedDate = computed<string | null>(() => {
    const [a, d] = [arrival.value, departure.value];
    if (a && d) {
        return a.date === d.date ? a.date : null;
    }
    return (a ?? d)?.date ?? null;
});

async function save(): Promise<void> {
    if (!props.stopover.uuid) return;
    saving.value = true;
    try {
        await api.trips.updateTripStopover(props.tripUuid, props.stopover.uuid, {
            ...(station.value?.uuid ? { stationUuid: station.value.uuid } : {}),
            arrivalPlanned: toIso(arrivalPlanned.value),
            departurePlanned: toIso(departurePlanned.value),
            arrivalReal: toIso(arrivalReal.value),
            departureReal: toIso(departureReal.value),
            arrivalPlatformPlanned: arrivalPlatformPlanned.value || null,
            departurePlatformPlanned: departurePlatformPlanned.value || null,
            arrivalPlatformReal: arrivalPlatformReal.value || null,
            departurePlatformReal: departurePlatformReal.value || null,
            cancelled: cancelled.value,
        });
        notyf?.success(trans('settings.saved'));
        expanded.value = false;
        emit('changed');
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        saving.value = false;
    }
}

async function remove(): Promise<void> {
    if (!props.stopover.uuid) return;
    deleting.value = true;
    try {
        await api.trips.deleteTripStopover(props.tripUuid, props.stopover.uuid);
        notyf?.success(trans('trip.edit.stopover-deleted'));
        emit('changed');
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        deleting.value = false;
    }
}
</script>

<template>
    <div class="border border-base-300 rounded-box">
        <button
            type="button"
            class="w-full flex items-center gap-3 px-3 py-2 text-left hover:bg-base-200 rounded-box"
            @click="expanded = !expanded"
        >
            <ChevronDown class="size-4 shrink-0 transition-transform" :class="{ 'rotate-180': expanded }" />
            <span class="flex-1 min-w-0">
                <span class="block font-medium" :class="{ 'line-through opacity-60': stopover.cancelled }">
                    {{ stopover.station?.name ?? stopover.name }}
                </span>
                <span class="flex flex-wrap items-baseline gap-x-3 text-xs text-base-content/50">
                    <span v-if="sharedDate">{{ sharedDate }}</span>
                    <span v-if="arrival" class="tabular-nums">
                        <span class="mr-1 opacity-70">{{ trans('time.arrival.short') }}</span>
                        <span v-if="!sharedDate" class="mr-1">{{ arrival.date }}</span>
                        {{ arrival.time }}
                    </span>
                    <span v-if="departure" class="tabular-nums">
                        <span class="mr-1 opacity-70">{{ trans('time.departure.short') }}</span>
                        <span v-if="!sharedDate" class="mr-1">{{ departure.date }}</span>
                        {{ departure.time }}
                    </span>
                </span>
            </span>
            <span v-if="stopover.cancelled" class="badge badge-error badge-sm">
                {{ trans('stationboard.stop-cancelled') }}
            </span>
        </button>

        <div v-if="expanded" class="px-3 pb-3 space-y-3 border-t border-base-300 pt-3">
            <fieldset class="fieldset p-0">
                <legend class="fieldset-legend">{{ trans('stationboard.stopover') }}</legend>
                <StationAutocomplete v-model="station" />
            </fieldset>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('export.title.arrival_planned') }}</legend>
                    <input
                        v-model="arrivalPlanned"
                        type="datetime-local"
                        class="input input-bordered input-sm w-full"
                    />
                </fieldset>
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('export.title.departure_planned') }}</legend>
                    <input
                        v-model="departurePlanned"
                        type="datetime-local"
                        class="input input-bordered input-sm w-full"
                    />
                </fieldset>
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('export.title.arrival_real') }}</legend>
                    <input v-model="arrivalReal" type="datetime-local" class="input input-bordered input-sm w-full" />
                </fieldset>
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('export.title.departure_real') }}</legend>
                    <input v-model="departureReal" type="datetime-local" class="input input-bordered input-sm w-full" />
                </fieldset>
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('trip.edit.arrival-platform-planned') }}</legend>
                    <input v-model="arrivalPlatformPlanned" type="text" class="input input-bordered input-sm w-full" />
                </fieldset>
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('trip.edit.departure-platform-planned') }}</legend>
                    <input
                        v-model="departurePlatformPlanned"
                        type="text"
                        class="input input-bordered input-sm w-full"
                    />
                </fieldset>
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('trip.edit.arrival-platform-real') }}</legend>
                    <input v-model="arrivalPlatformReal" type="text" class="input input-bordered input-sm w-full" />
                </fieldset>
                <fieldset class="fieldset p-0">
                    <legend class="fieldset-legend">{{ trans('trip.edit.departure-platform-real') }}</legend>
                    <input v-model="departurePlatformReal" type="text" class="input input-bordered input-sm w-full" />
                </fieldset>
            </div>

            <label class="label cursor-pointer justify-start gap-3">
                <input v-model="cancelled" type="checkbox" class="checkbox checkbox-sm" />
                <span class="label-text">{{ trans('stationboard.stop-cancelled') }}</span>
            </label>

            <div class="flex items-center justify-between gap-2">
                <button
                    type="button"
                    class="btn btn-ghost btn-sm text-error gap-1.5"
                    :disabled="deleting"
                    @click="remove"
                >
                    <span v-if="deleting" class="loading loading-spinner loading-xs" />
                    <Trash2 v-else class="size-4" />
                    {{ trans('delete') }}
                </button>
                <button type="button" class="btn btn-primary btn-sm gap-1.5" :disabled="saving" @click="save">
                    <span v-if="saving" class="loading loading-spinner loading-xs" />
                    <Save v-else class="size-4" />
                    {{ trans('save') }}
                </button>
            </div>
        </div>
    </div>
</template>
