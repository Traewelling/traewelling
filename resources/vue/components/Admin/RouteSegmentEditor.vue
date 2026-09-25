<script setup lang="ts">
import type { Feature, FeatureCollection, LineString } from 'geojson';
import * as maplibregl from 'maplibre-gl';
import { onMounted, onUnmounted, ref } from 'vue';
import '../../../js/maplibre';
import { Api, type RouteSegmentResource } from '../../../types/Api.gen';
import { buildTransitBasemapStyle } from '../Map/transitBasemapStyle';

const api = new Api({ baseUrl: window.location.origin + '/api' });

const props = defineProps<{
    segment: RouteSegmentResource;
}>();

const emit = defineEmits<{
    saved: [polyline: string, distance: number];
}>();

type LngLatPair = [number, number];

const CONNECTOR_THRESHOLD = 0.0001;
const EDITABLE_LAYERS = ['existing-line', 'preview-line'];

const mapEl = ref<HTMLDivElement | null>(null);
const status = ref('');
const statusCls = ref<'muted' | 'warning' | 'success' | 'danger'>('muted');
const saveEnabled = ref(false);
const previewDistance = ref<number | null>(null);

let map: maplibregl.Map | null = null;
let markerA: maplibregl.Marker | null = null;
let markerB: maplibregl.Marker | null = null;
let intermediates: maplibregl.Marker[] = [];
let existingPts: LngLatPair[] = [];
let previewPts: LngLatPair[] | null = null;
let pendingController: AbortController | null = null;

