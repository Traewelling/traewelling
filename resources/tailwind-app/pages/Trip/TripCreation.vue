<script setup lang="ts">
import { FileSpreadsheet, GripVertical, Hash, Layers, MapPin, Plus, Route, Save, Tag, Timer } from '@lucide/vue';
import { trans } from 'laravel-vue-i18n';
import { DateTime } from 'luxon';
import { GeoJSONFeature, LngLat, LngLatBounds } from 'maplibre-gl';
import { Notyf } from 'notyf';
import { computed, inject, nextTick, onMounted, ref, useTemplateRef, watch } from 'vue';
import { useRouter } from 'vue-router';
import { Api, StationResource } from '../../../types/Api.gen';
import GenericMap from '../../../vue/components/Map/GenericMap.vue';
import CsvImporterDrawer, { ImportedStop } from '../../components/TripCreation/CsvImporterDrawer.vue';
import OperatorSearch from '../../components/TripCreation/OperatorSearch.vue';
import StationRow from '../../components/TripCreation/StationRow.vue';
import { useStopoverDrag } from '../../composables/useStopoverDrag';
import { TRANSPORT_CATEGORIES } from '../../composables/useTransportCategories';
import AppLayout from '../../layouts/AppLayout.vue';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });
const notyf = inject('notyf') as Notyf;
const router = useRouter();

type Stopover = { uid: number; station: StationResource | null; arrivalPlanned: string; departurePlanned: string };

let stopoverUid = 0;
function makeStopover(base: DateTime): Stopover {
    return {
        uid: stopoverUid++,
        station: null,
        arrivalPlanned: '',
        departurePlanned: base.toFormat("yyyy-MM-dd'T'HH:mm"),
    };
}

const DISALLOWED = ['fahrrad', 'auto', 'fuss', 'fuß', 'foot', 'car', 'bike'];

const lineName = ref('');
const journeyNumber = ref('');
const selectedCategory = ref<(typeof TRANSPORT_CATEGORIES)[number]>(TRANSPORT_CATEGORIES[0]);
const selectedOperator = ref<{ uuid: string; name: string } | null>(null);

const originStation = ref<StationResource | null>(null);
const originDeparture = ref('');
const destinationStation = ref<StationResource | null>(null);
const destinationArrival = ref('');
const stopovers = ref<Stopover[]>([]);

const showDisallowed = computed(() => DISALLOWED.some((d) => lineName.value.toLowerCase().includes(d)));

const originRow = useTemplateRef<InstanceType<typeof StationRow>>('originRow');
const destinationRow = useTemplateRef<InstanceType<typeof StationRow>>('destinationRow');
const stopoverRows = useTemplateRef<InstanceType<typeof StationRow>[]>('stopoverRows');
const csvDrawer = useTemplateRef<InstanceType<typeof CsvImporterDrawer>>('csvDrawer');

const { draggedIndex, dropTargetIndex, onDragStart, onDragOver, onDrop, onDragEnd } = useStopoverDrag(stopovers);

function toIso(localStr: string): string {
    const dt = DateTime.fromISO(localStr);
    return dt.isValid ? dt.toISO()! : '';
}

function latestTime(): DateTime {
    const times: DateTime[] = [
        originDeparture.value,
        destinationArrival.value,
        ...stopovers.value.flatMap((s) => [s.departurePlanned, s.arrivalPlanned]),
    ]
        .filter(Boolean)
        .map((v) => DateTime.fromISO(v))
        .filter((d) => d.isValid);
    return times.sort((a, b) => b.toMillis() - a.toMillis())[0] ?? DateTime.now();
}

function addStopover(atIndex?: number): void {
    const stop = makeStopover(latestTime());
    if (atIndex !== undefined) {
        stopovers.value.splice(atIndex, 0, stop);
    } else {
        stopovers.value.push(stop);
    }
}

function removeStopover(index: number): void {
    stopovers.value.splice(index, 1);
}

