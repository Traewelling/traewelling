<script setup lang="ts">
import {onBeforeUnmount, onMounted, ref, type Ref} from 'vue'
import {
  MglFullscreenControl,
  MglGeolocateControl,
  MglMap,
  MglMarker,
  MglNavigationControl,
  MglPopup,
  MglRasterLayer,
  MglRasterSource,
} from '@indoorequal/vue-maplibre-gl';
import {LngLat, LngLatBoundsLike, StyleSpecification} from 'maplibre-gl';
import {Api, AreaResource, StationResource} from "../../../types/Api.gen";

const api = new Api({baseUrl: window.location.origin + '/api/v1'});

const style: StyleSpecification = {
  version: 8,
  projection: {type: 'globe'},
  sources: {},
  layers: [],
};
const center = ref(new LngLat(8.403, 49));
const osmTiles = ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'];
const osmAttribution =
    'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
const zoom = ref(13);

interface Props {
  apiUrl?: string;
  limit?: number;
  initialCenter?: [number, number];
  initialZoom?: number;
  minZoomForData?: number;
}

const props = withDefaults(defineProps<Props>(), {
  apiUrl: '/api/v1/stations',
  limit: 250,
  initialCenter: () => [48.993316, 8.401525],
  initialZoom: 15,
  minZoomForData: 11,
})

const bounds = ref<LngLatBoundsLike | undefined>(undefined)
const mapComponent: Ref<InstanceType<typeof MglMap> | null> = ref(null)

const loading = ref<boolean>(false)
const error = ref<string>('')
const stations = ref<StationResource[]>([])

let debounceTimer: ReturnType<typeof setTimeout> | null = null
const DEBOUNCE_MS = 350

function getAreaName(station: StationResource): string {
  if (!Array.isArray(station.areas)) return ''
  const areaPrimary = station.areas.find((a: AreaResource) => a?.default)
  const areaFallback = station.areas.length ? station.areas[0] : null
  return areaPrimary?.name || areaFallback?.name || ''
}

function onMapLoad(): void {
  if (zoom.value > props.minZoomForData) {
    fetchStationsForCurrentView()
  }
}

function onMapMoveEnd(event: any): void {
  if (debounceTimer !== null) {
    clearTimeout(debounceTimer)
  }

  if (zoom.value < props.minZoomForData) {
    return
  }

  debounceTimer = setTimeout(fetchStationsForCurrentView, DEBOUNCE_MS)
}

function fetchStationsForCurrentView(): void {
  if (!center.value && !zoom.value) return

  // Calculate map bounds from center and zoom
  const centerLng = center.value.lng
  const centerLat = center.value.lat
  const mapWidthInDegrees = 360 / Math.pow(2, zoom.value) * 10 // Approximation
  const mapHeightInDegrees = 180 / Math.pow(2, zoom.value) * 10 // Approximation
  const min_lon = centerLng - mapWidthInDegrees / 2
  const max_lon = centerLng + mapWidthInDegrees / 2
  const min_lat = centerLat - mapHeightInDegrees / 2
  const max_lat = centerLat + mapHeightInDegrees / 2

  loading.value = true
  error.value = ''

  const url = new URL(props.apiUrl, window.location.origin)
  url.searchParams.set('limit', Math.min(Math.max(props.limit, 1), 100).toString())

  api.stations.indexStation({
    min_lat: min_lat,
    max_lat: max_lat,
    min_lon: min_lon,
    max_lon: max_lon,
    limit: Math.min(Math.max(props.limit, 1), 250),
  })
      .then((res) => {
        for (const s of res.data.data || []) {
          if (!Number.isFinite(s?.latitude) || !Number.isFinite(s?.longitude)) continue
          if (stations.value.some((st) => st.id === s.id)) continue
          stations.value.push(s)
        }
        loading.value = false
      })
      .catch((e: unknown) => {
        console.error(e)
        error.value = e instanceof Error ? e.message : 'Error'
        loading.value = false
      })
}

onMounted(() => {
  fetchStationsForCurrentView();
})

onBeforeUnmount(() => {
  if (debounceTimer !== null) {
    clearTimeout(debounceTimer)
  }
})
</script>
<template>
  <div class="station-map-wrapper">
    <mgl-map
        ref="mapComponent"
        :map-style="style"
        v-model:center="center"
        v-model:zoom="zoom"
        :max-zoom="18"
        :bounds="bounds"
        height="60vh"
        @map:boxzoomend="onMapMoveEnd"
        @map:moveend="onMapMoveEnd"
        @load="onMapLoad"
    >
      <mgl-fullscreen-control/>
      <mgl-navigation-control
          position="top-right"
          :show-zoom="true"
          :show-compass="true"
      />
      <mgl-geolocate-control/>
      <mgl-raster-source
          source-id="raster-source"
          :tiles="osmTiles"
          :tile-size="256"
          :maxzoom="18"
          :attribution="osmAttribution"
      >
        <mgl-raster-layer layer-id="raster-layer"/>
      </mgl-raster-source>

      <mgl-marker
          v-for="station in stations"
          :key="station.id"
          :coordinates="[station.longitude, station.latitude]"
      >
        <mgl-popup>
          <div style="min-width: 220px">
            <div style="font-weight:600; margin-bottom: 2px">{{ station.name ?? '???' }}</div>
            <div style="font-size: 12px; color:#555">
              <div><b>ID:</b> {{ String(station.id ?? '–') }}</div>
              <div><b>IBNR:</b> {{ station.ibnr ?? '–' }} &nbsp; <b>RIL:</b> {{ station.rilIdentifier ?? '–' }}</div>
              <div v-show="station.areas"><b>Gebiet:</b> {{ (getAreaName(station)) }}</div>
              <div><b>Lat/Lon:</b> {{ Number(station.latitude).toFixed(5) }}, {{ Number(station.longitude).toFixed(5) }}
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
      This map is for debugging purposes only and shows station locations based on data from the API.
      You may zoom very far in to see all stations, as we only load a limited number of stations per request.
      As you move the map, new stations will be loaded automatically.
      Expect a lagging experience when too many stations are in the view.
    </div>
  </div>
</template>
