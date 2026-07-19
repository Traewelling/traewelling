<script setup lang="ts">
import {
    MglCircleLayer,
    MglFullscreenControl,
    MglGeoJsonSource,
    MglGeolocateControl,
    MglMap,
    MglMarker,
    MglNavigationControl,
    MglPopup,
    MglRasterLayer,
    MglRasterSource,
    useMap,
} from '@indoorequal/vue-maplibre-gl';
import type { FeatureCollection, Point } from 'geojson';
import { LngLat, MapLayerMouseEvent, StyleSpecification } from 'maplibre-gl';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Api, AreaResource, StationResource } from '../../../types/Api.gen';
import AppLayout from '../../layouts/AppLayout.vue';

const props = withDefaults(defineProps<Props>(), {
    limit: 1000,
    minZoomForData: 11,
});

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const style: StyleSpecification = {
    version: 8,
    projection: { type: 'globe' },
    sources: {},
    layers: [],
};

const center = ref(new LngLat(10.6898, 51.7459));
const osmTiles = ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'];
const osmAttribution = 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
const zoom = ref(5.5);

interface Props {
    limit?: number;
    minZoomForData?: number;
}

const loading = ref<boolean>(false);
const error = ref<string>('');
const stations = ref<StationResource[]>([]);

const selectedStation = ref<StationResource | null>(null);
const selectedIdentifiers = computed(() =>
    (selectedStation.value?.identifiers ?? []).filter(
        (identifier) => Number.isFinite(identifier.latitude) && Number.isFinite(identifier.longitude),
    ),
);

const map = useMap();

function onStationClick(event: MapLayerMouseEvent): void {
    const clickedId = event.features?.[0]?.properties?.id;
    if (clickedId === undefined) return;
    selectedStation.value = stations.value.find((s) => String(s.id) === String(clickedId)) ?? null;
}

function setCursor(cursor: string): void {
    if (map.map) {
        map.map.getCanvas().style.cursor = cursor;
    }
}

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
const DEBOUNCE_MS = 350;

const stationsGeoJson = computed<FeatureCollection<Point>>(() => ({
    type: 'FeatureCollection',
    features: stations.value.map((station) => ({
        type: 'Feature',
        geometry: {
            type: 'Point',
            coordinates: [station.longitude, station.latitude],
        },
        properties: {
            id: station.id,
            name: station.name,
        },
    })),
}));

function getAreaName(station: StationResource): string {
    if (!Array.isArray(station.areas)) return '';
    const areaPrimary = station.areas.find((a: AreaResource) => a?.default);
    const areaFallback = station.areas.length ? station.areas[0] : null;
    return areaPrimary?.name || areaFallback?.name || '';
}

function onMapLoad(): void {
    if (zoom.value > props.minZoomForData) {
        fetchStationsForCurrentView();
    }
}

function onMapMoveEnd(): void {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }

    if (zoom.value < props.minZoomForData) {
        return;
    }

    debounceTimer = setTimeout(fetchStationsForCurrentView, DEBOUNCE_MS);
}

function fetchStationsForCurrentView(): void {
    if (!center.value && !zoom.value) return;

    const centerLng = center.value.lng;
    const centerLat = center.value.lat;
    const mapWidthInDegrees = (360 / Math.pow(2, zoom.value)) * 10;
    const mapHeightInDegrees = (180 / Math.pow(2, zoom.value)) * 10;
    const min_lon = centerLng - mapWidthInDegrees / 2;
    const max_lon = centerLng + mapWidthInDegrees / 2;
    const min_lat = centerLat - mapHeightInDegrees / 2;
    const max_lat = centerLat + mapHeightInDegrees / 2;

    loading.value = true;
    error.value = '';

    api.stations
        .indexStation({
            min_lat: min_lat,
            max_lat: max_lat,
            min_lon: min_lon,
            max_lon: max_lon,
            limit: Math.min(Math.max(props.limit, 1), 1000),
            withIdentifiers: true,
        })
        .then((res) => {
            for (const s of res.data.data || []) {
                if (!Number.isFinite(s?.latitude) || !Number.isFinite(s?.longitude)) continue;
                if (stations.value.some((st) => st.id === s.id)) continue;
                stations.value.push(s);
            }
            loading.value = false;
        })
        .catch((e: unknown) => {
            console.error(e);
            error.value = e instanceof Error ? e.message : 'Error';
            loading.value = false;
        });
}

