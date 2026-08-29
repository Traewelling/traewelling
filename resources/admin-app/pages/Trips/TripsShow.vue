<script setup lang="ts">
import { ArrowLeft, Trash2 } from '@lucide/vue';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import {
    type AdminStatusResource,
    type AdminStopoverResource,
    type AdminTripResource,
    Api,
    type StationIdentifierResource,
} from '../../../types/Api.gen';
import BackendLayout from '../../layouts/BackendLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const route = useRoute();
const tripId = computed(() => Number(route.params.id));

const trip = ref<AdminTripResource | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);
const rerouting = ref(false);
const rerouteSuccess = ref(false);
const creatingSegment = ref<number | null>(null);
const deletingStopover = ref<number | null>(null);

const identifierModalOpen = ref(false);
const identifierModalStopover = ref<AdminStopoverResource | null>(null);
const identifierModalNext = ref<AdminStopoverResource | null>(null);
const identifierModalFromList = ref<StationIdentifierResource[]>([]);
const identifierModalToList = ref<StationIdentifierResource[]>([]);
const identifierModalFromId = ref('');
const identifierModalToId = ref('');
const identifierModalLoading = ref(false);
const identifierModalError = ref<string | null>(null);

async function openIdentifierModal(stopover: AdminStopoverResource, next: AdminStopoverResource): Promise<void> {
    identifierModalStopover.value = stopover;
    identifierModalNext.value = next;
    identifierModalFromId.value = '';
    identifierModalToId.value = '';
    identifierModalError.value = null;
    identifierModalLoading.value = true;
    identifierModalOpen.value = true;
    try {
        const [fromRes, toRes] = await Promise.all([
            api.stations.showStation(stopover.station!.id!, { withIdentifiers: true }),
            api.stations.showStation(next.station!.id!, { withIdentifiers: true }),
        ]);
        identifierModalFromList.value = fromRes.data.data?.identifiers ?? [];
        identifierModalToList.value = toRes.data.data?.identifiers ?? [];
    } catch (e) {
        identifierModalError.value = e instanceof Error ? e.message : 'Failed to load identifiers';
    } finally {
        identifierModalLoading.value = false;
    }
}

async function confirmIdentifierSegment(): Promise<void> {
    if (!identifierModalStopover.value || !identifierModalNext.value) return;
    creatingSegment.value = identifierModalStopover.value.id!;
    identifierModalError.value = null;
    try {
        const res = await api.routeSegments.createRouteSegment({
            from_station_id: identifierModalStopover.value.station!.id!,
            to_station_id: identifierModalNext.value.station!.id!,
            stopover_id: identifierModalStopover.value.id,
            from_identifier_id: identifierModalFromId.value || undefined,
            to_identifier_id: identifierModalToId.value || undefined,
        });
        window.location.href = `${window.location.origin}/admin/routesegment/${res.data.data!.id}`;
    } catch (e) {
        identifierModalError.value = e instanceof Error ? e.message : 'Failed to create segment';
        creatingSegment.value = null;
    }
}

async function fetchTrip(): Promise<void> {
    loading.value = true;
    error.value = null;
    try {
        const res = await api.admin.getAdminTrip(tripId.value);
        trip.value = res.data.data ?? null;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Unknown error';
    } finally {
        loading.value = false;
    }
}

async function dispatchReroute(): Promise<void> {
    rerouting.value = true;
    rerouteSuccess.value = false;
    try {
        await api.admin.rerouteAdminTrip(tripId.value);
        rerouteSuccess.value = true;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Reroute failed';
    } finally {
        rerouting.value = false;
    }
}

async function createSegment(
    stopover: AdminStopoverResource,
    nextStopover: AdminStopoverResource,
    withIdentifiers: boolean,
): Promise<void> {
    creatingSegment.value = stopover.id!;
    try {
        const res = await api.routeSegments.createRouteSegment({
            from_station_id: stopover.station!.id!,
            to_station_id: nextStopover.station!.id!,
            stopover_id: stopover.id,
            ...(withIdentifiers && stopover.stationIdentifierId && nextStopover.stationIdentifierId
                ? {
                      from_identifier_id: stopover.stationIdentifierId,
                      to_identifier_id: nextStopover.stationIdentifierId,
                  }
                : {}),
        });
        window.location.href = `${window.location.origin}/admin/routesegment/${res.data.data!.id}`;
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to create segment';
        creatingSegment.value = null;
    }
}

async function deleteStopover(stopover: AdminStopoverResource): Promise<void> {
    if (!trip.value?.uuid || !stopover.uuid) return;
    if (!confirm(`Delete stopover "${stopover.station?.name}"?`)) return;
    deletingStopover.value = stopover.id!;
    error.value = null;
    try {
        await api.trips.deleteTripStopover(trip.value.uuid, stopover.uuid);
        await fetchTrip();
    } catch (e) {
        error.value = e instanceof Error ? e.message : 'Failed to delete stopover (referenced by checkins?)';
    } finally {
        deletingStopover.value = null;
    }
}

