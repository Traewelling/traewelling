<script setup lang="ts">
import L from 'leaflet';
import { onMounted, onUnmounted, ref } from 'vue';
import { Api, type RouteSegmentResource } from '../../../types/Api.gen';

const api = new Api({ baseUrl: window.location.origin + '/api' });

declare function setTilingLayer(provider: string, map: L.Map): void;

const props = defineProps<{
    segment: RouteSegmentResource;
}>();

const emit = defineEmits<{
    saved: [polyline: string, distance: number];
}>();

const mapEl = ref<HTMLDivElement | null>(null);
const status = ref('');
const statusCls = ref<'muted' | 'warning' | 'success' | 'danger'>('muted');
const saveEnabled = ref(false);
const previewDistance = ref<number | null>(null);

let map: L.Map | null = null;
let markerA: L.Marker | null = null;
let markerB: L.Marker | null = null;
let intermediates: L.Marker[] = [];
let existingPolyline: L.Polyline | null = null;
let connectorA: L.Polyline | null = null;
let connectorB: L.Polyline | null = null;
let previewPolyline: L.Polyline | null = null;
let pendingController: AbortController | null = null;

// The library encodes [lon, lat] pairs; decoded as [lat, lng] for Leaflet.
function decodePolyline(encoded: string, precision: number = 5): [number, number][] {
    const factor = Math.pow(10, precision);
    const coords: [number, number][] = [];
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
        coords.push([lat / factor, lng / factor]);
    }
    return coords;
}

function makeIcon(label: string, color: string): L.DivIcon {
    return L.divIcon({
        className: '',
        html: `<div style="background:${color};color:#fff;font-weight:bold;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.5);font-size:13px;">${label}</div>`,
        iconSize: [28, 28],
        iconAnchor: [14, 14],
    });
}

function setStatus(msg: string, cls: 'muted' | 'warning' | 'success' | 'danger' = 'muted'): void {
    status.value = msg;
    statusCls.value = cls;
}

function pointToSegDist(p: L.LatLng, a: L.LatLng, b: L.LatLng): number {
    const dx = b.lat - a.lat,
        dy = b.lng - a.lng;
    const lenSq = dx * dx + dy * dy;
    if (lenSq === 0) return Math.hypot(p.lat - a.lat, p.lng - a.lng);
    const t = Math.max(0, Math.min(1, ((p.lat - a.lat) * dx + (p.lng - a.lng) * dy) / lenSq));
    return Math.hypot(p.lat - (a.lat + t * dx), p.lng - (a.lng + t * dy));
}

function nearestSegmentIndex(latlng: L.LatLng): number {
    const wps = collectWaypoints();
    let best = 0,
        bestDist = Infinity;
    for (let i = 0; i < wps.length - 1; i++) {
        const d = pointToSegDist(latlng, L.latLng(wps[i]), L.latLng(wps[i + 1]));
        if (d < bestDist) {
            bestDist = d;
            best = i;
        }
    }
    return best;
}

function renumberIntermediates(): void {
    intermediates.forEach((m, i) => m.setIcon(makeIcon(String(i + 1), '#ff8800')));
}

function addIntermediateMarker(lat: number, lng: number, insertAt: number = intermediates.length): L.Marker {
    const marker = L.marker([lat, lng], {
        draggable: true,
        icon: makeIcon('?', '#ff8800'),
    })
        .addTo(map!)
        .bindTooltip('Intermediate – click to remove');

    marker.on('dragend', () => requestPreview());
    marker.on('click', () => removeIntermediate(marker));

    intermediates.splice(insertAt, 0, marker);
    renumberIntermediates();
    return marker;
}

function removeIntermediate(marker: L.Marker): void {
    intermediates = intermediates.filter((m) => m !== marker);
    map!.removeLayer(marker);
    renumberIntermediates();
    requestPreview();
}

function clearIntermediates(): void {
    intermediates.forEach((m) => map!.removeLayer(m));
    intermediates = [];
}

function collectWaypoints(): { lat: number; lng: number }[] {
    const pts: { lat: number; lng: number }[] = [];
    pts.push({ lat: markerA!.getLatLng().lat, lng: markerA!.getLatLng().lng });
    intermediates.forEach((m) => pts.push({ lat: m.getLatLng().lat, lng: m.getLatLng().lng }));
    pts.push({ lat: markerB!.getLatLng().lat, lng: markerB!.getLatLng().lng });
    return pts;
}

function drawExistingPolyline(pts: [number, number][]): void {
    if (existingPolyline) {
        map!.removeLayer(existingPolyline);
        existingPolyline = null;
    }
    if (connectorA) {
        map!.removeLayer(connectorA);
        connectorA = null;
    }
    if (connectorB) {
        map!.removeLayer(connectorB);
        connectorB = null;
    }
    if (pts.length < 2) return;

    existingPolyline = L.polyline(pts, { color: '#3388ff', weight: 8, opacity: 0.5 }).addTo(map!);
    addPolylineDragBehavior(existingPolyline);

    const threshold = 0.0001;
    const posA = markerA!.getLatLng();
    const posB = markerB!.getLatLng();
    const first = pts[0];
    const last = pts[pts.length - 1];

    if (Math.abs(posA.lat - first[0]) > threshold || Math.abs(posA.lng - first[1]) > threshold) {
        connectorA = L.polyline([posA, first], { color: '#28a745', weight: 2, opacity: 0.8, dashArray: '5 5' }).addTo(
            map!,
        );
    }
    if (Math.abs(posB.lat - last[0]) > threshold || Math.abs(posB.lng - last[1]) > threshold) {
        connectorB = L.polyline([posB, last], { color: '#dc3545', weight: 2, opacity: 0.8, dashArray: '5 5' }).addTo(
            map!,
        );
    }
}

