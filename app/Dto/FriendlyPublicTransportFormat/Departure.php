<?php

namespace App\Dto\FriendlyPublicTransportFormat;

use App\Models\TrainStation;

class Departure
{
    public readonly string $tripId;
    public readonly Stop $stop;
    public readonly ?DTM $when;
    public readonly ?DTM $plannedWhen;
    public readonly ?int $delay;
    public readonly ?string $platform;
    public readonly ?string $plannedPlatform;
    public readonly ?string $prognosisType;
    public readonly string $direction;
    public readonly ?string $provenance;
    public readonly Line $line;
    public readonly array $remarks;
    public readonly ?string $origin;
    public readonly Stop $destination;
    public readonly ?TrainStation $station;

    public function __construct(object $data)
    {
        $this->tripId = $data->tripId;
        $this->stop = $data->stop;
        $this->when = new DTM($data->when);
        $this->plannedWhen = new DTM($data->plannedWhen);
        $this->delay = $data->delay;
        $this->platform = $data->platform;
        $this->plannedPlatform = $data->plannedPlatform;
        $this->prognosisType = $data->prognosisType;
        $this->direction = $data->direction;
        $this->provenance = $data->provenance;
        $this->line = $data->line;
        $this->remarks = $data->remarks;
        $this->origin = $data->origin;
        $this->destination = $data->destination;
        $this->station = $data->station;
    }

}
