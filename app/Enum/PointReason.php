<?php
declare(strict_types=1);

namespace App\Enum;

/**
 * @OA\Schema(
 *     title="PointsReason",
 *     description="What is the reason for the points calculation factor? (0=in time => 100%, 1=good enough => 25%, 2=not sufficient (1 point), 3=forced => no points, 4=manual trip => no points, 5=points disabled)",
 *     type="integer",
 *     enum={0,1,2,3,4,5},
 *     example=1
 * )
 */
enum PointReason: int
{
    case IN_TIME        = 0;
    case GOOD_ENOUGH    = 1;
    case NOT_SUFFICIENT = 2;
    case FORCED         = 3;

    /**
     * Trip was manually created by the user => no points.
     */
    case MANUAL_TRIP = 4;

    case POINTS_DISABLED = 5;
}
