<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Checkin;
use App\Models\Station;
use Illuminate\Support\Collection;

abstract class StationService
{
    public static function getNearbyStations(Station $station, $maxDistanceKilometers = 10, int $limit = 15): Collection
    {
        $latitude = $station->latitude;
        $longitude = $station->longitude;

        $latDelta = $maxDistanceKilometers / 111;  // ca. 1° ≈ 111 km
        $lngDelta = $maxDistanceKilometers / abs(cos(deg2rad($latitude)) * 111);

        $minLat = $latitude - $latDelta;
        $maxLat = $latitude + $latDelta;
        $minLng = $longitude - $lngDelta;
        $maxLng = $longitude + $lngDelta;

        $earthRadius = 6371;

        return Station::selectRaw("*, (
    $earthRadius * acos(
        cos(radians(?)) * cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) * sin(radians(latitude))
    )
) AS distance", [$latitude, $longitude, $latitude])
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng])
            ->where('id', '!=', $station->id)
            ->orderBy('distance')
            ->limit($limit)
            ->get();
    }

    public static function getLatestCheckins(Station $station, int $limit = 10): Collection
    {
        return Checkin::where(function ($query) use ($station) {
            $query->whereHas('originStopover', function ($subQuery) use ($station) {
                $subQuery->where('train_station_id', $station->id);
            })->orWhereHas('destinationStopover', function ($subQuery) use ($station) {
                $subQuery->where('train_station_id', $station->id);
            });
        })
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
