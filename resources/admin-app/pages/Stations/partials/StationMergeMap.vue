<script setup lang="ts">
import { Check, ExternalLink, GitMerge, X } from '@lucide/vue';
import maplibregl, { type GeoJSONSource, type MapLayerMouseEvent } from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Api, type StationResource } from '../../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const MIN_ZOOM = 11;
const FETCH_LIMIT = 1000;
const DEBOUNCE_MS = 350;
const MAX_MERGE_STATIONS = 10;

const mapElement = ref<HTMLDivElement | null>(null);
const stations = ref<StationResource[]>([]);
const selectedStationIds = ref<number[]>([]);
const loading = ref(false);
const merging = ref(false);
const error = ref<string | null>(null);
const progress = ref<string | null>(null);
const zoom = ref(5.5);
const confirmModalOpen = ref(false);
const selectionBox = ref<{ left: number; top: number; width: number; height: number } | null>(null);

let map: maplibregl.Map | null = null;
let debounceTimer: ReturnType<typeof setTimeout> | null = null;
let requestSequence = 0;
let selectionStart: { x: number; y: number } | null = null;

const selectedStations = computed(() =>
    selectedStationIds.value
        .map((id) => stations.value.find((station) => station.id === id))
        .filter((station): station is StationResource => station !== undefined)
        .sort((a, b) => a.id! - b.id!),
);

const targetStation = computed(() => selectedStations.value[0] ?? null);
const sourceStations = computed(() => selectedStations.value.slice(1));
const canMerge = computed(
    () => selectedStations.value.length >= 2 && selectedStations.value.length <= MAX_MERGE_STATIONS && !merging.value,
);

function stationData(): GeoJSON.FeatureCollection<GeoJSON.Point> {
    return {
        type: 'FeatureCollection',
        features: stations.value.map((station) => ({
            type: 'Feature',
            geometry: {
                type: 'Point',
                coordinates: [station.longitude!, station.latitude!],
            },
            properties: {
                id: station.id,
                name: station.name,
                selected: selectedStationIds.value.includes(station.id!),
                target: station.id === targetStation.value?.id,
            },
        })),
    };
}

function updateMapData(): void {
    const source = map?.getSource('stations') as GeoJSONSource | undefined;
    source?.setData(stationData());
}

function toggleStation(stationId: number): void {
    if (merging.value) return;

    if (selectedStationIds.value.includes(stationId)) {
        selectedStationIds.value = selectedStationIds.value.filter((id) => id !== stationId);
    } else if (selectedStationIds.value.length >= MAX_MERGE_STATIONS) {
        error.value = `A maximum of ${MAX_MERGE_STATIONS} stations can be merged at once.`;
        return;
    } else {
        selectedStationIds.value = [...selectedStationIds.value, stationId];
    }
    error.value = null;
    updateMapData();
}

function clearSelection(): void {
    selectedStationIds.value = [];
    error.value = null;
    progress.value = null;
    updateMapData();
}

function onStationClick(event: MapLayerMouseEvent): void {
    const stationId = Number(event.features?.[0]?.properties?.id);
    if (Number.isFinite(stationId)) {
        toggleStation(stationId);
    }
}

function pointInMap(event: MouseEvent): { x: number; y: number } | null {
    const canvas = map?.getCanvas();
    if (!canvas) return null;

    const bounds = canvas.getBoundingClientRect();
    return {
        x: Math.min(Math.max(event.clientX - bounds.left, 0), bounds.width),
        y: Math.min(Math.max(event.clientY - bounds.top, 0), bounds.height),
    };
}

function startAreaSelection(event: MouseEvent): void {
    if (!event.shiftKey || event.button !== 0 || merging.value) return;

    const point = pointInMap(event);
    if (!point) return;

    event.preventDefault();
    selectionStart = point;
    selectionBox.value = { left: point.x, top: point.y, width: 0, height: 0 };
    map?.dragPan.disable();
    document.addEventListener('mousemove', updateAreaSelection);
    document.addEventListener('mouseup', finishAreaSelection, { once: true });
}

function updateAreaSelection(event: MouseEvent): void {
    if (!selectionStart) return;

    const point = pointInMap(event);
    if (!point) return;

    selectionBox.value = {
        left: Math.min(selectionStart.x, point.x),
        top: Math.min(selectionStart.y, point.y),
        width: Math.abs(point.x - selectionStart.x),
        height: Math.abs(point.y - selectionStart.y),
    };
}