function formatTime(iso: string | null | undefined): string {
    if (!iso) return '—';
    return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

onMounted(fetchTrip);
watch(tripId, fetchTrip);
</script>

<template>
    <BackendLayout>
        <div v-if="loading" class="flex justify-center py-16">
            <span class="loading loading-spinner loading-lg" />
        </div>

        <div v-else-if="error" role="alert" class="alert alert-error mb-4">
            <span>{{ error }}</span>
        </div>

        <template v-else-if="trip">
            <div class="flex items-center gap-3 mb-6">
                <router-link to="/admin/trips" class="btn btn-ghost btn-sm gap-1">
                    <ArrowLeft class="w-4 h-4" />
                    Trips
                </router-link>
                <h1 class="text-2xl font-bold">Trip #{{ trip.id }}</h1>
                <span class="font-mono text-sm text-base-content/50">{{ trip.lineName }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[2fr_3fr] gap-6">
                <div class="space-y-4">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body gap-3">
                            <h2 class="card-title text-base">Details</h2>
                            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                                <dt class="text-base-content/50 font-medium">ID</dt>
                                <dd class="font-mono">{{ trip.id }}</dd>

                                <dt class="text-base-content/50 font-medium">UUID</dt>
                                <dd class="font-mono text-xs break-all">{{ trip.uuid }}</dd>

                                <dt class="text-base-content/50 font-medium">Trip ID</dt>
                                <dd class="font-mono text-xs break-all">
                                    <a
                                        v-if="trip.source === 'TRANSITOUS'"
                                        :href="`https://api.transitous.org/api/v6/trip?tripId=${encodeURIComponent(trip.tripId!)}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="link link-hover"
                                        >{{ trip.tripId }}</a
                                    >
                                    <template v-else>{{ trip.tripId }}</template>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Category</dt>
                                <dd class="font-mono text-xs">{{ trip.category }}</dd>

                                <dt class="text-base-content/50 font-medium">Mode</dt>
                                <dd class="font-mono text-xs">{{ trip.mode }}</dd>

                                <dt class="text-base-content/50 font-medium">Number</dt>
                                <dd class="font-mono text-xs">{{ trip.number }}</dd>

                                <dt class="text-base-content/50 font-medium">Line</dt>
                                <dd>{{ trip.lineName }}</dd>

                                <dt class="text-base-content/50 font-medium">Journey Nr.</dt>
                                <dd class="font-mono">{{ trip.journeyNumber }}</dd>

                                <dt class="text-base-content/50 font-medium">Operator</dt>
                                <dd>{{ trip.operator }}</dd>

                                <dt class="text-base-content/50 font-medium">Source</dt>
                                <dd class="flex items-center gap-2">
                                    <span>{{ trip.source }}</span>
                                    <a
                                        v-if="trip.user"
                                        :href="`/admin/users/${trip.user.id}`"
                                        class="link link-hover text-xs text-base-content/60"
                                    >
                                        @{{ trip.user.username }}
                                    </a>
                                </dd>

                                <dt class="text-base-content/50 font-medium">Last refreshed</dt>
                                <dd class="text-xs text-base-content/70">
                                    {{ trip.lastRefreshed ? new Date(trip.lastRefreshed).toLocaleString() : '' }}
                                </dd>
                            </dl>

                            <div class="mt-2 flex items-center gap-2">
                                <button class="btn btn-sm btn-primary" :disabled="rerouting" @click="dispatchReroute">
                                    <span v-if="rerouting" class="loading loading-spinner loading-xs" />
                                    Dispatch Reroute Job
                                </button>
                                <span v-if="rerouteSuccess" class="text-xs text-success">Job dispatched</span>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Statuses</h2>
                            <p v-if="!trip.statuses?.length" class="text-sm text-error font-medium">
                                No statuses for this trip.
                            </p>
                            <table v-else class="table table-sm">
                                <tbody>
                                    <tr
                                        v-for="status in trip.statuses as AdminStatusResource[]"
                                        :key="status.id"
                                        class="hover"
                                    >
                                        <td class="w-10 pr-0 align-middle">
                                            <img
                                                :src="status.user?.profilePicture"
                                                :alt="status.user?.username"
                                                class="block w-8 h-8 rounded-full object-cover shrink-0"
                                            />
                                        </td>
                                        <td class="text-sm">
                                            <div>{{ status.user?.displayName }}</div>
                                            <a
                                                :href="`/admin/users/${status.user?.id}`"
                                                class="link link-hover text-xs text-base-content/60"
                                            >
                                                @{{ status.user?.username }}
                                            </a>
                                            <div>
                                                <a
                                                    :href="`/admin/statuses/${status.id}`"
                                                    class="link link-hover text-xs"
                                                >
                                                    #{{ status.id }}
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-sm">
                                            <router-link
                                                v-if="status.checkin?.origin?.id"
                                                :to="`/admin/stations/${status.checkin.origin.id}`"
                                                class="link link-hover"
                                            >
                                                {{ status.checkin.origin.name }}
                                            </router-link>
                                            <div class="text-xs text-base-content/60">
                                                dep {{ formatTime(status.checkin?.origin?.departurePlanned) }}
                                            </div>
                                        </td>
                                        <td class="text-sm">
                                            <router-link
                                                v-if="status.checkin?.destination?.id"
                                                :to="`/admin/stations/${status.checkin.destination.id}`"
                                                class="link link-hover"
                                            >
                                                {{ status.checkin.destination.name }}
                                            </router-link>
                                            <div class="text-xs text-base-content/60">
                                                arr {{ formatTime(status.checkin?.destination?.arrivalPlanned) }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body">
                            <h2 class="card-title text-base">Stopovers</h2>
                            <table class="table table-sm w-full">
                                <colgroup>
                                    <col />
                                    <col class="w-20" />
                                    <col class="w-28" />
                                    <col class="w-28" />
                                    <col class="w-10" />
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>ID</th>
                                        <th>Arrival</th>
                                        <th>Departure</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(stopover, index) in trip.stopovers" :key="stopover.id">
                                        <tr class="hover">
                                            <td class="text-sm">
                                                <a
                                                    :href="`/admin/stations/${stopover.station?.id}`"
                                                    class="link link-hover"
                                                >
                                                    {{ stopover.station?.name }}
                                                </a>
                                            </td>
                                            <td class="font-mono text-xs">{{ stopover.station?.id }}</td>
                                            <td class="tabular-nums text-xs">
                                                <span>{{ formatTime(stopover.arrivalPlanned) }}</span>
                                                <span v-if="stopover.arrivalReal" class="text-base-content/50 ml-1">
                                                    / {{ formatTime(stopover.arrivalReal) }}
                                                </span>
                                            </td>
                                            <td class="tabular-nums text-xs">
                                                <span>{{ formatTime(stopover.departurePlanned) }}</span>
                                                <span v-if="stopover.departureReal" class="text-base-content/50 ml-1">
                                                    / {{ formatTime(stopover.departureReal) }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <button
                                                    class="btn btn-xs btn-ghost text-error"
                                                    :disabled="deletingStopover === stopover.id"
                                                    title="Delete stopover"
                                                    @click="deleteStopover(stopover)"
                                                >
                                                    <span
                                                        v-if="deletingStopover === stopover.id"
                                                        class="loading loading-spinner loading-xs"
                                                    />
                                                    <Trash2 v-else class="w-3 h-3" />
                                                </button>
                                            </td>
                                        </tr>

                                        <tr v-if="index < (trip.stopovers?.length ?? 0) - 1" class="bg-base-200">
                                            <td colspan="5" class="py-1 text-center text-xs text-base-content/50">
                                                <template v-if="stopover.routeSegmentId">
                                                    <a
                                                        :href="`/admin/routesegment/${stopover.routeSegmentId}`"
                                                        class="link link-hover"
                                                    >
                                                        Route segment
                                                    </a>
                                                    <span
                                                        v-if="stopover.routeSegmentType === 'identifier'"
                                                        class="badge badge-success badge-xs ml-2"
                                                    >
                                                        identifier → identifier
                                                    </span>
                                                    <template v-else>
                                                        <span class="badge badge-warning badge-xs ml-2">
                                                            station → station
                                                        </span>
                                                        <button
                                                            v-if="
                                                                stopover.stationIdentifierId &&
                                                                trip!.stopovers![index + 1].stationIdentifierId
                                                            "
                                                            class="btn btn-xs btn-ghost ml-2"
                                                            :disabled="!!creatingSegment"
                                                            @click="
                                                                createSegment(
                                                                    stopover,
                                                                    trip!.stopovers![index + 1],
                                                                    true,
                                                                )
                                                            "
                                                        >
                                                            <span
                                                                v-if="creatingSegment === stopover.id"
                                                                class="loading loading-spinner loading-xs"
                                                            />
                                                            upgrade to identifier → identifier
                                                        </button>
                                                        <button
                                                            v-else
                                                            class="btn btn-xs btn-ghost ml-2"
                                                            :disabled="!!creatingSegment"
                                                            @click="
                                                                openIdentifierModal(
                                                                    stopover,
                                                                    trip!.stopovers![index + 1],
                                                                )
                                                            "
                                                        >
                                                            upgrade to identifier → identifier
                                                        </button>
                                                    </template>
                                                </template>
                                                <template v-else>
                                                    No route segment
                                                    <template
                                                        v-if="
                                                            stopover.stationIdentifierId &&
                                                            trip!.stopovers![index + 1].stationIdentifierId
                                                        "
                                                    >
                                                        <button
                                                            class="btn btn-xs btn-primary ml-2"
                                                            :disabled="creatingSegment === stopover.id"
                                                            @click="
                                                                createSegment(
                                                                    stopover,
                                                                    trip!.stopovers![index + 1],
                                                                    true,
                                                                )
                                                            "
                                                        >
                                                            <span
                                                                v-if="creatingSegment === stopover.id"
                                                                class="loading loading-spinner loading-xs"
                                                            />
                                                            identifier → identifier
                                                        </button>
                                                        <button
                                                            class="btn btn-xs btn-ghost ml-1"
                                                            :disabled="creatingSegment === stopover.id"
                                                            @click="
                                                                createSegment(
                                                                    stopover,
                                                                    trip!.stopovers![index + 1],
                                                                    false,
                                                                )
                                                            "
                                                        >
                                                            station → station
                                                        </button>
                                                    </template>
                                                    <template v-else>
                                                        <button
                                                            class="btn btn-xs btn-primary ml-2"
                                                            :disabled="creatingSegment === stopover.id"
                                                            @click="
                                                                openIdentifierModal(
                                                                    stopover,
                                                                    trip!.stopovers![index + 1],
                                                                )
                                                            "
                                                        >
                                                            identifier → identifier
                                                        </button>
                                                        <button
                                                            class="btn btn-xs btn-ghost ml-1"
                                                            :disabled="creatingSegment === stopover.id"
                                                            @click="
                                                                createSegment(
                                                                    stopover,
                                                                    trip!.stopovers![index + 1],
                                                                    false,
                                                                )
                                                            "
                                                        >
                                                            <span
                                                                v-if="creatingSegment === stopover.id"
                                                                class="loading loading-spinner loading-xs"
                                                            />
                                                            station → station
                                                        </button>
                                                    </template>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Identifier picker modal -->
        <div v-if="identifierModalOpen" class="modal modal-open" role="dialog">
            <div class="modal-box max-w-lg">
                <h3 class="font-bold text-lg mb-1">Create identifier → identifier segment</h3>
                <p class="text-sm text-base-content/60 mb-4">
                    <span class="font-medium">{{ identifierModalStopover?.station?.name }}</span>
                    →
                    <span class="font-medium">{{ identifierModalNext?.station?.name }}</span>
                </p>

                <div v-if="identifierModalLoading" class="flex justify-center py-6">
                    <span class="loading loading-spinner loading-md" />
                </div>

                <template v-else>
                    <div class="form-control mb-3">
                        <label class="label pb-1">
                            <span class="label-text">
                                From identifier
                                <span class="text-base-content/50">({{ identifierModalStopover?.station?.name }})</span>
                            </span>
                        </label>
                        <select v-model="identifierModalFromId" class="select select-sm select-bordered font-mono">
                            <option value="">— none —</option>
                            <option v-for="ident in identifierModalFromList" :key="ident.id" :value="ident.id">
                                {{ ident.type }} · {{ ident.identifier }}
                            </option>
                        </select>
                    </div>

                    <div class="form-control mb-4">
                        <label class="label pb-1">
                            <span class="label-text">
                                To identifier
                                <span class="text-base-content/50">({{ identifierModalNext?.station?.name }})</span>
                            </span>
                        </label>
                        <select v-model="identifierModalToId" class="select select-sm select-bordered font-mono">
                            <option value="">— none —</option>
                            <option v-for="ident in identifierModalToList" :key="ident.id" :value="ident.id">
                                {{ ident.type }} · {{ ident.identifier }}
                            </option>
                        </select>
                    </div>

                    <div
                        v-if="!identifierModalFromList.length || !identifierModalToList.length"
                        role="alert"
                        class="alert alert-warning py-2 text-sm mb-3"
                    >
                        One or both stations have no identifiers. Add identifiers first.
                    </div>

                    <div v-if="identifierModalError" role="alert" class="alert alert-error py-2 text-sm mb-3">
                        {{ identifierModalError }}
                    </div>
                </template>

                <div class="modal-action mt-0">
                    <button
                        class="btn btn-ghost btn-sm"
                        :disabled="!!creatingSegment"
                        @click="identifierModalOpen = false"
                    >
                        Cancel
                    </button>
                    <button
                        class="btn btn-primary btn-sm"
                        :disabled="
                            !identifierModalFromId ||
                            !identifierModalToId ||
                            !!creatingSegment ||
                            identifierModalLoading
                        "
                        @click="confirmIdentifierSegment"
                    >
                        <span v-if="creatingSegment" class="loading loading-spinner loading-xs" />
                        Create
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="identifierModalOpen = false" />
        </div>
    </BackendLayout>
</template>
