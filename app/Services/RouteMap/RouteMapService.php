<?php

declare(strict_types=1);

namespace App\Services\RouteMap;

use App\Dto\Coordinate;
use App\Dto\RouteMap\RouteMapEntryDto;
use App\Dto\RouteMap\RouteMapFilterDto;
use App\Enum\HafasTravelType;
use App\Models\User;
use App\Repositories\RouteMapRepository;
use App\Services\GeoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Traewelling\GooglePolyline\PolylineTranscoder;

class RouteMapService
{
    private const int LOOKUP_CHUNK_SIZE = 2000;

    private const int APPROXIMATED_POLYLINE_PRECISION = 5;

    public function __construct(
        private readonly RouteMapRepository $repository,
        private readonly GeoService $geoService,
    ) {}

    public function getRouteMap(User $user, RouteMapFilterDto $filter): Collection
    {
        $stretches = $this->repository->getTravelledStretches($user, $filter);

        /** @var array<string, HafasTravelType[]> $categoriesPerSegment */
        $categoriesPerSegment = [];
        /** @var array<string, array{from: int, to: int, categories: HafasTravelType[]}> $unroutedLegs */
        $unroutedLegs = [];

        foreach ($stretches as $stretch) {
            $categories = $this->parseCategories($stretch->categories);

            if ($stretch->route_segment_id !== null) {
                $categoriesPerSegment[$stretch->route_segment_id] = array_merge(
                    $categoriesPerSegment[$stretch->route_segment_id] ?? [],
                    $categories
                );

                continue;
            }

            $from = (int) $stretch->from_station_id;
            $to = (int) $stretch->to_station_id;
            $key = $from . ':' . $to;

            $unroutedLegs[$key] ??= ['from' => $from, 'to' => $to, 'categories' => []];
            $unroutedLegs[$key]['categories'] = array_merge($unroutedLegs[$key]['categories'], $categories);
        }

        $segments = $this->loadRouteSegments(array_keys($categoriesPerSegment));
        $stations = $this->loadStations(array_unique(array_merge(
            array_column($unroutedLegs, 'from'),
            array_column($unroutedLegs, 'to'),
        )));

        $entries = [];

        foreach ($categoriesPerSegment as $segmentId => $categories) {
            $segment = $segments->get($segmentId);
            if ($segment === null) {
                continue;
            }

            $entries[] = new RouteMapEntryDto(
                routeSegmentId: (string) $segment->id,
                fromStationUuid: null,
                toStationUuid: null,
                polyline: (string) $segment->polyline,
                polylinePrecision: (int) $segment->polyline_precision,
                distance: $segment->distance === null ? null : (int) $segment->distance,
                pathType: $segment->path_type,
                categories: $this->sortCategories($categories),
                approximated: false,
            );
        }

        foreach ($unroutedLegs as $leg) {
            $entry = $this->buildApproximatedEntry($leg, $stations);
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return collect($entries);
    }

    private function buildApproximatedEntry(array $leg, Collection $stations): ?RouteMapEntryDto
    {
        $from = $stations->get($leg['from']);
        $to = $stations->get($leg['to']);

        if ($from === null || $to === null || $leg['from'] === $leg['to']) {
            return null;
        }

        $fromCoordinate = new Coordinate((float) $from->latitude, (float) $from->longitude);
        $toCoordinate = new Coordinate((float) $to->latitude, (float) $to->longitude);

        return new RouteMapEntryDto(
            routeSegmentId: null,
            fromStationUuid: $from->uuid,
            toStationUuid: $to->uuid,
            polyline: new PolylineTranscoder()->encodePolyline(
                [$fromCoordinate->toArray(), $toCoordinate->toArray()],
                self::APPROXIMATED_POLYLINE_PRECISION
            ),
            polylinePrecision: self::APPROXIMATED_POLYLINE_PRECISION,
            distance: (int) $this->geoService->getDistance($fromCoordinate, $toCoordinate),
            pathType: null,
            categories: $this->sortCategories($leg['categories']),
            approximated: true,
        );
    }

    /**
     * @param  string[]  $ids
     * @return Collection<string, object>
     */
    private function loadRouteSegments(array $ids): Collection
    {
        return $this->loadInChunks($ids, static fn (array $chunk) => DB::table('route_segments')
            ->whereIn('id', $chunk)
            ->select(['id', 'polyline', 'polyline_precision', 'distance', 'path_type'])
            ->get());
    }

    /**
     * @param  int[]  $ids
     * @return Collection<int, object>
     */
    private function loadStations(array $ids): Collection
    {
        return $this->loadInChunks($ids, static fn (array $chunk) => DB::table('train_stations')
            ->whereIn('id', $chunk)
            ->select(['id', 'uuid', 'latitude', 'longitude'])
            ->get());
    }

    /**
     * @param  array<int|string>  $ids
     * @return Collection<int|string, object>
     */
    private function loadInChunks(array $ids, callable $loader): Collection
    {
        if ($ids === []) {
            return collect();
        }

        return collect(array_chunk(array_values($ids), self::LOOKUP_CHUNK_SIZE))
            ->flatMap(static fn (array $chunk) => $loader($chunk)->all())
            ->keyBy('id');
    }

    /**
     * @return HafasTravelType[]
     */
    private function parseCategories(?string $concatenated): array
    {
        if ($concatenated === null || $concatenated === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $category) => HafasTravelType::tryFrom(trim($category)),
            explode(',', $concatenated)
        )));
    }

    /**
     * @param  HafasTravelType[]  $categories
     * @return HafasTravelType[]
     */
    private function sortCategories(array $categories): array
    {
        $unique = array_values(array_unique($categories, SORT_REGULAR));
        usort($unique, static fn (HafasTravelType $a, HafasTravelType $b) => strcmp($a->value, $b->value));

        return $unique;
    }
}