function clearPreview(existingPts: [number, number][]): void {
    if (previewPolyline) {
        map!.removeLayer(previewPolyline);
        previewPolyline = null;
        drawExistingPolyline(existingPts);
    }
    saveEnabled.value = false;
    previewDistance.value = null;
}

function addPolylineDragBehavior(polyline: L.Polyline): void {
    polyline.options.interactive = true;
    const el = polyline.getElement();
    if (el) (el as HTMLElement).style.cursor = 'crosshair';

    polyline.on('mousedown', function (e: L.LeafletMouseEvent) {
        L.DomEvent.stop(e);
        map!.dragging.disable();
        const insertAt = nearestSegmentIndex(e.latlng);
        const marker = addIntermediateMarker(e.latlng.lat, e.latlng.lng, insertAt);
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        const draggable = (marker as unknown as Record<string, any>).dragging?._draggable;
        if (draggable) draggable._onDown(e.originalEvent);
        marker.once('dragend', () => map!.dragging.enable());
    });
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

        if (previewPolyline) map!.removeLayer(previewPolyline);
        if (existingPolyline) map!.removeLayer(existingPolyline);
        if (connectorA) map!.removeLayer(connectorA);
        if (connectorB) map!.removeLayer(connectorB);

        const latLngs: [number, number][] = (data.coordinates ?? []).map((c) => [c.lat!, c.lng!]);
        previewPolyline = L.polyline(latLngs, { color: '#ff6600', weight: 8, opacity: 0.7 }).addTo(map!);
        addPolylineDragBehavior(previewPolyline);

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

onMounted(() => {
    if (!mapEl.value) return;

    const existingPts = decodePolyline(props.segment.polyline ?? '', props.segment.polylinePrecision ?? 5);

    const fromLat = props.segment.fromIdentifier?.latitude ?? props.segment.fromStation?.latitude ?? 0;
    const fromLng = props.segment.fromIdentifier?.longitude ?? props.segment.fromStation?.longitude ?? 0;
    const toLat = props.segment.toIdentifier?.latitude ?? props.segment.toStation?.latitude ?? 0;
    const toLng = props.segment.toIdentifier?.longitude ?? props.segment.toStation?.longitude ?? 0;

    map = L.map(mapEl.value);
    setTilingLayer('open-railway-map', map);

    markerA = L.marker([fromLat, fromLng], { draggable: true, icon: makeIcon('A', '#28a745') })
        .addTo(map)
        .bindTooltip('Origin (A)');
    markerB = L.marker([toLat, toLng], { draggable: true, icon: makeIcon('B', '#dc3545') })
        .addTo(map)
        .bindTooltip('Destination (B)');

    // Restore saved custom waypoints if present
    const saved = props.segment.customWaypoints;
    if (saved && saved.length >= 2) {
        const a = saved[0];
        const b = saved[saved.length - 1];
        markerA.setLatLng([a.lat!, a.lng!]);
        markerB.setLatLng([b.lat!, b.lng!]);
        for (let i = 1; i < saved.length - 1; i++) {
            addIntermediateMarker(saved[i].lat!, saved[i].lng!);
        }
    }

    drawExistingPolyline(existingPts);

    if (existingPts.length > 1) {
        map.fitBounds(L.latLngBounds([...existingPts, [fromLat, fromLng], [toLat, toLng]]));
    } else {
        map.setView([fromLat, fromLng], 12);
    }

    markerA.on('dragend', () => {
        drawExistingPolyline(existingPts);
        requestPreview();
    });
    markerB.on('dragend', () => {
        drawExistingPolyline(existingPts);
        requestPreview();
    });

    // Trigger initial preview if custom waypoints exist
    if (saved && saved.length >= 2) {
        requestPreview();
    }
});

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});

function reset(): void {
    if (!map) return;
    const fromLat = props.segment.fromIdentifier?.latitude ?? props.segment.fromStation?.latitude ?? 0;
    const fromLng = props.segment.fromIdentifier?.longitude ?? props.segment.fromStation?.longitude ?? 0;
    const toLat = props.segment.toIdentifier?.latitude ?? props.segment.toStation?.latitude ?? 0;
    const toLng = props.segment.toIdentifier?.longitude ?? props.segment.toStation?.longitude ?? 0;
    markerA!.setLatLng([fromLat, fromLng]);
    markerB!.setLatLng([toLat, toLng]);
    clearIntermediates();
    const existingPts = decodePolyline(props.segment.polyline ?? '', props.segment.polylinePrecision ?? 5);
    clearPreview(existingPts);
    drawExistingPolyline(existingPts);
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
