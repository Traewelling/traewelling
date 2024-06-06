<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="PointsCalculation",
 *     @OA\Xml(
 *         name="PointsCalculation"
 *     )
 * )
 */
class PointsCalculation
{
    /**
     * @OA\Property(
     *     title="base",
     *     description="Basepoints for this type of vehicle",
     *     example=0.5
     * )
     *
     * @var float
     **/
    private $base;

    /**
     * @OA\Property(
     *     title="distance",
     *     description="Points for the travelled distance",
     *     example=0.25
     * )
     *
     * @var float;
     */
    public $distance;

    /**
     * @OA\Property(
     *     title="factor",
     *     example=0.25
     * )
     *
     * @var float;
     */
    public $factor;

    /**
     * @OA\Property(
     *     title="reason",
     *     example=1,
     *     ref="#/components/schemas/PointReason"
     * )
     *
     */
    public $reason;


}
