<?php
declare(strict_types=1);

namespace App\Enum;

final class TravelType extends BasicEnum
{
    public const EXPRESS  = 'express';
    public const REGIONAL = 'regional';
    public const SUBURBAN = 'suburban';
    public const BUS      = 'bus';
    public const FERRY    = 'ferry';
    public const SUBWAY   = 'subway';
    public const TRAM     = 'tram';
}
