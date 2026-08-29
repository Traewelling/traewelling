<?php

declare(strict_types=1);

namespace App\Dto\RouteMap;

use App\Enum\Business;
use App\Enum\HafasTravelType;
use Illuminate\Support\Carbon;

/**
 * Filter criteria for a user's route map.
 */
readonly class RouteMapFilterDto
{
    /**
     * @param  HafasTravelType[]  $travelTypes  Empty means "no restriction on the mode of transport".
     * @param  Business[]  $travelPurposes  Empty means "no restriction on the purpose of travel".
     */
    public function __construct(
        public ?Carbon $from = null,
        public ?Carbon $until = null,
        public array $travelTypes = [],
        public array $travelPurposes = [],
        public bool $includeApproximated = true,
    ) {}
}
