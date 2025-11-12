<script setup lang="ts">
import {
  MglFullscreenControl,
  MglGeoJsonSource,
  MglGeolocateControl,
  MglLineLayer,
  MglMap,
  MglNavigationControl,
  MglRasterLayer,
  MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import {GeoJSONFeature, LngLat, LngLatBounds, StyleSpecification} from "maplibre-gl";
import {PropType, ref, watch} from "vue";
import {LivePointDto} from "../../../types/Api.gen";
import LiveMapPoint from "./LiveMapPoint.vue";

defineProps({
  polyLines: {
    type: Array as PropType<GeoJSONFeature[]>,
    required: false,
    default: () => [],
  },
  bounds: {
    type: Object as PropType<LngLatBounds>,
    default: LngLatBounds.fromLngLat(new LngLat(9.902056, 49.843), 100000)
  },
  lineColor: {
    type: String,
    default: '#c72730'
  },
  livePositions: {
    type: Array as PropType<LivePointDto[]>,
    required: false,
    default: () => [],
  },
});

const isDarkMode = ref(false);

watch(
    () => document.documentElement.dataset.bsTheme,
    (newValue) => {
      isDarkMode.value = newValue === 'dark';
    },
    {immediate: true, deep: true}
);

// Map setup
const style: StyleSpecification = {
  version: 8,
  projection: {type: 'globe'},
  sources: {},
  layers: [],
};
const osmTiles = [
  'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
];

const ormTiles = [
  'https://a.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
  'https://b.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
  'https://c.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png',
];
const ormAttribution =
    '<a href="https://openrailwaymap.org" target="reservoirwatch">OpenRailwayMap</a>';
const osmAttribution =
    'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
</script>

<template>
  <mgl-map
      ref="mapComponent"
      :map-style="style"
      :max-zoom="18"
      :bounds="bounds"
      height="60vh"
  >
    <mgl-fullscreen-control/>
    <mgl-navigation-control
        position="top-right"
        :show-zoom="false"
        :show-compass="true"
    />
    <mgl-geolocate-control/>
    <!-- ToDo: Switch base layers based on dark mode https://github.com/Traewelling/traewelling/issues/4132 -->
    <!-- ToDo: Vector layers https://github.com/Traewelling/traewelling/issues/4131 -->
    <mgl-raster-source source-id="osm-source" :tiles="osmTiles" :attribution="osmAttribution">
      <mgl-raster-layer layer-id="osm-layer" :paint="{'raster-opacity': .5, 'raster-saturation': -1}"/>
    </mgl-raster-source>

    <!-- ToDo: Make overlay layer toggleable w/ different map styles https://github.com/Traewelling/traewelling/issues/4133 -->
    <mgl-raster-source
        source-id="orm-source"
        :tiles="ormTiles"
        :tile-size="256"
        :maxzoom="18"
        :attribution="ormAttribution"
    >
      <mgl-raster-layer layer-id="orm-layer" :paint="{'raster-opacity': 0.5}"/>
    </mgl-raster-source>
    <mgl-geo-json-source
        v-if="polyLines.length > 0"
        :data="{
          type: 'FeatureCollection',
          features: polyLines,
        }"
        source-id="polylines"
    >
      <mgl-line-layer
          layer-id="line"
          source-id="polylines"
          :layout="{
                    'line-cap': 'round',
                    'line-join': 'round',
                    visibility: 'visible',
                }"
          :paint="{
                    'line-color': lineColor,
                    'line-width': 4,
                    'line-opacity': 0.8,
                }"
      />
    </mgl-geo-json-source>
    <slot/>
    <LiveMapPoint :point="point" v-for="point in livePositions" :key="point.statusId"/>
  </mgl-map>
</template>
