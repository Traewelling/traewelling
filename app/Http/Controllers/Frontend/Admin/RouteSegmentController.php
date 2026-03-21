<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend\Admin;

use App\Dto\Coordinate;
use App\Exceptions\BRouterException;
use App\Models\RouteSegment;
use App\Services\BRouterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Traewelling\GooglePolyline\PolylineTranscoder;

class RouteSegmentController
{
    public function renderSegment(string $id): View
    {
        $segment = RouteSegment::findOrFail($id);

        return view('admin.routesegment.show', [
            'segment' => $segment,
        ]);
    }

    /**
     * EXPERIMENTAL ADMIN TEST: TODO: Move later to contribution system!
     * Proxy waypoints to BRouter and return the resulting GeoJSON coordinates.
     */
    public function brouterPreview(Request $request, string $id, BRouterService $brouter): JsonResponse
    {
        RouteSegment::findOrFail($id);

        $validated = $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric'],
            'waypoints.*.lng' => ['required', 'numeric'],
        ]);

        $waypoints = array_map(
            static fn (array $w) => new Coordinate((float) $w['lat'], (float) $w['lng']),
            $validated['waypoints'],
        );

        try {
            $route = $brouter->getRoute($waypoints);
        } catch (BRouterException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'coordinates' => array_map(
                static fn (Coordinate $c) => ['lat' => $c->latitude, 'lng' => $c->longitude],
                $route->coordinates,
            ),
            'distance' => $route->distanceInMeters,
        ]);
    }

    /**
     * EXPERIMENTAL ADMIN TEST: TODO: Move later to contribution system!
     * Save custom waypoints and regenerate the segment's polyline via BRouter.
     */
    public function saveFromBrouter(Request $request, string $id, BRouterService $brouter): RedirectResponse
    {
        $segment = RouteSegment::findOrFail($id);

        $validated = $request->validate([
            'waypoints' => ['required', 'array', 'min:2'],
            'waypoints.*.lat' => ['required', 'numeric'],
            'waypoints.*.lng' => ['required', 'numeric'],
        ]);

        $waypointDtos = array_map(
            static fn (array $w) => new Coordinate((float) $w['lat'], (float) $w['lng']),
            $validated['waypoints'],
        );

        try {
            $route = $brouter->getRoute($waypointDtos);
        } catch (BRouterException $e) {
            return back()->with('alert-danger', 'BRouter error: ' . $e->getMessage());
        }

        // Encode route coordinates as Google Polyline (precision 5)
        // encodePolyline expects [[lon, lat], ...]
        $transcoder = new PolylineTranscoder();
        $encodedPolyline = $transcoder->encodePolyline(
            array_map(static fn (Coordinate $c) => [$c->longitude, $c->latitude], $route->coordinates),
            5,
        );

        // Persist custom waypoints for easy reloading on next page visit
        $customWaypoints = array_map(
            static fn (Coordinate $c) => ['lat' => $c->latitude, 'lng' => $c->longitude],
            $waypointDtos,
        );

        $segment->update([
            'polyline' => $encodedPolyline,
            'polyline_precision' => 5,
            'distance' => $route->distanceInMeters,
            // duration is intentionally not updated — it reflects the scheduled timetable, not the routed distance
            'custom_waypoints' => $customWaypoints,
        ]);

        return back()->with('alert-success', 'Segment successfully updated via BRouter.');
    }
}