function finishAreaSelection(event: MouseEvent): void {
    document.removeEventListener('mousemove', updateAreaSelection);

    const point = pointInMap(event);
    const start = selectionStart;
    selectionStart = null;
    map?.dragPan.enable();

    if (map && start && point && Math.abs(point.x - start.x) > 3 && Math.abs(point.y - start.y) > 3) {
        const features = map.queryRenderedFeatures(
            [
                [Math.min(start.x, point.x), Math.min(start.y, point.y)],
                [Math.max(start.x, point.x), Math.max(start.y, point.y)],
            ],
            { layers: ['stations-circles'] },
        );
        const areaStationIds = features
            .map((feature) => Number(feature.properties?.id))
            .filter((stationId) => Number.isFinite(stationId));
        const newStationIds = areaStationIds.filter((stationId) => !selectedStationIds.value.includes(stationId));
        const availableSlots = MAX_MERGE_STATIONS - selectedStationIds.value.length;
        selectedStationIds.value = [
            ...selectedStationIds.value,
            ...newStationIds.slice(0, Math.max(availableSlots, 0)),
        ];
        if (newStationIds.length > availableSlots) {
            error.value = `A maximum of ${MAX_MERGE_STATIONS} stations can be merged at once.`;
        } else {
            error.value = null;
        }
        updateMapData();
    }

    selectionBox.value = null;
}

async function fetchStationsForCurrentView(): Promise<void> {
    if (!map || map.getZoom() < MIN_ZOOM) return;

    const bounds = map.getBounds();
    const currentRequest = ++requestSequence;
    loading.value = true;
    error.value = null;

    try {
        const response = await api.stations.indexStation({
            min_lat: bounds.getSouth(),
            max_lat: bounds.getNorth(),
            min_lon: bounds.getWest(),
            max_lon: bounds.getEast(),
            limit: FETCH_LIMIT,
            withIdentifiers: true,
        });

        if (currentRequest !== requestSequence) return;

        const visibleStations = (response.data.data ?? []).filter(
            (station) =>
                station.id !== undefined && Number.isFinite(station.latitude) && Number.isFinite(station.longitude),
        );
        const selectedOutsideView = selectedStations.value.filter(
            (selected) => !visibleStations.some((station) => station.id === selected.id),
        );
        stations.value = [...visibleStations, ...selectedOutsideView];
        updateMapData();
    } catch (exception) {
        if (currentRequest === requestSequence) {
            error.value = exception instanceof Error ? exception.message : 'Failed to load stations';
        }
    } finally {
        if (currentRequest === requestSequence) {
            loading.value = false;
        }
    }
}

function scheduleStationFetch(): void {
    zoom.value = map?.getZoom() ?? zoom.value;

    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }

    if (zoom.value < MIN_ZOOM) return;
    debounceTimer = setTimeout(fetchStationsForCurrentView, DEBOUNCE_MS);
}

async function mergeSelectedStations(): Promise<void> {
    if (!targetStation.value || !sourceStations.value.length || selectedStations.value.length > MAX_MERGE_STATIONS) {
        return;
    }

    const targetId = targetStation.value.id!;
    const sources = [...sourceStations.value];
    merging.value = true;
    error.value = null;
    confirmModalOpen.value = false;

    try {
        for (let sourceIndex = 0; sourceIndex < sources.length; sourceIndex++) {
            const source = sources[sourceIndex];
            const identifiers = source.identifiers ?? [];

            for (let identifierIndex = 0; identifierIndex < identifiers.length; identifierIndex++) {
                progress.value = `Station ${sourceIndex + 1}/${sources.length}: moving identifier ${identifierIndex + 1}/${identifiers.length} from #${source.id}...`;
                await api.stations.moveStationIdentifier(source.id!, identifiers[identifierIndex].id!, {
                    target_station_id: targetId,
                });
            }

            progress.value = `Station ${sourceIndex + 1}/${sources.length}: moving all remaining references from #${source.id}...`;
            await api.stations.moveStationUsages(source.id!, { target_station_id: targetId });

            progress.value = `Station ${sourceIndex + 1}/${sources.length}: deleting empty station #${source.id}...`;
            await api.stations.deleteStation(source.id!);
        }

        progress.value = `${sources.length} station${sources.length === 1 ? '' : 's'} merged into #${targetId}.`;
        stations.value = [];
        selectedStationIds.value = [];
        updateMapData();
        await fetchStationsForCurrentView();
    } catch (exception) {
        error.value = exception instanceof Error ? exception.message : 'Merge failed';
    } finally {
        merging.value = false;
        updateMapData();
    }
}

function initializeMap(): void {
    if (!mapElement.value) return;

    map = new maplibregl.Map({
        container: mapElement.value,
        style: 'https://tiles.openfreemap.org/styles/positron',
        center: [10.6898, 51.7459],
        zoom: zoom.value,
        maxZoom: 18,
        boxZoom: false,
    });

    map.addControl(new maplibregl.NavigationControl(), 'top-right');
    map.addControl(new maplibregl.GeolocateControl(), 'top-right');

    map.on('load', () => {
        map?.addSource('stations', { type: 'geojson', data: stationData() });
        map?.addLayer({
            id: 'stations-circles',
            type: 'circle',
            source: 'stations',
            paint: {
                'circle-radius': ['interpolate', ['linear'], ['zoom'], 11, 4, 18, 9],
                'circle-color': ['case', ['get', 'target'], '#16a34a', ['get', 'selected'], '#e11d48', '#2563eb'],
                'circle-stroke-color': '#ffffff',
                'circle-stroke-width': ['case', ['get', 'selected'], 3, 1],
                'circle-opacity': 0.9,
            },
        });
        map?.on('click', 'stations-circles', onStationClick);
        map?.on('mouseenter', 'stations-circles', () => {
            if (map) map.getCanvas().style.cursor = 'pointer';
        });
        map?.on('mouseleave', 'stations-circles', () => {
            if (map) map.getCanvas().style.cursor = '';
        });
        map?.getCanvas().addEventListener('mousedown', startAreaSelection);
        scheduleStationFetch();
    });
    map.on('moveend', scheduleStationFetch);
}

