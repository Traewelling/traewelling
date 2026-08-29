<script setup lang="ts">
import {
    MglGeoJsonSource,
    MglGeolocateControl,
    MglLineLayer,
    MglMap,
    MglNavigationControl,
} from '@indoorequal/vue-maplibre-gl';
import type { Feature, LineString } from 'geojson';
import { DataDrivenPropertyValueSpecification, LngLat, LngLatBounds } from 'maplibre-gl';
import { computed } from 'vue';
import { RouteMapEntryResource } from '../../../../types/Api.gen';
import { buildTransitBasemapStyle } from '../../../../vue/components/Map/transitBasemapStyle';
import { decodePolyline } from '../../../helpers/polyline';
import { categoryColor, primaryCategory } from '../categoryColors';

const props = defineProps<{
    entries: RouteMapEntryResource[];
}>();

const DEFAULT_BOUNDS = LngLatBounds.fromLngLat(new LngLat(9.9, 49.8), 500000);

const isDarkMode = document.documentElement.classList.contains('dark');
const mapStyle = buildTransitBasemapStyle(isDarkMode ? 'dark' : 'light');

const brouterAttribution = '&copy; <a href="https://brouter.de" target="_blank">BRouter</a>';

const lineWidth: DataDrivenPropertyValueSpecification<number> = [
    'interpolate',
    ['linear'],
    ['zoom'],
    4,
    1,
    8,
    2,
    12,
    3.5,
    16,
    6,
];
const casingWidth: DataDrivenPropertyValueSpecification<number> = [
    'interpolate',
    ['linear'],
    ['zoom'],
    4,
    2.5,
    8,
    4,
    12,
    6,
    16,
    9,
];

const routes = computed(() => {
    const routed: Feature<LineString>[] = [];
    const approximated: Feature<LineString>[] = [];

    let west = Infinity;
    let south = Infinity;
    let east = -Infinity;
    let north = -Infinity;

    for (const entry of props.entries) {
        const coordinates = decodePolyline(entry.polyline, entry.polylinePrecision);
        if (coordinates.length < 2) {
            continue;
        }

        for (const [lng, lat] of coordinates) {
            if (lng < west) west = lng;
            if (lng > east) east = lng;
            if (lat < south) south = lat;
            if (lat > north) north = lat;
        }

        const feature: Feature<LineString> = {
            type: 'Feature',
            geometry: { type: 'LineString', coordinates },
            properties: { color: categoryColor(primaryCategory(entry.categories)) },
        };

        (entry.approximated ? approximated : routed).push(feature);
    }

    const bounds = Number.isFinite(west) ? new LngLatBounds([west, south], [east, north]) : undefined;

    return { routed, approximated, bounds };
});

const bounds = computed(() => routes.value.bounds ?? DEFAULT_BOUNDS);
</script>

<template>
    <mgl-map
        :map-style="mapStyle"
        :bounds="bounds"
        :fit-bounds-options="{ padding: 32, animate: false }"
        :max-zoom="17"
        height="100%"
        :attribution-control="{ compact: true }"
    >
        <mgl-navigation-control position="top-right" :show-zoom="true" :show-compass="true" />
        <mgl-geolocate-control />

        <mgl-geo-json-source
            v-if="routes.approximated.length > 0"
            source-id="route-map-approximated"
            :data="{ type: 'FeatureCollection', features: routes.approximated }"
        >
            <mgl-line-layer
                layer-id="route-map-approximated-line"
                source-id="route-map-approximated"
                :layout="{ 'line-cap': 'butt', 'line-join': 'round' }"
                :paint="{
                    'line-color': ['get', 'color'],
                    'line-width': lineWidth,
                    'line-opacity': 0.75,
                    'line-dasharray': [2, 2],
                }"
            />
        </mgl-geo-json-source>

        <mgl-geo-json-source
            v-if="routes.routed.length > 0"
            source-id="route-map-routed"
            :data="{ type: 'FeatureCollection', features: routes.routed }"
            :attribution="brouterAttribution"
        >
            <mgl-line-layer
                layer-id="route-map-casing"
                source-id="route-map-routed"
                :layout="{ 'line-cap': 'round', 'line-join': 'round' }"
                :paint="{ 'line-color': '#181818', 'line-width': casingWidth, 'line-opacity': 0.45 }"
            />
            <mgl-line-layer
                layer-id="route-map-line"
                source-id="route-map-routed"
                :layout="{ 'line-cap': 'round', 'line-join': 'round' }"
                :paint="{ 'line-color': ['get', 'color'], 'line-width': lineWidth }"
            />
        </mgl-geo-json-source>
    </mgl-map>
</template>
