<script setup lang="ts">
import {
    MglGeoJsonSource,
    MglGeolocateControl,
    MglLineLayer,
    MglMap,
    MglNavigationControl,
    MglRasterLayer,
    MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import { GeoJSONFeature, LngLat, LngLatBounds, StyleSpecification } from 'maplibre-gl';
import { computed, PropType } from 'vue';
import { LivePointDto, MapProvider } from '../../../types/Api.gen';
import LiveMapPoint from './LiveMapPoint.vue';

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
        default: MapProvider.Cargo,
    },
});

const isDarkMode = document.documentElement.dataset.bsTheme === 'dark';

const cartoAttribution =
    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attribution">CARTO</a>';
const ormAttribution = '<a href="https://openrailwaymap.org" target="_blank">OpenRailwayMap</a>';

const cartoVoyagerTiles = [
    'https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
    'https://b.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
    'https://c.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
    'https://d.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}.png',
];
const cartoVoyagerNoLabelsTiles = [
    'https://a.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}.png',
    'https://b.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}.png',
    'https://c.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}.png',
    'https://d.basemaps.cartocdn.com/rastertiles/voyager_nolabels/{z}/{x}/{y}.png',
];

const cartoDarkAllTiles = [
    'https://a.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
    'https://b.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
    'https://c.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
    'https://d.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png',
];
const cartoDarkNoLabelsTiles = [
    'https://a.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png',
    'https://b.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png',
    'https://c.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png',
    'https://d.basemaps.cartocdn.com/dark_nolabels/{z}/{x}/{y}.png',
];

const ormTiles = [
    'https://a.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
    'https://b.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
    'https://c.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
];

const useNoLabels = props.mapProvider === MapProvider.OpenRailwayMap;
const baseTiles: string[] = isDarkMode
    ? useNoLabels
        ? cartoDarkNoLabelsTiles
        : cartoDarkAllTiles
    : useNoLabels
      ? cartoVoyagerNoLabelsTiles
      : cartoVoyagerTiles;

const showOrmLayer = computed(() => props.mapProvider === MapProvider.OpenRailwayMap);

const effectiveBounds = computed(
    () => (props.polyLines.length ? boundsFromPolylines(props.polyLines) : undefined) ?? props.bounds ?? DEFAULT_BOUNDS,
);

const style: StyleSpecification = {
    version: 8,
    projection: { type: 'globe' },
    sources: {},
    layers: [],
};
</script>

<template>
    <mgl-map
        :map-style="style"
        :max-zoom="18"
        :bounds="effectiveBounds"
        height="45vh"
        :attribution-control="{ compact: true }"
    >
        <mgl-navigation-control position="top-right" :show-zoom="false" :show-compass="true" />
        <mgl-geolocate-control />

        <mgl-raster-source source-id="base-source" :tiles="baseTiles" :attribution="cartoAttribution">
            <mgl-raster-layer layer-id="base-layer" />
        </mgl-raster-source>

        <mgl-raster-source
            v-if="showOrmLayer"
            source-id="orm-source"
            :tiles="ormTiles"
            :tile-size="256"
            :maxzoom="18"
            :attribution="ormAttribution"
        >
            <mgl-raster-layer layer-id="orm-layer" :paint="{ 'raster-opacity': 0.35, 'raster-saturation': -1 }" />
        </mgl-raster-source>

        <mgl-geo-json-source
            v-if="polyLines.length > 0"
            :data="{
                type: 'FeatureCollection',
                features: polyLines,
            }"
            source-id="polylines"
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
        <slot />
        <LiveMapPoint v-for="point in livePositions" :key="point.statusId" :point="point" />
    </mgl-map>
</template>