onMounted(() => {
    if (zoom.value > props.minZoomForData) {
        fetchStationsForCurrentView();
    }
});

onBeforeUnmount(() => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
});
</script>
<template>
    <AppLayout>
        <div class="station-map-wrapper">
            <mgl-map
                v-model:center="center"
                v-model:zoom="zoom"
                :map-style="style"
                :max-zoom="18"
                height="60vh"
                @map:boxzoomend="onMapMoveEnd"
                @map:moveend="onMapMoveEnd"
                @load="onMapLoad"
            >
                <mgl-fullscreen-control />
                <mgl-navigation-control position="top-right" :show-zoom="true" :show-compass="true" />
                <mgl-geolocate-control />
                <mgl-raster-source
                    source-id="raster-source"
                    :tiles="osmTiles"
                    :tile-size="256"
                    :maxzoom="18"
                    :attribution="osmAttribution"
                >
                    <mgl-raster-layer layer-id="raster-layer" />
                </mgl-raster-source>

                <mgl-geo-json-source source-id="stations-points" :data="stationsGeoJson">
                    <mgl-circle-layer
                        layer-id="stations-circles"
                        :paint="{
                            'circle-radius': ['interpolate', ['linear'], ['zoom'], 11, 3, 18, 7],
                            'circle-color': '#2563eb',
                            'circle-stroke-color': '#ffffff',
                            'circle-stroke-width': 1,
                            'circle-opacity': 0.85,
                        }"
                        @click="onStationClick"
                        @mouseenter="setCursor('pointer')"
                        @mouseleave="setCursor('')"
                    />
                </mgl-geo-json-source>

                <mgl-popup
                    v-if="selectedStation"
                    :coordinates="[selectedStation.longitude, selectedStation.latitude]"
                    :close-on-click="false"
                    @close="selectedStation = null"
                >
                    <div style="min-width: 220px">
                        <div style="font-weight: 600; margin-bottom: 2px">
                            {{ selectedStation.name ?? '???' }}
                        </div>
                        <div style="font-size: 12px; color: #555">
                            <div><b>ID:</b> {{ String(selectedStation.id ?? '–') }}</div>
                            <div v-show="selectedStation.areas"><b>Gebiet:</b> {{ getAreaName(selectedStation) }}</div>
                            <div>
                                <b>Lat/Lon:</b> {{ Number(selectedStation.latitude).toFixed(5) }},
                                {{ Number(selectedStation.longitude).toFixed(5) }}
                            </div>
                            <div v-if="selectedStation.identifiers?.length" style="margin-top: 4px">
                                <b>Identifiers:</b> {{ selectedStation.identifiers.length }}
                            </div>
                        </div>
                    </div>
                </mgl-popup>

                <template v-if="selectedStation">
                    <mgl-marker
                        v-for="identifier in selectedIdentifiers"
                        :key="'ident-' + (identifier.id ?? identifier.type + identifier.identifier)"
                        :coordinates="[Number(identifier.longitude), Number(identifier.latitude)]"
                        color="#e11d48"
                        :scale="0.6"
                    >
                        <mgl-popup>
                            <div style="min-width: 200px">
                                <div style="font-weight: 600; margin-bottom: 2px">
                                    {{ selectedStation.name }} · {{ identifier.type }}
                                </div>
                                <div style="font-size: 12px; color: #555">
                                    <div style="font-family: monospace">{{ identifier.identifier }}</div>
                                    <div v-if="identifier.name">{{ identifier.name }}</div>
                                    <div>
                                        <b>Lat/Lon:</b> {{ Number(identifier.latitude).toFixed(5) }},
                                        {{ Number(identifier.longitude).toFixed(5) }}
                                    </div>
                                </div>
                            </div>
                        </mgl-popup>
                    </mgl-marker>
                </template>
            </mgl-map>
            <div v-if="zoom < (minZoomForData || 11)" class="alert alert-warning mt-2">
                Zoom in to load stations (min zoom: {{ minZoomForData }}).
            </div>

            <div class="alert alert-info mt-2">
                This map is for debugging purposes only and shows station locations based on data from the API. You may
                zoom very far in to see all stations, as we only load a limited number of stations per request. As you
                move the map, new stations will be loaded automatically. Expect a lagging experience when too many
                stations are in the view.
            </div>
        </div>
    </AppLayout>
</template>
