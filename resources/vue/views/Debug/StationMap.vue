<template>
  <div class="station-map-wrapper">
    <div ref="mapEl" class="leaflet-map"/>

    <div v-if="zoomTooFar" class="alert alert-warning mt-2">
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

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import L from 'leaflet'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).toString(),
  iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).toString(),
  shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).toString()
})

const props = defineProps({
  apiUrl: { type: String, default: '/api/v1/stations' },
  limit: { type: Number, default: 250 },
  initialCenter: {
    type: Array,
    default: () => [48.993316, 8.401525] // Karlsruhe Hbf
  },
  initialZoom: { type: Number, default: 15 },
  minZoomForData: { type: Number, default: 14 }
})

const mapEl = ref(null)
let map
let markersLayer

const loading = ref(false)
const error = ref('')
const zoomTooFar = ref(true)

let debounceTimer = null
const DEBOUNCE_MS = 350

function updateZoomState() {
  if (!map) return
  zoomTooFar.value = map.getZoom() < props.minZoomForData
}

function fetchStationsForCurrentView() {
  if (!map) return

  updateZoomState()

  const b = map.getBounds()
  const min_lat = b.getSouth()
  const max_lat = b.getNorth()
  const min_lon = b.getWest()
  const max_lon = b.getEast()

  loading.value = true
  error.value = ''

  const url = new URL(props.apiUrl, window.location.origin)
  url.searchParams.set('min_lat', min_lat.toFixed(6))
  url.searchParams.set('max_lat', max_lat.toFixed(6))
  url.searchParams.set('min_lon', min_lon.toFixed(6))
  url.searchParams.set('max_lon', max_lon.toFixed(6))
  url.searchParams.set('limit', Math.min(Math.max(props.limit, 1), 100).toString())

  fetch(url.toString(), { headers: { 'Accept': 'application/json' } })
      .then(async (res) => {
        if (!res.ok) {
          const text = await res.text().catch(() => '')
          throw new Error(`API ${res.status}: ${text || res.statusText}`)
        }
        return res.json()
      })
      .then(json => {
        const items = Array.isArray(json?.data) ? json.data : []

        const uniq = new Map()
        for (const s of items) {
          if (!Number.isFinite(s?.latitude) || !Number.isFinite(s?.longitude)) continue
          const key = `${s.id ?? 'noid'}@${s.latitude},${s.longitude}`
          if (!uniq.has(key)) uniq.set(key, s)
        }
        renderMarkers([...uniq.values()])
      })
      .catch((e) => {
        console.error(e)
        error.value = e?.message ?? 'Error'
      })
      .finally(() => {
        loading.value = false
      })
}

function renderMarkers(stations) {
  if (!markersLayer) return
  markersLayer.clearLayers()

  stations.forEach(s => {
    const m = L.marker([s.latitude, s.longitude])
    const areaPrimary = Array.isArray(s.areas) ? s.areas.find(a => a?.default) : null
    const areaFallback = Array.isArray(s.areas) && s.areas.length ? s.areas[0] : null
    const areaText = (areaPrimary?.name || areaFallback?.name || '').toString()

    const popupHtml = `
      <div style="min-width: 220px">
        <div style="font-weight:600; margin-bottom: 2px">${escapeHtml(s.name ?? '???')}</div>
        <div style="font-size: 12px; color:#555">
          <div><b>ID:</b> ${escapeHtml(String(s.id ?? '–'))}</div>
          <div><b>IBNR:</b> ${s.ibnr ?? '–'} &nbsp; <b>RIL:</b> ${escapeHtml(s.rilIdentifier ?? '–')}</div>
          ${areaText ? `<div><b>Gebiet:</b> ${escapeHtml(areaText)}</div>` : ''}
          <div><b>Lat/Lon:</b> ${Number(s.latitude).toFixed(5)}, ${Number(s.longitude).toFixed(5)}</div>
        </div>
      </div>
    `
    m.bindPopup(popupHtml, { maxWidth: 320 })
    markersLayer.addLayer(m)
  })
}

function escapeHtml(str) {
  return String(str)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;')
}

onMounted(() => {
  map = L.map(mapEl.value, {
    zoomControl: true,
    attributionControl: true
  }).setView(props.initialCenter, props.initialZoom)

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> contributors'
  }).addTo(map)

  markersLayer = L.layerGroup().addTo(map)

  updateZoomState()
  if (!zoomTooFar.value) {
    fetchStationsForCurrentView()
  }

  map.on('moveend', () => {
    window.clearTimeout(debounceTimer)
    updateZoomState()
    if (zoomTooFar.value) {
      if (markersLayer) markersLayer.clearLayers()
      return
    }
    debounceTimer = window.setTimeout(fetchStationsForCurrentView, DEBOUNCE_MS)
  })
})

onBeforeUnmount(() => {
  if (map) map.remove()
})
</script>

<style scoped>
.leaflet-map {
  width: 100%;
  height: 60vh;
  border-radius: 12px;
  overflow: hidden;
}

.station-map-wrapper {
  position: relative;
}
</style>
