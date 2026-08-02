<script setup lang="ts">
import { ArrowRight, Pencil, Plus, Route, Trash2 } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { Notyf } from 'notyf';
import { inject, ref } from 'vue';
import { Api, TripResource } from '../../../types/Api.gen';
import TransportIcon from '../../components/TransportIcon.vue';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;

const trips = ref<TripResource[]>([]);
const nextCursor = ref<string | null>(null);
const hasMore = ref(false);
const loading = ref(true);
const tripToDelete = ref<TripResource | null>(null);
const deleting = ref(false);

async function fetchTrips(): Promise<void> {
    loading.value = true;
    try {
        const response = await api.trips.getOwnTrips({ cursor: nextCursor.value ?? undefined });
        const data = await response.json();
        trips.value.push(...(data.data ?? []));
        nextCursor.value = data.meta?.next_cursor ?? null;
        hasMore.value = nextCursor.value !== null;
    } finally {
        loading.value = false;
    }
}

function departureOf(trip: TripResource): string | null {
    return trip.stopovers?.[0]?.departurePlanned ?? trip.stopovers?.[0]?.arrivalPlanned ?? null;
}

function formatDate(iso: string | null): string {
    if (!iso) return '';
    const dt = DateTime.fromISO(iso);
    return dt.isValid ? dt.toFormat('dd.MM.yyyy HH:mm') : '';
}

async function confirmDelete(): Promise<void> {
    const trip = tripToDelete.value;
    if (!trip?.uuid) return;

    deleting.value = true;
    try {
        await api.trips.deleteTrip(trip.uuid);
        trips.value = trips.value.filter((entry) => entry.uuid !== trip.uuid);
        tripToDelete.value = null;
        notyf?.success(trans('trip.delete.deleted'));
    } catch {
        notyf?.error(trans('generic.error'));
    } finally {
        deleting.value = false;
    }
}

fetchTrips();
</script>

<template>
    <AppLayout>
        <div class="flex items-center justify-between mt-4 mb-4 gap-4">
            <h1 class="text-2xl font-bold">{{ trans('trip.list.heading') }}</h1>
            <router-link :to="{ name: 'trip-create' }" class="btn btn-primary btn-sm gap-2">
                <Plus class="size-4" />
                {{ trans('trip_creation.title') }}
            </router-link>
        </div>

        <div v-if="loading && trips.length === 0" class="flex justify-center py-12">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="trips.length === 0" class="text-center py-12 text-base-content/50">
            <Route class="size-8 mx-auto mb-2" />
            <p>{{ trans('trip.list.empty') }}</p>
        </div>

        <ul v-else class="list bg-base-100 rounded-box shadow-md">
            <li v-for="trip in trips" :key="trip.uuid ?? trip.id" class="list-row items-center gap-3">
                <TransportIcon :product="trip.category" :mode="trip.mode" class="size-6 shrink-0" />

                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold">{{ trip.lineName }}</span>
                        <span v-if="trip.journeyNumber" class="badge badge-ghost badge-sm">
                            {{ trip.journeyNumber }}
                        </span>
                    </div>
                    <div class="flex items-center gap-1.5 text-sm text-base-content/70 flex-wrap">
                        <span class="truncate">{{ trip.origin?.name }}</span>
                        <ArrowRight class="size-3.5 shrink-0" />
                        <span class="truncate">{{ trip.destination?.name }}</span>
                    </div>
                    <div class="flex flex-wrap gap-x-3 text-xs text-base-content/50">
                        <span>{{ formatDate(departureOf(trip)) }}</span>
                        <span v-if="trip.operator">{{ trip.operator.name }}</span>
                        <span v-if="trip.stopovers?.length">
                            {{ trans('trip.list.stopover-count', { count: trip.stopovers.length.toString() }) }}
                        </span>
                        <span>
                            {{ trans('trip.list.checkin-count', { count: trip.checkinCount.toString() }) }}
                        </span>
                    </div>
                </div>

                <div v-if="trip.uuid" class="flex items-center gap-1">
                    <router-link
                        :to="{ name: 'trip-edit', params: { uuid: trip.uuid } }"
                        class="btn btn-ghost btn-sm gap-1.5"
                    >
                        <Pencil class="size-4" />
                        {{ trans('edit') }}
                    </router-link>
                    <span
                        class="tooltip tooltip-left"
                        :data-tip="trip.checkinCount > 0 ? trans('trip.delete.blocked') : trans('trip.delete.title')"
                    >
                        <button
                            class="btn btn-ghost btn-sm btn-square text-error"
                            :disabled="trip.checkinCount > 0"
                            @click="tripToDelete = trip"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </span>
                </div>
            </li>
        </ul>

        <dialog class="modal" :class="{ 'modal-open': tripToDelete !== null }">
            <div class="modal-box">
                <h3 class="font-bold text-lg">{{ trans('trip.delete.title') }}</h3>
                <p class="py-3 text-sm">{{ trans('trip.delete.description') }}</p>
                <div class="modal-action">
                    <button class="btn btn-ghost" :disabled="deleting" @click="tripToDelete = null">
                        {{ trans('cancel') }}
                    </button>
                    <button class="btn btn-error" :disabled="deleting" @click="confirmDelete">
                        <span v-if="deleting" class="loading loading-spinner loading-xs" />
                        {{ trans('delete') }}
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop" @submit.prevent="tripToDelete = null">
                <button>close</button>
            </form>
        </dialog>

        <div v-if="hasMore" class="flex justify-center py-4">
            <button class="btn btn-ghost btn-sm" :disabled="loading" @click="fetchTrips">
                <span v-if="loading" class="loading loading-spinner loading-xs" />
                {{ trans('load-more') }}
            </button>
        </div>
    </AppLayout>
</template>
