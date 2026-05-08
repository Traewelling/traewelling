<script setup lang="ts">
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import type { Station, StationIdentifier } from '../../../../types/Api.gen';

const props = defineProps<{
    station: Station;
    identifiers: StationIdentifier[];
}>();

const mapEl = ref<HTMLDivElement | null>(null);
let map: maplibregl.Map | null = null;
const markers: maplibregl.Marker[] = [];

type Point = { lat: number; lng: number; label: string; color: string; popupHtml: string };

function row(key: string, value: string | null | undefined): string {
    if (value == null) return '';
    return `<tr><td style="color:#666;padding-right:8px;white-space:nowrap">${key}</td><td style="font-family:monospace">${value}</td></tr>`;
}

function collectPoints(): Point[] {
    const pts: Point[] = [];

    if (props.station.latitude != null && props.station.longitude != null) {
        const html = `
            <strong style="display:block;margin-bottom:4px">Station</strong>
            <table style="border-collapse:collapse;font-size:12px">
                ${row('Name', props.station.name)}
                ${row('ID', String(props.station.id))}
                ${row('Lat', props.station.latitude?.toFixed(6))}
                ${row('Lon', props.station.longitude?.toFixed(6))}
            </table>`;
        pts.push({
            lat: props.station.latitude,
            lng: props.station.longitude,
            label: 'S',
            color: '#2563eb',
            popupHtml: html,
        });
    }

    props.identifiers.forEach((ident) => {
        if (ident.latitude != null && ident.longitude != null) {
            const html = `
                <strong style="display:block;margin-bottom:4px">Identifier</strong>
                <table style="border-collapse:collapse;font-size:12px">
                    ${row('Type', ident.type)}
                    ${row('Value', ident.identifier)}
                    ${ident.name ? row('Name', ident.name) : ''}
                    ${ident.origin ? row('Origin', ident.origin) : ''}
                    ${row('Lat', ident.latitude?.toFixed(6))}
                    ${row('Lon', ident.longitude?.toFixed(6))}
                    ${row('UUID', ident.id)}
                </table>`;
            pts.push({ lat: ident.latitude, lng: ident.longitude, label: 'I', color: '#16a34a', popupHtml: html });
        }
    });

    return pts;
}

function makeMarkerEl(color: string, label: string): HTMLElement {
    const el = document.createElement('div');
    el.style.cssText = `
        width: 24px; height: 24px; border-radius: 50%;
        background: ${color}; color: #fff; font-weight: bold;
        font-size: 11px; display: flex; align-items: center; justify-content: center;
        border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); cursor: pointer;
    `;
    el.textContent = label;
    return el;
}

function placeMarkers(pts: Point[]): void {
    markers.forEach((m) => m.remove());
    markers.length = 0;

    pts.forEach(({ lat, lng, label, color, popupHtml }) => {
        const m = new maplibregl.Marker({ element: makeMarkerEl(color, label) })
            .setLngLat([lng, lat])
            .setPopup(new maplibregl.Popup({ offset: 16 }).setHTML(popupHtml))
            .addTo(map!);
        markers.push(m);
    });
}

function fitPoints(pts: Point[]): void {
    if (!pts.length || !map) return;

    if (pts.length === 1) {
        map.setCenter([pts[0].lng, pts[0].lat]);
        map.setZoom(14);
        return;
    }

    const bounds = pts.reduce(
        (b, p) => b.extend([p.lng, p.lat]),
        new maplibregl.LngLatBounds([pts[0].lng, pts[0].lat], [pts[0].lng, pts[0].lat]),
    );
    map.fitBounds(bounds, { padding: 60, maxZoom: 16 });
}

function initMap(): void {
    if (!mapEl.value) return;

    const isDark = document.documentElement.dataset.bsTheme === 'dark';
    const ofmStyle = isDark
        ? 'https://tiles.openfreemap.org/styles/liberty'
        : 'https://tiles.openfreemap.org/styles/positron';

    map = new maplibregl.Map({
        container: mapEl.value,
        style: ofmStyle,
        center: [props.station.longitude ?? 10, props.station.latitude ?? 51],
        zoom: 12,
    });

    map.addControl(new maplibregl.NavigationControl({ showCompass: false }), 'top-right');

    map.on('load', () => {
        map!.addSource('orm', {
            type: 'raster',
            tiles: ['a', 'b', 'c'].map((s) => `https://${s}.tiles.openrailwaymap.org/standard/{z}/{x}/{y}.png`),
            tileSize: 256,
            attribution: '© <a href="https://openrailwaymap.org">OpenRailwayMap</a>',
        });
        map!.addLayer({ id: 'orm', type: 'raster', source: 'orm', paint: { 'raster-opacity': 0.6 } });

        const pts = collectPoints();
        placeMarkers(pts);
        fitPoints(pts);
    });
}

onMounted(initMap);

watch(
    () => [props.station, props.identifiers] as const,
    () => {
        if (!map) return;
        const pts = collectPoints();
        placeMarkers(pts);
        fitPoints(pts);
    },
    { deep: true },
);

onUnmounted(() => {
    markers.forEach((m) => m.remove());
    map?.remove();
    map = null;
});
</script>

<template>
    <div class="card bg-base-100 shadow">
        <div class="card-body pb-0">
            <h2 class="card-title text-base mb-2">Coordinates</h2>
            <div class="flex gap-4 text-xs text-base-content/60 mb-2">
                <span class="flex items-center gap-1">
                    <span
                        class="inline-block w-4 h-4 rounded-full"
                        style="background: #2563eb; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3)"
                    />
                    Station
                </span>
                <span class="flex items-center gap-1">
                    <span
                        class="inline-block w-4 h-4 rounded-full"
                        style="background: #16a34a; border: 2px solid #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3)"
                    />
                    Identifier
                </span>
            </div>
        </div>
        <div ref="mapEl" style="height: 320px" class="rounded-b-box overflow-hidden" />
    </div>
</template>

<style>
.maplibregl-popup-content {
    color: #111 !important;
}
</style>
