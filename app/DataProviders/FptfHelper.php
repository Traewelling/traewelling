<?php

namespace App\DataProviders;

use App\Enum\TravelType;

class FptfHelper
{
    public static function checkTravelType(?TravelType $type, TravelType $travelType): string {
        return (is_null($type) || $type === $travelType) ? 'true' : 'false';
    }
}
