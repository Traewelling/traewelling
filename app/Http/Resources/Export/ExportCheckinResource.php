<?php

declare(strict_types=1);

namespace App\Http\Resources\Export;

use App\Enum\StatusTagKey;
use App\Http\Resources\DataSourceResource;
use App\Http\Resources\OperatorResource;
use App\Http\Resources\StationResource;
use App\Models\Checkin;
use App\Models\StatusTag;
use App\Models\Stopover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportCheckinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Checkin $this */
        $manualJourneyNumber = $this->relationLoaded('statusTags')
            ? $this->statusTags->firstWhere('key', StatusTagKey::JOURNEY_NUMBER->value)
            : StatusTag::whereStatusId($this->status_id)->whereRaw('`key` = ?', [StatusTagKey::JOURNEY_NUMBER->value])->first();

        return [
            'trip_id' => (int) $this->trip->id,
            'external_trip_id' => (string) $this->trip->trip_id,
            'category' => (string) $this->trip->category->value,
            'mode' => $this->trip->mode ? (string) $this->trip->mode->value : null,
            'number' => (string) $this->trip->number,
            'lineName' => (string) $this->trip->linename,
            'routeColor' => $this->trip->route_color,
            'routeTextColor' => $this->trip->route_text_color,
            'journeyNumber' => $this->trip->journey_number,
            'manualJourneyNumber' => $manualJourneyNumber?->value,
            'distance' => (int) $this->distance,
            'points' => (int) $this->points,
            'duration' => (int) $this->duration,
            'manualDeparture' => $this->manual_departure?->toIso8601String(),
            'manualArrival' => $this->manual_arrival?->toIso8601String(),
            'origin' => new ExportStopoverResource($this->originStopover),
            'destination' => new ExportStopoverResource($this->destinationStopover),
            'tripOrigin' => new StationResource($this->trip->originStation),
            'tripDestination' => new StationResource($this->trip->destinationStation),
            'stopovers' => ExportStopoverResource::collection($this->checkinStopovers()),
            'operator' => $this->trip->operator
                ? new OperatorResource($this->trip->operator)
                : null,
            'dataSource' => $this->trip->motisSourceLicense
                ? new DataSourceResource($this->trip->motisSourceLicense)
                : null,
        ];
    }

    /**
     * Returns only the stopovers between the checkin's origin and destination.
     * Falls back to just origin+destination if the stopover IDs can't be located
     * in the trip's stopover list (e.g. trip data is incomplete).
     */
    private function checkinStopovers(): iterable
    {
        /** @var Checkin $this */
        $stopovers = $this->trip->stopovers;
        $originId = $this->origin_stopover_id;
        $destinationId = $this->destination_stopover_id;

        $originIdx = $stopovers->search(fn (Stopover $s) => $s->id === $originId);
        $destinationIdx = $stopovers->search(fn (Stopover $s) => $s->id === $destinationId);

        if ($originIdx !== false && $destinationIdx !== false && $originIdx <= $destinationIdx) {
            return $stopovers->slice($originIdx, $destinationIdx - $originIdx + 1)->values();
        }

        return array_filter([$this->originStopover, $this->destinationStopover]);
    }
}
