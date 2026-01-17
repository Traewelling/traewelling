<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, type Ref, ref } from 'vue';
import {
    MglFullscreenControl,
    MglGeoJsonSource,
    MglGeolocateControl,
    MglHeatmapLayer,
    MglMap,
    MglMarker,
    MglNavigationControl,
    MglPopup,
    MglRasterLayer,
    MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import { LngLat, LngLatBoundsLike, StyleSpecification } from 'maplibre-gl';
import { Api, AreaResource, StationResource } from '../../../types/Api.gen';
import type { FeatureCollection, Point } from 'geojson';

const props = withDefaults(defineProps<Props>(), {
    apiUrl: '/api/v1/stations',
    limit: 250,
    initialCenter: () => [48.993316, 8.401525],
    initialZoom: 15,
    minZoomForData: 11,
    heatmapMaxZoom: 11,
});

const api = new Api({ baseUrl: window.location.origin + '/api/v1' });

const style: StyleSpecification = {
    version: 8,
    projection: { type: 'globe' },
    sources: {},
    layers: [],
};
const center = ref(new LngLat(8.403, 49));
const osmTiles = ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'];
const osmAttribution = 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
const zoom = ref(13);

interface Props {
    apiUrl?: string;
    limit?: number;
    initialCenter?: [number, number];
    initialZoom?: number;
    minZoomForData?: number;
    heatmapMaxZoom?: number;
}

const bounds = ref<LngLatBoundsLike | undefined>(undefined);
const mapComponent: Ref<InstanceType<typeof MglMap> | null> = ref(null);

const loading = ref<boolean>(false);
const error = ref<string>('');
const stations = ref<StationResource[]>([]);

let debounceTimer: ReturnType<typeof setTimeout> | null = null;
const DEBOUNCE_MS = 350;

const showHeatmap = computed(() => zoom.value <= props.heatmapMaxZoom);

const stationsGeoJson = computed<FeatureCollection<Point>>(() => ({
    type: 'FeatureCollection',
    features: stations.value.map(station => ({
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

function onMapMoveEnd(event: any): void {
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

    const url = new URL(props.apiUrl, window.location.origin);
    url.searchParams.set('limit', Math.min(Math.max(props.limit, 1), 100).toString());

    api.stations
        .indexStation({
            min_lat: min_lat,
            max_lat: max_lat,
            min_lon: min_lon,
            max_lon: max_lon,
            limit: Math.min(Math.max(props.limit, 1), 250),
        })
        .then(res => {
            for (const s of res.data.data || []) {
                if (!Number.isFinite(s?.latitude) || !Number.isFinite(s?.longitude)) continue;
                if (stations.value.some(st => st.id === s.id)) continue;
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
    fetchStationsForCurrentView();
});

onBeforeUnmount(() => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
});
</script>
<template>
    <div class="station-map-wrapper">
        <mgl-map
            ref="mapComponent"
            v-model:center="center"
            v-model:zoom="zoom"
            :map-style="style"
            :max-zoom="18"
            :bounds="bounds"
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

            <mgl-geo-json-source v-if="showHeatmap" source-id="stations-heat" :data="stationsGeoJson">
                <mgl-heatmap-layer
                    layer-id="stations-heatmap"
                    :paint="{
                        'heatmap-weight': 1,
                        'heatmap-intensity': 1,
                        'heatmap-color': [
                            'interpolate',
                            ['linear'],
                            ['heatmap-density'],
                            0,
                            'rgba(33,102,172,0)',
                            0.2,
                            'rgb(103,169,207)',
                            0.4,
                            'rgb(209,229,240)',
                            0.6,
                            'rgb(253,219,199)',
                            0.8,
                            'rgb(239,138,98)',
                            1,
                            'rgb(178,24,43)',
                        ],
                        'heatmap-radius': ['interpolate', ['linear'], ['zoom'], 0, 2, 9, 20],
                        'heatmap-opacity': 0.8,
                    }"
                />
            </mgl-geo-json-source>

            <mgl-marker
                v-for="station in stations"
                v-if="!showHeatmap"
                :key="station.id"
                :coordinates="[station.longitude, station.latitude]"
            >
                <mgl-popup>
                    <div style="min-width: 220px">
                        <div style="font-weight: 600; margin-bottom: 2px">
                            {{ station.name ?? '???' }}
                        </div>
                        <div style="font-size: 12px; color: #555">
                            <div><b>ID:</b> {{ String(station.id ?? '–') }}</div>
                            <div>
                                <b>IBNR:</b> {{ station.ibnr ?? '–' }} &nbsp; <b>RIL:</b>
                                {{ station.rilIdentifier ?? '–' }}
                            </div>
                            <div v-show="station.areas"><b>Gebiet:</b> {{ getAreaName(station) }}</div>
                            <div>
                                <b>Lat/Lon:</b> {{ Number(station.latitude).toFixed(5) }},
                                {{ Number(station.longitude).toFixed(5) }}
                            </div>
                        </div>
                    </div>
                </mgl-popup>
            </mgl-marker>
        </mgl-map>
        <div v-if="zoom < (minZoomForData || 11)" class="alert alert-warning mt-2">
            Zoom in to load stations (min zoom: {{ minZoomForData }}).
        </div>

        <div class="alert alert-info mt-2">
            This map is for debugging purposes only and shows station locations based on data from the API. You may zoom
            very far in to see all stations, as we only load a limited number of stations per request. As you move the
            map, new stations will be loaded automatically. Expect a lagging experience when too many stations are in
            the view.
            <span v-if="showHeatmap"
                ><br /><b>Heatmap mode active:</b> Zoom in past level {{ heatmapMaxZoom }} to see individual
                markers.</span
            >
        </div>
    </div>
</template>
