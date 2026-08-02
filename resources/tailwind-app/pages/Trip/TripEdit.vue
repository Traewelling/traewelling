<script setup lang="ts">
import {
    ArrowLeft,
    ChevronsLeft,
    ChevronsRight,
    Clock,
    Hash,
    Layers,
    MapPin,
    Plus,
    Route,
    Save,
    Tag,
} from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { computed, inject, ref } from 'vue';
import { useRoute } from 'vue-router';
import { Api, HafasTravelType, StationResource, TripResource } from '../../../types/Api.gen';
import StationAutocomplete from '../../components/StationAutocomplete.vue';
import StopoverEditor from '../../components/Trip/StopoverEditor.vue';
import OperatorSearch from '../../components/TripCreation/OperatorSearch.vue';
import { TRANSPORT_CATEGORIES } from '../../composables/useTransportCategories';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;
const route = useRoute();

const tripUuid = computed(() => route.params.uuid as string);

const trip = ref<TripResource | null>(null);
const notFound = ref(false);
const refreshing = ref(false);
const savingTrip = ref(false);

const category = ref<string>(TRANSPORT_CATEGORIES[0].value);
const lineName = ref('');
const journeyNumber = ref('');
const operator = ref<{ uuid: string; name: string } | null>(null);

const SHIFT_UNIT_FACTORS = { minutes: 1, hours: 60, days: 1440 } as const;
type ShiftUnit = keyof typeof SHIFT_UNIT_FACTORS;

const shiftAmount = ref<number | null>(1);
const shiftUnit = ref<ShiftUnit>('hours');
const shifting = ref(false);

const newStation = ref<StationResource | null>(null);
const newArrival = ref('');
const newDeparture = ref('');
const addingStopover = ref(false);

function toIso(local: string): string | null {
    if (!local) return null;
    const dt = DateTime.fromISO(local);
    return dt.isValid ? dt.toISO() : null;
}

function syncMetadataForm(data: TripResource): void {
    category.value = data.category;
    lineName.value = data.lineName ?? '';
    journeyNumber.value = data.journeyNumber ? String(data.journeyNumber) : '';
    operator.value = data.operator ? { uuid: data.operator.uuid, name: data.operator.name } : null;
}

async function fetchTrip(syncForm = false): Promise<void> {
    refreshing.value = true;
    try {
        const response = await api.trips.getTrip(tripUuid.value);
        const data = response.data?.data as TripResource;
        trip.value = data;

        if (syncForm) {
            syncMetadataForm(data);
        }
    } catch {
        if (trip.value === null) {
            notFound.value = true;
        } else {
            notyf?.error(trans('generic.error'));
        }
    } finally {
        refreshing.value = false;
    }
}

async function saveTrip(): Promise<void> {
    savingTrip.value = true;
    try {
        await api.trips.updateTrip(tripUuid.value, {
            category: category.value as HafasTravelType,
            lineName: lineName.value,
            journeyNumber: journeyNumber.value ? Number(journeyNumber.value) : null,
            operatorUuid: operator.value?.uuid ?? null,
        });
        notyf?.success(trans('settings.saved'));
        await fetchTrip(true);
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        savingTrip.value = false;
    }
}

/**
 * Moves the whole trip in time. The backend shifts every stopover by the same offset, so the
 * relative timing of the trip stays intact and no stopover needs to be touched individually.
 */
async function shiftStopovers(direction: 1 | -1): Promise<void> {
    // The direction comes from the button, so a typed sign is irrelevant
    const amount = Math.abs(Number(shiftAmount.value));
    if (!Number.isFinite(amount) || amount === 0) {
        notyf?.error(trans('trip.edit.shift-amount-required'));
        return;
    }

    shifting.value = true;
    try {
        await api.trips.shiftTripStopovers(tripUuid.value, {
            minutes: Math.round(amount * SHIFT_UNIT_FACTORS[shiftUnit.value]) * direction,
        });
        notyf?.success(trans('trip.edit.stopovers-shifted'));
        await fetchTrip();
    } catch (error) {
        const message = (error as { error?: { message?: string } })?.error?.message;
        notyf?.error(message ?? trans('generic.error'));
    } finally {
        shifting.value = false;
    }
}

