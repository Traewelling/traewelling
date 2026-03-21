<?php

declare(strict_types=1);

namespace App\Dto\BRouter;

use App\Dto\Coordinate;

readonly class RouteDto
{
    /**
     * @param  Coordinate[]  $coordinates
     */
    public function __construct(
        public array $coordinates,
        public int $distanceInMeters,
    ) {}
}
