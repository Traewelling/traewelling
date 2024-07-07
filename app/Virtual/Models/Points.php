<?php

namespace App\Virtual\Models;

use DateTime;

/**
 * @OA\Schema(
 *     title="Points",
 *     description="Points model",
 *     @OA\Xml(
 *         name="Points"
 *     )
 * )
 */
class Points
{
    /**
     * @OA\Property(
     *     title="points",
     *     description="points",
     *     format="int",
     *     example=1
     * )
     *
     * @var integer
     **/
    private $points;

    /**
     * @OA\Property(
     *     title="calculation",
     *     description="",
     *     ref="#/components/schemas/PointsCalculation"
     * )
     *
     * @var string;
     */
    public $calculation;

    /**
     * @OA\Property(
     *     title="additional",
     *     description="extra points that can be given",
     *     @OA\Items(
     *
     *     example={"identifier": "extrapoints", "points": 4, "divisibile": false}
     *     )
     * )
     *
     * @var array
     **/
    private $additional;



}
