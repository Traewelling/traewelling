<?php

namespace App\Dto\FriendlyPublicTransportFormat;

use App\Traits\FriendlyPublicTransportFormat\DepartureArrivalTimes;

class Trip
{
    use DepartureArrivalTimes;
    public readonly Stop $origin;
    public readonly Stop $destination;

    public readonly bool $reachable;
    public readonly ?object $polyline; // ToDo: Whoever want's to hang themselves, go for it
    public readonly Line $line;
    public readonly string $direction;
    public readonly array $stopovers;
    public readonly array $remarks;
    public readonly ?string $loadFactor;
    public readonly string $id;
    public readonly ?int $realtimeDataUpdatedAt;

    //ToDo add correct data->attributes
    public function __construct(array $data) {
        $this->route_id = $data->route_id;
        $this->trip_id = $data->trip_id;
        $this->route_short_name = $data->route_short_name;
        $this->route_type=$data->route_type;
        $this->agency_id=$data->agency_id;
        $this->agency_name=$data->agency_name;
        $this->origin = $data->origin;
        $this->destination = $data->destination;
        $this->arrival = $data->arrival;
        $this->plannedArrival = $data->arrival;
        $this->arrivalDelay = null;
        $this->arrivalPlatform = "";
        $this->plannedArrivalPlatform = "";
        $this->arrivalPrognosisType = null;
        $this->departure = $data->departure;
        $this->plannedDeparture = $data->departure;
        $this->departureDelay = null;
        $this->departurePlatform = "";
        $this->plannedDeparturePlatform = "";
        $this->departurePrognosisType = null;
        $this->reachable = true;
        $this->line = new Line($data);
        $this->polyline = new stdClass();
        $this->stopovers = $data->stopovers;
        $this->remarks = [];
        $this->loadFactor = "nothing";
        $this->realtimeDataUpdatedAt = null;
    }
}
