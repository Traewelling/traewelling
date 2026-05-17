<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DataProviders\DataProviderInterface;
use App\Dto\Internal\Departure;
use App\Enum\Queue;
use App\Enum\StatusTagKey;
use App\Enum\StatusVisibility;
use App\Models\Status;
use App\Models\StatusTag;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CorrectLineTagsFromStationboard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    public function __construct(private readonly Status $status)
    {
        $this->onQueue(Queue::LOW->value);
    }

    public function handle(DataProviderInterface $dataProvider): void
    {
        $status = $this->status->load([
            'checkin.originStopover.station',
            'checkin.trip',
            'tags',
        ]);

        $checkin = $status->checkin;
        $trip = $checkin?->trip;
        $originStopover = $checkin?->originStopover;

        if (!$trip || !$originStopover?->station || !$originStopover->departure_planned) {
            return;
        }

        $existingTagKeys = $status->tags->pluck('key')->all();

        $needsLineName = !in_array(StatusTagKey::LINE_NAME->value, $existingTagKeys, true);
        $needsLineColor = !in_array(StatusTagKey::LINE_COLOR->value, $existingTagKeys, true);

        if (!$needsLineName && !$needsLineColor) {
            return;
        }

        try {
            $departures = collect($dataProvider->getDepartures($originStopover->station, $originStopover->departure_planned));
        } catch (Throwable $e) {
            Log::warning('CorrectLineTagsFromStationboard: could not fetch departures', [
                'status_id' => $status->id,
                'station_id' => $originStopover->station->id,
                'error' => $e->getMessage(),
            ]);

            return;
        }

        /** @var Departure|null $departure */
        $departure = $departures->first(fn (Departure $d) => $d->trip->tripId === $checkin->trip_id);

        if ($departure === null) {
            return;
        }

        if ($needsLineName && $departure->trip->lineName !== $trip->linename) {
            StatusTag::create([
                'status_id' => $status->id,
                'key' => StatusTagKey::LINE_NAME->value,
                'value' => $departure->trip->lineName,
                'visibility' => StatusVisibility::PUBLIC,
            ]);
        }

        if ($needsLineColor
            && $departure->trip->routeColor !== null
            && $departure->trip->routeColor !== $trip->route_color
        ) {
            StatusTag::create([
                'status_id' => $status->id,
                'key' => StatusTagKey::LINE_COLOR->value,
                'value' => $departure->trip->routeColor,
                'visibility' => StatusVisibility::PUBLIC,
            ]);
        }
    }
}
