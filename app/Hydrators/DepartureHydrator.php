<?php

namespace App\Hydrators;

use App\Dto\Internal\Departure;
use App\Http\Resources\StationResource;
use Illuminate\Support\Collection;

class DepartureHydrator
{

    public static function map(Collection $collection): Collection {
        return $collection->map(function($request) {
            return self::mapSingle($request);
        });
    }

    public static function mapSingle(Departure $request) {
        $content = [
            "tripId"              => $request->trip->tripId,
            "stop"                => [
                "type"     => "stop",
                "id"       => $request->station->ibnr,
                "name"     => $request->station->name,
                "location" => [
                    "type"      => "location",
                    "id"        => $request->station->ibnr,
                    "latitude"  => $request->station->latitude,
                    "longitude" => $request->station->longitude
                ],
                "products" => [
                    "nationalExpress" => true, //TODO
                    "national"        => true, //TODO
                    "regionalExp"     => true, //TODO
                    "regional"        => true, //TODO
                    "suburban"        => true, //TODO
                    "bus"             => true, //TODO
                    "ferry"           => true, //TODO
                    "subway"          => true, //TODO
                    "tram"            => true, //TODO
                    "taxi"            => true, //TODO
                ]
            ],
            "when"                => $request->realDeparture?->toIso8601String(),
            "plannedWhen"         => $request->plannedDeparture->toIso8601String(),
            "delay"               => $request->getDelay(), //TODO: make it deprecated
            "platform"            => null,
            "plannedPlatform"     => null,
            "direction"           => $request->trip->direction,
            "provenance"          => null,
            "line"                => [
                "type"        => "line",
                "id"          => $request->trip->lineName,
                "fahrtNr"     => $request->trip->number,
                "name"        => $request->trip->lineName,
                "public"      => true,
                "adminCode"   => "80____",
                "productName" => $request->trip->lineName, //TODO
                "mode"        => "train", //TODO
                "product"     => $request->trip->category,
                "operator"    => null,/*[ //TODO
                    "type" => "operator",
                    "id"   => "db-fernverkehr-ag",
                    "name" => "DB Fernverkehr AG"
                ]*/
            ],
            "remarks"             => null,
            "origin"              => null,
            "destination"         => [
                "type"     => "stop",
                "id"       => 0,
                "name"     => $request->trip->direction,
                "location" => [
                    "type"      => "location",
                    "id"        => 0,
                    "latitude"  => 0,
                    "longitude" => 0
                ],
                "products" => [
                    "nationalExpress" => true, //TODO
                    "national"        => true, //TODO
                    "regionalExp"     => true, //TODO
                    "regional"        => true, //TODO
                    "suburban"        => true, //TODO
                    "bus"             => true, //TODO
                    "ferry"           => true, //TODO
                    "subway"          => true, //TODO
                    "tram"            => true, //TODO
                    "taxi"            => true, //TODO
                ]
            ],
            "currentTripPosition" => null, //TODO
            /*[
            "type"      => "location",
            "latitude"  => 48.725382,
            "longitude" => 8.142888
        ],*/
            "loadFactor"          => null,
            "station"             => new StationResource($request->station)
        ];

        // convert to stdClass
        return json_decode(json_encode($content));
    }
}
