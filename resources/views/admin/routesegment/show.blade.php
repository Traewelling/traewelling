@php use App\Http\Controllers\Backend\User\ProfilePictureController; @endphp
@extends('admin.layout')

@section('title', 'Segment ' . $segment->id)

@php
    /** @var \App\Models\RouteSegment $segment */
    $coordinates = $segment->getCoordinates();
    $points = [];
    foreach ($coordinates as $coord) {
        $points[] = [(float)$coord->latitude, (float)$coord->longitude];
    }
    $fromStation     = $segment->fromStation;
    $toStation       = $segment->toStation;
    $customWaypoints = $segment->custom_waypoints ?? null;
@endphp

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div id="vue-route-segment-panel"
                 data-segment-id="{{ $segment->id }}"
                 data-from-station-id="{{ $fromStation->id }}"
                 data-to-station-id="{{ $toStation->id }}"></div>
        </div>

        <div class="col-md-8">
            <!-- BRouter Editor -->
            <div class="card mb-2">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><strong>BRouter Route Editor</strong></span>
                    <div class="d-flex gap-2 align-items-center">
                        <span id="brouter-status" class="text-muted small"></span>
                        <button class="btn btn-sm btn-warning" id="btn-reset-waypoints">Reset to Stations</button>
                        <button class="btn btn-sm btn-success" id="btn-save" disabled>Save to Segment</button>
                    </div>
                </div>
                <div class="card-body p-1">
                    <div id="map-editor" style="height: 60vh;"></div>
                </div>
            </div>

            <!-- BRouter preview info -->
            <div id="brouter-info" class="alert alert-info d-none">
                <strong>BRouter Preview:</strong>
                Distance: <span id="preview-distance">-</span> km &nbsp;|&nbsp;
                Duration: <span id="preview-duration">-</span>
            </div>

            <!-- links to all trips in use -->
            <div class="accordion" id="tripsAccordion">
                <div class="accordion-item">
                    <h5 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            Trips using this Segment: {{ $segment->trips->count() }}
                        </button>
                    </h5>
                    <div class="flex flex-wrap accordion-collapse collapse p-2" id="collapseOne" aria-labelledby="headingOne" data-bs-parent="#tripsAccordion">
                        @foreach($segment->trips as $trip)
                            <a class="btn btn-primary btn-sm mt-1" href="{{ route('admin.trips.show', $trip) }}">Trip #{{ $trip->id }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    {{-- Hidden form for saving (waypoint inputs are injected dynamically) --}}
    <form id="form-save" method="POST" action="{{ route('admin.routesegment.save-from-brouter', $segment->id) }}" class="d-none">
        @csrf
        <div id="waypoints-container"></div>
    </form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const segmentId   = @json($segment->id);
    const existingPts = @json($points);         // [[lat, lng], ...]
    const fromStation = { lat: @json((float)$fromStation->latitude), lng: @json((float)$fromStation->longitude) };
    const toStation   = { lat: @json((float)$toStation->latitude),   lng: @json((float)$toStation->longitude) };
    const savedWaypoints = @json($customWaypoints);  // [[lat, lng], ...] or null

    const previewUrl = @json(route('admin.routesegment.brouter-preview', $segment->id));
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').content;

    // ── Map setup ──────────────────────────────────────────────────────────────
    const map = L.map('map-editor');
    setTilingLayer('open-railway-map', map);

    // Show existing polyline in blue + connector lines from A/B to first/last point
    let existingPolyline = null;
    let connectorA = null;
    let connectorB = null;

    function drawExistingPolyline() {
        if (existingPolyline) { map.removeLayer(existingPolyline); existingPolyline = null; }
        if (connectorA)        { map.removeLayer(connectorA);       connectorA = null; }
        if (connectorB)        { map.removeLayer(connectorB);       connectorB = null; }

        if (existingPts.length < 2) return;

        existingPolyline = L.polyline(existingPts, { color: '#3388ff', weight: 8, opacity: 0.5 }).addTo(map);
        addPolylineDragBehavior(existingPolyline);

        const firstPt = existingPts[0];
        const lastPt  = existingPts[existingPts.length - 1];
        const posA    = markerA.getLatLng();
        const posB    = markerB.getLatLng();

        // Only draw connector if A/B are not already at the first/last polyline point
        const threshold = 0.0001;
        if (Math.abs(posA.lat - firstPt[0]) > threshold || Math.abs(posA.lng - firstPt[1]) > threshold) {
            connectorA = L.polyline([posA, firstPt], { color: '#28a745', weight: 2, opacity: 0.8, dashArray: '5 5' }).addTo(map);
        }
        if (Math.abs(posB.lat - lastPt[0]) > threshold || Math.abs(posB.lng - lastPt[1]) > threshold) {
            connectorB = L.polyline([posB, lastPt], { color: '#dc3545', weight: 2, opacity: 0.8, dashArray: '5 5' }).addTo(map);
        }
    }

    if (existingPts.length > 1) {
        // Draw after markers exist (called below after marker creation)
    } else {
        map.setView([fromStation.lat, fromStation.lng], 12);
    }

    // BRouter preview polyline (orange)
    let previewPolyline = null;

    // ── Marker icons ──────────────────────────────────────────────────────────
    function makeIcon(label, color) {
        return L.divIcon({
            className: '',
            html: `<div style="background:${color};color:#fff;font-weight:bold;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.5);font-size:13px;">${label}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });
    }

    // ── Waypoint state ────────────────────────────────────────────────────────
    // markerA and markerB are always present; intermediates is an ordered array of markers
    let markerA = L.marker([fromStation.lat, fromStation.lng], {
        draggable: true,
        icon: makeIcon('A', '#28a745'),
    }).addTo(map).bindTooltip('Origin (A)');

    let markerB = L.marker([toStation.lat, toStation.lng], {
        draggable: true,
        icon: makeIcon('B', '#dc3545'),
    }).addTo(map).bindTooltip('Destination (B)');

    let intermediates = []; // ordered list of markers

    // If there are saved custom waypoints, restore them
    if (savedWaypoints && savedWaypoints.length >= 2) {
        const a = savedWaypoints[0];
        const b = savedWaypoints[savedWaypoints.length - 1];
        markerA.setLatLng([a.lat, a.lng]);
        markerB.setLatLng([b.lat, b.lng]);
        for (let i = 1; i < savedWaypoints.length - 1; i++) {
            addIntermediateMarker(savedWaypoints[i].lat, savedWaypoints[i].lng);
        }
    }

    // Draw existing polyline + connectors now that markers are positioned
    drawExistingPolyline();
    if (existingPts.length > 1) {
        const bounds = L.latLngBounds([
            ...existingPts,
            markerA.getLatLng(),
            markerB.getLatLng(),
        ]);
        map.fitBounds(bounds);
    }

    // ── Geometry helper: squared distance from point p to segment [a, b] ──────
    function pointToSegmentDist(p, a, b) {
        const dx = b.lat - a.lat, dy = b.lng - a.lng;
        const lenSq = dx * dx + dy * dy;
        if (lenSq === 0) return Math.hypot(p.lat - a.lat, p.lng - a.lng);
        const t = Math.max(0, Math.min(1, ((p.lat - a.lat) * dx + (p.lng - a.lng) * dy) / lenSq));
        return Math.hypot(p.lat - (a.lat + t * dx), p.lng - (a.lng + t * dy));
    }

    // Find which segment of collectWaypoints() the point is nearest to,
    // return the insertion index for intermediates[].
    function nearestSegmentIndex(latlng) {
        const wps = collectWaypoints();
        let best = 0, bestDist = Infinity;
        for (let i = 0; i < wps.length - 1; i++) {
            const d = pointToSegmentDist(latlng, wps[i], wps[i + 1]);
            if (d < bestDist) { bestDist = d; best = i; }
        }
        return best; // insert new intermediate at intermediates.splice(best, 0, ...)
    }

    // ── Intermediate marker helpers ───────────────────────────────────────────
    function renumberIntermediates() {
        intermediates.forEach((m, i) => m.setIcon(makeIcon(i + 1, '#ff8800')));
    }

    function addIntermediateMarker(lat, lng, insertAt = intermediates.length) {
        const marker = L.marker([lat, lng], {
            draggable: true,
            icon: makeIcon('?', '#ff8800'),
        }).addTo(map).bindTooltip('Intermediate – click to remove');

        marker.on('dragend', () => requestPreview());
        marker.on('click', () => removeIntermediate(marker));

        intermediates.splice(insertAt, 0, marker);
        renumberIntermediates();
        return marker;
    }

    function removeIntermediate(marker) {
        intermediates = intermediates.filter(m => m !== marker);
        map.removeLayer(marker);
        renumberIntermediates();
        requestPreview();
    }

    // ── Make a polyline draggable to insert intermediate waypoints ────────────
    function addPolylineDragBehavior(polyline) {
        polyline.options.interactive = true;
        const el = polyline.getElement();
        if (el) el.style.cursor = 'crosshair';

        polyline.on('mouseover', function () {
            const e = this.getElement();
            if (e) e.style.cursor = 'crosshair';
        });

        polyline.on('mousedown', function (e) {
            L.DomEvent.stop(e);
            map.dragging.disable();

            const insertAt = nearestSegmentIndex(e.latlng);
            const marker = addIntermediateMarker(e.latlng.lat, e.latlng.lng, insertAt);

            // Hand over drag control to the new marker immediately
            if (marker.dragging && marker.dragging._draggable) {
                marker.dragging._draggable._onDown(e.originalEvent);
            }

            marker.once('dragend', () => map.dragging.enable());
        });
    }

    // ── Collect ordered waypoints ─────────────────────────────────────────────
    function collectWaypoints() {
        const pts = [];
        pts.push(markerA.getLatLng());
        for (const m of intermediates) {
            pts.push(m.getLatLng());
        }
        pts.push(markerB.getLatLng());
        return pts.map(ll => ({ lat: ll.lat, lng: ll.lng }));
    }

    // ── BRouter request ───────────────────────────────────────────────────────
    let pendingRequest = null;

    async function requestPreview() {
        setStatus('Requesting BRouter…', 'text-warning');
        document.getElementById('btn-save').disabled = true;

        if (pendingRequest) {
            pendingRequest.abort();
        }
        const controller = new AbortController();
        pendingRequest = controller;

        const waypoints = collectWaypoints();

        try {
            const res = await fetch(previewUrl, {
                method: 'POST',
                signal: controller.signal,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ waypoints }),
            });

            const data = await res.json();

            if (!res.ok || data.error) {
                setStatus('BRouter error: ' + (data.error ?? res.statusText), 'text-danger');
                clearPreview();
                return;
            }

            drawPreview(data);
            showPreviewInfo(data);
            setStatus('Route received ✓', 'text-success');
            document.getElementById('btn-save').disabled = false;

        } catch (e) {
            if (e.name !== 'AbortError') {
                setStatus('Request failed: ' + e.message, 'text-danger');
                clearPreview();
            }
        } finally {
            if (pendingRequest === controller) {
                pendingRequest = null;
            }
        }
    }

    function drawPreview(data) {
        clearPreview();
        // Hide existing polyline and connectors once a preview is available
        if (existingPolyline) { existingPolyline.remove(); }
        if (connectorA)       { connectorA.remove(); }
        if (connectorB)       { connectorB.remove(); }
        const latLngs = data.coordinates.map(c => [c.lat, c.lng]);
        previewPolyline = L.polyline(latLngs, { color: '#ff6600', weight: 8, opacity: 0.7 }).addTo(map);
        addPolylineDragBehavior(previewPolyline);
    }

    function clearPreview() {
        if (previewPolyline) {
            map.removeLayer(previewPolyline);
            previewPolyline = null;
            // Restore existing polyline and connectors
            drawExistingPolyline();
        }
        document.getElementById('brouter-info').classList.add('d-none');
    }

    function showPreviewInfo(data) {
        document.getElementById('preview-distance').textContent = (data.distance / 1000).toFixed(2);
        document.getElementById('preview-duration').textContent = new Date(data.duration * 1000).toISOString().substring(11, 19);
        document.getElementById('brouter-info').classList.remove('d-none');
    }

    function setStatus(msg, cls) {
        const el = document.getElementById('brouter-status');
        el.textContent = msg;
        el.className = 'small ' + cls;
    }

    // ── Drag listeners ────────────────────────────────────────────────────────
    markerA.on('dragend', () => { drawExistingPolyline(); requestPreview(); });
    markerB.on('dragend', () => { drawExistingPolyline(); requestPreview(); });

    // ── Button actions ────────────────────────────────────────────────────────
    document.getElementById('btn-reset-waypoints').addEventListener('click', () => {
        markerA.setLatLng([fromStation.lat, fromStation.lng]);
        markerB.setLatLng([toStation.lat, toStation.lng]);
        clearIntermediates();
        drawExistingPolyline();
        requestPreview();
    });

    function clearIntermediates() {
        for (const m of intermediates) {
            map.removeLayer(m);
        }
        intermediates = [];
    }

    document.getElementById('btn-save').addEventListener('click', () => {
        const waypoints = collectWaypoints();
        const container = document.getElementById('waypoints-container');
        container.innerHTML = '';
        waypoints.forEach((wp, i) => {
            const lat = document.createElement('input');
            lat.type = 'hidden';
            lat.name = `waypoints[${i}][lat]`;
            lat.value = wp.lat;
            const lng = document.createElement('input');
            lng.type = 'hidden';
            lng.name = `waypoints[${i}][lng]`;
            lng.value = wp.lng;
            container.appendChild(lat);
            container.appendChild(lng);
        });
        document.getElementById('form-save').submit();
    });

    // ── Initial preview if custom waypoints exist ─────────────────────────────
    if (savedWaypoints && savedWaypoints.length >= 2) {
        requestPreview();
    }
});
</script>
@endsection
