<?php
declare(strict_types=1);

namespace App\Enum;

/**
 * @todo Mit HafasTravelType abgleichen - warum wird dieses Enum hier für HAFAS Requests genutzt und nicht das HafasTravelType?
 *
 * @OA\Schema(
 *      title="travelType",
 *      type="string",
 *      enum={"express", "regional", "suburban", "bus", "ferry", "subway", "tram", "taxi", "plane"},
 *      example="suburban"
 *  )
 */
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
    case PLANE    = 'plane';
}
