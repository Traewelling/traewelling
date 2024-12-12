<?php

declare(strict_types=1);

namespace App\Helpers;

enum HCK
{
    public const string DEPARTURES_SUCCESS = 'monitoring-counter-HafasDeparturesSuccess';
    public const string DEPARTURES_FAILURE = 'monitoring-counter-HafasDeparturesFailure';
    public const string DEPARTURES_NOT_OK  = 'monitoring-counter-HafasDeparturesNotOk';
    public const string TRIPS_SUCCESS      = 'monitoring-counter-HafasTripsSuccess';
    public const string TRIPS_FAILURE      = 'monitoring-counter-HafasTripsFailure';
    public const string TRIPS_NOT_OK       = 'monitoring-counter-HafasTripsNotOk';
    public const string TRIPS_502          = 'monitoring-counter-HafasTrips502';
    public const string STOPS_SUCCESS      = 'monitoring-counter-HafasStopsSuccess';
    public const string STOPS_FAILURE      = 'monitoring-counter-HafasStopsFailure';
    public const string STOPS_NOT_OK       = 'monitoring-counter-HafasStopsNotOk';
    public const string STATIONS_SUCCESS   = 'monitoring-counter-HafasStationsSuccess';
    public const string STATIONS_FAILURE   = 'monitoring-counter-HafasStationsFailure';
    public const string STATIONS_NOT_OK    = 'monitoring-counter-HafasStationsNotOk';
    public const string LOCATIONS_SUCCESS  = 'monitoring-counter-HafasLocationsSuccess';
    public const string LOCATIONS_FAILURE  = 'monitoring-counter-HafasLocationsFailure';
    public const string LOCATIONS_NOT_OK   = 'monitoring-counter-HafasLocationsNotOk';
    public const string NEARBY_SUCCESS     = 'monitoring-counter-HafasNearbySuccess';
    public const string NEARBY_FAILURE     = 'monitoring-counter-HafasNearbyFailure';
    public const string NEARBY_NOT_OK      = 'monitoring-counter-HafasNearbyNotOk';

    /**
     * @return array {string: string}
     */
    public static function getFailures(): array {
        return [
            self::DEPARTURES_FAILURE => 'Departures',
            self::TRIPS_FAILURE      => 'Trips',
            self::STOPS_FAILURE      => 'Stops',
            self::STATIONS_FAILURE   => 'Stations',
            self::LOCATIONS_FAILURE  => 'Locations',
            self::NEARBY_FAILURE     => 'Nearby',
        ];
    }

    /**
     * @return array {string: string}
     */
    public static function getNotOks(): array {
        return [
            self::DEPARTURES_NOT_OK => 'Departures',
            self::TRIPS_NOT_OK      => 'Trips',
            self::STOPS_NOT_OK      => 'Stops',
            self::STATIONS_NOT_OK   => 'Stations',
            self::LOCATIONS_NOT_OK  => 'Locations',
            self::NEARBY_NOT_OK     => 'Nearby',
        ];
    }

    /**
     * @return array {string: string}
     */
    public static function getSuccesses(): array {
        return [
            self::DEPARTURES_SUCCESS => 'Departures',
            self::TRIPS_SUCCESS      => 'Trips',
            self::STOPS_SUCCESS      => 'Stops',
            self::STATIONS_SUCCESS   => 'Stations',
            self::LOCATIONS_SUCCESS  => 'Locations',
            self::NEARBY_SUCCESS     => 'Nearby',
        ];
    }
}
