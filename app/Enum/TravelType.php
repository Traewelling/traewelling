<?php
declare(strict_types=1);

namespace App\Enum;

enum TravelType: string
{
    case EXPRESS  = 'express';
    case REGIONAL = 'regional';
    case SUBURBAN = 'suburban';
    case BUS      = 'bus';
    case FERRY    = 'ferry';
    case SUBWAY   = 'subway';
    case TRAM     = 'tram';
    case TAXI     = 'taxi';
}