async function addStopover(): Promise<void> {
    if (!newStation.value?.uuid) {
        notyf?.error(trans('trip.edit.station-required'));
        return;
    }
    if (!newArrival.value && !newDeparture.value) {
        notyf?.error(trans('trip.edit.time-required'));
        return;
    }

    addingStopover.value = true;
    try {
        await api.trips.createTripStopover(tripUuid.value, {
            stationUuid: newStation.value.uuid,
            arrivalPlanned: toIso(newArrival.value),
            departurePlanned: toIso(newDeparture.value),
        });
        notyf?.success(trans('trip.edit.stopover-added'));
        newStation.value = null;
        newArrival.value = '';
        newDeparture.value = '';
        await fetchTrip();
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        addingStopover.value = false;
    }
}

fetchTrip(true);
</script>

<template>
    <AppLayout>
        <div class="flex items-center gap-3 mt-4 mb-4">
            <router-link :to="{ name: 'trip-list' }" class="btn btn-ghost btn-sm btn-circle">
                <ArrowLeft class="size-4" />
            </router-link>
            <h1 class="text-2xl font-bold">{{ trans('trip.edit.title') }}</h1>
            <span v-if="refreshing && trip" class="loading loading-spinner loading-xs opacity-50" />
        </div>

        <div v-if="notFound" role="alert" class="alert alert-error">
            {{ trans('trip.edit.not-found') }}
        </div>

        <div v-else-if="!trip" class="flex justify-center py-12">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else class="flex flex-col lg:flex-row lg:items-start gap-4 pb-8">
            <div class="flex flex-col gap-4 lg:w-[22rem] lg:shrink-0">
                <!-- Trip metadata -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4 space-y-4">
                        <h2 class="card-title text-base">
                            <Tag class="size-4" />
                            {{ trans('trip_creation.form.trip_data') }}
                        </h2>

                        <div class="grid grid-cols-2 gap-3">
                            <fieldset class="fieldset p-0">
                                <legend class="fieldset-legend">
                                    <Route class="size-3.5 inline mr-1" />
                                    {{ trans('trip_creation.form.line') }}
                                </legend>
                                <input
                                    v-model="lineName"
                                    type="text"
                                    class="input input-bordered input-sm w-full"
                                    :placeholder="trans('trip_creation.form.line.placeholder')"
                                />
                            </fieldset>
                            <fieldset class="fieldset p-0">
                                <legend class="fieldset-legend">
                                    <Hash class="size-3.5 inline mr-1" />
                                    {{ trans('trip_creation.form.number') }}
                                </legend>
                                <input
                                    v-model="journeyNumber"
                                    type="text"
                                    class="input input-bordered input-sm w-full"
                                    :placeholder="trans('trip_creation.form.number.placeholder')"
                                />
                            </fieldset>
                        </div>

                        <fieldset class="fieldset p-0">
                            <legend class="fieldset-legend">
                                <Layers class="size-3.5 inline mr-1" />
                                {{ trans('trip_creation.form.travel_type') }}
                            </legend>
                            <select v-model="category" class="select select-bordered select-sm w-full">
                                <option v-for="cat in TRANSPORT_CATEGORIES" :key="cat.value" :value="cat.value">
                                    {{ cat.emoji }} {{ trans('transport_types.' + cat.value) }}
                                </option>
                            </select>
                        </fieldset>

                        <OperatorSearch v-model="operator" />

                        <div class="flex justify-end">
                            <button
                                type="button"
                                class="btn btn-primary btn-sm gap-1.5"
                                :disabled="savingTrip"
                                @click="saveTrip"
                            >
                                <span v-if="savingTrip" class="loading loading-spinner loading-xs" />
                                <Save v-else class="size-4" />
                                {{ trans('save') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add stopover -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4 space-y-3">
                        <h2 class="card-title text-base">
                            <Plus class="size-4" />
                            {{ trans('trip_creation.form.add_stopover') }}
                        </h2>

                        <fieldset class="fieldset p-0">
                            <legend class="fieldset-legend">{{ trans('stationboard.stopover') }}</legend>
                            <StationAutocomplete v-model="newStation" />
                        </fieldset>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
                            <fieldset class="fieldset p-0">
                                <legend class="fieldset-legend">{{ trans('export.title.arrival_planned') }}</legend>
                                <input
                                    v-model="newArrival"
                                    type="datetime-local"
                                    class="input input-bordered input-sm w-full"
                                />
                            </fieldset>
                            <fieldset class="fieldset p-0">
                                <legend class="fieldset-legend">{{ trans('export.title.departure_planned') }}</legend>
                                <input
                                    v-model="newDeparture"
                                    type="datetime-local"
                                    class="input input-bordered input-sm w-full"
                                />
                            </fieldset>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="button"
                                class="btn btn-outline btn-sm gap-1.5"
                                :disabled="addingStopover"
                                @click="addStopover"
                            >
                                <span v-if="addingStopover" class="loading loading-spinner loading-xs" />
                                <Plus v-else class="size-4" />
                                {{ trans('trip_creation.form.add_stopover') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 lg:flex-1 lg:min-w-0">
                <!-- Stopovers -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4 space-y-3">
                        <h2 class="card-title text-base">
                            <MapPin class="size-4" />
                            {{ trans('trip_creation.form.stations') }}
                        </h2>

                        <p class="text-xs text-base-content/60">{{ trans('trip.edit.stopover-hint') }}</p>

                        <StopoverEditor
                            v-for="stopover in trip.stopovers"
                            :key="stopover.uuid ?? stopover.stopoverId"
                            :trip-uuid="tripUuid"
                            :stopover="stopover"
                            @changed="fetchTrip()"
                        />
                    </div>
                </div>

                <!-- Shift all stopovers in time -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4 space-y-3">
                        <h2 class="card-title text-base">
                            <Clock class="size-4" />
                            {{ trans('trip.edit.shift-title') }}
                        </h2>

                        <p class="text-xs text-base-content/60">{{ trans('trip.edit.shift-hint') }}</p>

                        <div class="join join-vertical sm:join-horizontal w-full sm:w-auto">
                            <button
                                type="button"
                                class="btn btn-sm join-item gap-1.5"
                                :disabled="shifting"
                                @click="shiftStopovers(-1)"
                            >
                                <span v-if="shifting" class="loading loading-spinner loading-xs" />
                                <ChevronsLeft v-else class="size-4" />
                                {{ trans('time.earlier') }}
                            </button>
                            <input
                                v-model.number="shiftAmount"
                                type="number"
                                min="1"
                                step="1"
                                class="input input-bordered input-sm join-item w-full sm:w-20 text-center"
                                :aria-label="trans('time.amount')"
                            />
                            <select
                                v-model="shiftUnit"
                                class="select select-bordered select-sm join-item w-full sm:w-28"
                                :aria-label="trans('time.unit')"
                            >
                                <option value="minutes">{{ trans('time.minutes') }}</option>
                                <option value="hours">{{ trans('time.hours') }}</option>
                                <option value="days">{{ trans('time.days') }}</option>
                            </select>
                            <button
                                type="button"
                                class="btn btn-sm join-item gap-1.5"
                                :disabled="shifting"
                                @click="shiftStopovers(1)"
                            >
                                {{ trans('time.later') }}
                                <span v-if="shifting" class="loading loading-spinner loading-xs" />
                                <ChevronsRight v-else class="size-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