const stopoverBounds = computed(() =>
    stopovers.value.map((stop, i) => {
        const prev =
            i === 0
                ? originDeparture.value
                : stopovers.value[i - 1].departurePlanned ||
                  stopovers.value[i - 1].arrivalPlanned ||
                  originDeparture.value;
        const next =
            i === stopovers.value.length - 1
                ? destinationArrival.value
                : stopovers.value[i + 1].arrivalPlanned ||
                  stopovers.value[i + 1].departurePlanned ||
                  destinationArrival.value;
        const minArr = prev || '';
        const minDep = stop.arrivalPlanned && minArr && stop.arrivalPlanned > minArr ? stop.arrivalPlanned : minArr;
        const maxDep = next || '';
        const maxArr =
            stop.departurePlanned && maxDep && stop.departurePlanned < maxDep ? stop.departurePlanned : maxDep;
        return { minArrival: minArr, minDeparture: minDep, maxArrival: maxArr, maxDeparture: maxDep };
    }),
);

const minDestinationArrival = computed<string>(() => {
    if (stopovers.value.length === 0) return originDeparture.value || '';
    const last = stopovers.value[stopovers.value.length - 1];
    return last.departurePlanned || last.arrivalPlanned || originDeparture.value || '';
});

const canDistribute = computed(
    () => !!originDeparture.value && !!destinationArrival.value && stopovers.value.length > 0,
);

function distributeTimes(): void {
    const start = DateTime.fromISO(originDeparture.value);
    const end = DateTime.fromISO(destinationArrival.value);
    if (!start.isValid || !end.isValid || end <= start) return;
    const n = stopovers.value.length + 1;
    stopovers.value.forEach((stop, i) => {
        const t = DateTime.fromMillis(start.toMillis() + ((i + 1) / n) * (end.toMillis() - start.toMillis()));
        const localStr = t.toFormat("yyyy-MM-dd'T'HH:mm");
        stop.arrivalPlanned = localStr;
        stop.departurePlanned = localStr;
    });
}

function validateTimes(): boolean {
    try {
        const origin = DateTime.fromISO(originDeparture.value);
        if (!origin.isValid) return false;
        let cursor = origin;
        for (const s of stopovers.value) {
            const arr = DateTime.fromISO(s.arrivalPlanned || s.departurePlanned);
            const dep = DateTime.fromISO(s.departurePlanned || s.arrivalPlanned);
            if (!arr.isValid || !dep.isValid) return false;
            if (arr < cursor || dep < arr) return false;
            cursor = dep;
        }
        const dest = DateTime.fromISO(destinationArrival.value);
        return dest.isValid && dest >= cursor;
    } catch {
        return false;
    }
}

function onCsvImported(stops: ImportedStop[]): void {
    if (stops.length < 2) {
        notyf.error(trans('trip_creation.csv_import.errors.min_two_rows'));
        return;
    }
    const [first, ...rest] = stops;
    const last = rest.pop()!;
    const middle = rest;

    if (first.station) {
        originStation.value = first.station;
        originRow.value?.setStation(first.station);
    }
    if (first.departurePlanned) originDeparture.value = first.departurePlanned;
    if (last.station) {
        destinationStation.value = last.station;
        destinationRow.value?.setStation(last.station);
    }
    if (last.arrivalPlanned) destinationArrival.value = last.arrivalPlanned;

    stopovers.value = middle.map((s) => ({
        uid: stopoverUid++,
        station: s.station,
        arrivalPlanned: s.arrivalPlanned,
        departurePlanned: s.departurePlanned,
    }));

    nextTick(() => {
        const rows = Array.isArray(stopoverRows.value) ? stopoverRows.value : [];
        middle.forEach((s, i) => rows[i]?.setStation(s.station));
    });
}

const MARKER_COLORS = { origin: '#16a34a', stopover: '#6b7280', destination: '#dc2626' };

const mapMarkers = computed(() => {
    const out: { id: string; lat: number; lng: number; color: string; title?: string }[] = [];
    if (originStation.value?.latitude && originStation.value?.longitude) {
        out.push({
            id: 'origin',
            lat: originStation.value.latitude,
            lng: originStation.value.longitude,
            color: MARKER_COLORS.origin,
            title: originStation.value.name,
        });
    }
    stopovers.value.forEach((s, i) => {
        if (s.station?.latitude && s.station?.longitude) {
            out.push({
                id: `stop-${i}`,
                lat: s.station.latitude,
                lng: s.station.longitude,
                color: MARKER_COLORS.stopover,
                title: s.station.name,
            });
        }
    });
    if (destinationStation.value?.latitude && destinationStation.value?.longitude) {
        out.push({
            id: 'destination',
            lat: destinationStation.value.latitude,
            lng: destinationStation.value.longitude,
            color: MARKER_COLORS.destination,
            title: destinationStation.value.name,
        });
    }
    return out;
});