onMounted(initializeMap);

onUnmounted(() => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    requestSequence++;
    document.removeEventListener('mousemove', updateAreaSelection);
    document.removeEventListener('mouseup', finishAreaSelection);
    map?.getCanvas().removeEventListener('mousedown', startAreaSelection);
    map?.remove();
    map = null;
});
</script>

<template>
    <div class="card bg-base-100 shadow mb-6">
        <div class="card-body gap-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="card-title text-base">Merge stations on map</h2>
                    <p class="text-sm text-base-content/60">
                        Select 2 to {{ MAX_MERGE_STATIONS }} stations. The station with the lowest ID is kept as the
                        target.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        class="btn btn-sm btn-ghost"
                        :disabled="!selectedStations.length || merging"
                        @click="clearSelection"
                    >
                        <X class="h-4 w-4" />
                        Clear
                    </button>
                    <button class="btn btn-sm btn-primary" :disabled="!canMerge" @click="confirmModalOpen = true">
                        <GitMerge class="h-4 w-4" />
                        Merge {{ selectedStations.length || '' }} stations
                    </button>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-box border border-base-300">
                <div ref="mapElement" class="h-[55vh] min-h-96 w-full" />
                <div
                    v-if="selectionBox"
                    class="pointer-events-none absolute border-2 border-primary bg-primary/20"
                    :style="{
                        left: `${selectionBox.left}px`,
                        top: `${selectionBox.top}px`,
                        width: `${selectionBox.width}px`,
                        height: `${selectionBox.height}px`,
                    }"
                />
                <div v-if="loading" class="pointer-events-none absolute left-3 top-3 badge badge-neutral gap-2 shadow">
                    <span class="loading loading-spinner loading-xs" />
                    Loading stations
                </div>
            </div>

            <div v-if="zoom < MIN_ZOOM" role="alert" class="alert alert-warning py-2 text-sm">
                Zoom in to load stations (minimum zoom: {{ MIN_ZOOM }}).
            </div>

            <p class="text-xs text-base-content/60">
                Click markers individually, or hold Shift and drag a rectangle to add all stations in an area. A maximum
                of {{ MAX_MERGE_STATIONS }} stations can be selected.
            </p>

            <div v-if="error" role="alert" class="alert alert-error py-2 text-sm">
                {{ error }}
            </div>

            <div v-if="progress" role="status" class="alert alert-info py-2 text-sm">
                <span v-if="merging" class="loading loading-spinner loading-sm" />
                <Check v-else class="h-4 w-4" />
                {{ progress }}
            </div>

            <div v-if="selectedStations.length" class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium">Selected:</span>
                <button
                    v-for="station in selectedStations"
                    :key="station.id"
                    type="button"
                    class="badge h-auto gap-1 py-1"
                    :class="station.id === targetStation?.id ? 'badge-success' : 'badge-error'"
                    :disabled="merging"
                    @click="toggleStation(station.id!)"
                >
                    <span class="font-mono">#{{ station.id }}</span>
                    {{ station.name }}
                    <span v-if="station.id === targetStation?.id" class="font-semibold">target</span>
                    <X class="h-3 w-3" />
                </button>
            </div>
        </div>
    </div>

    <div v-if="confirmModalOpen" class="modal modal-open" role="dialog">
        <div class="modal-box">
            <h3 class="text-lg font-bold">Merge {{ selectedStations.length }} stations?</h3>
            <p class="mt-2 text-sm">
                All identifiers and usages from the following stations will be moved to
                <strong>#{{ targetStation?.id }} {{ targetStation?.name }}</strong
                >. The emptied source stations will then be deleted.
            </p>
            <ul class="mt-3 space-y-1 text-sm">
                <li v-for="station in sourceStations" :key="station.id" class="flex items-center gap-2">
                    <span class="font-mono">#{{ station.id }}</span>
                    <span>{{ station.name }}</span>
                    <a
                        :href="`/admin/stations/${station.id}`"
                        target="_blank"
                        class="btn btn-ghost btn-xs"
                        title="Open station details"
                    >
                        <ExternalLink class="h-3 w-3" />
                    </a>
                </li>
            </ul>
            <div class="modal-action">
                <button class="btn btn-ghost" @click="confirmModalOpen = false">Cancel</button>
                <button class="btn btn-error" @click="mergeSelectedStations">
                    <GitMerge class="h-4 w-4" />
                    Merge into #{{ targetStation?.id }}
                </button>
            </div>
        </div>
        <div class="modal-backdrop" @click="confirmModalOpen = false" />
    </div>
</template>
