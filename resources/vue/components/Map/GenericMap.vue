<script setup lang="ts">
import {
    MglGeoJsonSource,
    MglGeolocateControl,
    MglLineLayer,
    MglMap,
    MglMarker,
    MglNavigationControl,
    MglRasterLayer,
    MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import { trans } from 'laravel-vue-i18n';
import { GeoJSONFeature, LngLat, LngLatBounds } from 'maplibre-gl';
import { computed, PropType, ref } from 'vue';
import { LivePointDto, MapProvider } from '../../../types/Api.gen';
import { useMapConsentStore } from '../../stores/mapConsent';
import { useUserStore } from '../../stores/user';
import LiveMapPoint from './LiveMapPoint.vue';
import { buildTransitBasemapStyle } from './transitBasemapStyle';

const DEFAULT_BOUNDS = LngLatBounds.fromLngLat(new LngLat(9.902056, 49.843), 100000);

function withPadding(bounds: LngLatBounds, padding = 0.1): LngLatBounds {
    const sw = bounds.getSouthWest();
    const ne = bounds.getNorthEast();
    const latPad = (ne.lat - sw.lat) * padding;
    const lngPad = (ne.lng - sw.lng) * padding;
    return new LngLatBounds([sw.lng - lngPad, sw.lat - latPad], [ne.lng + lngPad, ne.lat + latPad]);
}

function boundsFromPolylines(features: GeoJSONFeature[], padding = 0.1): LngLatBounds | undefined {
    const bounds = new LngLatBounds();
    for (const feature of features) {
        const geom = feature.geometry as { type: string; coordinates: unknown } | undefined;
        if (!geom) continue;
        const pairs: number[][] =
            geom.type === 'MultiLineString'
                ? (geom.coordinates as number[][][]).flat()
                : (geom.coordinates as number[][]);
        for (const [lng, lat] of pairs) {
            bounds.extend([lng, lat]);
        }
    }
    if (bounds.isEmpty()) return undefined;
    return withPadding(bounds, padding);
}

const props = defineProps({
    polyLines: {
        type: Array as PropType<GeoJSONFeature[]>,
        required: false,
        default: () => [],
    },
    bounds: {
        type: Object as PropType<LngLatBounds>,
        default: undefined,
    },
    lineColor: {
        type: String,
        default: '#c72730',
    },
    livePositions: {
        type: Array as PropType<LivePointDto[]>,
        required: false,
        default: () => [],
    },
    mapProvider: {
        type: String as PropType<MapProvider>,
        default: MapProvider.OpenFreeMap,
    },
    previewPolyline: {
        type: Object as PropType<GeoJSONFeature | null>,
        default: null,
    },
    previewMarkers: {
        type: Array as PropType<{ id: string; lat: number; lng: number; color: string; title?: string }[]>,
        default: () => [],
    },
    cooperativeGestures: {
        type: Boolean,
        default: true,
    },
});

const userStore = useUserStore();
const mapConsentStore = useMapConsentStore();

// Non-authenticated users must explicitly consent before the map loads external tile content
const showMap = computed(() => userStore.isAuthenticated || mapConsentStore.mapConsentGiven);

const rememberConsent = ref(false);

function activateMap() {
    mapConsentStore.giveMapConsent(rememberConsent.value);
}

const isDarkMode = document.documentElement.dataset.bsTheme === 'dark';

const ormAttribution = '&copy; <a href="https://openrailwaymap.org" target="_blank">OpenRailwayMap</a>';
const brouterAttribution = '&copy; <a href="https://brouter.de" target="_blank">BRouter</a>';

const ormTiles = [
    'https://a.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
    'https://b.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
    'https://c.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
];

const showOrmLayer = computed(() => props.mapProvider === MapProvider.OpenRailwayMap);

const effectiveBounds = computed(
    () => (props.polyLines.length ? boundsFromPolylines(props.polyLines) : undefined) ?? props.bounds ?? DEFAULT_BOUNDS,
);

const mapStyle = buildTransitBasemapStyle(isDarkMode ? 'dark' : 'light');
</script>

<template>
    <!-- Consent gate: shown to non-authenticated users who haven't yet activated the map -->
    <div v-if="!showMap" class="map-consent-gate">
        <div class="map-consent-content">
            <p class="map-consent-text">
                {{ trans('map.consent.notice') }}
            </p>
            <label class="map-consent-remember">
                <input v-model="rememberConsent" type="checkbox" />
                {{ trans('map.consent.remember') }}
            </label>
            <button class="map-consent-btn" @click="activateMap">
                {{ trans('map.consent.load') }}
            </button>
        </div>
    </div>
    <div v-else class="generic-map-wrapper">
        <!-- Map -->
        <mgl-map
            :map-style="mapStyle"
            :max-zoom="18"
            :bounds="effectiveBounds"
            height="45vh"
            :attribution-control="{ compact: true }"
            :cooperative-gestures="cooperativeGestures"
        >
            <mgl-navigation-control position="top-right" :show-zoom="false" :show-compass="true" />
            <mgl-geolocate-control />

            <mgl-raster-source
                v-if="showOrmLayer"
                source-id="orm-source"
                :tiles="ormTiles"
                :tile-size="256"
                :maxzoom="18"
                :attribution="ormAttribution"
            >
                <mgl-raster-layer layer-id="orm-layer" :paint="{ 'raster-opacity': 0.6, 'raster-saturation': -1 }" />
            </mgl-raster-source>

            <mgl-geo-json-source
                v-if="polyLines.length > 0"
                :data="{
                    type: 'FeatureCollection',
                    features: polyLines,
                }"
                source-id="polylines"
                :attribution="brouterAttribution"
            >
                <!-- Dark casing underneath for contrast on all base maps -->
                <mgl-line-layer
                    layer-id="line-casing"
                    source-id="polylines"
                    :layout="{
                        'line-cap': 'round',
                        'line-join': 'round',
                        visibility: 'visible',
                    }"
                    :paint="{
                        'line-color': '#181818',
                        'line-width': 7,
                        'line-opacity': 0.9,
                    }"
                />
                <!-- Colored route line on top -->
                <mgl-line-layer
                    layer-id="line-main"
                    source-id="polylines"
                    :layout="{
                        'line-cap': 'round',
                        'line-join': 'round',
                        visibility: 'visible',
                    }"
                    :paint="{
                        'line-color': lineColor,
                        'line-width': 5,
                        'line-opacity': 1,
                    }"
                />
            </mgl-geo-json-source>
            <mgl-geo-json-source
                v-if="previewPolyline"
                source-id="preview-polyline"
                :data="{ type: 'FeatureCollection', features: [previewPolyline] }"
                attribution="&copy; <a href='https://brouter.de' target='_blank'>BRouter</a>"
            >
                <mgl-line-layer
                    layer-id="preview-casing"
                    source-id="preview-polyline"
                    :layout="{ 'line-cap': 'round', 'line-join': 'round' }"
                    :paint="{ 'line-color': '#1e1e1e', 'line-width': 5, 'line-opacity': 0.25 }"
                />
                <mgl-line-layer
                    layer-id="preview-line"
                    source-id="preview-polyline"
                    :layout="{ 'line-cap': 'round', 'line-join': 'round' }"
                    :paint="{
                        'line-color': previewPolyline?.properties?.routed ? '#2563eb' : '#9ca3af',
                        'line-width': 3,
                        'line-dasharray': previewPolyline?.properties?.routed ? [1] : [2, 2],
                    }"
                />
            </mgl-geo-json-source>

            <mgl-marker v-for="m in previewMarkers" :key="m.id" :coordinates="[m.lng, m.lat]">
                <template #marker>
                    <div
                        class="w-3.5 h-3.5 rounded-full border-2 border-white shadow-md"
                        :style="{ backgroundColor: m.color }"
                        :title="m.title"
                    />
                </template>
            </mgl-marker>

            <slot />
            <LiveMapPoint v-for="point in livePositions" :key="point.statusId" :point="point" />
        </mgl-map>
    </div>
</template>

<style scoped>
.generic-map-wrapper {
    position: relative;
}

/* Consent gate placeholder */
.map-consent-gate {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 45vh;
    background-color: #e8e8e8;
    color: #333;
}

@media (prefers-color-scheme: dark) {
    .map-consent-gate {
        background-color: #2a2a2a;
        color: #ccc;
    }
}

.map-consent-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    max-width: 340px;
    text-align: center;
    padding: 1.5rem;
}

.map-consent-text {
    margin: 0;
    font-size: 0.9rem;
    line-height: 1.4;
}

.map-consent-remember {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.85rem;
    cursor: pointer;
}

.map-consent-btn {
    padding: 0.45rem 1.1rem;
    border: none;
    border-radius: 4px;
    background-color: #c72730;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
}

.map-consent-btn:hover {
    background-color: #a31f27;
}
</style>