const mapBounds = computed<LngLatBounds>(() => {
    const valid = mapMarkers.value;
    if (!valid.length) return LngLatBounds.fromLngLat(new LngLat(10.0, 51.0), 500000);
    const b = new LngLatBounds();
    for (const m of valid) b.extend([m.lng, m.lat]);
    const sw = b.getSouthWest();
    const ne = b.getNorthEast();
    const latPad = Math.max((ne.lat - sw.lat) * 0.25, 0.03);
    const lngPad = Math.max((ne.lng - sw.lng) * 0.25, 0.03);
    return new LngLatBounds([sw.lng - lngPad, sw.lat - latPad], [ne.lng + lngPad, ne.lat + latPad]);
});

const routePolyline = ref<GeoJSONFeature | null>(null);
let routeDebounce: ReturnType<typeof setTimeout> | null = null;

const orderedStationIds = computed<number[]>(() => {
    const ids: number[] = [];
    if (originStation.value?.id) ids.push(originStation.value.id as number);
    stopovers.value.forEach((s) => {
        if (s.station?.id) ids.push(s.station.id as number);
    });
    if (destinationStation.value?.id) ids.push(destinationStation.value.id as number);
    return ids;
});

async function fetchRoutePreview(): Promise<void> {
    const ids = orderedStationIds.value;
    if (ids.length < 2) {
        routePolyline.value = null;
        return;
    }
    try {
        const res = await api.trips.routePreviewTrip({
            category: selectedCategory.value.value as Parameters<typeof api.trips.routePreviewTrip>[0]['category'],
            stationIds: ids,
        });
        routePolyline.value = (res.data?.data as GeoJSONFeature) ?? null;
    } catch {
        routePolyline.value = null;
    }
}

watch(
    [orderedStationIds, () => selectedCategory.value.value],
    () => {
        if (routeDebounce) clearTimeout(routeDebounce);
        routeDebounce = setTimeout(fetchRoutePreview, 600);
    },
    { deep: true },
);

function submit(): void {
    if (showDisallowed.value) {
        notyf.error(trans('trip_creation.limitations.6'));
        return;
    }
    if (!validateTimes()) {
        notyf.error(trans('trip_creation.no-valid-times'));
        return;
    }

    const formData = {
        originId: originStation.value?.id ?? '',
        originDeparturePlanned: toIso(originDeparture.value),
        destinationId: destinationStation.value?.id ?? '',
        destinationArrivalPlanned: toIso(destinationArrival.value),
        lineName: lineName.value,
        journeyNumber:
            journeyNumber.value && !isNaN(parseInt(journeyNumber.value)) ? parseInt(journeyNumber.value) : null,
        operatorId: selectedOperator.value?.uuid ?? null,
        category: selectedCategory.value.value,
        stopovers: stopovers.value.map((s) => ({
            stationId: s.station?.id ?? '',
            ...(s.arrivalPlanned ? { arrival: toIso(s.arrivalPlanned) } : {}),
            ...(s.departurePlanned ? { departure: toIso(s.departurePlanned) } : {}),
        })),
    };

    api.trips
        .createTrip(formData)
        .then((response) => {
            const result = response.data?.data;
            router.push({
                name: 'checkin',
                query: {
                    tripId: result.tripId,
                    lineName: result.lineName,
                    departure: formData.originDeparturePlanned,
                    category: result.category,
                    originName: result.origin?.name,
                },
            });
        })
        .catch((error) => {
            if (error?.status === 403 || error?.status === 422 || error?.status === 400) {
                notyf.error(error.error?.message ?? trans('messages.exception.general-values'));
            } else {
                notyf.error(trans('messages.exception.general-values'));
            }
        });
}

onMounted(() => {
    const stationId = new URLSearchParams(window.location.search).get('from');
    if (stationId) {
        api.stations.showStation(stationId).then((result) => {
            const station = result.data?.data as StationResource;
            if (station) {
                originStation.value = station;
                originRow.value?.setStation(station);
            }
        });
    }
});
</script>

