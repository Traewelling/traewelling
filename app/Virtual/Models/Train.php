<?php

namespace App\Virtual\Models;

/**
 * @OA\Schema(
 *     title="Train",
 *     description="Train model",
 *     @OA\Xml(
 *         name="Train"
 *     )
 * )
 */
class Train
{
    /**
     * @OA\Property (
     *     title="trip",
     *     description="ID",
     *     example=1
     * )
     *
     * @var integer
     */
    private $trip;

    /**
     * @OA\Property (
     *     title="hafasId",
     *     description="Deutsche bahn internal HAFAS ID",
     *     example="1|323306|1|80|17072022"
     * )
     *
     * @var string
     */
    private $hafasId;

    /**
     * @OA\Property (
     *      ref="#/components/schemas/TrainCategoryEnum"
     * )
     *
     * @var string
     */
    private $category;

    /**
     * @OA\Property (
     *     title="number",
     *     description="number of train",
     *     example="4-a6s4-4"
     * )
     *
     * @var string
     */
    private $number;

    /**
     * @OA\Property (
     *     title="journeyNumber",
     *     description="The number of the journey. Given by the transport company.",
     *     example="3697",
     *     nullable=true
     * )
     *
     * @var int
     */
    private $journeyNumber;


    /**
     * @OA\Property (
     *     title="lineName",
     *     description="name of the transport line",
     *     example="S 4"
     * )
     *
     * @var string
     */
    private $lineName;

    /**
     * @OA\Property (
     *     title="distance",
     *     description="travelled distance of this checkin in meters",
     *     example=3349
     * )
     *
     * @var integer
     */
    private $distance;

    /**
     * @OA\Property (
     *     title="points",
     *     description="points achieved for this checkin",
     *     example=7
     * )
     *
     * @var integer
     */
    private $points;

    /**
     * @OA\Property (
     *     title="duration",
     *     description="time traveled in this checkin in minutes",
     *     example=7
     * )
     *
     * @var integer
     */
    private $duration;

    /**
     * @OA\Property (
     *     title="origin",
     *     description="model of origin stopover"
     * )
     *
     * @var Stopover
     */
    private $origin;

    /**
     * @OA\Property (
     *     title="destination",
     *     description="model of destination stopover"
     * )
     *
     * @var Stopover
     */
    private $destination;

    /**
     * @OA\Property (
     *     title="operator",
     *     description="Operator of the mean of transport",
     *     nullable=true
     * )
     *
     * @var Operator
     */
    private $operator;
}
