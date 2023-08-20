<?php

namespace App\Helpers;

use App\Dto\FriendlyPublicTransportFormat\Departure;
use App\Dto\FriendlyPublicTransportFormat\Line;
use App\Dto\FriendlyPublicTransportFormat\Operator;
use App\Dto\FriendlyPublicTransportFormat\Stop;
use Illuminate\Support\Collection;

class FPTFBuilder
{

    public static function forDeparture($departures): Collection {
        $self = new self();
        foreach ($departures as $key => $item) {
            $departures[$key] = new Departure($self->traverseItemsForDeparture($item));
        }
        return $departures;
    }


    private function traverseItemsForDeparture($items){
        foreach ($items as $key => $item) {
            if (in_array($key, ['stop', 'line', 'destination'])) {
                $items->{$key} = $this->{$key}($item);
            }
        }
        return $items;
    }

    private function stop($data): Stop {
        return new Stop(
            $data->id,
            $data->name,
            $data->location->latitude,
            $data->location->longitude
        );
    }

    private function destination($data): Stop {
        return $this->stop($data);
    }

    private function line($data): Line {
        foreach ($data as $key => $item) {
            if ($key === 'operator') {
                $data->{$key} = new Operator($item);
            }
        }
        return new Line($data);
    }


}
