<?php

namespace App\Dto\FriendlyPublicTransportFormat;

use Carbon\Carbon;
use DateTimeZone;

class DTM extends Carbon implements \JsonSerializable
{
    public function __construct($datetime = 'now', $timezone = null, ?string $day = null)
    {
        parent::__construct($datetime, $timezone ?? new DateTimeZone("Europe/Berlin"));
        if ($day && strlen($day) === 8) {
            $this->setDate(
                substr($day, 0, 4),
                substr($day, 4, 2),
                substr($day, 6, 2)
            );
        }
    }

    public function jsonSerialize(): mixed
    {
        return $this->format("c");
    }
}