function decodePolyline(encoded: string, precision: number = 5): LngLatPair[] {
    const factor = Math.pow(10, precision);
    const coords: LngLatPair[] = [];
    let lat = 0,
        lng = 0,
        i = 0;
    while (i < encoded.length) {
        let b,
            shift = 0,
            result = 0;
        do {
            b = encoded.charCodeAt(i++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 32);
        lat += result & 1 ? ~(result >> 1) : result >> 1;
        shift = 0;
        result = 0;
        do {
            b = encoded.charCodeAt(i++) - 63;
            result |= (b & 0x1f) << shift;
            shift += 5;
        } while (b >= 32);
        lng += result & 1 ? ~(result >> 1) : result >> 1;
        coords.push([lng / factor, lat / factor]);
    }
    return coords;
}

function stationCoordinates(): { from: LngLatPair; to: LngLatPair } {
    return {
        from: [
            props.segment.fromIdentifier?.longitude ?? props.segment.fromStation?.longitude ?? 0,
            props.segment.fromIdentifier?.latitude ?? props.segment.fromStation?.latitude ?? 0,
        ],
        to: [
            props.segment.toIdentifier?.longitude ?? props.segment.toStation?.longitude ?? 0,
            props.segment.toIdentifier?.latitude ?? props.segment.toStation?.latitude ?? 0,
        ],
    };
}

function makeMarkerElement(label: string, color: string, title: string): HTMLElement {
    const el = document.createElement('div');
    el.style.cssText = `background:${color};color:#fff;font-weight:bold;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.5);font-size:13px;cursor:grab;`;
    el.textContent = label;
    el.title = title;
    return el;
}

function setStatus(msg: string, cls: 'muted' | 'warning' | 'success' | 'danger' = 'muted'): void {
    status.value = msg;
    statusCls.value = cls;
}

function lineFeature(coordinates: LngLatPair[]): Feature<LineString> {
    return { type: 'Feature', properties: {}, geometry: { type: 'LineString', coordinates } };
}

function lineCollection(...lines: LngLatPair[][]): FeatureCollection<LineString> {
    return { type: 'FeatureCollection', features: lines.filter((line) => line.length >= 2).map(lineFeature) };
}

function setSourceData(sourceId: string, data: FeatureCollection<LineString>): void {
    map?.getSource<maplibregl.GeoJSONSource>(sourceId)?.setData(data);
}

function toPair(lngLat: maplibregl.LngLat): LngLatPair {
    return [lngLat.lng, lngLat.lat];
}

function isOffset(a: LngLatPair, b: LngLatPair): boolean {
    return Math.abs(a[0] - b[0]) > CONNECTOR_THRESHOLD || Math.abs(a[1] - b[1]) > CONNECTOR_THRESHOLD;
}

function renderLines(): void {
    if (!map || !markerA || !markerB) return;

    if (previewPts) {
        setSourceData('existing', lineCollection());
        setSourceData('connector-a', lineCollection());
        setSourceData('connector-b', lineCollection());
        setSourceData('preview', lineCollection(previewPts));
        return;
    }

    setSourceData('preview', lineCollection());
    setSourceData('existing', lineCollection(existingPts));

    if (existingPts.length < 2) {
        setSourceData('connector-a', lineCollection());
        setSourceData('connector-b', lineCollection());
        return;
    }

    const posA = toPair(markerA.getLngLat());
    const posB = toPair(markerB.getLngLat());
    const first = existingPts[0];
    const last = existingPts[existingPts.length - 1];
    setSourceData('connector-a', isOffset(posA, first) ? lineCollection([posA, first]) : lineCollection());
    setSourceData('connector-b', isOffset(posB, last) ? lineCollection([posB, last]) : lineCollection());
}

function pointToSegDist(p: LngLatPair, a: LngLatPair, b: LngLatPair): number {
    const dx = b[0] - a[0],
        dy = b[1] - a[1];
    const lenSq = dx * dx + dy * dy;
    if (lenSq === 0) return Math.hypot(p[0] - a[0], p[1] - a[1]);
    const t = Math.max(0, Math.min(1, ((p[0] - a[0]) * dx + (p[1] - a[1]) * dy) / lenSq));
    return Math.hypot(p[0] - (a[0] + t * dx), p[1] - (a[1] + t * dy));
}

function nearestSegmentIndex(point: LngLatPair): number {
    const wps = collectWaypoints().map((wp): LngLatPair => [wp.lng, wp.lat]);
    let best = 0,
        bestDist = Infinity;
    for (let i = 0; i < wps.length - 1; i++) {
        const d = pointToSegDist(point, wps[i], wps[i + 1]);
        if (d < bestDist) {
            bestDist = d;
            best = i;
        }
    }
    return best;
}

function renumberIntermediates(): void {
    intermediates.forEach((m, i) => {
        m.getElement().textContent = String(i + 1);
    });
}

function addIntermediateMarker(lngLat: LngLatPair, insertAt: number = intermediates.length): maplibregl.Marker {
    const marker = new maplibregl.Marker({
        element: makeMarkerElement('?', '#ff8800', 'Intermediate: click to remove'),
        draggable: true,
    })
        .setLngLat(lngLat)
        .addTo(map!);

    marker.on('dragend', () => requestPreview());
    marker.on('click', () => removeIntermediate(marker));

    intermediates.splice(insertAt, 0, marker);
    renumberIntermediates();
    return marker;
}

function removeIntermediate(marker: maplibregl.Marker): void {
    intermediates = intermediates.filter((m) => m !== marker);
    marker.remove();
    renumberIntermediates();
    requestPreview();
}

function clearIntermediates(): void {
    intermediates.forEach((m) => m.remove());
    intermediates = [];
}

function collectWaypoints(): { lat: number; lng: number }[] {
    return [markerA!, ...intermediates, markerB!].map((m) => ({ lat: m.getLngLat().lat, lng: m.getLngLat().lng }));
}

function clearPreview(): void {
    previewPts = null;
    renderLines();
    saveEnabled.value = false;
    previewDistance.value = null;
}

function enableLineDragging(): void {
    for (const layerId of EDITABLE_LAYERS) {
        map!.on('mouseenter', layerId, () => (map!.getCanvas().style.cursor = 'crosshair'));
        map!.on('mouseleave', layerId, () => (map!.getCanvas().style.cursor = ''));
        map!.on('mousedown', layerId, (e) => {
            e.preventDefault();
            const point = toPair(e.lngLat);
            const marker = addIntermediateMarker(point, nearestSegmentIndex(point));

            const onMove = (moveEvent: maplibregl.MapMouseEvent) => marker.setLngLat(moveEvent.lngLat);
            map!.on('mousemove', onMove);
            map!.once('mouseup', () => {
                map!.off('mousemove', onMove);
                requestPreview();
            });
        });
    }
}

async function requestPreview(): Promise<void> {
    setStatus('Requesting BRouter…', 'warning');
    saveEnabled.value = false;

    if (pendingController) {
        pendingController.abort();
    }
    const controller = new AbortController();
    pendingController = controller;

    try {
        const res = await api.routeSegments.brouterPreviewRouteSegment(
            props.segment.id!,
            { waypoints: collectWaypoints(), path_type: props.segment.pathType ?? null },
            { signal: controller.signal },
        );

        const data = res.data;

        previewPts = (data.coordinates ?? []).map((c): LngLatPair => [c.lng!, c.lat!]);
        renderLines();

        previewDistance.value = data.distance ?? null;
        saveEnabled.value = true;
        setStatus('Route received ✓', 'success');
    } catch (e: unknown) {
        if (e instanceof Error && e.name !== 'AbortError') {
            setStatus('Request failed: ' + e.message, 'danger');
        }
    } finally {
        if (pendingController === controller) {
            pendingController = null;
        }
    }
}

async function save(): Promise<void> {
    setStatus('Saving…', 'warning');
    saveEnabled.value = false;

    try {
        const res = await api.routeSegments.applyPolylineToRouteSegment(props.segment.id!, {
            waypoints: collectWaypoints(),
        });

        setStatus('Saved ✓', 'success');
        emit('saved', res.data.polyline!, res.data.distance!);
    } catch (e: unknown) {
        setStatus('Save failed: ' + (e instanceof Error ? e.message : String(e)), 'danger');
        saveEnabled.value = true;
    }
}

function addLineLayers(): void {
    for (const sourceId of ['existing', 'connector-a', 'connector-b', 'preview']) {
        map!.addSource(sourceId, { type: 'geojson', data: lineCollection() });
    }

    const lineLayout = { 'line-cap': 'round', 'line-join': 'round' } as const;
    map!.addLayer({
        id: 'existing-line',
        type: 'line',
        source: 'existing',
        layout: lineLayout,
        paint: { 'line-color': '#3388ff', 'line-width': 8, 'line-opacity': 0.5 },
    });
    map!.addLayer({
        id: 'connector-a-line',
        type: 'line',
        source: 'connector-a',
        paint: { 'line-color': '#28a745', 'line-width': 2, 'line-opacity': 0.8, 'line-dasharray': [2.5, 2.5] },
    });
    map!.addLayer({
        id: 'connector-b-line',
        type: 'line',
        source: 'connector-b',
        paint: { 'line-color': '#dc3545', 'line-width': 2, 'line-opacity': 0.8, 'line-dasharray': [2.5, 2.5] },
    });
    map!.addLayer({
        id: 'preview-line',
        type: 'line',
        source: 'preview',
        layout: lineLayout,
        paint: { 'line-color': '#ff6600', 'line-width': 8, 'line-opacity': 0.7 },
    });
}

onMounted(() => {
    if (!mapEl.value) return;

    existingPts = decodePolyline(props.segment.polyline ?? '', props.segment.polylinePrecision ?? 5);
    const { from, to } = stationCoordinates();

    const isDark = document.documentElement.dataset.bsTheme === 'dark';
    map = new maplibregl.Map({
        container: mapEl.value,
        style: buildTransitBasemapStyle(isDark ? 'dark' : 'light'),
        center: from,
        zoom: 12,
        attributionControl: { compact: true },
    });
    map.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');

    if (existingPts.length > 1) {
        const bounds = new maplibregl.LngLatBounds(from, from);
        [...existingPts, to].forEach((point) => bounds.extend(point));
        map.fitBounds(bounds, { padding: 40, duration: 0 });
    }

    markerA = new maplibregl.Marker({ element: makeMarkerElement('A', '#28a745', 'Origin (A)'), draggable: true })
        .setLngLat(from)
        .addTo(map);
    markerB = new maplibregl.Marker({ element: makeMarkerElement('B', '#dc3545', 'Destination (B)'), draggable: true })
        .setLngLat(to)
        .addTo(map);

    // Restore saved custom waypoints if present
    const saved = props.segment.customWaypoints;
    const hasSavedWaypoints = !!saved && saved.length >= 2;
    if (hasSavedWaypoints) {
        const a = saved[0];
        const b = saved[saved.length - 1];
        markerA.setLngLat([a.lng!, a.lat!]);
        markerB.setLngLat([b.lng!, b.lat!]);
        for (let i = 1; i < saved.length - 1; i++) {
            addIntermediateMarker([saved[i].lng!, saved[i].lat!]);
        }
    }

    for (const marker of [markerA, markerB]) {
        marker.on('drag', renderLines);
        marker.on('dragend', () => requestPreview());
    }

    map.on('load', () => {
        addLineLayers();
        enableLineDragging();
        renderLines();

        // Trigger initial preview if custom waypoints exist
        if (hasSavedWaypoints) {
            requestPreview();
        }
    });
});

onUnmounted(() => {
    pendingController?.abort();
    if (map) {
        map.remove();
        map = null;
    }
});

function reset(): void {
    if (!map) return;
    const { from, to } = stationCoordinates();
    markerA!.setLngLat(from);
    markerB!.setLngLat(to);
    clearIntermediates();
    clearPreview();
    requestPreview();
}
</script>

<template>
    <div class="card bg-base-100 shadow">
        <div class="card-body p-3 gap-2">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <h2 class="card-title text-base">BRouter Route Editor</h2>
                <div class="flex items-center gap-2 flex-wrap">
                    <span
                        class="text-xs"
                        :class="{
                            'text-base-content/50': statusCls === 'muted',
                            'text-warning': statusCls === 'warning',
                            'text-success': statusCls === 'success',
                            'text-error': statusCls === 'danger',
                        }"
                        >{{ status }}</span
                    >
                    <span v-if="previewDistance !== null" class="text-xs text-base-content/50 tabular-nums">
                        {{ (previewDistance / 1000).toFixed(2) }} km
                    </span>
                    <button class="btn btn-sm btn-warning" @click="reset">Reset</button>
                    <button class="btn btn-sm btn-success" :disabled="!saveEnabled" @click="save">
                        Save to Segment
                    </button>
                </div>
            </div>
        </div>
        <div ref="mapEl" style="height: 60vh"></div>
    </div>
</template>
