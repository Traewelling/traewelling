<?php

use App\Dto\Coordinate;
use App\Services\GeoService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Issue #4910: some feeds (e.g. the purely numeric DELFI ids in Saxony-Anhalt) reuse a stop id for another stop in a
 * later export. Clean up data after fix from PR#5095.
 */
return new class() extends Migration
{
    public function up(): void
    {
        $maxDistance = (int) config('trwl.motis.max_cache_distance');
        // Any pair farther apart than $maxDistance differs by at least this many degrees in latitude or longitude
        // Inlined instead of bound, because SQLite compares a bound float as text
        $minDegreeDifference = sprintf('%.8F', $maxDistance / (111_320 * M_SQRT2));
        $geoService = new GeoService();
        $retiredAt = now();

        DB::table('station_identifiers')
            ->join('train_stations', 'train_stations.id', '=', 'station_identifiers.station_id')
            ->where('station_identifiers.type', 'motis')
            ->where('station_identifiers.identifier', 'not like', '%#retired-%')
            ->whereNotNull('station_identifiers.latitude')
            ->whereNotNull('station_identifiers.longitude')
            ->whereNotNull('train_stations.latitude')
            ->whereNotNull('train_stations.longitude')
            ->where(function (Builder $query) use ($minDegreeDifference) {
                $query->whereRaw('ABS(station_identifiers.latitude - train_stations.latitude) > ' . $minDegreeDifference)
                    ->orWhereRaw('ABS(station_identifiers.longitude - train_stations.longitude) > ' . $minDegreeDifference);
            })
            ->get([
                'station_identifiers.id',
                'station_identifiers.identifier',
                'station_identifiers.latitude',
                'station_identifiers.longitude',
                'train_stations.latitude as station_latitude',
                'train_stations.longitude as station_longitude',
            ])
            ->filter(fn (object $row): bool => $geoService->getDistance(
                new Coordinate((float) $row->latitude, (float) $row->longitude),
                new Coordinate((float) $row->station_latitude, (float) $row->station_longitude),
            ) > $maxDistance)
            ->each(fn (object $row) => DB::table('station_identifiers')
                ->where('id', $row->id)
                ->update([
                    'identifier' => $row->identifier . '#retired-' . $retiredAt->timestamp,
                    'relevance' => -9_000,
                    'latitude' => $row->station_latitude,
                    'longitude' => $row->station_longitude,
                    'updated_at' => $retiredAt,
                ]));
    }
};
