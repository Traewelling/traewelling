<?php

namespace App\Virtual\Types;

/**
 * @OA\Schema(
 *     title="PointsReason",
 *     description="What is the reason for the points calculation factor? (0=in time => 100%, 1=good enough => 25%, 2=not sufficient (1 point), 3=forced => no points)",
 *     type="integer",
 *     enum={0,1,2,3},
 *     example=1
 * )
 */
class PointsReasonEnum
{

}
