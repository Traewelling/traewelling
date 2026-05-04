<?php

declare(strict_types=1);

namespace App\Services\Trip;

use App\Dto\Coordinate;
use App\Enum\BRouterProfile;
use App\Enum\HafasTravelType;
use App\Enum\SegmentPathType;
use App\Exceptions\BRouterException;
use App\Models\Station;
use App\Services\BRouterService;
use App\Services\GeodesicService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JsonException;

readonly class RoutePreviewService
{
    public function __construct(
        private BRouterService $brouter,
        private GeodesicService $geodesic,
    ) {}

    public function build(array $stationIds, HafasTravelType $category): array
    {
        $waypoints = $this->resolveWaypoints($stationIds);

        $pathType = $category->getSegmentPathType();
        $profile = $pathType?->getBRouterProfile();

        [$coordinates, $routed] = match (true) {
            $pathType === SegmentPathType::GREAT_CIRCLE => [$this->geodesicArc($waypoints), false],
            $profile !== null => $this->bRouterSegments($waypoints, $profile),
            default => [$this->straightLine($waypoints), false],
        };

        return [
            'type' => 'Feature',
            'geometry' => ['type' => 'LineString', 'coordinates' => $coordinates],
            'properties' => ['routed' => $routed],
        ];
    }

    /**
     * @param  int[]  $stationIds
     * @return Coordinate[]
     *
     * @throws \InvalidArgumentException
     */
    private function resolveWaypoints(array $stationIds): array
    {
        /** @var Collection<int, Station> $stations */
        $stations = Station::findMany($stationIds)->keyBy('id');

        $waypoints = [];
        foreach ($stationIds as $id) {
            $station = $stations->get($id);
            if ($station === null || $station->latitude === null || $station->longitude === null) {
                throw new \InvalidArgumentException("Station {$id} not found or has no coordinates.");
            }
            $waypoints[] = new Coordinate((float) $station->latitude, (float) $station->longitude);
        }

        return $waypoints;
    }

    /**
     * Great-circle arcs between every consecutive pair.
     *
     * @param  Coordinate[]  $waypoints
     * @return array<array{float, float}>
     */
    private function geodesicArc(array $waypoints): array
    {
        $coords = [];
        foreach (array_keys(array_slice($waypoints, 0, -1)) as $i) {
            $segment = $this->geodesic->interpolate($waypoints[$i], $waypoints[$i + 1], 40);
            foreach ($segment as $j => $c) {
                if ($j === 0 && $coords !== []) {
                    continue;
                }
                $coords[] = [$c->longitude, $c->latitude];
            }
        }

        return $coords;
    }

    /**
     * Route each consecutive pair via BRouter; fall back to a straight line per segment on failure.
     *
     * @param  Coordinate[]  $waypoints
     * @return array{array<array{float, float}>, bool}
     */
    private function bRouterSegments(array $waypoints, BRouterProfile $profile): array
    {
        $coords = [];
        $routed = false;

        foreach (array_keys(array_slice($waypoints, 0, -1)) as $i) {
            $from = $waypoints[$i];
            $to = $waypoints[$i + 1];

            try {
                $route = $this->brouter->getRoute([$from, $to], $profile);
                foreach ($route->coordinates as $j => $c) {
                    if ($j === 0 && $coords !== []) {
                        continue;
                    }
                    $coords[] = [$c->longitude, $c->latitude];
                }
                $routed = true;
            } catch (BRouterException|GuzzleException|JsonException) {
                if ($coords === [] || end($coords) !== [$from->longitude, $from->latitude]) {
                    $coords[] = [$from->longitude, $from->latitude];
                }
                $coords[] = [$to->longitude, $to->latitude];
            }
        }

        return [$coords, $routed];
    }

    /**
     * @param  Coordinate[]  $waypoints
     * @return array<array{float, float}>
     */
    private function straightLine(array $waypoints): array
    {
        return array_map(static fn (Coordinate $c) => [$c->longitude, $c->latitude], $waypoints);
    }
}