<template>
    <AppLayout>
        <h1 class="text-2xl font-bold mt-4 mb-4">{{ trans('trip_creation.title') }}</h1>

        <div class="flex flex-col lg:flex-row gap-4 pb-8">
            <!-- Left column: form -->
            <div class="flex flex-col gap-4 lg:w-[55%]">
                <!-- Trip metadata -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4 space-y-4">
                        <h2 class="card-title text-base">
                            <Tag class="w-4 h-4" />
                            {{ trans('trip_creation.form.trip_data') }}
                        </h2>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text flex items-center gap-1">
                                        <Route class="w-3.5 h-3.5" />
                                        {{ trans('trip_creation.form.line') }}
                                    </span>
                                </label>
                                <input
                                    v-model="lineName"
                                    type="text"
                                    class="input input-bordered input-sm"
                                    :placeholder="trans('trip_creation.form.line.placeholder')"
                                />
                            </div>
                            <div class="form-control">
                                <label class="label py-1">
                                    <span class="label-text flex items-center gap-1">
                                        <Hash class="w-3.5 h-3.5" />
                                        {{ trans('trip_creation.form.number') }}
                                    </span>
                                </label>
                                <input
                                    v-model="journeyNumber"
                                    type="text"
                                    class="input input-bordered input-sm"
                                    :placeholder="trans('trip_creation.form.number.placeholder')"
                                />
                            </div>
                        </div>

                        <div v-if="showDisallowed" role="alert" class="alert alert-error py-2 text-sm">
                            {{ trans('trip_creation.limitations.6') }}
                            <a
                                :href="trans('trip_creation.limitations.6.link')"
                                target="_blank"
                                class="link font-semibold"
                            >
                                {{ trans('trip_creation.limitations.6.rules') }}
                            </a>
                        </div>

                        <fieldset class="fieldset p-0">
                            <legend class="fieldset-legend">
                                <Layers class="w-3.5 h-3.5 inline mr-1" />
                                {{ trans('trip_creation.form.travel_type') }}
                            </legend>
                            <select v-model="selectedCategory" class="select select-bordered select-sm w-full">
                                <option v-for="cat in TRANSPORT_CATEGORIES" :key="cat.value" :value="cat">
                                    {{ cat.emoji }} {{ trans('transport_types.' + cat.value) }}
                                </option>
                            </select>
                        </fieldset>

                        <OperatorSearch v-model="selectedOperator" />
                    </div>
                </div>

                <!-- Stops -->
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4 space-y-2">
                        <h2 class="card-title text-base mb-2">
                            <MapPin class="w-4 h-4" />
                            {{ trans('trip_creation.form.stations') }}
                        </h2>

                        <!-- Origin -->
                        <div class="flex gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-success shrink-0 mt-3" />
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-base-content/60 mb-1">
                                    {{ trans('trip_creation.form.origin') }}
                                </p>
                                <StationRow
                                    ref="originRow"
                                    :placeholder="trans('trip_creation.form.origin')"
                                    :show-arrival="false"
                                    :show-departure="true"
                                    :departure-value="originDeparture"
                                    @select-station="(s) => (originStation = s)"
                                    @update-departure="(v) => (originDeparture = v)"
                                />
                            </div>
                        </div>

                        <!-- Add-button after origin -->
                        <div class="flex items-center gap-2 py-0.5 pl-1">
                            <div class="flex-1 border-t border-dashed border-base-300" />
                            <button
                                type="button"
                                class="btn btn-ghost btn-xs gap-1 text-base-content/40 hover:text-primary"
                                @click="addStopover(0)"
                            >
                                <Plus class="w-3 h-3" />
                                {{ trans('trip_creation.form.add_stopover') }}
                            </button>
                            <div class="flex-1 border-t border-dashed border-base-300" />
                        </div>

                        <!-- Stopovers -->
                        <template v-for="(stop, i) in stopovers" :key="stop.uid">
                            <div
                                class="flex gap-2 rounded-lg transition-opacity"
                                :class="{
                                    'opacity-30': draggedIndex === i,
                                    'outline outline-2 outline-primary outline-offset-1':
                                        dropTargetIndex === i && draggedIndex !== i,
                                }"
                                draggable="true"
                                @dragstart="onDragStart(i, $event)"
                                @dragover="onDragOver(i, $event)"
                                @drop="onDrop(i)"
                                @dragend="onDragEnd"
                            >
                                <GripVertical
                                    class="w-4 h-4 shrink-0 mt-3 text-base-content/25 cursor-grab active:cursor-grabbing"
                                />
                                <span class="w-2.5 h-2.5 rounded-full bg-base-content/25 shrink-0 mt-3" />
                                <div class="flex-1">
                                    <p class="text-xs font-semibold text-base-content/60 mb-1">
                                        {{ trans('trip_creation.form.stopover') }}
                                    </p>
                                    <StationRow
                                        ref="stopoverRows"
                                        :placeholder="trans('trip_creation.form.stopover')"
                                        :show-arrival="true"
                                        :show-departure="true"
                                        :can-delete="true"
                                        :min-arrival="stopoverBounds[i]?.minArrival"
                                        :min-departure="stopoverBounds[i]?.minDeparture"
                                        :max-arrival="stopoverBounds[i]?.maxArrival"
                                        :max-departure="stopoverBounds[i]?.maxDeparture"
                                        :arrival-value="stop.arrivalPlanned"
                                        :departure-value="stop.departurePlanned"
                                        @select-station="(s) => (stop.station = s)"
                                        @update-arrival="(v) => (stop.arrivalPlanned = v)"
                                        @update-departure="(v) => (stop.departurePlanned = v)"
                                        @delete="removeStopover(i)"
                                    />
                                </div>
                            </div>

                            <!-- Add-button after this stopover -->
                            <div class="flex items-center gap-2 py-0.5 pl-1">
                                <div class="flex-1 border-t border-dashed border-base-300" />
                                <button
                                    type="button"
                                    class="btn btn-ghost btn-xs gap-1 text-base-content/40 hover:text-primary"
                                    @click="addStopover(i + 1)"
                                >
                                    <Plus class="w-3 h-3" />
                                    {{ trans('trip_creation.form.add_stopover') }}
                                </button>
                                <div class="flex-1 border-t border-dashed border-base-300" />
                            </div>
                        </template>

                        <!-- Destination -->
                        <div class="flex gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-error shrink-0 mt-3" />
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-base-content/60 mb-1">
                                    {{ trans('trip_creation.form.destination') }}
                                </p>
                                <StationRow
                                    ref="destinationRow"
                                    :placeholder="trans('trip_creation.form.destination')"
                                    :show-arrival="true"
                                    :show-departure="false"
                                    :min-arrival="minDestinationArrival"
                                    :arrival-value="destinationArrival"
                                    @select-station="(s) => (destinationStation = s)"
                                    @update-arrival="(v) => (destinationArrival = v)"
                                />
                            </div>
                        </div>

                        <!-- Distribute times tool -->
                        <div v-if="canDistribute" class="flex justify-end pt-1">
                            <button
                                type="button"
                                class="btn btn-ghost btn-xs gap-1.5 text-base-content/50 hover:text-primary"
                                @click="distributeTimes"
                            >
                                <Timer class="w-3.5 h-3.5" />
                                {{ trans('trip_creation.form.distribute-times') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right column: map + actions -->
            <div class="flex flex-col gap-4 lg:flex-1">
                <div class="card bg-base-100 shadow-sm overflow-hidden">
                    <GenericMap
                        :bounds="mapBounds"
                        :preview-polyline="routePolyline"
                        :preview-markers="mapMarkers"
                        style="height: 280px"
                    />
                </div>

                <div class="flex items-center justify-between gap-4">
                    <button type="button" class="btn btn-ghost btn-sm gap-2" @click="csvDrawer?.open()">
                        <FileSpreadsheet class="w-4 h-4" />
                        {{ trans('trip_creation.csv_import.button') }}
                    </button>

                    <button type="button" class="btn btn-primary gap-2" :disabled="showDisallowed" @click="submit">
                        <Save class="w-4 h-4" />
                        {{ trans('trip_creation.form.save') }}
                    </button>
                </div>
            </div>
        </div>

        <CsvImporterDrawer ref="csvDrawer" :max-items="50" @imported="onCsvImported" />
    </AppLayout>
</template>
